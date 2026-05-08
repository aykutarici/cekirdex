<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CekirdexCategory extends Model
{
    protected $table = 'cekirdex_categories';

    protected $fillable = [
        'cekirdex_restaurant_id',
        'name', 'slug', 'description', 'image',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'sort_order' => 'integer',
    ];

    public static function generateSlug(int $restaurantId, string $name): string
    {
        $base = Str::slug($name) ?: 'kategori';
        $slug = $base;
        $i = 2;
        while (self::where('cekirdex_restaurant_id', $restaurantId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }

    public function products()
    {
        return $this->hasMany(CekirdexProduct::class, 'cekirdex_category_id');
    }
}
