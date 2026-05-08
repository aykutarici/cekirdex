<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexOrder extends Model
{
    protected $table = 'cekirdex_orders';

    public const STATUSES = [
        'new'        => 'Yeni',
        'confirmed'  => 'Onaylandı',
        'preparing'  => 'Hazırlanıyor',
        'ready'      => 'Hazır',
        'served'     => 'Servis edildi',
        'delivered'  => 'Teslim edildi',
        'closed'     => 'Kapandı',
        'cancelled'  => 'İptal',
    ];

    public const TYPE_DINE_IN  = 'dine_in';
    public const TYPE_TAKEAWAY = 'takeaway';
    public const TYPE_DELIVERY = 'delivery';

    public const TYPE_LABELS = [
        self::TYPE_DINE_IN  => 'Masada',
        self::TYPE_TAKEAWAY => 'Gel-al',
        self::TYPE_DELIVERY => 'Adrese teslim',
    ];

    protected $fillable = [
        'cekirdex_restaurant_id', 'cekirdex_branch_id', 'cekirdex_table_id',
        'cekirdex_customer_user_id',
        'order_number', 'public_code', 'order_type',
        'guest_name', 'guest_phone',
        'contact_name', 'contact_phone', 'contact_email',
        'delivery_address', 'delivery_lat', 'delivery_lng', 'delivery_fee',
        'eta_minutes', 'ready_at', 'delivered_at',
        'subtotal', 'tax', 'service_charge', 'discount', 'total',
        'status', 'payment_status', 'payment_method',
        'note', 'ip_address', 'user_agent',
    ];

    protected $casts = [
        'subtotal'       => 'decimal:2',
        'tax'            => 'decimal:2',
        'service_charge' => 'decimal:2',
        'discount'       => 'decimal:2',
        'delivery_fee'   => 'decimal:2',
        'total'          => 'decimal:2',
        'delivery_lat'   => 'decimal:7',
        'delivery_lng'   => 'decimal:7',
        'eta_minutes'    => 'integer',
        'ready_at'       => 'datetime',
        'delivered_at'   => 'datetime',
    ];

    public static function newOrderNumber(): string
    {
        // Örn: C-2026-A1B2C3
        return 'C-'.now()->format('Y').'-'.strtoupper(\Illuminate\Support\Str::random(6));
    }

    /** Halka açık, müşteriye verilen kısa kod (durum sayfası için). */
    public static function newPublicCode(): string
    {
        do {
            $code = strtoupper(\Illuminate\Support\Str::random(8));
        } while (self::where('public_code', $code)->exists());
        return $code;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getTypeLabelAttribute(): string
    {
        return self::TYPE_LABELS[$this->order_type] ?? 'Masada';
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }

    public function branch()
    {
        return $this->belongsTo(CekirdexBranch::class, 'cekirdex_branch_id');
    }

    public function table()
    {
        return $this->belongsTo(CekirdexTable::class, 'cekirdex_table_id');
    }

    public function items()
    {
        return $this->hasMany(CekirdexOrderItem::class, 'cekirdex_order_id');
    }

    public function payments()
    {
        return $this->hasMany(CekirdexPayment::class, 'cekirdex_order_id');
    }
}
