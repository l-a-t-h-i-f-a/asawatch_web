<?php

namespace App\Jobs;

use App\Models\HasilDeteksi;
use App\Models\ItemMakanan;
use App\Models\PekerjaanAnalisis;
use App\Models\Sesi;
use App\Services\Nutrisi\LayananNutrisiGagalException;
use App\Services\Nutrisi\LayananVisionNutrisi;
use App\Support\KodeAnalisis;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AnalisisNutrisiJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    // Selaras dengan nutrisi.http.timeout ditambah ruang untuk membaca foto
    // dari disk — panggilan Gemini pada eval berkisar 2,7-5,1 detik.
    public int $timeout = 90;

    public function __construct(public string $pekerjaanId) {}

    public function backoff(): array
    {
        return [5, 15, 30];
    }

    public function handle(LayananVisionNutrisi $layanan): void
    {
        $pekerjaan = PekerjaanAnalisis::find($this->pekerjaanId);

        if (! $pekerjaan) {
            return;
        }

        $sesi = Sesi::withoutGlobalScopes()->with('hasilDeteksi')->find($pekerjaan->sesi_id);

        if (! $sesi || ! $sesi->foto_disk_path) {
            $pekerjaan->update(['status' => 'gagal', 'kode_galat' => KodeAnalisis::LAYANAN_NUTRISI_GAGAL]);

            return;
        }

        // Koreksi pengguna menang — job yang baru selesai belakangan tidak
        // boleh menimpa hasil yang sudah dikoreksi pengguna (bagian 6).
        if ($sesi->hasilDeteksi?->dikoreksi_user) {
            $pekerjaan->update(['status' => 'selesai']);

            return;
        }

        try {
            $hasil = $this->ambilHasilDenganCache($layanan, $sesi);

            DB::transaction(function () use ($sesi, $hasil) {
                HasilDeteksi::updateOrCreate(
                    ['sesi_id' => $sesi->id],
                    [
                        'indeks_glikemik_perkiraan' => $hasil['indeks_glikemik_perkiraan'],
                        'keyakinan' => $hasil['keyakinan'],
                        'total_kalori' => $hasil['total']['kalori'],
                        'total_karbohidrat' => $hasil['total']['karbohidrat'],
                        'total_protein' => $hasil['total']['protein'],
                        'total_lemak' => $hasil['total']['lemak'],
                        'total_gula_total' => $hasil['total']['gula_total'],
                        'total_serat' => $hasil['total']['serat'],
                        'zat_tidak_lengkap' => $hasil['zat_tidak_lengkap'] ?? [],
                    ]
                );

                ItemMakanan::where('sesi_id', $sesi->id)->delete();

                foreach (array_values($hasil['makanan']) as $urutan => $item) {
                    ItemMakanan::create([
                        'sesi_id' => $sesi->id,
                        'urutan' => $urutan,
                        'nama' => $item['nama'],
                        'sumber_gizi' => $item['sumber_gizi'] ?? null,
                        'cocok' => $item['cocok'] ?? null,
                        'porsi' => $item['porsi'] ?? null,
                        'estimasi_gram' => $item['estimasi_gram'] ?? null,
                        'kalori' => $item['nutrisi']['kalori'] ?? null,
                        'karbohidrat' => $item['nutrisi']['karbohidrat'] ?? null,
                        'protein' => $item['nutrisi']['protein'] ?? null,
                        'lemak' => $item['nutrisi']['lemak'] ?? null,
                        'gula_total' => $item['nutrisi']['gula_total'] ?? null,
                        'serat' => $item['nutrisi']['serat'] ?? null,
                    ]);
                }
            });

            $pekerjaan->update(['status' => 'selesai']);
        } catch (LayananNutrisiGagalException $e) {
            // Gagal itu status, bukan exception yang membuat sesi rusak —
            // sesi tanpa hasil nutrisi tetap sah (bagian 6).
            $pekerjaan->update(['status' => 'gagal', 'kode_galat' => $e->kode]);
        }
    }

    public function failed(?Throwable $exception): void
    {
        PekerjaanAnalisis::where('id', $this->pekerjaanId)->update([
            'status' => 'gagal',
            'kode_galat' => KodeAnalisis::WAKTU_HABIS,
        ]);
    }

    /**
     * Cache berdasarkan hash isi foto — foto yang sama cukup dianalisis
     * sekali (bagian 6).
     */
    private function ambilHasilDenganCache(LayananVisionNutrisi $layanan, Sesi $sesi): array
    {
        $cache = $sesi->foto_hash
            ? Sesi::withoutGlobalScopes()
                ->where('foto_hash', $sesi->foto_hash)
                ->where('id', '!=', $sesi->id)
                ->whereHas('hasilDeteksi')
                ->with('hasilDeteksi.itemMakanan')
                ->first()
            : null;

        if ($cache) {
            return $this->keArray($cache->hasilDeteksi);
        }

        return $layanan->analisis(Storage::disk('local')->path($sesi->foto_disk_path));
    }

    /**
     * Penanda zat tidak lengkap untuk hasil yang dipakai ulang lewat cache
     * foto. Gabungan dari apa yang tersimpan dan apa yang bisa diturunkan
     * dari itemnya: satu saja item yang tidak punya angka untuk suatu zat
     * berarti total zat itu memang tidak lengkap.
     *
     * Penurunan ini bukan sekadar pengaman — baris yang dibuat sebelum kolom
     * ini ada bernilai null, dan tanpa diturunkan ia akan mengaku lengkap.
     */
    private function zatTidakLengkap(HasilDeteksi $hasil): array
    {
        $peta = [
            'kalori' => 'kalori',
            'karbohidrat' => 'karbohidrat',
            'protein' => 'protein',
            'lemak' => 'lemak',
            'gula_total' => 'gula_total',
            'serat' => 'serat',
        ];

        $tidakLengkap = array_flip($hasil->zat_tidak_lengkap ?? []);

        foreach ($hasil->itemMakanan as $item) {
            foreach ($peta as $zat) {
                if ($item->{$zat} === null) {
                    $tidakLengkap[$zat] = true;
                }
            }
        }

        return array_values(array_intersect_key($peta, $tidakLengkap));
    }

    private function keArray(HasilDeteksi $hasil): array
    {
        return [
            'indeks_glikemik_perkiraan' => $hasil->indeks_glikemik_perkiraan,
            'keyakinan' => $hasil->keyakinan,
            'total' => [
                'kalori' => $hasil->total_kalori,
                'karbohidrat' => $hasil->total_karbohidrat,
                'protein' => $hasil->total_protein,
                'lemak' => $hasil->total_lemak,
                'gula_total' => $hasil->total_gula_total,
                'serat' => $hasil->total_serat,
            ],
            'zat_tidak_lengkap' => $this->zatTidakLengkap($hasil),
            'makanan' => $hasil->itemMakanan->map(fn ($i) => [
                'nama' => $i->nama,
                'sumber_gizi' => $i->sumber_gizi,
                'cocok' => $i->cocok,
                'porsi' => $i->porsi,
                'estimasi_gram' => $i->estimasi_gram,
                'nutrisi' => [
                    'kalori' => $i->kalori,
                    'karbohidrat' => $i->karbohidrat,
                    'protein' => $i->protein,
                    'lemak' => $i->lemak,
                    'gula_total' => $i->gula_total,
                    'serat' => $i->serat,
                ],
            ])->all(),
        ];
    }
}
