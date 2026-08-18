<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Profil;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $profil = $this->profil ?? (new Profil(['user_id' => $this->id]))->setRelation('user', $this->resource);

        return [
            'id' => $this->id,
            'nama' => $this->nama,
            'email' => $this->email,
            'email_terverifikasi' => $this->email_verified_at !== null,
            'profil' => new ProfilResource($profil),
        ];
    }
}
