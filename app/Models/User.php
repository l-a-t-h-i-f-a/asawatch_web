<?php

namespace App\Models;

use App\Notifications\AturUlangSandiNotification;
use App\Support\Peran;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * Catatan: 'peran' sengaja tidak ada di sini. Kolom itu menentukan hak
     * akses, jadi tidak boleh ikut terisi dari input request mana pun --
     * naikkan peran lewat setPeran()/perintah artisan, bukan mass assignment.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nama',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'peran' => Peran::class,
        ];
    }

    public function profil(): HasOne
    {
        return $this->hasOne(Profil::class);
    }

    public function sesi(): HasMany
    {
        return $this->hasMany(Sesi::class);
    }

    public function kalibrasi(): HasMany
    {
        return $this->hasMany(Kalibrasi::class);
    }

    public function perangkat(): HasMany
    {
        return $this->hasMany(Perangkat::class);
    }

    public function targetHarian(): HasOne
    {
        return $this->hasOne(TargetHarian::class);
    }

    public function isAdmin(): bool
    {
        return $this->peran === Peran::ADMIN;
    }

    /**
     * Semua akun berperan responden — dipakai di seluruh panel web, yang
     * memang hanya menampilkan data responden (bukan data admin sendiri).
     */
    public function scopeResponden(Builder $query): Builder
    {
        return $query->where('peran', Peran::RESPONDEN);
    }

    public function scopeAdmin(Builder $query): Builder
    {
        return $query->where('peran', Peran::ADMIN);
    }

    /** Ubah peran akun. Sengaja eksplisit, di luar jalur mass assignment. */
    public function setPeran(Peran $peran): bool
    {
        $this->peran = $peran;

        return $this->save();
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AturUlangSandiNotification($token));
    }
}
