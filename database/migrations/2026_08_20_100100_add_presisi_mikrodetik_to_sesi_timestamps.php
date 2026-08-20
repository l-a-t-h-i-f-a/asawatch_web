<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * updated_at pada sesi adalah stempel yang dipakai aturan "yang terbaru
 * menang" (bagian 7.1). DATETIME MySQL tanpa presisi memotong pecahan detik,
 * sehingga dua perubahan dalam detik yang sama tidak bisa dibedakan.
 *
 * SQLite (dipakai test suite) menyimpan timestamp sebagai teks dan tidak
 * mengenal perubahan presisi ini, jadi dilewati di sana.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('sesi', function (Blueprint $table) {
            $table->timestamp('created_at', 6)->nullable()->change();
            $table->timestamp('updated_at', 6)->nullable()->change();
            $table->softDeletes('deleted_at', 6)->change();
        });
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            return;
        }

        Schema::table('sesi', function (Blueprint $table) {
            $table->timestamp('created_at')->nullable()->change();
            $table->timestamp('updated_at')->nullable()->change();
            $table->softDeletes('deleted_at')->change();
        });
    }
};
