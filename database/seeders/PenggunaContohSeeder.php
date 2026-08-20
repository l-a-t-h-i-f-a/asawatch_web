<?php

namespace Database\Seeders;

use App\Models\HasilDeteksi;
use App\Models\ItemMakanan;
use App\Models\Kalibrasi;
use App\Models\Perangkat;
use App\Models\Profil;
use App\Models\Sampel;
use App\Models\Sesi;
use App\Models\TargetHarian;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Ramsey\Uuid\Uuid;

/**
 * Data contoh untuk pengguna NON-admin, supaya menu "Daftar Pengguna" milik
 * administrator ada isinya dan bisa ditelusuri sampai detail sesi.
 *
 * Pelengkap SesiLengkapSeeder, yang mengisi akun admin@asawatch.com sendiri —
 * akun itu sekarang berperan administrator (User::isAdmin()), jadi tidak bisa
 * dipakai untuk memeriksa tampilan dari sudut pandang pengguna biasa.
 *
 * Empat responden dengan pola pengukuran berbeda, dan sesi yang sengaja
 * bervariasi supaya setiap cabang tampilan ikut teruji:
 * - sesi selesai dengan hasil analisis nutrisi lengkap;
 * - sesi selesai TANPA hasil deteksi  -> cabang "Belum ada hasil analisis";
 * - sesi tidak_lengkap dengan sampel terlewat -> pil status merah;
 * - sesi berjalan dengan sampel menunggu      -> pil status biru;
 * - satu sesi dengan waktu_tidak_pasti = true -> badge "Waktu tidak pasti".
 *
 * Idempoten: ID sesi diturunkan deterministik dengan UUID v5 dari email +
 * urutan sesi, dan semua penulisan memakai updateOrCreate.
 *
 * Jalankan: php artisan db:seed --class=PenggunaContohSeeder
 */
class PenggunaContohSeeder extends Seeder
{
    private const KATA_SANDI = 'password123';

    /**
     * Offset kanonis sampel, sama dengan PenggabungSesi::OFFSET_KANONIS.
     */
    private const OFFSET_KANONIS = [-1800, 0, 3600, 7200];

    /**
     * Pola pengukuran per sesi, urut dari index 0 sampai 3.
     * Format: [status, gula_darah, detak_jantung, sistolik, diastolik, spo2].
     */
    private const POLA_SAMPEL = [
        'normal' => [
            ['terisi', 98, 72, 124, 78, 98],
            ['terisi', 108, 76, 126, 80, 98],
            ['terisi', 142, 84, 130, 82, 97],
            ['terisi', 118, 78, 126, 80, 98],
        ],
        'sedang' => [
            ['terisi', 112, 76, 128, 80, 97],
            ['terisi', 124, 82, 130, 82, 97],
            ['terisi', 168, 94, 136, 85, 96],
            ['terisi', 138, 84, 132, 83, 97],
        ],
        'tinggi' => [
            ['terisi', 132, 80, 136, 84, 96],
            ['terisi', 148, 86, 138, 86, 96],
            ['terisi', 214, 104, 146, 90, 95],
            ['terisi', 176, 92, 140, 88, 96],
        ],
        'terputus' => [
            ['terisi', 126, 78, 130, 82, 97],
            ['terisi', 140, 88, 134, 84, 97],
            ['terlewat', null, null, null, null, null],
            ['menunggu', null, null, null, null, null],
        ],
        'berjalan' => [
            ['terisi', 104, 74, 122, 78, 98],
            ['terisi', 119, 80, 125, 79, 98],
            ['menunggu', null, null, null, null, null],
            ['menunggu', null, null, null, null, null],
        ],
    ];

    /**
     * Menu makanan. Total nutrisi hasil_deteksi dihitung dari item-itemnya
     * (lihat totalkan()) supaya angka rincian dan angka total tidak pernah
     * berselisih.
     * Format item: [nama, porsi, gram, kalori, karbo, protein, lemak, gula, serat].
     */
    private const MENU = [
        'nasi_ayam' => [
            'indeks_glikemik' => 'tinggi',
            'keyakinan' => 0.82,
            'item' => [
                ['Nasi putih', '1 centong penuh', 180, 234, 51.6, 4.3, 0.4, 0.1, 0.8],
                ['Ayam goreng (dada)', '1 potong', 120, 296, 8.6, 25.4, 18.2, 0.3, 0.4],
                ['Tumis kacang panjang', '1 mangkuk kecil', 90, 78, 9.2, 2.4, 3.6, 2.1, 3.4],
                ['Teh manis hangat', '1 gelas', 200, 132, 26.4, 0.0, 0.0, 26.0, 0.0],
            ],
        ],
        'bubur_ikan' => [
            'indeks_glikemik' => 'sedang',
            'keyakinan' => 0.76,
            'item' => [
                ['Bubur nasi', '1 mangkuk', 250, 180, 39.0, 3.2, 0.6, 0.2, 0.6],
                ['Ikan kembung goreng', '1 ekor sedang', 80, 168, 0.0, 18.4, 10.2, 0.0, 0.0],
                ['Sayur bening bayam', '1 mangkuk kecil', 100, 36, 5.4, 2.8, 0.4, 1.2, 2.2],
                ['Pepaya potong', '3 potong', 120, 48, 12.1, 0.6, 0.2, 9.6, 2.1],
            ],
        ],
        'lontong_sayur' => [
            'indeks_glikemik' => 'tinggi',
            'keyakinan' => 0.69,
            'item' => [
                ['Lontong', '2 potong', 200, 218, 48.2, 3.8, 0.4, 0.2, 1.0],
                ['Sayur labu siam santan', '1 mangkuk', 120, 142, 9.6, 2.2, 10.4, 3.1, 2.4],
                ['Telur rebus', '1 butir', 55, 78, 0.6, 6.3, 5.3, 0.3, 0.0],
                ['Kopi susu manis', '1 gelas', 180, 118, 20.4, 2.6, 2.8, 19.8, 0.0],
            ],
        ],
    ];

    /**
     * Sesi per responden.
     * Format: [hari_lalu, jam, menit, status, pola_sampel, menu|null, waktu_tidak_pasti].
     */
    private const RESPONDEN = [
        [
            'nama' => 'Sumarni',
            'email' => 'sumarni@contoh.test',
            'tanggal_lahir' => '1958-02-03',
            'jenis_kelamin' => 'perempuan',
            'golongan_darah' => 'B',
            'tinggi_cm' => 152,
            'berat_kg' => 58.40,
            'perangkat' => ['C4:19:D1:8A:22:80', 'AsaWatch A1', '1.4.2', 82],
            'kalibrasi' => [142, 88, 136, 85],
            'target' => [1700, 210, 3500],
            'sesi' => [
                [0, 7, 15, 'selesai', 'tinggi', 'lontong_sayur', false],
                [1, 12, 30, 'selesai', 'sedang', 'nasi_ayam', false],
                [3, 18, 5, 'tidak_lengkap', 'terputus', null, true],
            ],
        ],
        [
            'nama' => 'Hartono Wibowo',
            'email' => 'hartono@contoh.test',
            'tanggal_lahir' => '1955-06-21',
            'jenis_kelamin' => 'laki-laki',
            'golongan_darah' => 'O',
            'tinggi_cm' => 167,
            'berat_kg' => 71.20,
            'perangkat' => ['C4:19:D1:8A:22:81', 'AsaWatch A2', '1.4.2', 64],
            'kalibrasi' => [134, 84, 129, 81],
            'target' => [1900, 240, 5000],
            'sesi' => [
                [0, 12, 45, 'berjalan', 'berjalan', null, false],
                [2, 8, 0, 'selesai', 'normal', 'bubur_ikan', false],
            ],
        ],
        [
            'nama' => 'Siti Aminah',
            'email' => 'siti.aminah@contoh.test',
            'tanggal_lahir' => '1961-11-09',
            'jenis_kelamin' => 'perempuan',
            'golongan_darah' => 'A',
            'tinggi_cm' => 149,
            'berat_kg' => 54.00,
            'perangkat' => ['C4:19:D1:8A:22:82', 'AsaWatch A1', '1.3.9', 38],
            'kalibrasi' => [128, 80, 124, 78],
            'target' => [1650, 200, 3000],
            'sesi' => [
                [1, 6, 50, 'selesai', 'normal', 'bubur_ikan', false],
                // Sesi selesai tanpa hasil deteksi: foto belum sempat dianalisis.
                [4, 19, 20, 'selesai', 'sedang', null, false],
            ],
        ],
        [
            'nama' => 'Bambang Sutrisno',
            'email' => 'bambang@contoh.test',
            'tanggal_lahir' => '1952-03-17',
            'jenis_kelamin' => 'laki-laki',
            'golongan_darah' => 'AB',
            'tinggi_cm' => 160,
            'berat_kg' => 63.80,
            'perangkat' => ['C4:19:D1:8A:22:83', 'AsaWatch A2', '1.4.0', 91],
            'kalibrasi' => [150, 92, 143, 89],
            'target' => [1800, 220, 2500],
            'sesi' => [
                [2, 13, 10, 'selesai', 'tinggi', 'nasi_ayam', false],
            ],
        ],
    ];

    public function run(): void
    {
        foreach (self::RESPONDEN as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'nama' => $data['nama'],
                    'password' => Hash::make(self::KATA_SANDI),
                    'email_verified_at' => now()->subMonths(2),
                ],
            );

            Profil::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'tanggal_lahir' => $data['tanggal_lahir'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'golongan_darah' => $data['golongan_darah'],
                    'tinggi_cm' => $data['tinggi_cm'],
                    'berat_kg' => $data['berat_kg'],
                ],
            );

            [$kalori, $karbohidrat, $langkah] = $data['target'];

            TargetHarian::updateOrCreate(
                ['user_id' => $user->id],
                ['kalori' => $kalori, 'karbohidrat' => $karbohidrat, 'langkah' => $langkah],
            );

            [$idBle, $namaPerangkat, $firmware, $baterai] = $data['perangkat'];

            Perangkat::updateOrCreate(
                ['user_id' => $user->id, 'id_ble' => $idBle],
                [
                    'nama' => $namaPerangkat,
                    'firmware' => $firmware,
                    'baterai_terakhir' => $baterai,
                    'terakhir_tersambung' => now()->subHours(3),
                ],
            );

            [$sisRef, $diaRef, $sisJam, $diaJam] = $data['kalibrasi'];

            Kalibrasi::updateOrCreate(
                ['user_id' => $user->id, 'waktu' => now()->subDays(5)->setTime(7, 0)],
                [
                    'sistolik_referensi' => $sisRef,
                    'diastolik_referensi' => $diaRef,
                    'sistolik_jam' => $sisJam,
                    'diastolik_jam' => $diaJam,
                ],
            );

            foreach ($data['sesi'] as $urutan => $spesifikasi) {
                $this->buatSesi($user, $urutan, $spesifikasi);
            }
        }

        $this->command?->info(
            count(self::RESPONDEN).' pengguna contoh siap. Kata sandi semuanya: '.self::KATA_SANDI
        );
    }

    private function buatSesi(User $user, int $urutan, array $spesifikasi): void
    {
        [$hariLalu, $jam, $menit, $status, $polaSampel, $menu, $waktuTidakPasti] = $spesifikasi;

        $waktuFoto = now()->subDays($hariLalu)->setTime($jam, $menit);

        $sesi = Sesi::withoutGlobalScopes()->updateOrCreate(
            ['id' => $this->idSesi($user->email, $urutan)],
            [
                'user_id' => $user->id,
                'waktu_foto' => $waktuFoto,
                't0' => $waktuFoto->copy()->addMinutes(8),
                'status' => $status,
                'waktu_tidak_pasti' => $waktuTidakPasti,
                // Tidak ada berkas foto sungguhan; panel admin tidak
                // menampilkan foto, dan path palsu justru akan membuat
                // GET /api/v1/foto/{sesi} gagal 404.
                'foto_disk_path' => null,
                'foto_hash' => null,
            ],
        );

        foreach (self::POLA_SAMPEL[$polaSampel] as $index => $nilai) {
            [$statusSampel, $gula, $detak, $sistolik, $diastolik, $spo2] = $nilai;

            Sampel::updateOrCreate(
                ['sesi_id' => $sesi->id, 'index' => $index],
                [
                    'detik_relatif_t0' => self::OFFSET_KANONIS[$index],
                    'status' => $statusSampel,
                    'dari_buffer' => $index === 3 && $statusSampel === 'terisi',
                    'gula_darah' => $gula,
                    'detak_jantung' => $detak,
                    'sistolik' => $sistolik,
                    'diastolik' => $diastolik,
                    'spo2' => $spo2,
                ],
            );
        }

        if ($menu === null) {
            return;
        }

        $this->buatHasilDeteksi($sesi->id, self::MENU[$menu]);
    }

    private function buatHasilDeteksi(string $sesiId, array $menu): void
    {
        HasilDeteksi::updateOrCreate(
            ['sesi_id' => $sesiId],
            array_merge(
                [
                    'indeks_glikemik_perkiraan' => $menu['indeks_glikemik'],
                    'keyakinan' => $menu['keyakinan'],
                    'dikoreksi_user' => false,
                ],
                $this->totalkan($menu['item']),
            ),
        );

        // Ditulis ulang dari nol, sama seperti AnalisisNutrisiJob, supaya
        // menjalankan seeder setelah menu berubah tidak meninggalkan item lama.
        ItemMakanan::where('sesi_id', $sesiId)->delete();

        foreach ($menu['item'] as $urutan => $item) {
            [$nama, $porsi, $gram, $kalori, $karbo, $protein, $lemak, $gula, $serat] = $item;

            ItemMakanan::create([
                'sesi_id' => $sesiId,
                'urutan' => $urutan,
                'nama' => $nama,
                'porsi' => $porsi,
                'estimasi_gram' => $gram,
                'kalori' => $kalori,
                'karbohidrat' => $karbo,
                'protein' => $protein,
                'lemak' => $lemak,
                'gula_total' => $gula,
                'serat' => $serat,
            ]);
        }
    }

    /**
     * @return array<string, float>
     */
    private function totalkan(array $item): array
    {
        // Posisi kolom nutrisi di dalam baris item, lihat konstanta MENU.
        $kolom = [
            'total_kalori' => 3,
            'total_karbohidrat' => 4,
            'total_protein' => 5,
            'total_lemak' => 6,
            'total_gula_total' => 7,
            'total_serat' => 8,
        ];

        $total = [];

        foreach ($kolom as $nama => $posisi) {
            $total[$nama] = round(array_sum(array_column($item, $posisi)), 1);
        }

        return $total;
    }

    /**
     * UUID v5 deterministik supaya seeder bisa dijalankan berulang tanpa
     * menumpuk sesi baru.
     */
    private function idSesi(string $email, int $urutan): string
    {
        return Uuid::uuid5(Uuid::NAMESPACE_DNS, "asawatch:sesi:{$email}:{$urutan}")->toString();
    }
}
