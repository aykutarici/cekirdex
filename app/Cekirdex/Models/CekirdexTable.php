<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CekirdexTable extends Model
{
    protected $table = 'cekirdex_tables';

    protected $fillable = [
        'cekirdex_restaurant_id', 'cekirdex_branch_id',
        'name', 'code', 'qr_token', 'capacity', 'photo', 'internal_note',
        'accepts_reservations', 'is_active',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'accepts_reservations' => 'boolean',
        'capacity'             => 'integer',
    ];

    public static function newQrToken(): string
    {
        do {
            $token = Str::random(24);
        } while (self::where('qr_token', $token)->exists());

        return $token;
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }

    public function branch()
    {
        return $this->belongsTo(CekirdexBranch::class, 'cekirdex_branch_id');
    }

    public function getMenuUrlAttribute(): string
    {
        return url('/cekirdex/m/'.$this->qr_token);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo ? asset('storage/'.$this->photo) : null;
    }

    public function orders()
    {
        return $this->hasMany(CekirdexOrder::class, 'cekirdex_table_id');
    }

    public function payments()
    {
        return $this->hasMany(CekirdexPayment::class, 'cekirdex_table_id');
    }

    public function billClosures()
    {
        return $this->hasMany(CekirdexBillClosure::class, 'cekirdex_table_id');
    }

    /**
     * Açık (kapanmamış, iptal edilmemiş) siparişleri.
     * Müşteri siparişleri verildikten sonra "served" olabilir ama hesap kapanana
     * kadar açık adisyon sayılırlar. "closed" durumu garson masayı kapattığında oluşur.
     */
    public function activeOrders()
    {
        return $this->hasMany(CekirdexOrder::class, 'cekirdex_table_id')
            ->whereNotIn('status', ['cancelled', 'closed']);
    }
}
