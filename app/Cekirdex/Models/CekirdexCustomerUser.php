<?php

namespace App\Cekirdex\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

/**
 * Çekirdex'in son tüketicisi (menüye QR ile gelen müşteri).
 * Personel hesaplarıyla (CekirdexUser) farklı tutulur — `cekirdex_customer` guard'ında çalışır.
 */
class CekirdexCustomerUser extends Authenticatable
{
    use Notifiable;

    protected $table = 'cekirdex_customer_users';

    protected $fillable = [
        'phone', 'name', 'email', 'password', 'avatar',
        'ip_address', 'user_agent', 'last_login_at', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'last_login_at' => 'datetime',
        'is_active'     => 'boolean',
    ];

    /** Telefonu normalize et (sadece rakam tut). */
    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        // 10 haneli ise başına 0 ekle (Türkiye standardı), 11+ ise olduğu gibi
        if (strlen($digits) === 10) $digits = '0'.$digits;
        return $digits;
    }

    public function likes()
    {
        return $this->hasMany(CekirdexProductLike::class, 'cekirdex_customer_user_id');
    }

    public function favorites()
    {
        return $this->hasMany(CekirdexProductFavorite::class, 'cekirdex_customer_user_id');
    }

    public function reviews()
    {
        return $this->hasMany(CekirdexProductReview::class, 'cekirdex_customer_user_id');
    }

    public function orders()
    {
        return $this->hasMany(CekirdexOrder::class, 'cekirdex_customer_user_id');
    }

    /**
     * Bu müşteri belirtilen restoranda en az bir sipariş verdi mi?
     * Beğen/Yorum yapabilmesi için gereken kontrol.
     */
    public function hasOrderedAt(int $restaurantId): bool
    {
        return CekirdexOrder::where('cekirdex_customer_user_id', $this->id)
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->where('status', '!=', 'cancelled')
            ->exists();
    }
}
