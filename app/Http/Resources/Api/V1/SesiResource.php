<?php

namespace App\Http\Resources\Api\V1;

use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\URL;

class SesiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $kadaluarsa = now()->addHour();

        return [
            'id' => $this->id,
            'waktu_foto' => Waktu::iso($this->waktu_foto),
            't0' => Waktu::iso($this->t0),
            'status' => $this->status,
            'waktu_tidak_pasti' => (bool) $this->waktu_tidak_pasti,
            'foto' => $this->foto_disk_path ? [
                'url' => URL::temporarySignedRoute('api.v1.foto.show', $kadaluarsa, ['sesi' => $this->id]),
                'kadaluarsa_pada' => Waktu::iso($kadaluarsa),
            ] : null,
            'sampel' => SampelResource::collection($this->whenLoaded('sampel')),
            'hasil' => $this->whenLoaded('hasilDeteksi', fn () => $this->hasilDeteksi ? new HasilDeteksiResource($this->hasilDeteksi) : null, null),
            'diperbarui_pada' => Waktu::iso($this->updated_at),
            'dihapus_pada' => Waktu::iso($this->deleted_at),
        ];
    }
}
