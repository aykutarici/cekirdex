<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class KdsController extends Controller
{
    private const KDS_STATUSES = ['confirmed', 'preparing', 'ready'];

    private const NEXT_STATUS = [
        'confirmed' => 'preparing',
        'preparing' => 'ready',
        'ready'     => 'served',
    ];

    public function index(Request $request): JsonResponse
    {
        $orders = $this->kitchenOrders($this->restaurantId($request));

        return response()->json(['data' => $orders]);
    }

    public function feed(Request $request): JsonResponse
    {
        return response()->json([
            'ok'    => true,
            'ts'    => now()->toIso8601String(),
            'orders' => $this->kitchenOrders($this->restaurantId($request)),
        ]);
    }

    public function advance(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findOrder($request, $orderId);

        $next = self::NEXT_STATUS[$order->status] ?? null;
        if (!$next) {
            return response()->json(['message' => 'Bu sipariş artık ilerletilemez.'], 422);
        }

        $patch = ['status' => $next];
        if ($next === 'ready') {
            $patch['ready_at'] = now();
        }
        $order->update($patch);

        return response()->json([
            'message' => 'Sipariş durumu güncellendi.',
            'status'  => $order->status,
        ]);
    }

    public function cancel(Request $request, int $orderId): JsonResponse
    {
        $order = $this->findOrder($request, $orderId);

        if ($order->status === 'cancelled') {
            return response()->json(['message' => 'Sipariş zaten iptal edilmiş.'], 422);
        }

        $order->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Sipariş iptal edildi.']);
    }

    private function kitchenOrders(int $restaurantId): array
    {
        return CekirdexOrder::query()
            ->with('items.product', 'table')
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->whereIn('status', self::KDS_STATUSES)
            ->orderBy('created_at')
            ->get()
            ->map(fn (CekirdexOrder $order) => [
                'id'           => $order->id,
                'order_number' => $order->order_number,
                'status'       => $order->status,
                'status_label' => $order->status_label,
                'table'        => $order->table?->name,
                'note'         => $order->note,
                'created_at'   => $order->created_at?->toIso8601String(),
                'items'        => $order->items->map(fn ($item) => [
                    'id'            => $item->id,
                    'name'          => $item->name,
                    'quantity'      => $item->quantity,
                    'variant_label' => $item->variant_label,
                    'note'          => $item->note,
                ])->values(),
            ])
            ->values()
            ->all();
    }

    private function findOrder(Request $request, int $orderId): CekirdexOrder
    {
        return CekirdexOrder::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($orderId);
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
