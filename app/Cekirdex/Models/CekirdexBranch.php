<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexBranch extends Model
{
    protected $table = 'cekirdex_branches';

    protected $fillable = [
        'cekirdex_restaurant_id',
        'slug', 'name', 'address', 'phone',
        'opening_hours', 'is_active',
    ];

    protected $casts = [
        'opening_hours' => 'array',
        'is_active'     => 'boolean',
    ];

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }

    public function tables()
    {
        return $this->hasMany(CekirdexTable::class, 'cekirdex_branch_id');
    }
}
