<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\PerangkatRequest;
use App\Http\Resources\Api\V1\PerangkatResource;
use App\Models\Perangkat;
use Illuminate\Http\Request;

class PerangkatController extends Controller
{
    public function index(Request $request)
    {
        return PerangkatResource::collection(
            $request->user()->perangkat()->orderByDesc('terakhir_tersambung')->get()
        );
    }

    public function upsert(PerangkatRequest $request, string $id_ble)
    {
        $perangkat = Perangkat::updateOrCreate(
            ['user_id' => $request->user()->id, 'id_ble' => $id_ble],
            $request->validated(),
        );

        return new PerangkatResource($perangkat);
    }

    public function destroy(Request $request, string $id_ble)
    {
        $request->user()->perangkat()->where('id_ble', $id_ble)->delete();

        return response()->json(['data' => ['pesan' => 'Perangkat dihapus.']]);
    }
}
