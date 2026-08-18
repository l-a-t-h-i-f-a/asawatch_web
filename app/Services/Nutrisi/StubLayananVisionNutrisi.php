<?php

namespace App\Services\Nutrisi;

/**
 * Placeholder provider — no real vision service is wired up yet (no API
 * key decided, see bagian 6 dokumen API). Returns a plausible-shaped but
 * fixed result so the rest of the analysis pipeline (job, caching by photo
 * hash, polling endpoints) can be built and tested end-to-end. Replace the
 * binding in AppServiceProvider once a real provider is chosen — nothing
 * else should need to change.
 */
class StubLayananVisionNutrisi implements LayananVisionNutrisi
{
    public function analisis(string $pathFotoAbsolut): array
    {
        return [
            'indeks_glikemik_perkiraan' => 'sedang',
            'keyakinan' => 0.5,
            'total' => [
                'kalori' => 500.0,
                'karbohidrat' => 60.0,
                'protein' => 20.0,
                'lemak' => 15.0,
                'gula_total' => 10.0,
                'serat' => 4.0,
            ],
            'makanan' => [
                [
                    'nama' => 'Makanan belum dikenali (mode pengembangan)',
                    'porsi' => '1 porsi',
                    'estimasi_gram' => 250.0,
                    'nutrisi' => [
                        'kalori' => 500.0,
                        'karbohidrat' => 60.0,
                        'protein' => 20.0,
                        'lemak' => 15.0,
                        'gula_total' => 10.0,
                        'serat' => 4.0,
                    ],
                ],
            ],
        ];
    }
}
