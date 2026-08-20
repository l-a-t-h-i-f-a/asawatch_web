<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\Peran;
use Illuminate\Console\Command;

/**
 * Satu-satunya jalur untuk mengubah peran akun. Sengaja lewat baris perintah:
 * tidak ada tombol di panel web yang bisa dipakai menaikkan diri sendiri atau
 * orang lain jadi admin bila sesi admin bocor.
 */
class KelolaAdmin extends Command
{
    protected $signature = 'asawatch:admin
                            {email? : Email akun yang mau diubah perannya}
                            {--cabut : Turunkan akun ini kembali jadi responden}';

    protected $description = 'Lihat daftar admin, atau angkat/cabut peran admin sebuah akun';

    public function handle(): int
    {
        $email = $this->argument('email');

        if (! $email) {
            return $this->tampilkanDaftar();
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $this->error("Akun dengan email {$email} tidak ditemukan.");

            return self::FAILURE;
        }

        return $this->option('cabut')
            ? $this->cabut($user)
            : $this->angkat($user);
    }

    private function tampilkanDaftar(): int
    {
        $admin = User::admin()->orderBy('nama')->get(['nama', 'email']);

        if ($admin->isEmpty()) {
            $this->warn('Belum ada akun berperan admin — portal web tidak bisa diakses siapa pun.');
            $this->line('Angkat satu akun dengan: php artisan asawatch:admin email@contoh.com');

            return self::SUCCESS;
        }

        $this->info("Administrator terdaftar ({$admin->count()}):");
        $this->table(['Nama', 'Email'], $admin->map(fn ($u) => [$u->nama, $u->email]));

        return self::SUCCESS;
    }

    private function angkat(User $user): int
    {
        if ($user->isAdmin()) {
            $this->line("{$user->email} sudah berperan admin.");

            return self::SUCCESS;
        }

        $user->setPeran(Peran::ADMIN);
        $this->info("{$user->email} sekarang berperan admin.");

        return self::SUCCESS;
    }

    private function cabut(User $user): int
    {
        if (! $user->isAdmin()) {
            $this->line("{$user->email} memang bukan admin.");

            return self::SUCCESS;
        }

        // Menolak mencabut admin terakhir — kalau tidak, portal web langsung
        // terkunci untuk semua orang dan hanya bisa dibuka lewat database.
        if (User::admin()->count() <= 1) {
            $this->error('Ini satu-satunya admin yang tersisa. Angkat admin lain dulu sebelum mencabutnya.');

            return self::FAILURE;
        }

        $user->setPeran(Peran::RESPONDEN);
        $this->info("{$user->email} sekarang berperan responden.");

        return self::SUCCESS;
    }
}
