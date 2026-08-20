<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Peran admin ditentukan kolom 'peran', bukan alamat email — email
        // di bawah cuma nilai awal yang boleh diganti.
        User::factory()->admin()->create([
            'nama' => 'Admin',
            'email' => 'admin@asawatch.com',
            'password' => Hash::make('password123'),
            'email_verified_at' => now(),
        ]);
    }
}
