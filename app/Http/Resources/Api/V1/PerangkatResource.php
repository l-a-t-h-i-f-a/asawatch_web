<?php

namespace App\Http\Resources\Api\V1;

use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerangkatResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id_ble' => $this->id_ble,
            'nama' => $this->nama,
            'firmware' => $this->firmware,
            'baterai_terakhir' => $this->baterai_terakhir,
            'terakhir_tersambung' => Waktu::iso($this->terakhir_tersambung),
        ];
    }
}
