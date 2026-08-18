<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Composite primary key (sesi_id, index) — Eloquent has no native support
 * for this, so lookups/updates are always done via explicit where() calls
 * rather than find()/save() on the model key.
 */
class Sampel extends Model
{
    protected $table = 'sampel';

    protected $primaryKey = 'sesi_id';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'sesi_id',
        'index',
        'detik_relatif_t0',
        'status',
        'dari_buffer',
        'gula_darah',
        'detak_jantung',
        'sistolik',
        'diastolik',
        'spo2',
    ];

    protected function casts(): array
    {
        return [
            'dari_buffer' => 'boolean',
        ];
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(Sesi::class);
    }
}
