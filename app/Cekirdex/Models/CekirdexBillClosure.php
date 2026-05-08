<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexBillClosure extends Model
{
    protected $table = 'cekirdex_bill_closures';

    public const DELIVERY_METHODS = [
        'emailed' => 'E-posta ile gönderildi',
        'printed' => 'Yazıcıdan basıldı',
        'handed'  => 'Elden verildi',
        'none'    => 'Belge istenmedi',
    ];

    protected $fillable = [
        'cekirdex_restaurant_id', 'cekirdex_table_id', 'closed_by_user_id',
        'delivery_method', 'recipient_email', 'email_sent',
        'subtotal', 'tax', 'service_charge', 'discount', 'total', 'paid', 'change_returned',
        'orders_snapshot', 'items_snapshot', 'payments_snapshot',
        'ip_address', 'note',
    ];

    protected $casts = [
        'subtotal'          => 'decimal:2',
        'tax'               => 'decimal:2',
        'service_charge'    => 'decimal:2',
        'discount'          => 'decimal:2',
        'total'             => 'decimal:2',
        'paid'              => 'decimal:2',
        'change_returned'   => 'decimal:2',
        'orders_snapshot'   => 'array',
        'items_snapshot'    => 'array',
        'payments_snapshot' => 'array',
        'email_sent'        => 'boolean',
    ];

    public function getDeliveryLabelAttribute(): string
    {
        return self::DELIVERY_METHODS[$this->delivery_method] ?? $this->delivery_method;
    }

    public function table()
    {
        return $this->belongsTo(CekirdexTable::class, 'cekirdex_table_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }
}
