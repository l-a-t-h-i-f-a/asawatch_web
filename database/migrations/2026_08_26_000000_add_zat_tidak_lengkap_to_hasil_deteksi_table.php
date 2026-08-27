<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Zat gizi yang totalnya diketahui tidak lengkap: ada makanan pada sesi ini
 * yang tidak menyumbang angka untuk zat itu — entah karena namanya tidak ada
 * di tabel TKPI, atau karena selnya kosong di sana (mis. serat pada 221 dari
 * 1146 bahan, gula pada semuanya).
 *
 * Totalnya tetap disimpan apa adanya sebagai jumlah parsial: satu kerupuk yang
 * tidak ketemu tidak boleh menghapus angka kalori seluruh piring. Kolom inilah
 * yang membedakan "459 kcal" dari "sekurang-kurangnya 459 kcal", dan yang
 * membedakan "0 g gula" dari "gula tidak diketahui".
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hasil_deteksi', function (Blueprint $table) {
            $table->json('zat_tidak_lengkap')->nullable()->after('total_serat');
        });
    }

    public function down(): void
    {
        Schema::table('hasil_deteksi', function (Blueprint $table) {
            $table->dropColumn('zat_tidak_lengkap');
        });
    }
};
