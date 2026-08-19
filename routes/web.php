<?php

use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExportController;
use App\Http\Controllers\Admin\RespondentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\VerifikasiEmailController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('admin.login');
});

Route::get('/email/verify/{id}/{hash}', VerifikasiEmailController::class)
    ->middleware('signed')
    ->name('verification.verify');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [AuthController::class, 'logout'])->name('logout');

    Route::middleware('auth')->group(function () {
        Route::get('/', fn () => redirect()->route('admin.dashboard'));
        Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('responden', [RespondentController::class, 'index'])->name('responden.index');
        Route::get('responden/detail', [RespondentController::class, 'latest'])->name('responden.detail');
        Route::get('responden/sesi-terbaru', [RespondentController::class, 'latestSession'])->name('responden.latest-session');
        Route::get('responden/{sesi}', [RespondentController::class, 'show'])->name('responden.show');
        Route::get('analitik', [AnalyticsController::class, 'index'])->name('analitik');
        Route::get('ekspor', [ExportController::class, 'index'])->name('ekspor.index');
        Route::get('ekspor/json', [ExportController::class, 'downloadJson'])->name('ekspor.json');
        Route::get('ekspor/csv', [ExportController::class, 'downloadCsv'])->name('ekspor.csv');

        Route::get('users', [UserController::class, 'index'])->name('users.index');
        Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
        Route::get('users/{user}/sesi/{sesi}', [UserController::class, 'showSession'])->name('users.session.show');
    });
});
