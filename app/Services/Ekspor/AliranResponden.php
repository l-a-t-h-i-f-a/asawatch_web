<?php

namespace App\Services\Ekspor;

use App\Models\User;
use App\Support\SaringanEkspor;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Menyusuri responden dalam lingkup ekspor, 50 akun sekaligus, dengan
 * saringan tanggal/sesi-uji sudah terpasang pada relasi yang dimuat.
 *
 * Dipakai ketiga format unduhan supaya isinya tidak bisa menyimpang satu
 * sama lain: yang membedakan JSON, CSV, dan XLSX hanyalah cara menulis, tidak
 * pernah baris mana yang ikut.
 *
 * Potongan diambil dari daftar id yang sudah terurut nama, bukan lewat
 * chunkById — chunkById memaksa paging per id sehingga urutan nama hanya
 * berlaku di dalam satu potongan, tidak di keseluruhan berkas.
 */
final class AliranResponden
{
    private const UKURAN_POTONGAN = 50;

    public function __construct(private readonly SaringanEkspor $saringan) {}

    /**
     * @param  list<string>  $relasi  relasi User yang perlu dimuat, mis. ['profil', 'sesi.sampel']
     * @param  callable(User): void  $tulis
     */
    public function setiap(array $relasi, callable $tulis): void
    {
        foreach ($this->saringan->lingkup->ids()->chunk(self::UKURAN_POTONGAN) as $potongan) {
            $responden = User::responden()
                ->whereIn('id', $potongan)
                ->with($this->relasi($relasi))
                ->get()
                ->sortBy('nama');

            foreach ($responden as $user) {
                $tulis($user);
            }
        }
    }

    /**
     * Sisipkan batasan saringan pada relasi berwaktu. Relasi lain (profil,
     * perangkat) tidak punya waktu kejadian, jadi ikut apa adanya.
     */
    private function relasi(array $relasi): array
    {
        // Closure eager-load menerima objek Relation, bukan Builder; getQuery()
        // membuka Builder di dalamnya supaya saringan bisa dipasang di sana.
        $konstrain = [
            'sesi' => fn (Relation $q) => $this->saringan->terapkan($q->getQuery())->orderBy('sesi.waktu_foto'),
            'kalibrasi' => fn (Relation $q) => $this->saringan->batasWaktu($q->getQuery(), 'kalibrasi.waktu')->orderBy('kalibrasi.waktu'),
        ];

        $hasil = [];

        foreach ($relasi as $nama) {
            // Induk yang dikonstrain tidak boleh ikut sebagai string biasa —
            // entri itu akan menimpa closure-nya dengan muatan tanpa saringan.
            if (! isset($konstrain[$nama])) {
                $hasil[] = $nama;
            }
        }

        foreach ($konstrain as $nama => $closure) {
            $diminta = in_array($nama, $relasi, true)
                || array_filter($relasi, fn (string $r) => str_starts_with($r, $nama.'.')) !== [];

            if ($diminta) {
                $hasil[$nama] = $closure;
            }
        }

        return $hasil;
    }
}
