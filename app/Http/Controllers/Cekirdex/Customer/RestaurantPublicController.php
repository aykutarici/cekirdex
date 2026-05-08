<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductLike;
use App\Cekirdex\Models\CekirdexProductReview;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

/**
 * Halka açık restoran landing sayfası — QR olmadan, slug ile erişim.
 * Müşteri menüyü görür, paket sipariş ve rezervasyon yapabilir.
 */
class RestaurantPublicController extends Controller
{
    public function show(string $slug)
    {
        $restaurant = CekirdexRestaurant::where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $categories = CekirdexCategory::where('cekirdex_restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')->get();

        $products = CekirdexProduct::where('cekirdex_restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with('variants')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        $productIds = $products->pluck('id')->all();
        $likeCounts = CekirdexProductLike::whereIn('cekirdex_product_id', $productIds)
            ->selectRaw('cekirdex_product_id, COUNT(*) as c')->groupBy('cekirdex_product_id')
            ->pluck('c', 'cekirdex_product_id');
        $reviewCounts = CekirdexProductReview::whereIn('cekirdex_product_id', $productIds)
            ->visible()
            ->selectRaw('cekirdex_product_id, COUNT(*) as c')->groupBy('cekirdex_product_id')
            ->pluck('c', 'cekirdex_product_id');
        $ratingAverages = CekirdexProductReview::whereIn('cekirdex_product_id', $productIds)
            ->visible()
            ->whereNotNull('rating')
            ->selectRaw('cekirdex_product_id, AVG(rating) as avg, COUNT(*) as c')
            ->groupBy('cekirdex_product_id')
            ->get()
            ->mapWithKeys(fn ($row) => [$row->cekirdex_product_id => ['avg' => round((float) $row->avg, 1), 'count' => (int) $row->c]])
            ->all();

        $customer = Auth::guard('cekirdex_customer')->user();
        $isOpen = $restaurant->isOpenNow();

        $products = $products->groupBy('cekirdex_category_id');

        return view('cekirdex.customer.restaurant-public', compact(
            'restaurant', 'categories', 'products',
            'likeCounts', 'reviewCounts', 'ratingAverages', 'customer', 'isOpen'
        ));
    }
}
