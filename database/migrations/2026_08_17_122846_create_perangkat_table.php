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
        Schema::create('perangkat', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('id_ble');
            $table->string('nama');
            $table->string('firmware')->nullable();
            $table->unsignedTinyInteger('baterai_terakhir')->nullable();
            $table->timestampTz('terakhir_tersambung')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'id_ble']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perangkat');
    }
};
