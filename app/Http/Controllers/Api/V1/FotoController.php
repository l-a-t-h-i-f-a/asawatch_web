<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\SesiFotoRequest;
use App\Http\Resources\Api\V1\SesiResource;
use App\Models\Sesi;
use Illuminate\Support\Facades\Storage;

/**
 * Dipisah dari PUT /sesi/{id} supaya gagal-unggah bisa diulang tanpa
 * mengirim ulang seluruh sesi (bagian 5.2). Foto disimpan di disk privat
 * ('local' -> storage/app/private, tidak diserve web) dan hanya bisa diakses
 * lewat show() di bawah, yang dijaga signed URL + auth:sanctum sekaligus —
 * tidak pernah ada URL publik ke foto makanan (bagian 2 aturan #5).
 */
class FotoController extends Controller
{
    public function store(SesiFotoRequest $request, Sesi $sesi)
    {
        $this->authorize('update', $sesi);

        $file = $request->file('foto');
        $hash = hash_file('sha256', $file->getRealPath());
        $namaBerkas = $sesi->id.'.'.$file->extension();
        $direktori = "foto/{$sesi->user_id}";

        Storage::disk('local')->putFileAs($direktori, $file, $namaBerkas);

        $sesi->update([
            'foto_disk_path' => "{$direktori}/{$namaBerkas}",
            'foto_hash' => $hash,
        ]);

        return new SesiResource($sesi->fresh(['sampel', 'hasilDeteksi.itemMakanan']));
    }

    public function show(Sesi $sesi)
    {
        $this->authorize('view', $sesi);

        if (! $sesi->foto_disk_path || ! Storage::disk('local')->exists($sesi->foto_disk_path)) {
            abort(404);
        }

        return Storage::disk('local')->response($sesi->foto_disk_path);
    }
}
