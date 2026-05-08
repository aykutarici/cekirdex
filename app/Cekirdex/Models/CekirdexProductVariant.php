<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexProductVariant extends Model
{
    protected $table = 'cekirdex_product_variants';

    protected $fillable = [
        'cekirdex_product_id', 'name', 'price_adjust',
        'is_default', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'price_adjust' => 'decimal:2',
        'is_default'   => 'boolean',
        'is_active'    => 'boolean',
        'sort_order'   => 'integer',
    ];

    public function product()
    {
        return $this->belongsTo(CekirdexProduct::class, 'cekirdex_product_id');
    }
}
