<?php

namespace App\Services\Ekspor;

/**
 * Daftar lembar dan kolom berkas ekspor — satu sumber kebenaran untuk baris
 * header, lembar "Kamus Data", dan urutan nilai yang ditulis PenyusunXlsx.
 *
 * Nama kolom sengaja mengikuti nama kolom database (snake_case, bahasa
 * Indonesia) alih-alih judul yang enak dibaca: berkas ini masuk ke SPSS/R,
 * dan nama variabel yang stabil lebih berharga daripada judul rapi. Satuan
 * dan penjelasan pindah ke Kamus Data.
 *
 * Aturan isi kolom:
 * - Nilai kosong ditulis sebagai sel kosong, bukan 0 atau "-", supaya "belum
 *   diukur" tidak tertukar dengan "nol".
 * - Boolean ditulis 1/0, mengikuti kolom sesi_uji pada ekspor CSV lama.
 * - Waktu punya sepasang kolom: *_wib sebagai sel tanggal Excel (yang dibaca
 *   manusia) dan *_utc sebagai teks ISO-8601 (yang dipakai mesin).
 */
final class SkemaEkspor
{
    public const LEMBAR_RINGKASAN = 'Ringkasan Sesi';

    public const LEMBAR_RESPONDEN = 'Responden';

    public const LEMBAR_SESI = 'Sesi';

    public const LEMBAR_PENGUKURAN = 'Pengukuran';

    public const LEMBAR_PENGUKURAN_LEBAR = 'Pengukuran Lebar';

    public const LEMBAR_ITEM_MAKANAN = 'Item Makanan';

    /** Jumlah slot sampel per sesi (offset kanonik -1800, 0, +3600, +7200). */
    public const SLOT_SAMPEL = 4;

    /** Metrik yang direkam tiap slot sampel, beserta satuannya. */
    private const METRIK_SAMPEL = [
        'gula_darah' => 'mg/dL',
        'detak_jantung' => 'bpm',
        'sistolik' => 'mmHg',
        'diastolik' => 'mmHg',
        'spo2' => '%',
    ];

    /**
     * Definisi kolom per lembar: nama => [tipe, satuan, keterangan].
     *
     * @return array<string, array<string, array{string, string, string}>>
     */
    public static function lembar(): array
    {
        return [
            self::LEMBAR_RINGKASAN => self::kolomRingkasan(),

            self::LEMBAR_RESPONDEN => [
                'user_id' => ['bilangan', '', 'Id akun responden — kunci penghubung antar-lembar'],
                'nama' => ['teks', '', ''],
                'email' => ['teks', '', ''],
                'terdaftar_pada_wib' => ['waktu', 'WIB', 'Akun dibuat'],
                'tanggal_lahir' => ['tanggal', '', 'Dari profil; kosong bila responden belum mengisi'],
                'jenis_kelamin' => ['teks', '', 'laki-laki / perempuan'],
                'golongan_darah' => ['teks', '', 'A / B / AB / O'],
                'tinggi_cm' => ['bilangan', 'cm', ''],
                'berat_kg' => ['desimal', 'kg', ''],
                'jumlah_sesi' => ['bilangan', '', 'Sesi milik responden ini yang tercakup saringan'],
            ],

            self::LEMBAR_SESI => [
                'sesi_id' => ['teks', '', 'UUID v4 yang dibuat aplikasi mobile, bukan server'],
                'user_id' => ['bilangan', '', ''],
                'nama_responden' => ['teks', '', ''],
                'waktu_foto_wib' => ['waktu', 'WIB', 'Saat foto makanan diambil'],
                'waktu_foto_utc' => ['teks', 'UTC', 'ISO-8601, kembar mesin dari kolom di sebelah kiri'],
                't0_wib' => ['waktu', 'WIB', 'Titik nol pengukuran; kosong bila belum ditetapkan'],
                't0_utc' => ['teks', 'UTC', 'ISO-8601'],
                'status' => ['teks', '', 'Status sesi apa adanya dari aplikasi'],
                'waktu_tidak_pasti' => ['1/0', '', 'Jam perangkat diragukan saat sesi direkam'],
                'sesi_uji' => ['1/0', '', 'Sesi pengujian: jadwal dimampatkan atau perangkat palsu'],
                'indeks_glikemik_perkiraan' => ['teks', '', 'Hasil analisis gizi atas foto'],
                'keyakinan' => ['desimal', '0–1', 'Keyakinan penyedia analisis'],
                'dikoreksi_user' => ['1/0', '', 'Hasil analisis sudah dikoreksi responden'],
                'total_kalori' => ['desimal', 'kkal', ''],
                'total_karbohidrat' => ['desimal', 'g', ''],
                'total_protein' => ['desimal', 'g', ''],
                'total_lemak' => ['desimal', 'g', ''],
                'total_gula_total' => ['desimal', 'g', ''],
                'total_serat' => ['desimal', 'g', ''],
                'zat_tidak_lengkap' => ['teks', '', 'Zat gizi yang gagal dideteksi, dipisah koma'],
                'jumlah_item_makanan' => ['bilangan', '', ''],
            ],

            self::LEMBAR_PENGUKURAN => [
                'user_id' => ['bilangan', '', ''],
                'nama' => ['teks', '', ''],
                'email' => ['teks', '', ''],
                'sesi_id' => ['teks', '', ''],
                'waktu_foto_wib' => ['waktu', 'WIB', ''],
                'status_sesi' => ['teks', '', ''],
                'sesi_uji' => ['1/0', '', ''],
                'index' => ['bilangan', '', 'Slot sampel 0–3, urut waktu'],
                'detik_relatif_t0' => ['bilangan', 'detik', 'Jarak dari t0; negatif berarti sebelum t0'],
                'menit_relatif_t0' => ['desimal', 'menit', 'detik_relatif_t0 ÷ 60'],
                'status_sampel' => ['teks', '', 'Hanya baris "terisi" yang punya angka pengukuran'],
                'dari_buffer' => ['1/0', '', 'Nilai diambil dari penyangga perangkat, bukan pembacaan langsung'],
                'gula_darah' => ['bilangan', 'mg/dL', ''],
                'detak_jantung' => ['bilangan', 'bpm', ''],
                'sistolik' => ['bilangan', 'mmHg', ''],
                'diastolik' => ['bilangan', 'mmHg', ''],
                'spo2' => ['bilangan', '%', ''],
            ],

            self::LEMBAR_PENGUKURAN_LEBAR => self::kolomLebar(),

            self::LEMBAR_ITEM_MAKANAN => [
                'sesi_id' => ['teks', '', ''],
                'user_id' => ['bilangan', '', ''],
                'urutan' => ['bilangan', '', 'Urutan item dalam satu piring'],
                'nama' => ['teks', '', 'Nama makanan hasil deteksi atau koreksi responden'],
                'sumber_gizi' => ['teks', '', 'Asal angka gizi'],
                'cocok' => ['teks', '', 'Tingkat kecocokan dengan basis data gizi'],
                'porsi' => ['teks', '', 'Porsi sebagaimana ditulis, mis. "1 centong"'],
                'estimasi_gram' => ['desimal', 'g', ''],
                'kalori' => ['desimal', 'kkal', ''],
                'karbohidrat' => ['desimal', 'g', ''],
                'protein' => ['desimal', 'g', ''],
                'lemak' => ['desimal', 'g', ''],
                'gula_total' => ['desimal', 'g', ''],
                'serat' => ['desimal', 'g', ''],
            ],
        ];
    }

    /**
     * Lembar ramah-baca: satu baris per sesi, judul kolom berbahasa manusia
     * lengkap dengan satuan.
     *
     * Ini satu-satunya lembar yang sengaja melanggar aturan penamaan
     * snake_case. Alasannya: peneliti yang membaca langsung di Excel tidak
     * seharusnya perlu membuka Kamus Data untuk tahu isi sebuah kolom.
     * Lembar mentah di belakangnya tetap memakai nama kolom database untuk
     * SPSS/R, dan kolom "ID sesi" di sini adalah jembatan ke sana.
     *
     * Label waktu (-30 mnt, saat makan, +1 jam, +2 jam) mengikuti jadwal
     * kanonik. Sesi uji boleh memampatkan jadwal, jadi kolom "Jenis sesi"
     * menandainya dan offset sebenarnya dibaca di lembar Pengukuran Lebar.
     *
     * @return array<string, array{string, string, string}>
     */
    private static function kolomRingkasan(): array
    {
        return [
            'Responden' => ['teks', '', 'Nama akun responden'],
            'Tanggal & jam makan' => ['waktu', 'WIB', 'Saat foto makanan diambil'],
            'Jenis sesi' => ['teks', '', 'Sesi biasa atau sesi uji (jadwal dimampatkan / perangkat palsu)'],
            'Status sesi' => ['teks', '', 'Status sesi apa adanya dari aplikasi'],
            'Gula darah -30 mnt (mg/dL)' => ['bilangan', 'mg/dL', 'Slot 0 — dari lembar Pengukuran Lebar, kolom gula_darah_s0'],
            'Gula darah saat makan (mg/dL)' => ['bilangan', 'mg/dL', 'Slot 1 (t0) — kolom gula_darah_s1'],
            'Gula darah +1 jam (mg/dL)' => ['bilangan', 'mg/dL', 'Slot 2 — kolom gula_darah_s2'],
            'Gula darah +2 jam (mg/dL)' => ['bilangan', 'mg/dL', 'Slot 3 — kolom gula_darah_s3'],
            'Titik terisi (dari 4)' => ['bilangan', '', 'Berapa dari 4 slot yang benar-benar terukur; 4 berarti sesi lengkap'],
            'Tekanan darah saat makan' => ['teks', 'mmHg', 'Sistolik/diastolik pada slot 1, mis. "125/82"'],
            'Detak jantung saat makan (bpm)' => ['bilangan', 'bpm', 'Slot 1 — kolom detak_jantung_s1'],
            'SpO2 saat makan (%)' => ['bilangan', '%', 'Slot 1 — kolom spo2_s1'],
            'Makanan terdeteksi' => ['teks', '', 'Nama item hasil analisis foto, dipisah koma'],
            'Kalori (kkal)' => ['desimal', 'kkal', 'Total seluruh item makanan pada sesi ini'],
            'Karbohidrat (g)' => ['desimal', 'g', 'Total seluruh item makanan pada sesi ini'],
            'Gula makanan (g)' => ['desimal', 'g', 'Total seluruh item makanan pada sesi ini'],
            'Indeks glikemik perkiraan' => ['teks', '', 'Perkiraan dari analisis foto, bukan pengukuran'],
            'ID sesi' => ['teks', '', 'Jembatan ke lembar mentah — cocokkan dengan kolom sesi_id di sana'],
        ];
    }

    /**
     * Lembar lebar: satu baris per sesi, keempat slot sampel dibentangkan
     * menyamping. Bentuk inilah yang dipakai uji berpasangan / repeated
     * measures, dan tanpa lembar ini setiap orang akan mem-pivot sendiri di
     * Excel — sumber galat yang tidak perlu.
     *
     * Slot dinomori s0–s3 mengikuti kolom `index`, bukan menit, karena sesi
     * uji boleh memampatkan jadwal sehingga offsetnya tidak kanonik. Offset
     * yang sebenarnya tetap terbaca di kolom detik_s0–s3.
     *
     * @return array<string, array{string, string, string}>
     */
    private static function kolomLebar(): array
    {
        $kolom = [
            'sesi_id' => ['teks', '', ''],
            'user_id' => ['bilangan', '', ''],
            'nama' => ['teks', '', ''],
            'waktu_foto_wib' => ['waktu', 'WIB', ''],
            'status_sesi' => ['teks', '', ''],
            'sesi_uji' => ['1/0', '', ''],
            'jumlah_sampel_terisi' => ['bilangan', '', 'Dari 4 slot; 4 berarti sesi lengkap'],
        ];

        for ($i = 0; $i < self::SLOT_SAMPEL; $i++) {
            $kolom["detik_s{$i}"] = ['bilangan', 'detik', "Offset sebenarnya slot ke-{$i} dari t0"];
        }

        foreach (self::METRIK_SAMPEL as $metrik => $satuan) {
            for ($i = 0; $i < self::SLOT_SAMPEL; $i++) {
                $kolom["{$metrik}_s{$i}"] = ['bilangan', $satuan, "{$metrik} pada slot ke-{$i}"];
            }
        }

        return $kolom;
    }

    /**
     * Satu kalimat "lembar ini untuk apa", dipakai lembar "Baca Ini Dulu" di
     * berkas sekaligus panel isi berkas di halaman ekspor — supaya keduanya
     * tidak pernah menjelaskan hal yang berbeda.
     *
     * @return array<string, string>
     */
    public static function penjelasan(): array
    {
        return [
            self::LEMBAR_RINGKASAN => 'Mulai dari sini. Satu baris per sesi, judul kolom sudah jelas.',
            self::LEMBAR_RESPONDEN => 'Profil tiap responden.',
            self::LEMBAR_SESI => 'Versi mentah dari Ringkasan Sesi.',
            self::LEMBAR_PENGUKURAN => 'Satu baris per titik ukur, 4 per sesi. Bentuk panjang.',
            self::LEMBAR_PENGUKURAN_LEBAR => 'Satu baris per sesi, 4 titik dibentangkan menyamping.',
            self::LEMBAR_ITEM_MAKANAN => 'Makanan yang terdeteksi di foto beserta gizinya.',
        ];
    }

    /** @return list<string> */
    public static function metrikSampel(): array
    {
        return array_keys(self::METRIK_SAMPEL);
    }

    /** @return list<string> */
    public static function header(string $lembar): array
    {
        return array_keys(self::lembar()[$lembar]);
    }

    /**
     * Isi lembar "Kamus Data": satu baris per kolom di seluruh berkas.
     *
     * @return list<array{string, string, string, string, string}>
     */
    public static function kamus(): array
    {
        $baris = [];

        foreach (self::lembar() as $lembar => $kolom) {
            foreach ($kolom as $nama => [$tipe, $satuan, $keterangan]) {
                $baris[] = [$lembar, $nama, $tipe, $satuan, $keterangan];
            }
        }

        return $baris;
    }
}
