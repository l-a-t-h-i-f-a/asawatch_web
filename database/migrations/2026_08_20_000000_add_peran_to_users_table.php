<?php

use App\Support\Peran;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /** Alamat email yang dulu dipakai sebagai penanda admin. */
    private const EMAIL_ADMIN_LAMA = 'admin@asawatch.com';

    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('peran')->default(Peran::RESPONDEN->value)->index();
        });

        // Pemasangan yang sudah berjalan tetap punya adminnya: akun dengan
        // alamat penanda lama dinaikkan ke peran admin.
        DB::table('users')
            ->where('email', self::EMAIL_ADMIN_LAMA)
            ->update(['peran' => Peran::ADMIN->value]);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['peran']);
            $table->dropColumn('peran');
        });
    }
};
