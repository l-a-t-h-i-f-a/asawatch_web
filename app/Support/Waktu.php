<?php

namespace App\Support;

use Carbon\CarbonInterface;

/**
 * Format waktu tetap: ISO-8601 UTC dengan mikrodetik, mis.
 * "2026-08-12T04:30:00.000000Z" (bagian 3.1 dokumen API). Dipakai di semua
 * Resource supaya tidak ada dua cara format tanggal yang berbeda.
 */
final class Waktu
{
    public static function iso(?CarbonInterface $waktu): ?string
    {
        return $waktu?->clone()->utc()->format('Y-m-d\TH:i:s.u\Z');
    }
}
