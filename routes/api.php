<?php

use App\Http\Controllers\Api\V1\AkunEksporController;
use App\Http\Controllers\Api\V1\AnalisisController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\FotoController;
use App\Http\Controllers\Api\V1\KalibrasiController;
use App\Http\Controllers\Api\V1\PerangkatController;
use App\Http\Controllers\Api\V1\ProfilController;
use App\Http\Controllers\Api\V1\SesiController;
use App\Http\Controllers\Api\V1\SinkronController;
use App\Http\Controllers\Api\V1\TargetHarianController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::prefix('auth')->name('auth.')->group(function () {
        Route::post('daftar', [AuthController::class, 'daftar'])->name('daftar');
        Route::post('masuk', [AuthController::class, 'masuk'])->name('masuk')->middleware('throttle:masuk');
        Route::post('lupa-sandi', [AuthController::class, 'lupaSandi'])->name('lupa-sandi')->middleware('throttle:lupa-sandi');
        Route::post('atur-ulang-sandi', [AuthController::class, 'aturUlangSandi'])->name('atur-ulang-sandi');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('keluar', [AuthController::class, 'keluar'])->name('keluar');
            Route::post('keluar-semua', [AuthController::class, 'keluarSemua'])->name('keluar-semua');
            Route::post('kirim-ulang-verifikasi', [AuthController::class, 'kirimUlangVerifikasi'])->name('kirim-ulang-verifikasi');
            Route::get('saya', [AuthController::class, 'saya'])->name('saya');
            Route::delete('akun', [AuthController::class, 'hapusAkun'])->name('hapus-akun');
        });
    });

    Route::middleware(['auth:sanctum', 'verified'])->group(function () {
        Route::get('profil', [ProfilController::class, 'show'])->name('profil.show');
        Route::put('profil', [ProfilController::class, 'update'])->name('profil.update');

        Route::get('sesi', [SesiController::class, 'index'])->name('sesi.index');
        Route::get('sesi/{sesi}', [SesiController::class, 'show'])->name('sesi.show');
        Route::put('sesi/{id}', [SesiController::class, 'upsert'])->name('sesi.upsert')->where('id', '[0-9a-fA-F-]{36}');
        Route::delete('sesi/{sesi}', [SesiController::class, 'destroy'])->name('sesi.destroy');

        Route::post('sesi/{sesi}/foto', [FotoController::class, 'store'])->name('sesi.foto.store');
        Route::get('foto/{sesi}', [FotoController::class, 'show'])->name('foto.show')->middleware('signed');

        Route::post('sesi/{sesi}/analisis', [AnalisisController::class, 'store'])->name('sesi.analisis.store')->middleware('throttle:analisis');
        Route::get('sesi/{sesi}/analisis', [AnalisisController::class, 'show'])->name('sesi.analisis.show');

        Route::get('kalibrasi', [KalibrasiController::class, 'index'])->name('kalibrasi.index');
        Route::post('kalibrasi', [KalibrasiController::class, 'store'])->name('kalibrasi.store');

        Route::get('perangkat', [PerangkatController::class, 'index'])->name('perangkat.index');
        Route::put('perangkat/{id_ble}', [PerangkatController::class, 'upsert'])->name('perangkat.upsert');
        Route::delete('perangkat/{id_ble}', [PerangkatController::class, 'destroy'])->name('perangkat.destroy');

        Route::get('target-harian', [TargetHarianController::class, 'show'])->name('target-harian.show');
        Route::put('target-harian', [TargetHarianController::class, 'update'])->name('target-harian.update');

        Route::get('sinkron', [SinkronController::class, 'index'])->name('sinkron.index');
        Route::post('sinkron', [SinkronController::class, 'store'])->name('sinkron.store');

        Route::get('akun/ekspor', [AkunEksporController::class, 'show'])->name('akun.ekspor');
    });
});
