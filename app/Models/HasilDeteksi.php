<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HasilDeteksi extends Model
{
    protected $table = 'hasil_deteksi';

    protected $primaryKey = 'sesi_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sesi_id',
        'indeks_glikemik_perkiraan',
        'keyakinan',
        'dikoreksi_user',
        'total_kalori',
        'total_karbohidrat',
        'total_protein',
        'total_lemak',
        'total_gula_total',
        'total_serat',
        'zat_tidak_lengkap',
    ];

    protected function casts(): array
    {
        return [
            'keyakinan' => 'float',
            'dikoreksi_user' => 'boolean',
            'total_kalori' => 'float',
            'total_karbohidrat' => 'float',
            'total_protein' => 'float',
            'total_lemak' => 'float',
            'total_gula_total' => 'float',
            'total_serat' => 'float',
            'zat_tidak_lengkap' => 'array',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(Sesi::class);
    }

    public function itemMakanan(): HasMany
    {
        return $this->hasMany(ItemMakanan::class, 'sesi_id', 'sesi_id')->orderBy('urutan');
    }
}
