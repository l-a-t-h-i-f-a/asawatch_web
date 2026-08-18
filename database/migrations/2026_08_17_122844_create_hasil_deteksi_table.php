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
        Schema::create('hasil_deteksi', function (Blueprint $table) {
            $table->uuid('sesi_id')->primary();
            $table->foreign('sesi_id')->references('id')->on('sesi')->cascadeOnDelete();
            $table->string('indeks_glikemik_perkiraan')->nullable();
            $table->float('keyakinan')->nullable();
            $table->boolean('dikoreksi_user')->default(false);
            $table->float('total_kalori')->nullable();
            $table->float('total_karbohidrat')->nullable();
            $table->float('total_protein')->nullable();
            $table->float('total_lemak')->nullable();
            $table->float('total_gula_total')->nullable();
            $table->float('total_serat')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hasil_deteksi');
    }
};
