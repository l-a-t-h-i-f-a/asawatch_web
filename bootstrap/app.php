<?php

use App\Exceptions\ApiException;
use App\Support\KodeGalat;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        //
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $galat = function (string $kode, string $pesan, ?array $detail, int $status) {
            return response()->json([
                'galat' => array_filter([
                    'kode' => $kode,
                    'pesan' => $pesan,
                    'detail' => $detail,
                ], fn ($v) => $v !== null),
            ], $status);
        };

        $isApi = fn (Request $request) => $request->is('api/*');

        $exceptions->render(function (ApiException $e, Request $request) use ($galat, $isApi) {
            if ($isApi($request)) {
                return $galat($e->kode, $e->getMessage(), $e->detail, $e->status);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) use ($galat, $isApi) {
            if ($isApi($request)) {
                return $galat(KodeGalat::VALIDASI_GAGAL, $e->getMessage(), $e->errors(), 422);
            }
        });

        $exceptions->render(function (AuthenticationException $e, Request $request) use ($galat, $isApi) {
            if ($isApi($request)) {
                return $galat(KodeGalat::TIDAK_TERAUTENTIKASI, 'Sesi tidak valid, silakan masuk kembali.', null, 401);
            }
        });

        $exceptions->render(function (AuthorizationException $e, Request $request) use ($galat, $isApi) {
            if ($isApi($request)) {
                return $galat(KodeGalat::TIDAK_DIIZINKAN, 'Kamu tidak diizinkan mengakses data ini.', null, 403);
            }
        });

        $exceptions->render(function (ModelNotFoundException|NotFoundHttpException $e, Request $request) use ($galat, $isApi) {
            if ($isApi($request)) {
                return $galat(KodeGalat::TIDAK_DITEMUKAN, 'Data yang dicari tidak ditemukan.', null, 404);
            }
        });

        $exceptions->render(function (TooManyRequestsHttpException $e, Request $request) use ($galat, $isApi) {
            if ($isApi($request)) {
                return $galat(KodeGalat::TERLALU_SERING, 'Terlalu banyak percobaan, coba lagi sebentar lagi.', null, 429);
            }
        });

        $exceptions->render(function (HttpExceptionInterface $e, Request $request) use ($galat, $isApi) {
            if ($isApi($request)) {
                return $galat(KodeGalat::GALAT_SERVER, $e->getMessage() ?: 'Terjadi kesalahan pada server.', null, $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) use ($galat, $isApi) {
            if ($isApi($request) && ! app()->hasDebugModeEnabled()) {
                return $galat(KodeGalat::GALAT_SERVER, 'Terjadi kesalahan pada server.', null, 500);
            }
        });
    })->create();
