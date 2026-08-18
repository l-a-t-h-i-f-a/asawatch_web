<?php

namespace App\Http\Resources\Api\V1;

use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class KalibrasiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'waktu' => Waktu::iso($this->waktu),
            'sistolik_referensi' => $this->sistolik_referensi,
            'diastolik_referensi' => $this->diastolik_referensi,
            'sistolik_jam' => $this->sistolik_jam,
            'diastolik_jam' => $this->diastolik_jam,
        ];
    }
}
