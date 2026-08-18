<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Profil extends Model
{
    protected $table = 'profil';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $keyType = 'int';

    protected $fillable = [
        'user_id',
        'tanggal_lahir',
        'jenis_kelamin',
        'golongan_darah',
        'tinggi_cm',
        'berat_kg',
    ];

    protected function casts(): array
    {
        return [
            'tanggal_lahir' => 'date',
            'berat_kg' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
