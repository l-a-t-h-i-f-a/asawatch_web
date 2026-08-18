<?php

namespace App\Models;

use App\Notifications\AturUlangSandiNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
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

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new AturUlangSandiNotification($token));
    }
}
