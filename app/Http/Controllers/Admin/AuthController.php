<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /** Percobaan gagal yang ditoleransi per kombinasi email + IP, per menit. */
    private const BATAS_PERCOBAAN = 5;

    public function showLogin()
    {
        if (Auth::user()?->isAdmin()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $kunci = $this->kunciPembatas($request);

        // Sebelumnya form ini tidak dibatasi sama sekali — satu-satunya akun
        // di baliknya adalah administrator, jadi tebakan sandi tak terbatas
        // berarti seluruh data responden ikut terbuka.
        if (RateLimiter::tooManyAttempts($kunci, self::BATAS_PERCOBAAN)) {
            $detik = RateLimiter::availableIn($kunci);

            throw ValidationException::withMessages([
                'email' => "Terlalu banyak percobaan masuk. Coba lagi dalam {$detik} detik.",
            ]);
        }

        if (! Auth::attempt($credentials)) {
            RateLimiter::hit($kunci);

            return back()
                ->withErrors(['email' => 'Email atau kata sandi salah.'])
                ->onlyInput('email');
        }

        // Portal web hanya untuk administrator — akun responden memakai
        // aplikasi mobile dan tidak punya halaman apa pun di sini.
        if (! Auth::user()->isAdmin()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            RateLimiter::hit($kunci);

            return back()
                ->withErrors(['email' => 'Akun ini tidak memiliki akses administrator.'])
                ->onlyInput('email');
        }

        RateLimiter::clear($kunci);
        $request->session()->regenerate();

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }

    private function kunciPembatas(Request $request): string
    {
        return 'admin-login|'.Str::lower((string) $request->input('email')).'|'.$request->ip();
    }
}
