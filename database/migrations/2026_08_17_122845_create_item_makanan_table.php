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
        Schema::create('item_makanan', function (Blueprint $table) {
            $table->uuid('sesi_id');
            $table->foreign('sesi_id')->references('id')->on('sesi')->cascadeOnDelete();
            $table->unsignedSmallInteger('urutan');
            $table->string('nama');
            $table->string('porsi')->nullable();
            $table->float('estimasi_gram')->nullable();
            $table->float('kalori')->nullable();
            $table->float('karbohidrat')->nullable();
            $table->float('protein')->nullable();
            $table->float('lemak')->nullable();
            $table->float('gula_total')->nullable();
            $table->float('serat')->nullable();
            $table->timestamps();

            $table->primary(['sesi_id', 'urutan']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('item_makanan');
    }
};
