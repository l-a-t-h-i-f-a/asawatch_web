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
        Schema::create('sampel', function (Blueprint $table) {
            $table->uuid('sesi_id');
            $table->foreign('sesi_id')->references('id')->on('sesi')->cascadeOnDelete();
            $table->smallInteger('index');
            $table->integer('detik_relatif_t0');
            $table->string('status');
            $table->boolean('dari_buffer')->default(false);
            $table->integer('gula_darah')->nullable();
            $table->integer('detak_jantung')->nullable();
            $table->integer('sistolik')->nullable();
            $table->integer('diastolik')->nullable();
            $table->integer('spo2')->nullable();
            $table->timestamps();

            $table->primary(['sesi_id', 'index']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sampel');
    }
};
