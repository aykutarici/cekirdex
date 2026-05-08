<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexPayment extends Model
{
    protected $table = 'cekirdex_payments';

    public const STATUSES = [
        'pending'   => 'Bekliyor',
        'paid'      => 'Ödendi',
        'refunded'  => 'İade edildi',
        'cancelled' => 'İptal',
        'failed'    => 'Başarısız',
    ];

    public const METHOD_LABELS = [
        'online_card'        => 'Kredi/Banka Kartı (Online)',
        'online_apple_pay'   => 'Apple Pay',
        'online_google_pay'  => 'Google Pay',
        'waiter_card'        => 'Garson — POS Kart',
        'waiter_cash'        => 'Garson — Nakit',
        'bank_transfer'      => 'Havale / EFT',
        'fast'               => 'FAST',
        'qr'                 => 'TR Karekod',
    ];

    public const SPLIT_FULL   = 'full';
    public const SPLIT_AMOUNT = 'amount';
    public const SPLIT_EQUAL  = 'equal';
    public const SPLIT_ITEMS  = 'items';

    protected $fillable = [
        'cekirdex_restaurant_id', 'cekirdex_table_id', 'cekirdex_order_id',
        'amount', 'status', 'method', 'provider', 'transaction_id',
        'split_mode', 'selected_items', 'portion_label',
        'split_total_parts', 'split_part_index',
        'payer_name', 'ip_address',
        'confirmed_by_user_id', 'confirmed_at', 'paid_at',
    ];

    protected $casts = [
        'amount'         => 'decimal:2',
        'selected_items' => 'array',
        'paid_at'        => 'datetime',
        'confirmed_at'   => 'datetime',
        'split_total_parts' => 'integer',
        'split_part_index'  => 'integer',
    ];

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function getMethodLabelAttribute(): string
    {
        return self::METHOD_LABELS[$this->method] ?? $this->method;
    }

    public function table()
    {
        return $this->belongsTo(CekirdexTable::class, 'cekirdex_table_id');
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }

    public function order()
    {
        return $this->belongsTo(CekirdexOrder::class, 'cekirdex_order_id');
    }
}
