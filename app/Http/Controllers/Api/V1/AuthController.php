<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\AturUlangSandiRequest;
use App\Http\Requests\Api\V1\DaftarRequest;
use App\Http\Requests\Api\V1\HapusAkunRequest;
use App\Http\Requests\Api\V1\LupaSandiRequest;
use App\Http\Requests\Api\V1\MasukRequest;
use App\Http\Resources\Api\V1\ProfilResource;
use App\Http\Resources\Api\V1\UserResource;
use App\Jobs\HapusAkunJob;
use App\Models\Profil;
use App\Models\User;
use App\Support\KodeGalat;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;

class AuthController extends Controller
{
    public function daftar(DaftarRequest $request)
    {
        $data = $request->validated();

        $user = User::create([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'password' => $data['kata_sandi'],
        ]);

        event(new Registered($user));

        $token = $user->createToken($data['nama_perangkat'])->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'profil' => new ProfilResource((new Profil(['user_id' => $user->id]))->setRelation('user', $user)),
            ],
        ], 201);
    }

    public function masuk(MasukRequest $request)
    {
        $data = $request->validated();

        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['kata_sandi'], $user->password)) {
            throw new ApiException(
                KodeGalat::VALIDASI_GAGAL,
                'Email atau kata sandi salah.',
                ['email' => ['Email atau kata sandi salah.']],
                422,
            );
        }

        $token = $user->createToken($data['nama_perangkat'])->plainTextToken;

        return response()->json([
            'data' => [
                'token' => $token,
                'profil' => new ProfilResource(
                    ($user->profil ?? (new Profil(['user_id' => $user->id])))->setRelation('user', $user)
                ),
            ],
        ]);
    }

    public function keluar(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['data' => ['pesan' => 'Berhasil keluar.']]);
    }

    public function keluarSemua(Request $request)
    {
        $request->user()->tokens()->delete();

        return response()->json(['data' => ['pesan' => 'Semua perangkat berhasil dikeluarkan.']]);
    }

    public function lupaSandi(LupaSandiRequest $request)
    {
        // Selalu 202, ada atau tidak email-nya — jangan bocorkan siapa yang punya akun.
        Password::sendResetLink($request->only('email'));

        return response()->json([
            'data' => ['pesan' => 'Jika email terdaftar, tautan atur ulang sandi sudah dikirim.'],
        ], 202);
    }

    public function aturUlangSandi(AturUlangSandiRequest $request)
    {
        $status = Password::reset(
            [
                'email' => $request->input('email'),
                'password' => $request->input('kata_sandi'),
                'token' => $request->input('token'),
            ],
            function (User $user, string $password) {
                $user->forceFill(['password' => $password])->save();
                $user->tokens()->delete();
            }
        );

        if ($status !== Password::PASSWORD_RESET) {
            throw new ApiException(
                KodeGalat::VALIDASI_GAGAL,
                'Token atur ulang sandi tidak valid atau sudah kedaluwarsa.',
                null,
                422,
            );
        }

        return response()->json(['data' => ['pesan' => 'Kata sandi berhasil diperbarui.']]);
    }

    public function kirimUlangVerifikasi(Request $request)
    {
        if ($request->user()->hasVerifiedEmail()) {
            return response()->json(['data' => ['pesan' => 'Email sudah terverifikasi.']]);
        }

        $request->user()->sendEmailVerificationNotification();

        return response()->json(['data' => ['pesan' => 'Email verifikasi sudah dikirim ulang.']], 202);
    }

    public function saya(Request $request)
    {
        return new UserResource($request->user()->load('profil'));
    }

    public function hapusAkun(HapusAkunRequest $request)
    {
        $user = $request->user();

        if (! Hash::check($request->input('kata_sandi'), $user->password)) {
            throw new ApiException(
                KodeGalat::VALIDASI_GAGAL,
                'Kata sandi salah.',
                ['kata_sandi' => ['Kata sandi salah.']],
                422,
            );
        }

        $user->tokens()->delete();
        $user->delete(); // soft delete — cabut akses seketika.

        HapusAkunJob::dispatch($user->id)->delay(now()->addDays(7));

        Mail::raw(
            "Akun AsaWatch kamu ({$user->email}) telah dijadwalkan untuk dihapus permanen dalam 7 hari. ".
            'Hubungi dukungan jika ini bukan permintaanmu.',
            fn ($message) => $message->to($user->email)->subject('Konfirmasi Penghapusan Akun AsaWatch')
        );

        return response()->json([
            'data' => ['pesan' => 'Akun akan dihapus permanen dalam 7 hari.'],
        ], 202);
    }
}
