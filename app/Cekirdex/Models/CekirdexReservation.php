<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CekirdexReservation extends Model
{
    protected $table = 'cekirdex_reservations';

    public const STATUSES = [
        'pending'    => 'Onay bekliyor',
        'confirmed'  => 'Onaylandı',
        'seated'     => 'Misafir geldi',
        'completed'  => 'Tamamlandı',
        'no_show'    => 'Gelmedi',
        'cancelled'  => 'İptal',
    ];

    protected $fillable = [
        'cekirdex_restaurant_id', 'cekirdex_branch_id', 'cekirdex_table_id',
        'cekirdex_customer_user_id',
        'public_code',
        'contact_name', 'contact_phone', 'contact_email',
        'reserved_for', 'duration_minutes', 'party_size',
        'status', 'note', 'admin_note',
        'confirmed_by_user_id', 'confirmed_at',
        'cancelled_at', 'cancelled_by',
        'ip_address',
    ];

    protected $casts = [
        'reserved_for'      => 'datetime',
        'duration_minutes'  => 'integer',
        'party_size'        => 'integer',
        'confirmed_at'      => 'datetime',
        'cancelled_at'      => 'datetime',
    ];

    public static function newPublicCode(): string
    {
        do {
            $code = strtoupper(Str::random(8));
        } while (self::where('public_code', $code)->exists());
        return $code;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }

    public function table()
    {
        return $this->belongsTo(CekirdexTable::class, 'cekirdex_table_id');
    }

    public function customer()
    {
        return $this->belongsTo(CekirdexCustomerUser::class, 'cekirdex_customer_user_id');
    }
}
