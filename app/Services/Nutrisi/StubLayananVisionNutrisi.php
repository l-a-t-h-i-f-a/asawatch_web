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
    /**
     * Jeda buatan meniru latensi provider sungguhan (beberapa detik). Bukan
     * kosmetik: jalur "sedang dianalisis" di app (spinner lalu kartu terisi
     * sendiri) hanya teruji kalau hasilnya tidak datang seketika.
     */
    private const JEDA_TIRUAN_DETIK = 3;

    public function analisis(string $pathFotoAbsolut): array
    {
        sleep(self::JEDA_TIRUAN_DETIK);

        return [
            'indeks_glikemik_perkiraan' => 'sedang',
            'keyakinan' => 0.82,
            'total' => [
                'kalori' => 430.0,
                'karbohidrat' => 45.0,
                'protein' => 28.0,
                'lemak' => 15.0,
                'gula_total' => 6.0,
                'serat' => 4.0,
            ],
            // Urutan array ini yang disimpan sebagai item_makanan.urutan dan
            // dipakai app untuk merangkai judul kartu (bagian 8).
            'makanan' => [
                [
                    'nama' => 'Nasi merah',
                    'porsi' => '1 centong',
                    'estimasi_gram' => 120.0,
                    'nutrisi' => [
                        'kalori' => 150.0,
                        'karbohidrat' => 32.0,
                        'protein' => 3.0,
                        'lemak' => 1.0,
                        'gula_total' => 0.5,
                        'serat' => 2.5,
                    ],
                ],
                [
                    'nama' => 'Ayam bakar',
                    'porsi' => '1 potong',
                    'estimasi_gram' => 100.0,
                    'nutrisi' => [
                        'kalori' => 195.0,
                        'karbohidrat' => 2.0,
                        'protein' => 23.0,
                        'lemak' => 11.0,
                        'gula_total' => 1.0,
                        'serat' => 0.0,
                    ],
                ],
                [
                    'nama' => 'Tumis buncis',
                    'porsi' => '1 mangkuk kecil',
                    'estimasi_gram' => 80.0,
                    'nutrisi' => [
                        'kalori' => 85.0,
                        'karbohidrat' => 11.0,
                        'protein' => 2.0,
                        'lemak' => 3.0,
                        'gula_total' => 4.5,
                        'serat' => 1.5,
                    ],
                ],
            ],
        ];
    }
}
