<?php

namespace App\Http\Resources\Api\V1;

use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TargetHarianResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'kalori' => $this->kalori,
            'karbohidrat' => $this->karbohidrat,
            'langkah' => $this->langkah,
            'diperbarui_pada' => Waktu::iso($this->updated_at) ?? Waktu::iso(now()),
        ];
    }
}
