<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexProductReview extends Model
{
    protected $table = 'cekirdex_product_reviews';

    protected $fillable = [
        'cekirdex_customer_user_id',
        'cekirdex_product_id',
        'cekirdex_restaurant_id',
        'content',
        'rating',
        'is_visible',
        'hidden_by_user_id',
        'hidden_at',
        'ip_address',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'rating'     => 'integer',
        'hidden_at'  => 'datetime',
    ];

    public function product()
    {
        return $this->belongsTo(CekirdexProduct::class, 'cekirdex_product_id');
    }

    public function user()
    {
        return $this->belongsTo(CekirdexCustomerUser::class, 'cekirdex_customer_user_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }
}
