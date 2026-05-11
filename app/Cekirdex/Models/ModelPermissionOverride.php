<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ModelPermissionOverride extends Model
{
    protected $fillable = [
        'model_type',
        'model_id',
        'permission_id',
        'effect',
        'cekirdex_restaurant_id',
    ];

    public function model(): MorphTo
    {
        return $this->morphTo();
    }

    public function permission(): BelongsTo
    {
        return $this->belongsTo(Permission::class);
    }
}
