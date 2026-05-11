<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        return response()->json(['data' => $this->readyOrders($this->restaurantId($request))]);
    }

    public function feed(Request $request): JsonResponse
    {
        return response()->json([
            'ok'    => true,
            'ts'    => now()->toIso8601String(),
            'orders' => $this->readyOrders($this->restaurantId($request)),
        ]);
    }

    public function serve(Request $request, int $orderId): JsonResponse
    {
        $order = CekirdexOrder::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($orderId);

        if ($order->status !== 'ready') {
            return response()->json(['message' => 'Bu sipariş servis için hazır değil.'], 422);
        }

        $order->update(['status' => 'served']);

        return response()->json(['message' => 'Sipariş servis edildi olarak işaretlendi.']);
    }

    private function readyOrders(int $restaurantId): array
    {
        return CekirdexOrder::query()
            ->with('table', 'items')
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->where('status', 'ready')
            ->where('order_type', CekirdexOrder::TYPE_DINE_IN)
            ->orderBy('ready_at')
            ->get()
            ->map(fn (CekirdexOrder $order) => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'table'        => $order->table?->name,
                'ready_at'     => $order->ready_at?->toIso8601String(),
                'created_at'   => $order->created_at?->toIso8601String(),
                'items'        => $order->items->map(fn ($item) => [
                    'name'     => $item->name,
                    'quantity' => $item->quantity,
                ])->values(),
            ])
            ->values()
            ->all();
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
