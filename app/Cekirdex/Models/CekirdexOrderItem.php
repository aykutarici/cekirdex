<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexOrderItem extends Model
{
    protected $table = 'cekirdex_order_items';

    protected $fillable = [
        'cekirdex_order_id', 'cekirdex_product_id',
        'cekirdex_product_variant_id', 'variant_label',
        'name', 'price', 'quantity',
        'options', 'note', 'subtotal', 'status',
    ];

    protected $casts = [
        'price'    => 'decimal:2',
        'quantity' => 'integer',
        'subtotal' => 'decimal:2',
        'options'  => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(CekirdexOrder::class, 'cekirdex_order_id');
    }

    public function product()
    {
        return $this->belongsTo(CekirdexProduct::class, 'cekirdex_product_id');
    }
}
