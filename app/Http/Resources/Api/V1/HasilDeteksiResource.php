<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * total.* disimpan apa adanya dari hasil deteksi — jangan dijumlahkan ulang
 * dari makanan.*.nutrisi (bagian 5.2 dokumen API: sebelum dikoreksi pengguna,
 * totalnya memang tidak selalu persis sama dengan jumlah per-item).
 */
class HasilDeteksiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'indeks_glikemik_perkiraan' => $this->indeks_glikemik_perkiraan,
            'keyakinan' => $this->keyakinan !== null ? (float) $this->keyakinan : null,
            'dikoreksi_user' => (bool) $this->dikoreksi_user,
            'total' => [
                'kalori' => $this->total_kalori,
                'karbohidrat' => $this->total_karbohidrat,
                'protein' => $this->total_protein,
                'lemak' => $this->total_lemak,
                'gula_total' => $this->total_gula_total,
                'serat' => $this->total_serat,
            ],
            'makanan' => ItemMakananResource::collection($this->itemMakanan),
        ];
    }
}
