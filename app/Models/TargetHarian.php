<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TargetHarian extends Model
{
    protected $table = 'target_harian';

    protected $primaryKey = 'user_id';

    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'kalori',
        'karbohidrat',
        'langkah',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
