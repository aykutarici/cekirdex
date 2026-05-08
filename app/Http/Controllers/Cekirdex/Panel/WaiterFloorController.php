<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexOrder;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Garson / servis ekranı — yeni siparişleri mutfağa iletir, hazır ürünleri teslim olarak kapatır.
 */
class WaiterFloorController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    public function index()
    {
        return view('cekirdex.panel.service.index');
    }

    public function feed(Request $request)
    {
        $rid = $this->rid();

        $orders = CekirdexOrder::with(['table', 'items'])
            ->where('cekirdex_restaurant_id', $rid)
            ->whereIn('status', ['new', 'ready'])
            ->orderBy('created_at')
            ->get();

        $payload = $orders->map(function ($o) {
            $place = $o->order_type === CekirdexOrder::TYPE_DINE_IN
                ? (optional($o->table)->name ?? '—')
                : trim($o->type_label.(($o->contact_name || $o->guest_name) ? ' · '.($o->contact_name ?: $o->guest_name) : ''));

            return [
                'id'             => $o->id,
                'order_number'   => $o->order_number,
                'table'          => $place ?: '—',
                'order_type'     => $o->order_type,
                'type_label'     => $o->type_label,
                'status'         => $o->status,
                'status_label'   => $o->status_label,
                'total'          => (float) $o->total,
                'note'           => $o->note,
                'guest_name'     => $o->guest_name,
                'created_at'     => $o->created_at->toIso8601String(),
                'created_human'  => $o->created_at->diffForHumans(null, true),
                'minutes_ago'    => (int) $o->created_at->diffInMinutes(now()),
                'items'          => $o->items->map(fn ($it) => [
                    'name'     => $it->name,
                    'quantity' => $it->quantity,
                    'note'     => $it->note,
                ])->values(),
            ];
        });

        return response()->json([
            'ok'         => true,
            'count'      => $payload->count(),
            'orders'     => $payload,
            'fetched_at' => now()->toIso8601String(),
        ]);
    }

    public function advance(Request $request, int $id)
    {
        $order = CekirdexOrder::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $next = match ($order->status) {
            'new'   => 'confirmed',
            'ready' => 'served',
            default => $order->status,
        };
        if ($next === $order->status) {
            return response()->json([
                'ok'      => false,
                'message' => 'Bu sipariş garson ekranında bu adımla ilerletilemez.',
            ], 422);
        }
        $order->update(['status' => $next]);

        return response()->json(['ok' => true, 'status' => $next]);
    }

    public function setStatus(Request $request, int $id)
    {
        $data = $request->validate([
            'status' => 'required|in:'.implode(',', array_keys(CekirdexOrder::STATUSES)),
        ]);
        $order = CekirdexOrder::where('cekirdex_restaurant_id', $this->rid())->findOrFail($id);
        $order->update(['status' => $data['status']]);

        return response()->json(['ok' => true, 'status' => $data['status']]);
    }
}
