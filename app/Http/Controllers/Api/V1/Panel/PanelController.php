<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexProductReview;
use App\Cekirdex\Models\CekirdexReservation;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PanelController extends Controller
{
    public function dashboard(Request $request): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);

        return response()->json([
            'metrics' => [
                'orders' => CekirdexOrder::query()->where('cekirdex_restaurant_id', $restaurantId)->count(),
                'tables' => CekirdexTable::query()->where('cekirdex_restaurant_id', $restaurantId)->count(),
                'reservations' => CekirdexReservation::query()->where('cekirdex_restaurant_id', $restaurantId)->count(),
                'staff' => CekirdexUser::query()->where('cekirdex_restaurant_id', $restaurantId)->count(),
            ],
            'recent_orders' => CekirdexOrder::query()
                ->with('table')
                ->where('cekirdex_restaurant_id', $restaurantId)
                ->latest()
                ->limit(5)
                ->get()
                ->map(fn (CekirdexOrder $order) => $this->orderSummary($order)),
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        return response()->json([
            'data' => CekirdexOrder::query()
                ->with('table', 'items')
                ->where('cekirdex_restaurant_id', $this->restaurantId($request))
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn (CekirdexOrder $order) => $this->orderSummary($order)),
        ]);
    }

    public function menu(Request $request): JsonResponse
    {
        return response()->json([
            'categories' => CekirdexCategory::query()
                ->with('products')
                ->where('cekirdex_restaurant_id', $this->restaurantId($request))
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function tables(Request $request): JsonResponse
    {
        return response()->json([
            'data' => CekirdexTable::query()
                ->where('cekirdex_restaurant_id', $this->restaurantId($request))
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function staff(Request $request): JsonResponse
    {
        return response()->json([
            'data' => CekirdexUser::query()
                ->where('cekirdex_restaurant_id', $this->restaurantId($request))
                ->orderBy('name')
                ->get(['id', 'name', 'email', 'phone', 'role', 'is_active', 'last_login_at']),
        ]);
    }

    public function reservations(Request $request): JsonResponse
    {
        return response()->json([
            'data' => CekirdexReservation::query()
                ->where('cekirdex_restaurant_id', $this->restaurantId($request))
                ->latest()
                ->limit(50)
                ->get(),
        ]);
    }

    public function reviews(Request $request): JsonResponse
    {
        return response()->json([
            'data' => CekirdexProductReview::query()
                ->with('product', 'user')
                ->where('cekirdex_restaurant_id', $this->restaurantId($request))
                ->latest()
                ->limit(50)
                ->get()
                ->map(fn (CekirdexProductReview $review) => [
                    'id' => $review->id,
                    'product' => $review->product?->name,
                    'customer' => $review->user?->name,
                    'content' => $review->content,
                    'rating' => $review->rating,
                    'is_visible' => $review->is_visible,
                    'created_at' => $review->created_at?->toIso8601String(),
                ]),
        ]);
    }

    public function settings(Request $request): JsonResponse
    {
        $actor = $this->actor($request);

        return response()->json([
            'restaurant' => $actor->restaurant,
        ]);
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

    private function orderSummary(CekirdexOrder $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'public_code' => $order->public_code,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'payment_status' => $order->payment_status,
            'total' => (float) $order->total,
            'table' => $order->table?->name,
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}
