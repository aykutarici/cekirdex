<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductFavorite;
use App\Cekirdex\Models\CekirdexProductLike;
use App\Cekirdex\Models\CekirdexProductReview;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function show(string $qrToken)
    {
        $table = CekirdexTable::where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();

        $restaurant = $table->restaurant;
        if (!$restaurant || !$restaurant->is_active) {
            abort(404);
        }

        $categories = CekirdexCategory::where('cekirdex_restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')->get();

        $products = CekirdexProduct::where('cekirdex_restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with('variants')
            ->orderBy('sort_order')->orderBy('id')
            ->get();

        // Beğeni ve yorum sayılarını ürün ID'lerine göre topla (tek query)
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
            ->mapWithKeys(fn ($row) => [$row->cekirdex_product_id => ['avg' => round((float)$row->avg, 1), 'count' => (int)$row->c]])
            ->all();

        // Login müşterinin favori/beğen durumları
        $myLikes = []; $myFavs = [];
        $customer = Auth::guard('cekirdex_customer')->user();
        if ($customer) {
            $myLikes = CekirdexProductLike::where('cekirdex_customer_user_id', $customer->id)
                ->whereIn('cekirdex_product_id', $productIds)
                ->pluck('cekirdex_product_id')->all();
            $myFavs = CekirdexProductFavorite::where('cekirdex_customer_user_id', $customer->id)
                ->whereIn('cekirdex_product_id', $productIds)
                ->pluck('cekirdex_product_id')->all();
        }
        $hasOrderedHere = $customer ? $customer->hasOrderedAt($restaurant->id) : false;

        $products = $products->groupBy('cekirdex_category_id');

        return view('cekirdex.customer.menu', compact(
            'table', 'restaurant', 'categories', 'products',
            'likeCounts', 'reviewCounts', 'ratingAverages',
            'myLikes', 'myFavs', 'customer', 'hasOrderedHere'
        ));
    }
}
