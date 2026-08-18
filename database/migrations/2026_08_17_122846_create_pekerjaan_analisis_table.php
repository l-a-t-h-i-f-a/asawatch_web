<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pekerjaan_analisis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('sesi_id');
            $table->foreign('sesi_id')->references('id')->on('sesi')->cascadeOnDelete();
            $table->string('status');
            $table->string('kode_galat')->nullable();
            $table->unsignedTinyInteger('percobaan')->default(0);
            $table->timestamps();

            $table->index('sesi_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pekerjaan_analisis');
    }
};
