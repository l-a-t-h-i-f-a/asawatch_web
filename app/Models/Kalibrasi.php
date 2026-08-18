<?php

namespace App\Models;

use App\Models\Scopes\OwnedByAuthUserScope;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Kalibrasi extends Model
{
    use HasUuids;

    protected $table = 'kalibrasi';

    protected $fillable = [
        'user_id',
        'waktu',
        'sistolik_referensi',
        'diastolik_referensi',
        'sistolik_jam',
        'diastolik_jam',
    ];

    protected function casts(): array
    {
        return [
            'waktu' => 'datetime',
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
