<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Satu-satunya tempat waktu diformat.
 *
 * Dua sudut pandang yang sengaja dipisah:
 *
 * - iso() untuk kontrak API/ekspor: ISO-8601 UTC dengan mikrodetik, mis.
 *   "2026-08-12T04:30:00.000000Z" (bagian 3.1 dokumen API). Aplikasi mobile
 *   membaca ini, jadi jangan diubah ke zona lokal.
 * - wib()/tanggal()/tanggalJam() untuk panel admin: peneliti membaca layar
 *   dalam waktu setempat, bukan UTC. Database dan Carbon tetap UTC
 *   (config/app.php), konversi hanya terjadi saat ditampilkan.
 */
final class Waktu
{
    /** Zona waktu untuk teks yang dibaca manusia di panel admin. */
    public const ZONA_TAMPILAN = 'Asia/Jakarta';

    public const LABEL_ZONA = 'WIB';

    public static function iso(?CarbonInterface $waktu): ?string
    {
        return $waktu?->clone()->utc()->format('Y-m-d\TH:i:s.u\Z');
    }

    /** Salinan waktu dalam zona tampilan; instance aslinya tidak diubah. */
    public static function wib(?CarbonInterface $waktu): ?CarbonInterface
    {
        return $waktu?->clone()->setTimezone(self::ZONA_TAMPILAN);
    }

    /** Tanggal saja, mis. "12 Agu 2026". Tanpa label zona karena tidak ambigu. */
    public static function tanggal(?CarbonInterface $waktu, string $format = 'd M Y'): ?string
    {
        return self::wib($waktu)?->translatedFormat($format);
    }

    /** Tanggal + jam berlabel, mis. "12 Agu 2026, 11:30 WIB". */
    public static function tanggalJam(?CarbonInterface $waktu, string $format = 'd M Y, H:i'): ?string
    {
        $teks = self::wib($waktu)?->translatedFormat($format);

        return $teks === null ? null : $teks.' '.self::LABEL_ZONA;
    }

    /**
     * Batas hari ini menurut WIB, dinyatakan dalam UTC supaya bisa langsung
     * dipakai membandingkan kolom timestamp yang tersimpan UTC.
     *
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    public static function rentangHariIni(): array
    {
        $awal = CarbonImmutable::now(self::ZONA_TAMPILAN)->startOfDay();

        return [$awal->utc(), $awal->endOfDay()->utc()];
    }

    /** Jam saja berlabel, mis. "11:30 WIB". */
    public static function jam(?CarbonInterface $waktu): ?string
    {
        return self::tanggalJam($waktu, 'H:i');
    }
}
