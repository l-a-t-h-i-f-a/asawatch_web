<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Composite primary key (sesi_id, index) — Eloquent has no native support
 * for this. $primaryKey hanya bisa menampung satu kolom, jadi tanpa
 * penyesuaian di bawah setiap UPDATE hasil save()/updateOrCreate() akan
 * berbunyi "where sesi_id = ?" saja dan menimpa keempat baris sampel milik
 * sesi itu sekaligus. setKeysForSaveQuery()/setKeysForSelectQuery()
 * menambahkan `index` ke klausa kunci supaya satu baris saja yang kena.
 *
 * Lookups tetap lewat where() eksplisit, bukan find().
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

    /**
     * Kunci baris = (sesi_id, index). Dipakai Eloquent untuk UPDATE/DELETE
     * saat save() atau updateOrCreate().
     */
    protected function setKeysForSaveQuery($query)
    {
        return $this->tambahKunciIndex(parent::setKeysForSaveQuery($query));
    }

    protected function setKeysForSelectQuery($query)
    {
        return $this->tambahKunciIndex(parent::setKeysForSelectQuery($query));
    }

    private function tambahKunciIndex(Builder $query): Builder
    {
        return $query->where('index', $this->getOriginal('index', $this->index));
    }

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(Sesi::class);
    }
}
