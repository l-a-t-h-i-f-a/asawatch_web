<?php

namespace App\Http\Controllers\Api\V1;

use App\Exceptions\ApiException;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SesiUpsertRequest;
use App\Http\Resources\Api\V1\SesiResource;
use App\Models\Sesi;
use App\Services\Sinkron\PenggabungSesi;
use App\Support\KodeGalat;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class SesiController extends Controller
{
    public function index(Request $request)
    {
        $query = Sesi::with(['sampel', 'hasilDeteksi.itemMakanan'])
            ->orderBy('updated_at')
            ->orderBy('id');

        if ($sejak = $request->query('sejak')) {
            $query->where('updated_at', '>', Carbon::parse($sejak));
        }

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $limit = min((int) $request->query('limit', 50), 200);

        return SesiResource::collection($query->cursorPaginate($limit)->withQueryString());
    }

    public function show(Sesi $sesi)
    {
        $this->authorize('view', $sesi);

        $sesi->load(['sampel', 'hasilDeteksi.itemMakanan']);

        return new SesiResource($sesi);
    }

    public function upsert(SesiUpsertRequest $request, string $id, PenggabungSesi $penggabung)
    {
        if (! Str::isUuid($id)) {
            throw new ApiException(
                KodeGalat::VALIDASI_GAGAL,
                'ID sesi harus UUID v4 yang valid.',
                ['id' => ['ID sesi harus UUID v4 yang valid.']],
                422,
            );
        }

        $data = array_merge($request->validated(), ['id' => $id]);

        $hasil = $penggabung->gabungkan($request->user()->id, $data);

        if ($hasil['status'] === 'ditolak') {
            throw new ApiException($hasil['kode'], $hasil['pesan'], null, 409);
        }

        return new SesiResource($hasil['sesi']);
    }

    public function destroy(Sesi $sesi)
    {
        $this->authorize('delete', $sesi);

        $sesi->delete();

        return response()->json(['data' => ['pesan' => 'Sesi dihapus.']]);
    }
}
