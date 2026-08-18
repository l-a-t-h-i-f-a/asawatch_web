<?php

namespace App\Models;

use App\Models\Scopes\OwnedByAuthUserScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Perangkat extends Model
{
    use HasUuids;

    protected $table = 'perangkat';

    protected $fillable = [
        'user_id',
        'id_ble',
        'nama',
        'firmware',
        'baterai_terakhir',
        'terakhir_tersambung',
    ];

    protected function casts(): array
    {
        return [
            'terakhir_tersambung' => 'datetime',
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
}
