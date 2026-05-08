<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CekirdexRestaurant extends Model
{
    protected $table = 'cekirdex_restaurants';

    protected $fillable = [
        'slug', 'name', 'logo', 'cover_image', 'description',
        'address', 'city', 'country', 'phone', 'email', 'website',
        'latitude', 'longitude',
        'currency', 'tax_rate', 'service_charge_rate',
        'accepts_online_payment',
        'accepts_takeaway', 'accepts_delivery', 'accepts_reservations',
        'delivery_radius_km', 'delivery_min_amount', 'delivery_fee',
        'reservation_slot_minutes', 'reservation_slot_interval_minutes',
        'reservation_capacity_mode', 'reservation_total_capacity',
        'reservation_table_count', 'reservation_seat_count', 'reservation_advance_days',
        'opening_hours',
        'primary_color', 'secondary_color',
        'status', 'is_active',
    ];

    protected $casts = [
        'tax_rate'                  => 'decimal:2',
        'service_charge_rate'       => 'decimal:2',
        'accepts_online_payment'    => 'boolean',
        'accepts_takeaway'          => 'boolean',
        'accepts_delivery'          => 'boolean',
        'accepts_reservations'      => 'boolean',
        'delivery_radius_km'        => 'decimal:2',
        'delivery_min_amount'       => 'decimal:2',
        'delivery_fee'              => 'decimal:2',
        'reservation_slot_minutes'          => 'integer',
        'reservation_slot_interval_minutes' => 'integer',
        'reservation_total_capacity'        => 'integer',
        'reservation_table_count'           => 'integer',
        'reservation_seat_count'             => 'integer',
        'reservation_advance_days'           => 'integer',
        'latitude'                  => 'decimal:7',
        'longitude'                 => 'decimal:7',
        'opening_hours'             => 'array',
        'is_active'                 => 'boolean',
    ];

    /** Restoranın halka açık landing URL'i (sadece slug atanmışsa). */
    public function getPublicUrlAttribute(): ?string
    {
        return $this->slug ? url('/cekirdex/r/'.$this->slug) : null;
    }

    public function getLogoUrlAttribute(): ?string
    {
        return $this->logo ? asset('storage/'.$this->logo) : null;
    }

    public function getCoverImageUrlAttribute(): ?string
    {
        return $this->cover_image ? asset('storage/'.$this->cover_image) : null;
    }

    /**
     * Şu an açık mı? Çalışma saatleri tanımlıysa kontrol eder.
     * Tanım yoksa (null) her zaman açık kabul eder.
     */
    public function isOpenNow(?\Carbon\Carbon $now = null): bool
    {
        $hours = $this->opening_hours;
        if (empty($hours) || !is_array($hours)) return true;
        $now ??= now();
        $key = strtolower($now->shortEnglishDayOfWeek); // mon/tue/...
        $today = $hours[$key] ?? null;
        if (empty($today) || count($today) < 2) return false;
        [$open, $close] = $today;
        return $now->format('H:i') >= $open && $now->format('H:i') <= $close;
    }

    public static function generateSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'restoran';
        $slug = $base;
        $i = 2;
        while (self::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }

    public function users()
    {
        return $this->hasMany(CekirdexUser::class, 'cekirdex_restaurant_id');
    }

    public function branches()
    {
        return $this->hasMany(CekirdexBranch::class, 'cekirdex_restaurant_id');
    }

    public function tables()
    {
        return $this->hasMany(CekirdexTable::class, 'cekirdex_restaurant_id');
    }

    public function categories()
    {
        return $this->hasMany(CekirdexCategory::class, 'cekirdex_restaurant_id');
    }

    public function products()
    {
        return $this->hasMany(CekirdexProduct::class, 'cekirdex_restaurant_id');
    }

    public function orders()
    {
        return $this->hasMany(CekirdexOrder::class, 'cekirdex_restaurant_id');
    }

    public function calls()
    {
        return $this->hasMany(CekirdexCall::class, 'cekirdex_restaurant_id');
    }

    public function reservations()
    {
        return $this->hasMany(CekirdexReservation::class, 'cekirdex_restaurant_id');
    }

    /** Müşteriye gönderilecek rezervasyon derin bağlantısı (sayfa + modal). */
    public function getReservationBookingUrlAttribute(): ?string
    {
        return $this->slug ? url('/cekirdex/r/'.$this->slug.'#rezervasyon') : null;
    }

    /**
     * Rezervasyon müsaitlik hesabında kullanılan eşzamanlı maksimum kişi sayısı.
     * Mod: total | counts | tables
     */
    public function effectiveReservationSeatCapacity(): int
    {
        $mode = $this->reservation_capacity_mode ?? 'tables';

        if ($mode === 'total') {
            $n = (int) ($this->reservation_total_capacity ?? 0);

            return max(1, $n > 0 ? $n : 40);
        }

        if ($mode === 'counts') {
            $seats = (int) ($this->reservation_seat_count ?? 0);
            if ($seats > 0) {
                return max(1, $seats);
            }
            $tc = (int) ($this->reservation_table_count ?? 0);
            if ($tc > 0) {
                return max(1, $tc * 4);
            }

            return max(1, $this->tables()->where('is_active', true)->count() * 4);
        }

        $sum = (int) $this->tables()
            ->where('is_active', true)
            ->where('accepts_reservations', true)
            ->sum('capacity');

        return max(1, $sum > 0 ? $sum : 8);
    }

    public function maxReservationPartySize(): int
    {
        return min(60, $this->effectiveReservationSeatCapacity());
    }
}
