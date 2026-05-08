<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductReview;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;

class ProductReviewController extends Controller
{
    /** Bir ürünün yorumlarını listele (en yeni önce). */
    public function index(Request $request, $qrToken, $id)
    {
        $product = CekirdexProduct::findOrFail((int) $id);
        $u = Auth::guard('cekirdex_customer')->user();

        $reviews = CekirdexProductReview::with('user:id,name,avatar')
            ->where('cekirdex_product_id', $product->id)
            ->visible()
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn ($r) => [
                'id'         => $r->id,
                'name'       => optional($r->user)->name ?? 'Misafir',
                'rating'     => $r->rating,
                'content'    => $r->content,
                'created_at' => $r->created_at->diffForHumans(),
                'is_mine'    => $u ? ($r->cekirdex_customer_user_id === $u->id) : false,
            ]);

        return response()->json([
            'ok'      => true,
            'reviews' => $reviews,
            'can_review' => $u ? $u->hasOrderedAt($product->cekirdex_restaurant_id) : false,
        ]);
    }

    /** Yorum yaz. Login + sipariş şartı. */
    public function store(Request $request, $qrToken, $id)
    {
        $u = Auth::guard('cekirdex_customer')->user();
        if (!$u) return response()->json(['ok' => false, 'message' => 'Giriş yapmalısınız.'], 401);

        $key = 'cekirdex-review:'.$u->id;
        if (RateLimiter::tooManyAttempts($key, 6)) {
            return response()->json(['ok' => false, 'message' => 'Çok fazla yorum. Bir dakika sonra tekrar deneyin.'], 429);
        }
        RateLimiter::hit($key, 60);

        $product = CekirdexProduct::findOrFail((int) $id);
        if (!$u->hasOrderedAt($product->cekirdex_restaurant_id)) {
            return response()->json(['ok' => false, 'message' => 'Yorum yapmak için bu restoranda en az bir sipariş vermiş olmalısınız.'], 403);
        }

        $data = $request->validate([
            'content' => 'required|string|min:3|max:1000',
            'rating'  => 'required|integer|min:1|max:5',
        ]);

        $review = CekirdexProductReview::create([
            'cekirdex_customer_user_id' => $u->id,
            'cekirdex_product_id'       => $product->id,
            'cekirdex_restaurant_id'    => $product->cekirdex_restaurant_id,
            'content'                   => trim($data['content']),
            'rating'                    => $data['rating'],
            'is_visible'                => true,
            'ip_address'                => $request->ip(),
        ]);

        return response()->json([
            'ok'     => true,
            'review' => [
                'id'         => $review->id,
                'name'       => $u->name,
                'rating'     => $review->rating,
                'content'    => $review->content,
                'created_at' => 'şimdi',
                'is_mine'    => true,
            ],
        ]);
    }

    /** Müşteri kendi yorumunu silsin. */
    public function destroy(Request $request, $qrToken, $reviewId)
    {
        $u = Auth::guard('cekirdex_customer')->user();
        if (!$u) return response()->json(['ok' => false, 'message' => 'Giriş yapmalısınız.'], 401);

        $review = CekirdexProductReview::where('cekirdex_customer_user_id', $u->id)->findOrFail((int) $reviewId);
        $review->delete();
        return response()->json(['ok' => true]);
    }
}
