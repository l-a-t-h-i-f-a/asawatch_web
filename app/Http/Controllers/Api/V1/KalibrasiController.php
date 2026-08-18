<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\KalibrasiRequest;
use App\Http\Resources\Api\V1\KalibrasiResource;
use App\Models\Kalibrasi;
use App\Support\KodeGalat;
use Illuminate\Http\Request;

class KalibrasiController extends Controller
{
    public function index(Request $request)
    {
        return KalibrasiResource::collection(
            $request->user()->kalibrasi()->orderByDesc('waktu')->get()
        );
    }

    public function store(KalibrasiRequest $request)
    {
        $data = $request->validated();

        if ($request->user()->kalibrasi()->where('waktu', $data['waktu'])->exists()) {
            throw new ApiException(
                KodeGalat::VALIDASI_GAGAL,
                'Sudah ada kalibrasi pada waktu yang sama.',
                ['waktu' => ['Sudah ada kalibrasi pada waktu yang sama.']],
                422,
            );
        }

        $kalibrasi = $request->user()->kalibrasi()->create($data);

        return new KalibrasiResource($kalibrasi);
    }
}
