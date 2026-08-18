<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Http\Request;

/**
 * Ditautkan dari email verifikasi (bagian 4 dokumen API: POST /auth/daftar
 * mengirim email verifikasi). Dibuka lewat browser di HP, bukan dipanggil
 * app — makanya bukan endpoint /api/v1 dan tidak butuh Sanctum, keamanannya
 * dari signed URL + kecocokan hash email (bawaan Laravel).
 */
class VerifikasiEmailController
{
    public function __invoke(Request $request, int $id, string $hash)
    {
        $user = User::findOrFail($id);

        if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
            abort(403, 'Tautan verifikasi tidak valid.');
        }

        if (! $user->hasVerifiedEmail()) {
            $user->markEmailAsVerified();
            event(new Verified($user));
        }

        return response('Email terverifikasi. Kembali ke aplikasi AsaWatch untuk melanjutkan.');
    }
}
