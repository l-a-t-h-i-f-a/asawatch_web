<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\KalibrasiResource;
use App\Http\Resources\Api\V1\PerangkatResource;
use App\Http\Resources\Api\V1\ProfilResource;
use App\Http\Resources\Api\V1\SesiResource;
use App\Models\Profil;
use Illuminate\Http\Request;

/**
 * Bagian 9 dokumen API menyebutkan pola job + tautan unduh, tapi tidak
 * merinci bentuknya seperti analisis foto. Untuk sekarang diimplementasikan
 * sinkron langsung (data pengguna biasanya kecil) — pindah ke job async
 * kalau volumenya nanti terbukti butuh itu.
 */
class AkunEksporController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'profil',
            'sesi.sampel',
            'sesi.hasilDeteksi.itemMakanan',
            'kalibrasi',
            'perangkat',
        ]);

        $profil = $user->profil ?? (new Profil(['user_id' => $user->id]))->setRelation('user', $user);

        return response()->json([
            'data' => [
                'profil' => new ProfilResource($profil),
                'sesi' => SesiResource::collection($user->sesi),
                'kalibrasi' => KalibrasiResource::collection($user->kalibrasi),
                'perangkat' => PerangkatResource::collection($user->perangkat),
            ],
        ]);
    }
}
