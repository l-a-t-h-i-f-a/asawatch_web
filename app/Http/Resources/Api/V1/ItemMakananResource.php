<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemMakananResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'urutan' => $this->urutan,
            'nama' => $this->nama,
            'porsi' => $this->porsi,
            'estimasi_gram' => $this->estimasi_gram !== null ? (float) $this->estimasi_gram : null,
            'nutrisi' => [
                'kalori' => $this->kalori !== null ? (float) $this->kalori : null,
                'karbohidrat' => $this->karbohidrat !== null ? (float) $this->karbohidrat : null,
                'protein' => $this->protein !== null ? (float) $this->protein : null,
                'lemak' => $this->lemak !== null ? (float) $this->lemak : null,
                'gula_total' => $this->gula_total !== null ? (float) $this->gula_total : null,
                'serat' => $this->serat !== null ? (float) $this->serat : null,
            ],
        ];
    }
}
