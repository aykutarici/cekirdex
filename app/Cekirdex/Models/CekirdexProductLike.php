<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexProductLike extends Model
{
    protected $table = 'cekirdex_product_likes';

    protected $fillable = [
        'cekirdex_customer_user_id',
        'cekirdex_product_id',
        'cekirdex_restaurant_id',
    ];

    public function product()
    {
        return $this->belongsTo(CekirdexProduct::class, 'cekirdex_product_id');
    }

    public function user()
    {
        return $this->belongsTo(CekirdexCustomerUser::class, 'cekirdex_customer_user_id');
    }
}
