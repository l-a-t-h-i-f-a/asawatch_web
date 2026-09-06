<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Masuk lewat akun Google.
 *
 * Dua perubahan, dan keduanya perlu:
 *
 * 1. `google_sub` menyimpan klaim `sub` dari ID token — identitas akun Google
 *    yang **tidak pernah berubah**. Email bisa berganti; `sub` tidak. Menautkan
 *    hanya lewat email berarti orang yang mengganti alamat Google-nya kembali
 *    sebagai pengguna baru, dengan riwayat kesehatan kosong.
 *
 * 2. `password` menjadi nullable. Pengguna yang lahir dari Google tidak pernah
 *    punya kata sandi, dan menaruh hash acak di sana justru berbahaya: alur
 *    "lupa sandi" akan memperlakukannya sebagai akun sandi biasa dan mengirim
 *    tautan reset yang membuat akun Google bisa diambil alih lewat email.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_sub')->nullable()->unique()->after('email');
        });

        // `change()` pada kolom butuh doctrine/dbal di Laravel lama; di Laravel
        // 11+ sudah bawaan. Kolom lain di baris ini ikut ditulis ulang, jadi
        // definisinya harus lengkap — kalau tidak, atribut yang tidak disebut
        // akan hilang diam-diam.
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['google_sub']);
            $table->dropColumn('google_sub');
        });
    }
};
