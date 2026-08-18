<?php

namespace App\Support;

/**
 * Fixed error-code vocabulary the mobile app branches on (bagian 3.1 dokumen
 * API). Adding new codes is fine; changing what an existing one means is not.
 */
final class KodeGalat
{
    public const VALIDASI_GAGAL = 'validasi_gagal';

    public const TIDAK_TERAUTENTIKASI = 'tidak_terautentikasi';

    public const TOKEN_KEDALUWARSA = 'token_kedaluwarsa';

    public const TIDAK_DIIZINKAN = 'tidak_diizinkan';

    public const TIDAK_DITEMUKAN = 'tidak_ditemukan';

    public const KONFLIK_VERSI = 'konflik_versi';

    public const TERLALU_SERING = 'terlalu_sering';

    public const LAYANAN_NUTRISI_GAGAL = 'layanan_nutrisi_gagal';

    public const GALAT_SERVER = 'galat_server';
}
