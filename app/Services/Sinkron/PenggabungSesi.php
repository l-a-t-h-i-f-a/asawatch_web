<?php

namespace App\Services\Sinkron;

use App\Models\HasilDeteksi;
use App\Models\ItemMakanan;
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
 * 5. hasil deteksi yang sudah dikoreksi pengguna tidak ditimpa kiriman yang
 *    tidak dikoreksi (bagian 6, sama seperti AnalisisNutrisiJob).
 * 6. hasil deteksi kiriman klien hanya diterima kalau dikoreksi_user true —
 *    selain itu hasil analisis server yang berlaku.
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
                    // Kedua stempel ikut dicatat: penolakan 409 tidak terlihat
                    // di layar app (2xx dianggap sukses), jadi tanpa ini beda
                    // jam klien-server hanya tampak sebagai "data tidak masuk".
                    Log::info('Sesi ditolak, stempel klien lebih tua', [
                        'sesi_id' => $data['id'],
                        'diperbarui_pada_klien' => $updatedAtKlien->toIso8601String(),
                        'updated_at_server' => $existing->updated_at->toIso8601String(),
                        'selisih_detik' => $existing->updated_at->diffInSeconds($updatedAtKlien),
                    ]);

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

            if (is_array($data['hasil'] ?? null)) {
                $this->terapkanHasil($sesi->id, $data['hasil']);
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
     * Simpan hasil deteksi yang ikut dikirim klien pada PUT /sesi/{id}
     * (bagian 5.2 "hasil"). Tanpa ini sesi yang diunduh di perangkat lain
     * kehilangan kartu gizinya.
     *
     * Aturan 5: koreksi pengguna menang — hasil yang sudah dikoreksi di
     * server tidak boleh ditimpa kiriman yang belum dikoreksi.
     */
    private function terapkanHasil(string $sesiId, array $hasil): void
    {
        $ada = HasilDeteksi::where('sesi_id', $sesiId)->first();
        $dikoreksi = (bool) ($hasil['dikoreksi_user'] ?? false);

        // Hasil deteksi itu produk server (bagian 5.2) — satu-satunya alasan
        // sah klien mengirimkannya kembali adalah koreksi pengguna. Kiriman
        // tanpa dikoreksi_user hanyalah gema dari hasil server sendiri, dan
        // menerimanya berbahaya: nilai yang tidak diketahui pulang sebagai nol
        // kalau klien mengubah null jadi nilai bawaan saat parsing.
        if (! $dikoreksi) {
            Log::info('Hasil deteksi kiriman klien diabaikan (bukan koreksi pengguna)', [
                'sesi_id' => $sesiId,
                'ada_hasil_server' => (bool) $ada,
            ]);

            return;
        }

        if ($ada && $ada->dikoreksi_user && ! $dikoreksi) {
            Log::info('Percobaan menimpa hasil deteksi terkoreksi diabaikan', ['sesi_id' => $sesiId]);

            return;
        }

        $total = $hasil['total'] ?? [];

        HasilDeteksi::updateOrCreate(
            ['sesi_id' => $sesiId],
            [
                'indeks_glikemik_perkiraan' => $hasil['indeks_glikemik_perkiraan'] ?? null,
                'keyakinan' => $hasil['keyakinan'] ?? null,
                'dikoreksi_user' => $dikoreksi,
                'total_kalori' => $total['kalori'] ?? null,
                'total_karbohidrat' => $total['karbohidrat'] ?? null,
                'total_protein' => $total['protein'] ?? null,
                'total_lemak' => $total['lemak'] ?? null,
                'total_gula_total' => $total['gula_total'] ?? null,
                'total_serat' => $total['serat'] ?? null,
                'zat_tidak_lengkap' => is_array($hasil['zat_tidak_lengkap'] ?? null)
                    ? array_values($hasil['zat_tidak_lengkap'])
                    : ($ada?->zat_tidak_lengkap ?? []),
            ]
        );

        // Hanya ganti daftar makanan kalau klien memang mengirimkannya —
        // hasil tanpa kunci "makanan" bukan berarti makanannya kosong.
        if (! is_array($hasil['makanan'] ?? null)) {
            return;
        }

        // Asal-usul angka gizi (entri TKPI + cara cocok) tidak ada di bagian
        // 5.2, jadi klien tidak pernah mengirimkannya kembali. Diselamatkan
        // dulu supaya sinkronisasi biasa tidak menghapusnya; kalau nama
        // makanannya berubah, catatan lamanya memang tidak berlaku lagi.
        $asalLama = ItemMakanan::where('sesi_id', $sesiId)
            ->get()
            ->keyBy('urutan');

        ItemMakanan::where('sesi_id', $sesiId)->delete();

        // Urutan mengikuti kiriman klien (bagian 8) — pakai "urutan" kalau
        // dikirim, kalau tidak jatuh ke posisi dalam array.
        foreach (array_values($hasil['makanan']) as $posisi => $item) {
            $nutrisi = $item['nutrisi'] ?? [];
            $urutan = $item['urutan'] ?? $posisi;
            $lama = $asalLama->get($urutan);
            $samaNama = $lama && $lama->nama === $item['nama'];

            ItemMakanan::create([
                'sesi_id' => $sesiId,
                'urutan' => $urutan,
                'nama' => $item['nama'],
                'sumber_gizi' => $samaNama ? $lama->sumber_gizi : null,
                'cocok' => $samaNama ? $lama->cocok : null,
                'porsi' => $item['porsi'] ?? null,
                'estimasi_gram' => $item['estimasi_gram'] ?? null,
                'kalori' => $nutrisi['kalori'] ?? null,
                'karbohidrat' => $nutrisi['karbohidrat'] ?? null,
                'protein' => $nutrisi['protein'] ?? null,
                'lemak' => $nutrisi['lemak'] ?? null,
                'gula_total' => $nutrisi['gula_total'] ?? null,
                'serat' => $nutrisi['serat'] ?? null,
            ]);
        }
    }

    /**
     * @return array{status: 'ditolak', kode: string, pesan: string}
     */
    private function tolak(string $pesan): array
    {
        return ['status' => 'ditolak', 'kode' => KodeGalat::KONFLIK_VERSI, 'pesan' => $pesan];
    }
}
