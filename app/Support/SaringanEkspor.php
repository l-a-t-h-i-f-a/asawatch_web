<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Saringan halaman ekspor: lingkup responden + rentang tanggal + sakelar
 * sesi uji. Sesi yang sudah dihapus responden tidak pernah ikut: laporan
 * penelitian hanya memuat data yang masih diakui pemiliknya.
 *
 * Satu objek dipakai bersama oleh ringkasan di layar dan ketiga unduhan,
 * supaya angka yang dibaca peneliti sebelum menekan tombol persis sama
 * dengan isi berkas yang turun.
 *
 * Batas tanggal ditulis peneliti dalam WIB (itu yang tertera di panel), tapi
 * kolom `waktu_foto` tersimpan UTC — konversi terjadi sekali di sini.
 */
final class SaringanEkspor
{
    /** Kolom penanda waktu sesi. Bukan t0: t0 boleh null, waktu_foto tidak. */
    private const KOLOM_WAKTU = 'sesi.waktu_foto';

    private function __construct(
        public readonly LingkupResponden $lingkup,
        public readonly ?CarbonImmutable $dari,
        public readonly ?CarbonImmutable $sampai,
        public readonly ?string $dariInput,
        public readonly ?string $sampaiInput,
        public readonly bool $sertakanSesiUji,
    ) {}

    public static function dari(Request $request): self
    {
        [$dari, $dariInput] = self::batas($request->query('dari'), awal: true);
        [$sampai, $sampaiInput] = self::batas($request->query('sampai'), awal: false);

        // Rentang terbalik diluruskan, bukan ditolak: peneliti yang keliru
        // memasang tanggal tetap dapat data, bukan halaman kosong.
        if ($dari !== null && $sampai !== null && $dari->gt($sampai)) {
            [$dari, $sampai] = [$sampai->startOfDay(), $dari->endOfDay()];
            [$dariInput, $sampaiInput] = [$sampaiInput, $dariInput];
        }

        return new self(
            lingkup: LingkupResponden::dari($request),
            dari: $dari,
            sampai: $sampai,
            dariInput: $dariInput,
            sampaiInput: $sampaiInput,
            // Tanpa parameter sama sekali (kunjungan pertama) artinya "apa
            // adanya": semua sesi ikut. Form selalu mengirim sesi_uji lewat
            // hidden input, jadi centang yang dilepas tetap terbaca.
            sertakanSesiUji: $request->has('sesi_uji') ? $request->boolean('sesi_uji') : true,
        );
    }

    /**
     * Tanggal "Y-m-d" dari form → batas hari WIB yang dinyatakan dalam UTC.
     *
     * @return array{0: ?CarbonImmutable, 1: ?string}
     */
    private static function batas(mixed $nilai, bool $awal): array
    {
        if (! is_string($nilai) || $nilai === '') {
            return [null, null];
        }

        try {
            $hari = CarbonImmutable::createFromFormat('!Y-m-d', $nilai, Waktu::ZONA_TAMPILAN);
        } catch (\Throwable) {
            return [null, null];
        }

        if ($hari === false) {
            return [null, null];
        }

        return [
            ($awal ? $hari->startOfDay() : $hari->endOfDay())->utc(),
            $hari->format('Y-m-d'),
        ];
    }

    /** Terapkan saringan pada query/relasi Sesi mana pun. */
    public function terapkan(Builder $query): Builder
    {
        if (! $this->sertakanSesiUji) {
            $query->where('sesi.sesi_uji', false);
        }

        return $this->batasWaktu($query, self::KOLOM_WAKTU);
    }

    /**
     * Batasi kolom waktu mana pun ke rentang yang dipilih. Dipakai juga oleh
     * kalibrasi, yang punya waktu sendiri dan bukan bagian dari sesi.
     */
    public function batasWaktu(Builder $query, string $kolom): Builder
    {
        if ($this->dari !== null) {
            $query->where($kolom, '>=', $this->dari);
        }

        if ($this->sampai !== null) {
            $query->where($kolom, '<=', $this->sampai);
        }

        return $query;
    }

    /** Ada saringan selain bawaan? Dipakai untuk menampilkan tombol reset. */
    public function aktif(): bool
    {
        return ! $this->lingkup->semua()
            || $this->dari !== null
            || $this->sampai !== null
            || ! $this->sertakanSesiUji;
    }

    /** Query string supaya saringan melekat di tautan unduhan. */
    public function parameterQuery(): array
    {
        return array_filter([
            ...$this->lingkup->parameterQuery(),
            'dari' => $this->dariInput,
            'sampai' => $this->sampaiInput,
            'sesi_uji' => $this->sertakanSesiUji ? null : '0',
        ], fn ($v) => $v !== null);
    }

    /** Mis. "1 Agu 2026 – 31 Agu 2026", "sejak 1 Agu 2026", "seluruh periode". */
    public function labelPeriode(): string
    {
        $dari = $this->dari === null ? null : Waktu::tanggal($this->dari);
        $sampai = $this->sampai === null ? null : Waktu::tanggal($this->sampai);

        return match (true) {
            $dari !== null && $sampai !== null => "{$dari} – {$sampai}",
            $dari !== null => "sejak {$dari}",
            $sampai !== null => "sampai {$sampai}",
            default => 'Seluruh periode',
        };
    }

    /**
     * Nama berkas unduhan, mis. "asawatch_pengukuran_semua_2026-09-08.csv".
     * Tanggalnya mengikuti hari WIB; isi berkas tetap punya kolom UTC.
     */
    public function namaBerkas(string $ekstensi, ?string $imbuhan = null): string
    {
        return implode('_', array_filter(['asawatch', $imbuhan, $this->slug(), Waktu::tanggal(now(), 'Y-m-d')])).'.'.$ekstensi;
    }

    /** Potongan nama berkas, mis. "semua_2026-08-01_2026-08-31". */
    public function slug(): string
    {
        return implode('_', array_filter([
            $this->lingkup->slug(),
            $this->dariInput,
            $this->sampaiInput,
        ]));
    }
}
