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
        Schema::create('kalibrasi', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestampTz('waktu');
            $table->unsignedSmallInteger('sistolik_referensi');
            $table->unsignedSmallInteger('diastolik_referensi');
            $table->unsignedSmallInteger('sistolik_jam');
            $table->unsignedSmallInteger('diastolik_jam');
            $table->timestamps();

            $table->unique(['user_id', 'waktu']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kalibrasi');
    }
};
