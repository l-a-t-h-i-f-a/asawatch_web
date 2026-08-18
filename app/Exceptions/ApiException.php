<?php

namespace App\Exceptions;

use Exception;

/**
 * Domain-level error carrying the {kode, pesan, detail} shape the API's
 * exception handler serializes as {"galat": {...}} (bagian 3.1 dokumen API).
 */
class ApiException extends Exception
{
    public function __construct(
        public readonly string $kode,
        string $pesan,
        public readonly ?array $detail = null,
        public readonly int $status = 400,
    ) {
        parent::__construct($pesan);
    }
}
