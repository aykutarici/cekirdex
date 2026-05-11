<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexProductReview;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewApiController extends Controller
{
    public function destroy(Request $request, int $id): JsonResponse
    {
        $review = $this->findReview($request, $id);
        $review->delete();

        return response()->json(null, 204);
    }

    public function toggleVisibility(Request $request, int $id): JsonResponse
    {
        $actor  = $this->actor($request);
        $review = $this->findReview($request, $id);

        $review->is_visible = !$review->is_visible;

        if (!$review->is_visible) {
            $review->hidden_by_user_id = $actor->id;
            $review->hidden_at         = now();
        } else {
            $review->hidden_by_user_id = null;
            $review->hidden_at         = null;
        }

        $review->save();

        return response()->json([
            'is_visible' => $review->is_visible,
            'message'    => $review->is_visible ? 'Yorum görünür yapıldı.' : 'Yorum gizlendi.',
        ]);
    }

    private function findReview(Request $request, int $id): CekirdexProductReview
    {
        return CekirdexProductReview::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($id);
    }

    private function restaurantId(Request $request): int
    {
        return (int) $this->actor($request)->cekirdex_restaurant_id;
    }

    private function actor(Request $request): CekirdexUser
    {
        $actor = $request->attributes->get('api_actor');
        abort_unless($actor instanceof CekirdexUser, 403, 'Bu endpoint restoran personeli içindir.');
        return $actor;
    }
}
