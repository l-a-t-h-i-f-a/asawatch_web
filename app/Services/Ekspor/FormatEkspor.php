<?php

namespace App\Services\Ekspor;

use App\Support\SaringanEkspor;

/**
 * Ketiga bentuk unduhan beserta salinan teks yang menjelaskannya.
 *
 * Dikumpulkan di satu enum supaya halaman ekspor, nama berkas, dan pemilihan
 * aksi di controller tidak pernah menyebut daftar format yang berbeda —
 * menambah format cukup menambah satu case di sini.
 */
enum FormatEkspor: string
{
    case XLSX = 'xlsx';
    case CSV = 'csv';
    case JSON = 'json';

    /** Nilai di luar daftar (salah ketik di URL) jatuh ke bawaan, bukan 404. */
    public static function dari(mixed $nilai): self
    {
        return is_string($nilai) ? self::tryFrom($nilai) ?? self::XLSX : self::XLSX;
    }

    public function judul(): string
    {
        return match ($this) {
            self::XLSX => 'Excel',
            self::CSV => 'CSV',
            self::JSON => 'JSON',
        };
    }

    public function untukSiapa(): string
    {
        return match ($this) {
            self::XLSX => 'Untuk dibaca langsung',
            self::CSV => 'Untuk SPSS, R, Python',
            self::JSON => 'Arsip mentah',
        };
    }

    public function ikon(): string
    {
        return match ($this) {
            self::XLSX => 'bi-file-earmark-spreadsheet',
            self::CSV => 'bi-filetype-csv',
            self::JSON => 'bi-filetype-json',
        };
    }

    public function penjelasan(): string
    {
        return match ($this) {
            self::XLSX => 'Berlembar-lembar. Mulai dari “Ringkasan Sesi”; lembar mentah dan “Kamus Data” menyusul di belakangnya.',
            self::CSV => 'Satu tabel datar, satu baris per titik pengukuran, nama kolom pendek tanpa spasi.',
            self::JSON => 'Seluruh data apa adanya, termasuk kalibrasi dan perangkat yang tidak masuk Excel/CSV.',
        };
    }

    /** Berkas ini berlembar-lembar, jadi panel isi berkas dirinci per lembar. */
    public function berlembar(): bool
    {
        return $this === self::XLSX;
    }

    public function namaBerkas(SaringanEkspor $saringan): string
    {
        return $saringan->namaBerkas($this->value, $this === self::CSV ? 'pengukuran' : null);
    }
}
