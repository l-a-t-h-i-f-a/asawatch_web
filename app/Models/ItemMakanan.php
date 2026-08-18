<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Composite primary key (sesi_id, urutan) — see Sampel for the same caveat.
 */
class ItemMakanan extends Model
{
    protected $table = 'item_makanan';

    protected $primaryKey = 'sesi_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sesi_id',
        'urutan',
        'nama',
        'porsi',
        'estimasi_gram',
        'kalori',
        'karbohidrat',
        'protein',
        'lemak',
        'gula_total',
        'serat',
    ];

    protected function casts(): array
    {
        return [
            'estimasi_gram' => 'float',
            'kalori' => 'float',
            'karbohidrat' => 'float',
            'protein' => 'float',
            'lemak' => 'float',
            'gula_total' => 'float',
            'serat' => 'float',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(Sesi::class);
    }
}
