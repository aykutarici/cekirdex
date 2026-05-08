<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexProductReview;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    public function index(Request $request)
    {
        $rid = $this->rid();
        $q = CekirdexProductReview::with(['product:id,name,image', 'user:id,name,phone'])
            ->where('cekirdex_restaurant_id', $rid)
            ->latest();

        if ($request->filled('product_id')) {
            $q->where('cekirdex_product_id', (int) $request->input('product_id'));
        }
        if ($request->filled('hidden')) {
            $q->where('is_visible', $request->input('hidden') === '1' ? false : true);
        }

        $reviews = $q->paginate(30)->withQueryString();
        $products = CekirdexProduct::where('cekirdex_restaurant_id', $rid)
            ->select('id', 'name')->orderBy('name')->get();

        $stats = [
            'total'   => CekirdexProductReview::where('cekirdex_restaurant_id', $rid)->count(),
            'visible' => CekirdexProductReview::where('cekirdex_restaurant_id', $rid)->visible()->count(),
            'hidden'  => CekirdexProductReview::where('cekirdex_restaurant_id', $rid)->where('is_visible', false)->count(),
        ];

        return view('cekirdex.panel.reviews.index', compact('reviews', 'products', 'stats'));
    }

    /** Yorum sil (kalıcı). */
    public function destroy(int $id)
    {
        $rid = $this->rid();
        $review = CekirdexProductReview::where('cekirdex_restaurant_id', $rid)->findOrFail($id);
        $review->delete();
        return back()->with('success', 'Yorum silindi.');
    }

    /** Yorum gizle/göster (silmeden). */
    public function toggleVisibility(int $id)
    {
        $rid = $this->rid();
        $review = CekirdexProductReview::where('cekirdex_restaurant_id', $rid)->findOrFail($id);
        $review->update([
            'is_visible'        => !$review->is_visible,
            'hidden_by_user_id' => !$review->is_visible ? null : Auth::guard('cekirdex')->id(),
            'hidden_at'         => !$review->is_visible ? null : now(),
        ]);
        return back()->with('success', $review->is_visible ? 'Yorum yeniden gösteriliyor.' : 'Yorum gizlendi.');
    }
}
