<?php

namespace App\Http\Controllers\Api\V1;

use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductFavorite;
use App\Cekirdex\Models\CekirdexProductLike;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerReactionController extends Controller
{
    public function toggleLike(Request $request, string $qrToken, int $productId): JsonResponse
    {
        $table   = $this->findTable($qrToken);
        $product = $this->findProduct($productId, $table->cekirdex_restaurant_id);

        /** @var CekirdexCustomerUser $actor */
        $actor = $request->attributes->get('api_actor');

        $existing = CekirdexProductLike::query()
            ->where('cekirdex_customer_user_id', $actor->id)
            ->where('cekirdex_product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $liked = false;
        } else {
            CekirdexProductLike::create([
                'cekirdex_customer_user_id' => $actor->id,
                'cekirdex_product_id'       => $product->id,
                'cekirdex_restaurant_id'    => $table->cekirdex_restaurant_id,
            ]);
            $liked = true;
        }

        return response()->json([
            'liked'      => $liked,
            'like_count' => CekirdexProductLike::where('cekirdex_product_id', $product->id)->count(),
        ]);
    }

    public function toggleFavorite(Request $request, string $qrToken, int $productId): JsonResponse
    {
        $table   = $this->findTable($qrToken);
        $product = $this->findProduct($productId, $table->cekirdex_restaurant_id);

        /** @var CekirdexCustomerUser $actor */
        $actor = $request->attributes->get('api_actor');

        $existing = CekirdexProductFavorite::query()
            ->where('cekirdex_customer_user_id', $actor->id)
            ->where('cekirdex_product_id', $product->id)
            ->first();

        if ($existing) {
            $existing->delete();
            $favorited = false;
        } else {
            CekirdexProductFavorite::create([
                'cekirdex_customer_user_id' => $actor->id,
                'cekirdex_product_id'       => $product->id,
                'cekirdex_restaurant_id'    => $table->cekirdex_restaurant_id,
            ]);
            $favorited = true;
        }

        return response()->json([
            'favorited'      => $favorited,
            'favorite_count' => CekirdexProductFavorite::where('cekirdex_product_id', $product->id)->count(),
        ]);
    }

    public function show(Request $request, string $qrToken, int $productId): JsonResponse
    {
        $table   = $this->findTable($qrToken);
        $product = $this->findProduct($productId, $table->cekirdex_restaurant_id);
        $actor   = $request->attributes->get('api_actor');

        $likeCount     = CekirdexProductLike::where('cekirdex_product_id', $product->id)->count();
        $favoriteCount = CekirdexProductFavorite::where('cekirdex_product_id', $product->id)->count();

        $liked     = false;
        $favorited = false;

        if ($actor instanceof CekirdexCustomerUser) {
            $liked     = CekirdexProductLike::where('cekirdex_customer_user_id', $actor->id)
                ->where('cekirdex_product_id', $product->id)->exists();
            $favorited = CekirdexProductFavorite::where('cekirdex_customer_user_id', $actor->id)
                ->where('cekirdex_product_id', $product->id)->exists();
        }

        return response()->json([
            'like_count'     => $likeCount,
            'favorite_count' => $favoriteCount,
            'liked'          => $liked,
            'favorited'      => $favorited,
        ]);
    }

    private function findTable(string $qrToken): CekirdexTable
    {
        return CekirdexTable::query()
            ->where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();
    }

    private function findProduct(int $productId, int $restaurantId): CekirdexProduct
    {
        return CekirdexProduct::query()
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->where('id', $productId)
            ->where('is_active', true)
            ->firstOrFail();
    }
}
