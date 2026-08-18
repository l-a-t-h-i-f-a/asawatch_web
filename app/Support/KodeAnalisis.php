<?php

namespace App\Support;

/**
 * Kode status pekerjaan analisis foto (bagian 6 dokumen API) — berbeda dari
 * KodeGalat, yang itu untuk galat di level HTTP envelope. LAYANAN_NUTRISI_GAGAL
 * sengaja punya nilai string yang sama dengan KodeGalat::LAYANAN_NUTRISI_GAGAL.
 */
final class KodeAnalisis
{
    public const FOTO_TIDAK_DIKENALI = 'foto_tidak_dikenali';

    public const LAYANAN_NUTRISI_GAGAL = 'layanan_nutrisi_gagal';

    public const WAKTU_HABIS = 'waktu_habis';
}
