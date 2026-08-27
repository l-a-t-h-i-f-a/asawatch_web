<?php

namespace App\Services\Nutrisi;

/**
 * One interface, one concrete provider bound in AppServiceProvider. Swapping
 * providers (third-party vision API, in-house model, manual food DB) should
 * only ever mean changing the binding — never touching the controller/job
 * that calls this (bagian 6 dokumen API).
 */
interface LayananVisionNutrisi
{
    /**
     * @return array{
     *     indeks_glikemik_perkiraan: ?string,
     *     keyakinan: ?float,
     *     total: array{kalori: ?float, karbohidrat: ?float, protein: ?float, lemak: ?float, gula_total: ?float, serat: ?float},
     *     zat_tidak_lengkap?: array<int, string>,
     *     makanan: array<int, array{nama: string, porsi: ?string, estimasi_gram: ?float, nutrisi: array<string, ?float>}>,
     * }
     *
     * Nilai null berarti "tidak diketahui", bukan nol: makanan yang tidak ada
     * di tabel gizi, atau zat yang tabelnya memang tidak punya kolomnya.
     *
     * @throws LayananNutrisiGagalException
     */
    public function analisis(string $pathFotoAbsolut): array;
}
