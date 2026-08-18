<?php

namespace App\Services\Nutrisi;

use Exception;

/**
 * $kode is one of foto_tidak_dikenali, layanan_nutrisi_gagal, waktu_habis
 * (bagian 6 dokumen API) — the job catches this and stores it as the
 * pekerjaan's kode_galat rather than letting the job hard-fail.
 */
class LayananNutrisiGagalException extends Exception
{
    public function __construct(public readonly string $kode, string $pesan)
    {
        parent::__construct($pesan);
    }
}
