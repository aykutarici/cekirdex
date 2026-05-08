<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductFavorite;
use App\Cekirdex\Models\CekirdexProductLike;
use App\Cekirdex\Models\CekirdexProductReview;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Müşterinin ürünlere reaksiyonları (beğen, favori, yorum sayıları).
 * Beğen/yorum için: login + ilgili restoranda en az 1 sipariş şartı.
 * Favori için: sadece login yeterli.
 */
class ProductReactionController extends Controller
{
    /** Belirli bir ürünün toplam beğen/fav/yorum sayısı + login kullanıcının durumu. */
    public function summary(Request $request, $qrToken, $id)
    {
        $product = CekirdexProduct::findOrFail((int) $id);
        $u = Auth::guard('cekirdex_customer')->user();

        $likeCount = $product->likes()->count();
        $reviewCount = $product->reviews()->visible()->count();

        $userState = ['liked' => false, 'favorited' => false, 'eligible' => false];
        if ($u) {
            $userState['liked']     = CekirdexProductLike::where('cekirdex_customer_user_id', $u->id)->where('cekirdex_product_id', $product->id)->exists();
            $userState['favorited'] = CekirdexProductFavorite::where('cekirdex_customer_user_id', $u->id)->where('cekirdex_product_id', $product->id)->exists();
            $userState['eligible']  = $u->hasOrderedAt($product->cekirdex_restaurant_id);
        }

        return response()->json([
            'ok'           => true,
            'like_count'   => $likeCount,
            'review_count' => $reviewCount,
            'user'         => $userState,
        ]);
    }

    /** Ürün beğenisini aç/kapa (toggle). Login + sipariş şartı. */
    public function toggleLike(Request $request, $qrToken, $id)
    {
        $u = Auth::guard('cekirdex_customer')->user();
        if (!$u) return response()->json(['ok' => false, 'message' => 'Giriş yapmalısınız.'], 401);

        $product = CekirdexProduct::findOrFail((int) $id);
        if (!$u->hasOrderedAt($product->cekirdex_restaurant_id)) {
            return response()->json(['ok' => false, 'message' => 'Beğenmek için bu restoranda en az bir sipariş vermiş olmalısınız.'], 403);
        }

        $like = CekirdexProductLike::where('cekirdex_customer_user_id', $u->id)
            ->where('cekirdex_product_id', $product->id)->first();
        if ($like) {
            $like->delete();
            $liked = false;
        } else {
            CekirdexProductLike::create([
                'cekirdex_customer_user_id' => $u->id,
                'cekirdex_product_id'       => $product->id,
                'cekirdex_restaurant_id'    => $product->cekirdex_restaurant_id,
            ]);
            $liked = true;
        }
        return response()->json([
            'ok'         => true,
            'liked'      => $liked,
            'like_count' => $product->likes()->count(),
        ]);
    }

    /** Favoriye ekle/kaldır. Sadece login yeterli. */
    public function toggleFavorite(Request $request, $qrToken, $id)
    {
        $u = Auth::guard('cekirdex_customer')->user();
        if (!$u) return response()->json(['ok' => false, 'message' => 'Giriş yapmalısınız.'], 401);

        $product = CekirdexProduct::findOrFail((int) $id);
        $fav = CekirdexProductFavorite::where('cekirdex_customer_user_id', $u->id)
            ->where('cekirdex_product_id', $product->id)->first();
        if ($fav) {
            $fav->delete();
            $favorited = false;
        } else {
            CekirdexProductFavorite::create([
                'cekirdex_customer_user_id' => $u->id,
                'cekirdex_product_id'       => $product->id,
                'cekirdex_restaurant_id'    => $product->cekirdex_restaurant_id,
            ]);
            $favorited = true;
        }
        return response()->json(['ok' => true, 'favorited' => $favorited]);
    }

    /** Login müşterinin bu restorandaki favori ürün ID'leri. */
    public function myFavorites(Request $request, string $qrToken)
    {
        $table = CekirdexTable::where('qr_token', $qrToken)->firstOrFail();
        $u = Auth::guard('cekirdex_customer')->user();
        if (!$u) return response()->json(['ok' => true, 'favorites' => []]);

        $ids = CekirdexProductFavorite::where('cekirdex_customer_user_id', $u->id)
            ->where('cekirdex_restaurant_id', $table->cekirdex_restaurant_id)
            ->pluck('cekirdex_product_id')->all();

        return response()->json(['ok' => true, 'favorites' => $ids]);
    }
}
