<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Asal-usul angka gizi tiap makanan: entri tabel TKPI yang dipakai, dan cara
 * namanya dicocokkan (tepat / alias / generik).
 *
 * Ada karena pencocokan nama dilonggarkan: "nasi goreng" yang tidak ada di
 * TKPI boleh memakai angka "Nasi gurih". Padanan longgar tidak terlihat dari
 * angkanya — 570 kcal tampak sama sahnya entah dari padanan atau dari entri
 * yang persis — sehingga tanpa kolom ini keputusan melonggarkan tidak bisa
 * dibatalkan: baris yang angkanya berasal dari padanan tidak akan bisa
 * dipisahkan lagi saat analisis.
 *
 * Khusus panel web dan ekspor. Tidak masuk kontrak API bagian 5.2 — aplikasi
 * tidak menampilkannya, dan responden tidak perlu memikirkannya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_makanan', function (Blueprint $table) {
            $table->string('sumber_gizi')->nullable()->after('nama');
            $table->string('cocok', 32)->nullable()->after('sumber_gizi');
        });
    }

    public function down(): void
    {
        Schema::table('item_makanan', function (Blueprint $table) {
            $table->dropColumn(['sumber_gizi', 'cocok']);
        });
    }
};
