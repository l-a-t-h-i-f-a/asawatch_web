<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Portal web ini khusus administrator — pengguna biasa memakai aplikasi
 * mobile. Tidak ada halaman "data saya" di sini, jadi akun non-admin
 * memang tidak punya apa pun untuk dilihat.
 */
class EnsureAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        abort_unless($request->user()?->isAdmin(), 403, 'Halaman ini hanya untuk administrator.');

        return $next($request);
    }
}
