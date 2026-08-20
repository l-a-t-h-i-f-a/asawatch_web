<?php

namespace App\Services\Sinkron;

use App\Models\Sampel;
use App\Models\Sesi;
use App\Support\KodeGalat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Satu-satunya tempat aturan konflik sinkronisasi hidup (bagian 7.1 dokumen
 * API). Dipakai baik oleh PUT /sesi/{id} (satu record) maupun POST /sinkron
 * (batch, memanggil gabungkan() per item).
 *
 * Empat aturan:
 * 1. Sampel yang sudah 'terisi' tidak pernah ditimpa.
 * 2. Sisanya last-write-wins per sesi, dibandingkan dengan diperbarui_pada klien.
 * 3. Penghapusan menang atas pembaruan.
 * 4. waktu_tidak_pasti tidak pernah dicabut server (sekali true, selamanya true).
 */
class PenggabungSesi
{
    /**
     * Offset kanonis dipakai untuk mengisi slot sampel yang belum pernah
     * dikirim sama sekali, supaya GET selalu mengembalikan 4 elemen (bagian
     * 5.2). Nilainya akan ditimpa begitu app mengirim pengukuran sungguhan
     * untuk index tersebut — asalkan slot itu belum berstatus 'terisi'.
     */
    private const OFFSET_KANONIS = [0 => -1800, 1 => 0, 2 => 3600, 3 => 7200];

    /**
     * @return array{status: 'diterima'|'ditolak', kode?: string, pesan?: string, sesi?: Sesi}
     */
    public function gabungkan(int $userId, array $data): array
    {
        return DB::transaction(function () use ($userId, $data) {
            $existing = Sesi::withoutGlobalScopes()
                ->withTrashed()
                ->where('id', $data['id'])
                ->where('user_id', $userId)
                ->lockForUpdate()
                ->first();

            $updatedAtKlien = isset($data['diperbarui_pada'])
                ? Carbon::parse($data['diperbarui_pada'])
                : now();

            $dihapusPadaKlien = ! empty($data['dihapus_pada'])
                ? Carbon::parse($data['dihapus_pada'])
                : null;

            if ($existing) {
                // Aturan 3: penghapusan menang atas pembaruan.
                if ($existing->trashed() && ! $dihapusPadaKlien) {
                    return $this->tolak('Sesi ini sudah dihapus dan tidak bisa dihidupkan kembali.');
                }

                // Aturan 2: last-write-wins per sesi.
                if (! $existing->trashed() && $updatedAtKlien->lt($existing->updated_at)) {
                    return $this->tolak('Ada versi yang lebih baru di server, tarik ulang sebelum mengirim lagi.');
                }
            }

            $baru = ! $existing;

            $sesi = $existing ?? new Sesi(['id' => $data['id'], 'user_id' => $userId]);
            $sesi->user_id = $userId;
            $sesi->foto_disk_path = $data['foto_disk_path'] ?? $sesi->foto_disk_path;
            $sesi->foto_hash = $data['foto_hash'] ?? $sesi->foto_hash;
            $sesi->waktu_foto = $data['waktu_foto'];
            $sesi->t0 = $data['t0'] ?? null;
            $sesi->status = $data['status'];

            // Penanda sesi pengujian ikut apa adanya dari klien — tidak ada
            // aturan "sekali true selamanya true" di sini, sesi yang salah
            // ditandai harus bisa dikoreksi dari app.
            $sesi->sesi_uji = (bool) ($data['sesi_uji'] ?? $sesi->sesi_uji ?? false);

            // Aturan 4: sekali true, server tidak pernah mengembalikannya ke false.
            $sesi->waktu_tidak_pasti = (bool) ($sesi->waktu_tidak_pasti || ($data['waktu_tidak_pasti'] ?? false));

            $sesi->save();

            if ($baru) {
                $this->buatSlotSampelAwal($sesi->id);
            }

            foreach ($data['sampel'] ?? [] as $s) {
                $this->terapkanSampel($sesi->id, $s);
            }

            if ($dihapusPadaKlien && ! $sesi->trashed()) {
                $sesi->delete();
            }

            return [
                'status' => 'diterima',
                'sesi' => $sesi->fresh(['sampel', 'hasilDeteksi', 'itemMakanan']),
            ];
        });
    }

    private function buatSlotSampelAwal(string $sesiId): void
    {
        foreach (self::OFFSET_KANONIS as $index => $offset) {
            Sampel::create([
                'sesi_id' => $sesiId,
                'index' => $index,
                'detik_relatif_t0' => $offset,
                'status' => 'menunggu',
                'dari_buffer' => false,
            ]);
        }
    }

    private function terapkanSampel(string $sesiId, array $s): void
    {
        $ada = Sampel::where('sesi_id', $sesiId)->where('index', $s['index'])->first();

        // Aturan 1: sampel yang sudah terisi tidak pernah ditimpa — abaikan
        // saja, jangan balas error (klien yang mengirim ulang batch lama
        // tidak sedang melakukan kesalahan).
        if ($ada && $ada->status === 'terisi') {
            if (($s['status'] ?? null) !== 'terisi'
                || $ada->gula_darah !== ($s['gula_darah'] ?? $ada->gula_darah)
                || $ada->detak_jantung !== ($s['detak_jantung'] ?? $ada->detak_jantung)) {
                Log::info('Percobaan menimpa sampel terisi diabaikan', [
                    'sesi_id' => $sesiId,
                    'index' => $s['index'],
                ]);
            }

            return;
        }

        $atribut = [
            'detik_relatif_t0' => $s['detik_relatif_t0'] ?? self::OFFSET_KANONIS[$s['index']] ?? 0,
            'status' => $s['status'],
            'dari_buffer' => $s['dari_buffer'] ?? false,
            'gula_darah' => $s['gula_darah'] ?? null,
            'detak_jantung' => $s['detak_jantung'] ?? null,
            'sistolik' => $s['sistolik'] ?? null,
            'diastolik' => $s['diastolik'] ?? null,
            'spo2' => $s['spo2'] ?? null,
        ];

        // Kunci baris selalu pasangan (sesi_id, index) — lihat catatan di
        // model Sampel: memakai sesi_id saja menimpa keempat slot sekaligus.
        if ($ada) {
            Sampel::where('sesi_id', $sesiId)->where('index', $s['index'])->update($atribut);

            return;
        }

        Sampel::create($atribut + ['sesi_id' => $sesiId, 'index' => $s['index']]);
    }

    /**
     * @return array{status: 'ditolak', kode: string, pesan: string}
     */
    private function tolak(string $pesan): array
    {
        return ['status' => 'ditolak', 'kode' => KodeGalat::KONFLIK_VERSI, 'pesan' => $pesan];
    }
}
