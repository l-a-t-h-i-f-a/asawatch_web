<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\HasilDeteksiResource;
use App\Jobs\AnalisisNutrisiJob;
use App\Models\PekerjaanAnalisis;
use App\Models\Sesi;
use App\Support\KodeGalat;

class AnalisisController extends Controller
{
    public function store(Sesi $sesi)
    {
        $this->authorize('update', $sesi);

        if (! $sesi->foto_disk_path) {
            throw new ApiException(
                KodeGalat::VALIDASI_GAGAL,
                'Sesi ini belum punya foto untuk dianalisis.',
                null,
                422,
            );
        }

        $pekerjaan = PekerjaanAnalisis::create([
            'sesi_id' => $sesi->id,
            'status' => 'antre',
            'percobaan' => 0,
        ]);

        AnalisisNutrisiJob::dispatch($pekerjaan->id);

        return response()->json([
            'status' => 'antre',
            'id_pekerjaan' => $pekerjaan->id,
        ], 202);
    }

    public function show(Sesi $sesi)
    {
        $this->authorize('view', $sesi);

        $pekerjaan = $sesi->pekerjaanAnalisis()->latest('created_at')->first();

        if (! $pekerjaan) {
            return response()->json(['status' => 'antre', 'kode' => null, 'hasil' => null]);
        }

        $sesi->loadMissing('hasilDeteksi.itemMakanan');

        return response()->json([
            'status' => $pekerjaan->status,
            'kode' => $pekerjaan->kode_galat,
            'hasil' => $sesi->hasilDeteksi ? new HasilDeteksiResource($sesi->hasilDeteksi) : null,
        ]);
    }
}
