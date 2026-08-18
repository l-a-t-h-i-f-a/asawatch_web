<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SampelResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'index' => $this->index,
            'detik_relatif_t0' => $this->detik_relatif_t0,
            'status' => $this->status,
            'dari_buffer' => (bool) $this->dari_buffer,
            'gula_darah' => $this->gula_darah,
            'detak_jantung' => $this->detak_jantung,
            'sistolik' => $this->sistolik,
            'diastolik' => $this->diastolik,
            'spo2' => $this->spo2,
        ];
    }
}
