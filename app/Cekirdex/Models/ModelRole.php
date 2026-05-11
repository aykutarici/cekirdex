<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelRole extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'role_id',
        'cekirdex_restaurant_id',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
