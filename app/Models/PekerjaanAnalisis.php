<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PekerjaanAnalisis extends Model
{
    use HasUuids;

    protected $table = 'pekerjaan_analisis';

    protected $fillable = [
        'sesi_id',
        'status',
        'kode_galat',
        'percobaan',
    ];

    public function sesi(): BelongsTo
    {
        return $this->belongsTo(Sesi::class);
    }
}
