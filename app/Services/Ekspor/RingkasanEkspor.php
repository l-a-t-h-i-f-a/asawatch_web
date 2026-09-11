<?php

namespace App\Services\Ekspor;

use App\Models\ItemMakanan;
use App\Models\Sampel;
use App\Models\Sesi;
use App\Support\SaringanEkspor;
use Illuminate\Database\Eloquent\Builder;

/**
 * Jumlah baris yang akan masuk berkas, dihitung dengan saringan yang sama
 * persis seperti unduhannya.
 *
 * Ditampilkan di layar sebelum tombol unduh ditekan: peneliti tahu isi berkas
 * sebelum mengunduhnya, dan tahu kalau kombinasi saringannya menghasilkan nol
 * baris tanpa harus membuka Excel dulu.
 */
final class RingkasanEkspor
{
    private function __construct(
        public readonly int $responden,
        public readonly int $sesi,
        public readonly int $sesiUji,
        public readonly int $sampel,
        public readonly int $sampelTerisi,
        public readonly int $itemMakanan,
    ) {}

    public static function hitung(SaringanEkspor $saringan): self
    {
        $ids = $saringan->lingkup->ids();
        $sesi = fn () => $saringan->terapkan(Sesi::query())->whereIn('sesi.user_id', $ids);
        $sampel = fn () => Sampel::whereHas('sesi', fn (Builder $q) => $saringan->terapkan($q)->whereIn('sesi.user_id', $ids));

        return new self(
            responden: $saringan->lingkup->jumlahResponden(),
            sesi: $sesi()->count(),
            sesiUji: $sesi()->where('sesi.sesi_uji', true)->count(),
            sampel: $sampel()->count(),
            sampelTerisi: $sampel()->where('sampel.status', 'terisi')->count(),
            itemMakanan: ItemMakanan::whereHas('sesi', fn (Builder $q) => $saringan->terapkan($q)->whereIn('sesi.user_id', $ids))->count(),
        );
    }

    /** Tidak ada satu pun sesi tercakup — tombol unduh dimatikan. */
    public function kosong(): bool
    {
        return $this->sesi === 0;
    }

    /** Jumlah baris per lembar XLSX, untuk lembar panduan dan panel di layar. */
    public function perLembar(): array
    {
        return [
            SkemaEkspor::LEMBAR_RINGKASAN => $this->sesi,
            SkemaEkspor::LEMBAR_RESPONDEN => $this->responden,
            SkemaEkspor::LEMBAR_SESI => $this->sesi,
            SkemaEkspor::LEMBAR_PENGUKURAN => $this->sampel,
            SkemaEkspor::LEMBAR_PENGUKURAN_LEBAR => $this->sesi,
            SkemaEkspor::LEMBAR_ITEM_MAKANAN => $this->itemMakanan,
        ];
    }
}
