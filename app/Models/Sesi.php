<?php

namespace App\Models;

use App\Models\Scopes\OwnedByAuthUserScope;
use Database\Factories\SesiFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sesi extends Model
{
    /** @use HasFactory<SesiFactory> */
    use HasFactory, SoftDeletes;

    protected $table = 'sesi';

    protected $keyType = 'string';

    public $incrementing = false;

    /**
     * Note: id is NOT auto-generated here — it is always supplied by the
     * mobile app (UUID v4 created client-side, per the API contract).
     */
    protected $fillable = [
        'id',
        'user_id',
        'foto_disk_path',
        'foto_hash',
        'waktu_foto',
        't0',
        'status',
        'waktu_tidak_pasti',
        'sesi_uji',
    ];

    protected function casts(): array
    {
        return [
            'waktu_foto' => 'datetime',
            't0' => 'datetime',
            'waktu_tidak_pasti' => 'boolean',
            'sesi_uji' => 'boolean',
            'deleted_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::addGlobalScope(new OwnedByAuthUserScope);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function sampel(): HasMany
    {
        return $this->hasMany(Sampel::class)->orderBy('index');
    }

    public function hasilDeteksi(): HasOne
    {
        return $this->hasOne(HasilDeteksi::class);
    }

    public function itemMakanan(): HasMany
    {
        return $this->hasMany(ItemMakanan::class)->orderBy('urutan');
    }

    public function pekerjaanAnalisis(): HasMany
    {
        return $this->hasMany(PekerjaanAnalisis::class);
    }
}
