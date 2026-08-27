<?php

namespace App\Services\Nutrisi;

use App\Support\KodeAnalisis;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Penyedia sungguhan: layanan vision Python (repo "Test API Food Detection").
 * Python yang memanggil Gemini dan menghitung gizi dari tabel TKPI; kelas ini
 * hanya mengantar foto dan menerjemahkan jawabannya ke bentuk bagian 5.2.
 *
 * Pemetaan sengaja hidup di sini, bukan di Python: dengan begitu skrip eval di
 * sana tetap memakai analisis() yang sama tanpa ikut berubah, dan kalau
 * kontrak 5.2 bergeser yang disunting cuma berkas ini.
 */
class LayananVisionNutrisiHttp implements LayananVisionNutrisi
{
    public function __construct(
        private readonly string $url,
        private readonly int $timeout,
    ) {}

    public function analisis(string $pathFotoAbsolut): array
    {
        $isi = @file_get_contents($pathFotoAbsolut);

        if ($isi === false) {
            throw new LayananNutrisiGagalException(
                KodeAnalisis::LAYANAN_NUTRISI_GAGAL,
                'Berkas foto tidak terbaca dari disk.',
            );
        }

        try {
            $respons = Http::timeout($this->timeout)
                ->acceptJson()
                ->post(rtrim($this->url, '/').'/analisis', [
                    'foto_base64' => base64_encode($isi),
                ]);
        } catch (ConnectionException $e) {
            // Timeout dan "layanan tidak menjawab" dibedakan: yang pertama
            // masih mungkin berhasil kalau diulang, yang kedua tidak.
            $waktuHabis = str_contains(strtolower($e->getMessage()), 'timed out')
                || str_contains(strtolower($e->getMessage()), 'timeout');

            throw new LayananNutrisiGagalException(
                $waktuHabis ? KodeAnalisis::WAKTU_HABIS : KodeAnalisis::LAYANAN_NUTRISI_GAGAL,
                'Layanan vision tidak menjawab: '.$e->getMessage(),
            );
        }

        if ($respons->failed()) {
            $kode = $respons->json('kode');

            throw new LayananNutrisiGagalException(
                $kode === KodeAnalisis::FOTO_TIDAK_DIKENALI
                    ? KodeAnalisis::FOTO_TIDAK_DIKENALI
                    : KodeAnalisis::LAYANAN_NUTRISI_GAGAL,
                $respons->json('pesan') ?? "Layanan vision menjawab {$respons->status()}.",
            );
        }

        return $this->keBentuk52($respons->json() ?? []);
    }

    /**
     * Bentuk Python -> bentuk bagian 5.2. Perbedaannya: nama kunci (gula ->
     * gula_total, gizi -> nutrisi, gram -> estimasi_gram), urutan yang mulai
     * dari 0, dan porsi yang di sana objek tapi di sini satu string.
     */
    private function keBentuk52(array $py): array
    {
        // Cadangan untuk server vision versi lama, yang mengirim 0.0 untuk zat
        // tanpa sumber data dan menandainya lewat zat_tanpa_sumber. Versi baru
        // sudah mengirim null per zat, jadi daftar ini tinggal kosong dan
        // cabang ini tidak berbuat apa-apa.
        $tanpaSumber = array_flip($py['zat_tanpa_sumber'] ?? []);

        $makanan = [];

        foreach (array_values($py['items'] ?? []) as $item) {
            $makanan[] = [
                'nama' => $item['nama'] ?? '(tidak dikenali)',
                // Asal-usul angka gizinya — entri TKPI yang dipakai dan cara
                // pencocokannya. Tidak ikut ke bagian 5.2; hanya untuk panel
                // web dan ekspor, supaya padanan longgar tetap terlacak.
                'sumber_gizi' => $item['sumber_gizi'] ?? null,
                'cocok' => $item['cocok'] ?? null,
                'porsi' => $item['porsi']['teks'] ?? null,
                'estimasi_gram' => isset($item['gram']) ? (float) $item['gram'] : null,
                // gizi null = makanannya tidak ada di TKPI. Nutrisinya dibiarkan
                // null seluruhnya, bukan nol, dengan alasan yang sama.
                'nutrisi' => $this->zat($item['gizi'] ?? null, $tanpaSumber),
            ];
        }

        if ($catatan = $py['catatan'] ?? []) {
            // Belum ada kolomnya di hasil_deteksi — dicatat dulu supaya
            // makanan yang belum tercocokkan ke TKPI tidak hilang diam-diam.
            Log::info('Analisis nutrisi punya catatan', [
                'catatan' => $catatan,
                'total_lengkap' => $py['total_lengkap'] ?? null,
                'model' => $py['meta']['model'] ?? null,
            ]);
        }

        return [
            // Keduanya belum punya sumber data: TKPI tidak memuat indeks
            // glikemik, dan Gemini tidak menghasilkan keyakinan terkalibrasi.
            // null berarti "belum dihitung" — jangan diisi nilai penampung.
            'indeks_glikemik_perkiraan' => null,
            'keyakinan' => null,
            'total' => $this->zat($py['total'] ?? null, $tanpaSumber),
            'zat_tidak_lengkap' => $this->zatTidakLengkap($py, $tanpaSumber),
            'makanan' => $makanan,
        ];
    }

    /**
     * Zat yang totalnya tidak lengkap, dengan nama versi bagian 5.2 (gula ->
     * gula_total). Angka totalnya tetap dikirim — ini penanda "sekurang-
     * kurangnya sekian", bukan pembatalan nilai.
     */
    private function zatTidakLengkap(array $py, array $tanpaSumber): array
    {
        $petaNama = [
            'kalori' => 'kalori',
            'karbohidrat' => 'karbohidrat',
            'protein' => 'protein',
            'lemak' => 'lemak',
            'gula' => 'gula_total',
            'serat' => 'serat',
        ];

        if (isset($py['total_tidak_lengkap'])) {
            $daftar = (array) $py['total_tidak_lengkap'];
        } else {
            // Server versi lama: satu-satunya sinyal yang ada adalah
            // total_lengkap (semua item ketemu di TKPI atau tidak) ditambah
            // zat yang memang tanpa sumber data.
            $daftar = ($py['total_lengkap'] ?? true) === false
                ? array_keys($petaNama)
                : array_keys($tanpaSumber);
        }

        // Urutan dijaga mengikuti urutan zat, bukan urutan kiriman, supaya
        // nilainya stabil untuk dibandingkan.
        return array_values(array_intersect_key(
            $petaNama,
            array_flip(array_map('strval', $daftar))
        ));
    }

    /**
     * Enam makro bagian 5.2 dari satu blok gizi Python. Kuncinya selalu ada
     * lengkap; yang tidak diketahui bernilai null.
     */
    private function zat(?array $gizi, array $tanpaSumber): array
    {
        $ambil = function (string $kunciPy) use ($gizi, $tanpaSumber): ?float {
            if ($gizi === null || isset($tanpaSumber[$kunciPy]) || ! isset($gizi[$kunciPy])) {
                return null;
            }

            return (float) $gizi[$kunciPy];
        };

        return [
            'kalori' => $ambil('kalori'),
            'karbohidrat' => $ambil('karbohidrat'),
            'protein' => $ambil('protein'),
            'lemak' => $ambil('lemak'),
            'gula_total' => $ambil('gula'),
            'serat' => $ambil('serat'),
        ];
    }
}
