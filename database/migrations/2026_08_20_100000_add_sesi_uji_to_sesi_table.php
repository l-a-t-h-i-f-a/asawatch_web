<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda sesi pengujian: sesi yang direkam dengan jadwal dimampatkan atau
 * perangkat palsu supaya jalur unggah bisa dilatih tanpa menunggu 2,5 jam.
 * Disimpan dan boleh ditampilkan, tetapi tidak pernah ikut agregat apa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesi', function (Blueprint $table) {
            $table->boolean('sesi_uji')->default(false)->after('waktu_tidak_pasti');
            $table->index(['user_id', 'sesi_uji']);
        });
    }

    public function down(): void
    {
        Schema::table('sesi', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'sesi_uji']);
            $table->dropColumn('sesi_uji');
        });
    }
};
