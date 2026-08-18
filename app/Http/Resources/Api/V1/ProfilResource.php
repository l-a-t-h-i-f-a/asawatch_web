<?php

namespace App\Http\Resources\Api\V1;

use App\Support\Waktu;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property \App\Models\Profil $resource
 */
class ProfilResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'nama' => $this->user->nama,
            'tanggal_lahir' => $this->tanggal_lahir?->format('Y-m-d'),
            'jenis_kelamin' => $this->jenis_kelamin,
            'golongan_darah' => $this->golongan_darah,
            'tinggi_cm' => $this->tinggi_cm,
            'berat_kg' => $this->berat_kg !== null ? (float) $this->berat_kg : null,
            'diperbarui_pada' => Waktu::iso($this->updated_at) ?? Waktu::iso(now()),
        ];
    }
}
