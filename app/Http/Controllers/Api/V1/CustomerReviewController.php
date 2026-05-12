<?php

namespace App\Http\Controllers\Api\V1;

use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductReview;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerReviewController extends Controller
{
    public function index(string $qrToken, int $productId): JsonResponse
    {
        $table   = $this->findTable($qrToken);
        $product = $this->findProduct($productId, $table->cekirdex_restaurant_id);

        $reviews = CekirdexProductReview::query()
            ->with('user:id,name')
            ->where('cekirdex_product_id', $product->id)
            ->whereNull('hidden_by_user_id')
            ->where('is_visible', true)
            ->orderByDesc('created_at')
            ->get()
            ->map(fn ($review) => [
                'id'         => $review->id,
                'rating'     => $review->rating,
                'content'    => $review->content,
                'user_name'  => $review->user?->name,
                'created_at' => $review->created_at?->toIso8601String(),
            ]);

        return response()->json(['reviews' => $reviews]);
    }

    public function store(Request $request, string $qrToken, int $productId): JsonResponse
    {
        $table   = $this->findTable($qrToken);
        $product = $this->findProduct($productId, $table->cekirdex_restaurant_id);

        /** @var CekirdexCustomerUser $actor */
        $actor = $request->attributes->get('api_actor');

        $data = $request->validate([
            'rating'  => ['required', 'integer', 'min:1', 'max:5'],
            'content' => ['nullable', 'string', 'max:500'],
        ]);

        $review = CekirdexProductReview::query()->updateOrCreate(
            [
                'cekirdex_customer_user_id' => $actor->id,
                'cekirdex_product_id'       => $product->id,
            ],
            [
                'cekirdex_restaurant_id' => $product->cekirdex_restaurant_id,
                'rating'                 => $data['rating'],
                'content'                => $data['content'] ?? null,
                'is_visible'             => true,
                'hidden_by_user_id'      => null,
                'hidden_at'              => null,
                'ip_address'             => $request->ip(),
            ]
        );

        return response()->json([
            'message' => 'Yorumunuz kaydedildi.',
            'review'  => [
                'id'         => $review->id,
                'rating'     => $review->rating,
                'content'    => $review->content,
                'created_at' => $review->created_at?->toIso8601String(),
            ],
        ], 201);
    }

    public function destroy(Request $request, string $qrToken, int $productId, int $reviewId): JsonResponse
    {
        $table = $this->findTable($qrToken);
        $this->findProduct($productId, $table->cekirdex_restaurant_id);

        /** @var CekirdexCustomerUser $actor */
        $actor = $request->attributes->get('api_actor');

        $review = CekirdexProductReview::query()
            ->where('id', $reviewId)
            ->where('cekirdex_product_id', $productId)
            ->where('cekirdex_customer_user_id', $actor->id)
            ->firstOrFail();

        $review->delete();

        return response()->json(['message' => 'Yorum silindi.']);
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
