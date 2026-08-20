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

/**
 * Data contoh untuk memeriksa tampilan panel admin: satu sesi makan yang
 * lengkap dari ujung ke ujung — 4 sampel terisi, hasil deteksi nutrisi,
 * item makanan, plus profil/perangkat/kalibrasi yang dibaca kartu-kartu
 * dashboard dan halaman detail.
 *
 * Nilainya sengaja dipilih supaya setiap bagian UI ada isinya:
 * - sampel index 2 melewati ketiga ambang triase dashboard (gula >= 180,
 *   sistolik >= 140, detak >= 100), jadi tabel "Pengukuran di Luar Rentang
 *   Normal" tidak kosong;
 * - satu item makanan punya gula_total >= 15 g supaya daftar di halaman
 *   analitik terisi.
 *
 * Idempoten: ID sesi dipatok tetap dan semua penulisan memakai
 * updateOrCreate, jadi seeder ini aman dijalankan berulang kali.
 *
 * Jalankan: php artisan db:seed --class=SesiLengkapSeeder
 */
class SesiLengkapSeeder extends Seeder
{
    private const EMAIL = 'admin@asawatch.com';

    private const SESI_ID = '0198f3c1-7a24-7b3e-9c55-1f2a4d6b8e01';

    public function run(): void
    {
        $user = User::firstOrCreate(
            ['email' => self::EMAIL],
            [
                'nama' => 'Admin',
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
            ],
        );

        Profil::updateOrCreate(
            ['user_id' => $user->id],
            [
                'tanggal_lahir' => '1957-04-12',
                'jenis_kelamin' => 'laki-laki',
                'golongan_darah' => 'O',
                'tinggi_cm' => 164,
                'berat_kg' => 62.50,
            ],
        );

        TargetHarian::updateOrCreate(
            ['user_id' => $user->id],
            ['kalori' => 1800, 'karbohidrat' => 220, 'langkah' => 4000],
        );

        Perangkat::updateOrCreate(
            ['user_id' => $user->id, 'id_ble' => 'C4:19:D1:8A:22:7F'],
            [
                'nama' => 'AsaWatch A1',
                'firmware' => '1.4.2',
                'baterai_terakhir' => 76,
                'terakhir_tersambung' => now()->subMinutes(12),
            ],
        );

        Kalibrasi::updateOrCreate(
            ['user_id' => $user->id, 'waktu' => now()->subDay()->setTime(7, 30)],
            [
                'sistolik_referensi' => 138,
                'diastolik_referensi' => 86,
                'sistolik_jam' => 132,
                'diastolik_jam' => 83,
            ],
        );

        // waktu_foto sengaja hari ini: KPI "Rata-rata Gula Darah Hari Ini"
        // menyaring sampel berdasarkan tanggal hari ini.
        $waktuFoto = today()->setTime(12, 5);

        $sesi = Sesi::withoutGlobalScopes()->updateOrCreate(
            ['id' => self::SESI_ID],
            [
                'user_id' => $user->id,
                'waktu_foto' => $waktuFoto,
                't0' => $waktuFoto->copy()->addMinutes(8),
                'status' => 'selesai',
                'waktu_tidak_pasti' => false,
                // Tidak ada berkas foto sungguhan — panel admin tidak
                // menampilkan foto, dan path palsu justru akan membuat
                // GET /api/v1/foto/{sesi} gagal 404.
                'foto_disk_path' => null,
                'foto_hash' => null,
            ],
        );

        $sampel = [
            // [index, detik_relatif_t0, gula, detak, sistolik, diastolik, spo2]
            [0, -1800, 112, 78, 128, 82, 97],
            [1, 0, 128, 84, 132, 84, 97],
            [2, 3600, 196, 102, 141, 88, 96],  // melewati ketiga ambang triase
            [3, 7200, 154, 88, 134, 85, 97],
        ];

        foreach ($sampel as [$index, $detik, $gula, $detak, $sis, $dia, $spo2]) {
            Sampel::updateOrCreate(
                ['sesi_id' => $sesi->id, 'index' => $index],
                [
                    'detik_relatif_t0' => $detik,
                    'status' => 'terisi',
                    'dari_buffer' => $index === 3,
                    'gula_darah' => $gula,
                    'detak_jantung' => $detak,
                    'sistolik' => $sis,
                    'diastolik' => $dia,
                    'spo2' => $spo2,
                ],
            );
        }

        $makanan = [
            // [urutan, nama, porsi, gram, kalori, karbo, protein, lemak, gula, serat]
            [0, 'Nasi putih', '1 centong penuh', 180, 234, 51.6, 4.3, 0.4, 0.1, 0.8],
            [1, 'Ayam goreng (dada)', '1 potong', 120, 296, 8.6, 25.4, 18.2, 0.3, 0.4],
            [2, 'Tumis kacang panjang', '1 mangkuk kecil', 90, 78, 9.2, 2.4, 3.6, 2.1, 3.4],
            [3, 'Teh manis hangat', '1 gelas', 200, 132, 26.4, 0.0, 0.0, 26.0, 0.0],
        ];

        HasilDeteksi::updateOrCreate(
            ['sesi_id' => $sesi->id],
            [
                'indeks_glikemik_perkiraan' => 'tinggi',
                'keyakinan' => 0.82,
                'dikoreksi_user' => false,
                'total_kalori' => 740.0,
                'total_karbohidrat' => 95.8,
                'total_protein' => 32.1,
                'total_lemak' => 22.2,
                'total_gula_total' => 28.5,
                'total_serat' => 4.6,
            ],
        );

        foreach ($makanan as [$urutan, $nama, $porsi, $gram, $kalori, $karbo, $protein, $lemak, $gula, $serat]) {
            ItemMakanan::updateOrCreate(
                ['sesi_id' => $sesi->id, 'urutan' => $urutan],
                [
                    'nama' => $nama,
                    'porsi' => $porsi,
                    'estimasi_gram' => $gram,
                    'kalori' => $kalori,
                    'karbohidrat' => $karbo,
                    'protein' => $protein,
                    'lemak' => $lemak,
                    'gula_total' => $gula,
                    'serat' => $serat,
                ],
            );
        }

        $this->command?->info("Sesi lengkap dibuat untuk {$user->email} (sesi {$sesi->id}).");
    }
}
