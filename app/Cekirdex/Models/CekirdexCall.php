<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;

class CekirdexCall extends Model
{
    protected $table = 'cekirdex_calls';

    public const TYPES = [
        'waiter'  => 'Garsonu Çağır',
        'water'   => 'Su',
        'check'   => 'Hesap',
        'napkin'  => 'Peçete',
        'ketchup' => 'Ketçap',
        'mayo'    => 'Mayonez',
        'spice'   => 'Baharat',
        'custom'  => 'Diğer',
    ];

    public const STATUSES = [
        'pending'   => 'Bekliyor',
        'responded' => 'Yanıtlandı',
        'closed'    => 'Kapandı',
    ];

    protected $fillable = [
        'cekirdex_restaurant_id', 'cekirdex_branch_id', 'cekirdex_table_id',
        'call_type', 'message', 'status',
        'responded_by_user_id', 'responded_at',
        'ip_address',
    ];

    protected $casts = [
        'responded_at' => 'datetime',
    ];

    public function getTypeLabelAttribute(): string
    {
        return self::TYPES[$this->call_type] ?? $this->call_type;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? $this->status;
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
