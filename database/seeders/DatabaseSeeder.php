<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\Peran;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Sengaja tanpa factory: fakerphp/faker ada di require-dev, sedangkan
     * seeder ini satu-satunya yang membuat akun admin — jadi ia harus tetap
     * jalan di server yang dipasang dengan "composer install --no-dev".
     */
    public function run(): void
    {
        // Peran admin ditentukan kolom 'peran', bukan alamat email — email
        // di bawah cuma nilai awal yang boleh diganti.
        $admin = User::firstOrCreate(
            ['email' => 'admin@asawatch.com'],
            [
                'nama' => 'Admin',
                'password' => Hash::make('password123'),
            ],
        );

        // 'peran' tidak fillable (lihat User::$fillable), jadi tidak bisa ikut
        // lewat firstOrCreate; dan menjalankan ulang seeder pada akun yang
        // perannya sudah diturunkan manual harus mengembalikannya ke admin.
        if (! $admin->isAdmin()) {
            $admin->setPeran(Peran::ADMIN);
        }

        if (! $admin->email_verified_at) {
            $admin->forceFill(['email_verified_at' => now()])->save();
        }
    }
}
