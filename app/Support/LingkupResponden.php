<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Resolusi filter "?responden=" yang dipakai halaman Analitik dan Ekspor.
 *
 * Satu tempat supaya kedua halaman selalu sepakat soal: siapa yang termasuk
 * responden (akun admin tidak pernah ikut), apa artinya id yang tidak valid,
 * dan bagaimana filter itu dibawa ke tautan unduhan.
 */
class LingkupResponden
{
    private function __construct(
        public readonly ?User $user,
        public readonly Collection $daftar,
    ) {}

    public static function dari(Request $request): self
    {
        $daftar = User::responden()->orderBy('nama')->get(['id', 'nama', 'email']);

        $dipilih = $request->query('responden');

        // id di luar daftar responden (termasuk id akun admin atau id asal
        // ketik) diperlakukan sebagai "semua responden", bukan 404 — filter
        // ini hanya mempersempit tampilan, bukan penentu hak akses.
        $user = $dipilih !== null && $dipilih !== ''
            ? User::responden()->whereKey($dipilih)->first()
            : null;

        return new self($user, $daftar);
    }

    public function semua(): bool
    {
        return $this->user === null;
    }

    /** Id responden yang tercakup lingkup ini. */
    public function ids(): Collection
    {
        return $this->user ? collect([$this->user->id]) : $this->daftar->pluck('id');
    }

    public function jumlahResponden(): int
    {
        return $this->user ? 1 : $this->daftar->count();
    }

    public function label(): string
    {
        return $this->user
            ? $this->user->nama
            : 'Semua responden ('.$this->daftar->count().')';
    }

    /** Potongan nama file unduhan, mis. "semua" atau "budi-santoso". */
    public function slug(): string
    {
        return $this->user ? str($this->user->nama)->slug()->value() : 'semua';
    }

    /** Query string untuk menjaga filter tetap melekat di tautan. */
    public function parameterQuery(): array
    {
        return $this->user ? ['responden' => $this->user->id] : [];
    }

    /** Batasi query apa pun yang punya kolom user_id ke lingkup ini. */
    public function batasi(Builder $query, string $kolom = 'user_id'): Builder
    {
        return $query->whereIn($kolom, $this->ids());
    }
}
