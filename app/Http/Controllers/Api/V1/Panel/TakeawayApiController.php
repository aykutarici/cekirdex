<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexUser;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TakeawayApiController extends Controller
{
    private const TAKEAWAY_TYPES   = [CekirdexOrder::TYPE_TAKEAWAY, CekirdexOrder::TYPE_DELIVERY];
    private const ACTIVE_STATUSES  = ['new', 'confirmed', 'preparing', 'ready'];

    public function index(Request $request): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);
        $tab          = $request->query('tab', 'active');

        $base = CekirdexOrder::query()
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->whereIn('order_type', self::TAKEAWAY_TYPES)
            ->with('items')
            ->latest();

        $orders = match ($tab) {
            'completed' => $base->whereIn('status', ['delivered', 'served', 'closed'])->limit(50)->get(),
            'cancelled' => $base->where('status', 'cancelled')->limit(50)->get(),
            default     => $base->whereIn('status', self::ACTIVE_STATUSES)->limit(50)->get(),
        };

        $counts = [
            'active'    => CekirdexOrder::query()->where('cekirdex_restaurant_id', $restaurantId)->whereIn('order_type', self::TAKEAWAY_TYPES)->whereIn('status', self::ACTIVE_STATUSES)->count(),
            'completed' => CekirdexOrder::query()->where('cekirdex_restaurant_id', $restaurantId)->whereIn('order_type', self::TAKEAWAY_TYPES)->whereIn('status', ['delivered', 'served', 'closed'])->count(),
            'cancelled' => CekirdexOrder::query()->where('cekirdex_restaurant_id', $restaurantId)->whereIn('order_type', self::TAKEAWAY_TYPES)->where('status', 'cancelled')->count(),
        ];

        return response()->json([
            'data'   => $orders->map(fn (CekirdexOrder $o) => $this->orderPayload($o))->values(),
            'counts' => $counts,
        ]);
    }

    public function feed(Request $request): JsonResponse
    {
        $orders = CekirdexOrder::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->whereIn('order_type', self::TAKEAWAY_TYPES)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->latest()
            ->limit(50)
            ->get(['id', 'order_number', 'order_type', 'status', 'total', 'created_at']);

        return response()->json([
            'ok'    => true,
            'ts'    => now()->toIso8601String(),
            'orders' => $orders->map(fn (CekirdexOrder $o) => [
                'id'           => $o->id,
                'number'       => $o->order_number,
                'type'         => $o->type_label,
                'status'       => $o->status,
                'status_label' => $o->status_label,
                'total'        => (float) $o->total,
                'minutes_ago'  => (int) $o->created_at?->diffInMinutes(now()),
            ]),
        ]);
    }

    public function show(Request $request, int $id): JsonResponse
    {
        $order = $this->findOrder($request, $id);

        return response()->json(['data' => $this->orderPayload($order, true)]);
    }

    public function confirm(Request $request, int $id): JsonResponse
    {
        $order = $this->findOrder($request, $id);
        $data  = $request->validate(['eta_minutes' => 'required|integer|min:5|max:240']);

        if ($order->status !== 'new') {
            return response()->json(['message' => 'Bu sipariş artık onaylanamaz.'], 422);
        }

        $order->update([
            'status'      => 'confirmed',
            'eta_minutes' => $data['eta_minutes'],
        ]);

        return response()->json(['message' => 'Sipariş onaylandı.', 'status' => $order->status]);
    }

    public function advance(Request $request, int $id): JsonResponse
    {
        $order = $this->findOrder($request, $id);

        $next = match ($order->status) {
            'new'       => 'confirmed',
            'confirmed' => 'preparing',
            'preparing' => 'ready',
            'ready'     => 'delivered',
            default     => null,
        };

        if (!$next) {
            return response()->json(['message' => 'Sipariş durumu artık ilerletilemez.'], 422);
        }

        $patch = ['status' => $next];
        if ($next === 'ready')     $patch['ready_at']     = now();
        if ($next === 'delivered') $patch['delivered_at'] = now();

        $order->update($patch);

        return response()->json(['message' => 'Sipariş durumu güncellendi.', 'status' => $order->status]);
    }

    public function cancel(Request $request, int $id): JsonResponse
    {
        $order = $this->findOrder($request, $id);
        $data  = $request->validate(['reason' => 'nullable|string|max:300']);

        if ($order->status === 'cancelled') {
            return response()->json(['message' => 'Sipariş zaten iptal edilmiş.'], 422);
        }

        $order->update([
            'status' => 'cancelled',
            'note'   => trim(($order->note ? $order->note."\n" : '').'İPTAL: '.($data['reason'] ?? '')),
        ]);

        return response()->json(['message' => 'Sipariş iptal edildi.']);
    }

    private function orderPayload(CekirdexOrder $order, bool $withItems = false): array
    {
        $payload = [
            'id'             => $order->id,
            'order_number'   => $order->order_number,
            'public_code'    => $order->public_code,
            'order_type'     => $order->order_type,
            'type_label'     => $order->type_label,
            'status'         => $order->status,
            'status_label'   => $order->status_label,
            'contact_name'   => $order->contact_name,
            'contact_phone'  => $order->contact_phone,
            'contact_email'  => $order->contact_email,
            'delivery_address' => $order->delivery_address,
            'eta_minutes'    => $order->eta_minutes,
            'total'          => (float) $order->total,
            'note'           => $order->note,
            'created_at'     => $order->created_at?->toIso8601String(),
        ];

        if ($withItems && $order->relationLoaded('items')) {
            $payload['items'] = $order->items->map(fn ($item) => [
                'id'            => $item->id,
                'name'          => $item->name,
                'quantity'      => $item->quantity,
                'price'         => (float) $item->price,
                'subtotal'      => (float) $item->subtotal,
                'variant_label' => $item->variant_label,
                'note'          => $item->note,
            ])->values();
        }

        return $payload;
    }

    private function findOrder(Request $request, int $id): CekirdexOrder
    {
        return CekirdexOrder::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->whereIn('order_type', self::TAKEAWAY_TYPES)
            ->with('items')
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
