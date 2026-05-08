<?php

namespace App\Cekirdex\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class CekirdexProduct extends Model
{
    protected $table = 'cekirdex_products';

    public const ALLERGENS = [
        'gluten'    => ['Gluten',    '🌾'],
        'lactose'   => ['Laktoz',    '🥛'],
        'nuts'      => ['Fıstık',    '🥜'],
        'egg'       => ['Yumurta',   '🥚'],
        'fish'      => ['Balık',     '🐟'],
        'shellfish' => ['Kabuklu',   '🦐'],
        'soy'       => ['Soya',      '🫘'],
        'sesame'    => ['Susam',     '🌱'],
        'spicy'     => ['Acı',       '🌶'],
        'vegan'     => ['Vegan',     '🌿'],
        'vegetarian'=> ['Vejetaryen','🥗'],
        'halal'     => ['Helal',     '☪'],
    ];

    protected $fillable = [
        'cekirdex_restaurant_id', 'cekirdex_category_id',
        'name', 'slug', 'description', 'image',
        'price', 'discount_price', 'preparation_minutes',
        'is_popular', 'is_new', 'is_active',
        'is_in_stock', 'track_stock', 'stock_quantity',
        'options', 'allergens', 'sort_order',
    ];

    protected $casts = [
        'price'               => 'decimal:2',
        'discount_price'      => 'decimal:2',
        'preparation_minutes' => 'integer',
        'is_popular'          => 'boolean',
        'is_new'              => 'boolean',
        'is_active'           => 'boolean',
        'is_in_stock'         => 'boolean',
        'track_stock'         => 'boolean',
        'stock_quantity'      => 'integer',
        'options'             => 'array',
        'allergens'           => 'array',
        'sort_order'          => 'integer',
    ];

    public static function generateSlug(int $restaurantId, string $name): string
    {
        $base = Str::slug($name) ?: 'urun';
        $slug = $base;
        $i = 2;
        while (self::where('cekirdex_restaurant_id', $restaurantId)->where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }
        return $slug;
    }

    public function getEffectivePriceAttribute(): float
    {
        return (float) ($this->discount_price ?: $this->price);
    }

    /**
     * Ürün görselinin tam URL'i.
     * - Boşsa null döner (UI fallback ikonu gösterir).
     * - "stock:slug" prefix'i ise dinamik stok SVG endpoint'ine yönlenir.
     * - Aksi takdirde public storage path'i olarak çözümlenir.
     */
    public function getImageUrlAttribute(): ?string
    {
        if (empty($this->image)) return null;
        if (str_starts_with($this->image, 'stock:')) {
            $slug = substr($this->image, 6);
            return url('/cekirdex/stock-image/'.$slug.'.svg');
        }
        return asset('storage/'.$this->image);
    }

    public function restaurant()
    {
        return $this->belongsTo(CekirdexRestaurant::class, 'cekirdex_restaurant_id');
    }

    public function category()
    {
        return $this->belongsTo(CekirdexCategory::class, 'cekirdex_category_id');
    }

    public function likes()
    {
        return $this->hasMany(CekirdexProductLike::class, 'cekirdex_product_id');
    }

    public function favorites()
    {
        return $this->hasMany(CekirdexProductFavorite::class, 'cekirdex_product_id');
    }

    public function reviews()
    {
        return $this->hasMany(CekirdexProductReview::class, 'cekirdex_product_id');
    }

    public function variants()
    {
        return $this->hasMany(CekirdexProductVariant::class, 'cekirdex_product_id')
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id');
    }

    /** Sipariş alınabilir mi? (aktif + stokta) */
    public function isAvailable(): bool
    {
        if (!$this->is_active || !$this->is_in_stock) return false;
        if ($this->track_stock && $this->stock_quantity !== null && $this->stock_quantity <= 0) return false;
        return true;
    }

    /**
     * Sepet satırı için birim fiyat ve varyasyon (ürün varyantlıysa; yoksa sadece ana fiyat).
     *
     * @return array{ok:bool, unit_price?:float, variant?:CekirdexProductVariant|null, variant_label?:string|null, message?:string}
     */
    public function resolveOrderLine(?int $variantId): array
    {
        $base = (float) ($this->discount_price ?: $this->price);
        $list = $this->relationLoaded('variants')
            ? $this->variants
            : $this->variants()->get();

        if ($list->isEmpty()) {
            return ['ok' => true, 'unit_price' => round($base, 2), 'variant' => null, 'variant_label' => null];
        }

        $v = ($variantId !== null && $variantId > 0)
            ? $list->firstWhere('id', $variantId)
            : ($list->firstWhere('is_default', true) ?? $list->first());

        if (!$v) {
            return ['ok' => false, 'message' => 'Geçersiz seçenek: '.$this->name];
        }

        $unit = round($base + (float) $v->price_adjust, 2);

        return ['ok' => true, 'unit_price' => $unit, 'variant' => $v, 'variant_label' => $v->name];
    }

    /** Yıldız puanı ortalaması (görünür yorumlar için, 1 ondalık). */
    public function getAverageRatingAttribute(): ?float
    {
        $avg = $this->reviews()
            ->where('is_visible', true)
            ->whereNotNull('rating')
            ->avg('rating');
        return $avg ? round((float) $avg, 1) : null;
    }
}
