<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexCall;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexOrderItem;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class OrderController extends Controller
{
    public function place(Request $request, string $qrToken)
    {
        $key = 'cekirdex-order:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 8)) {
            return response()->json(['ok' => false, 'message' => 'Çok hızlı sipariş gönderildi.'], 429);
        }
        RateLimiter::hit($key, 60);

        $table = CekirdexTable::where('qr_token', $qrToken)->where('is_active', true)->firstOrFail();
        $restaurant = $table->restaurant;
        if (!$restaurant || !$restaurant->is_active) {
            return response()->json(['ok' => false, 'message' => 'Restoran şu anda sipariş alamıyor.'], 422);
        }

        $data = $request->validate([
            'items'                 => 'required|array|min:1|max:50',
            'items.*.product_id'    => 'required|integer|exists:cekirdex_products,id',
            'items.*.quantity'      => 'required|integer|min:1|max:50',
            'items.*.variant_id'    => 'nullable|integer',
            'items.*.note'          => 'nullable|string|max:500',
            'guest_name'            => 'nullable|string|max:120',
            'guest_phone'           => 'nullable|string|max:32',
            'note'                  => 'nullable|string|max:1000',
        ]);

        // Restoranın ürünleri olduğundan emin ol — fiyatları DB'den al (client manipülasyonuna karşı)
        $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
        $products = CekirdexProduct::where('cekirdex_restaurant_id', $restaurant->id)
            ->where('is_active', true)
            ->with('variants')
            ->whereIn('id', $productIds)->get()->keyBy('id');

        if ($products->count() !== $productIds->count()) {
            return response()->json(['ok' => false, 'message' => 'Bazı ürünler artık menüde değil.'], 422);
        }

        $resolved = [];
        foreach ($data['items'] as $line) {
            $p = $products[$line['product_id']];
            if (!$p->isAvailable()) {
                return response()->json([
                    'ok' => false,
                    'message' => 'Şu ürün stokta değil: '.$p->name,
                ], 422);
            }
            $vid = isset($line['variant_id']) && $line['variant_id'] ? (int) $line['variant_id'] : null;
            $r = $p->resolveOrderLine($vid);
            if (!($r['ok'] ?? false)) {
                return response()->json(['ok' => false, 'message' => $r['message'] ?? 'Geçersiz ürün seçeneği.'], 422);
            }
            $qty = (int) $line['quantity'];
            $unit = (float) $r['unit_price'];
            $resolved[] = [
                'p'      => $p,
                'qty'    => $qty,
                'unit'   => $unit,
                'sub'    => round($unit * $qty, 2),
                'note'   => $line['note'] ?? null,
                'vid'    => $r['variant']?->id,
                'vlabel' => $r['variant_label'],
            ];
        }

        $needByPid = [];
        foreach ($resolved as $row) {
            $pid = $row['p']->id;
            $needByPid[$pid] = ($needByPid[$pid] ?? 0) + $row['qty'];
        }
        foreach ($needByPid as $pid => $need) {
            $p = $products[$pid];
            if ($p->track_stock && $p->stock_quantity !== null && $p->stock_quantity < $need) {
                return response()->json([
                    'ok' => false,
                    'message' => $p->name.' için yeterli stok yok (kalan: '.$p->stock_quantity.').',
                ], 422);
            }
        }

        try {
            $order = DB::transaction(function () use ($data, $request, $table, $restaurant, $resolved, $needByPid) {
                $locked = CekirdexProduct::whereIn('id', array_keys($needByPid))
                    ->where('cekirdex_restaurant_id', $restaurant->id)
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                foreach ($needByPid as $pid => $need) {
                    $p = $locked[$pid];
                    if (!$p->isAvailable()) {
                        throw new \RuntimeException('oos:'.$p->name);
                    }
                    if ($p->track_stock && $p->stock_quantity !== null && $p->stock_quantity < $need) {
                        throw new \RuntimeException('stock:'.$p->name);
                    }
                }

                $subtotal = 0.0;
                foreach ($resolved as $row) {
                    $subtotal += $row['sub'];
                }
                $tax = round($subtotal * ((float) $restaurant->tax_rate / 100), 2);
                $svc = round($subtotal * ((float) $restaurant->service_charge_rate / 100), 2);
                $total = round($subtotal + $tax + $svc, 2);

                $customerUserId = Auth::guard('cekirdex_customer')->id();
                $order = CekirdexOrder::create([
                    'cekirdex_restaurant_id'    => $restaurant->id,
                    'cekirdex_branch_id'        => $table->cekirdex_branch_id,
                    'cekirdex_table_id'         => $table->id,
                    'cekirdex_customer_user_id' => $customerUserId,
                    'order_number'   => CekirdexOrder::newOrderNumber(),
                    'guest_name'     => $data['guest_name']  ?? null,
                    'guest_phone'    => $data['guest_phone'] ?? null,
                    'subtotal'       => $subtotal,
                    'tax'            => $tax,
                    'service_charge' => $svc,
                    'total'          => $total,
                    'status'         => 'new',
                    'payment_status' => 'pending',
                    'note'           => $data['note'] ?? null,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => substr((string) $request->userAgent(), 0, 500),
                ]);

                foreach ($resolved as $r) {
                    $p = $r['p'];
                    $dispName = $r['vlabel'] ? $p->name.' · '.$r['vlabel'] : $p->name;
                    CekirdexOrderItem::create([
                        'cekirdex_order_id'           => $order->id,
                        'cekirdex_product_id'         => $p->id,
                        'cekirdex_product_variant_id' => $r['vid'],
                        'variant_label'               => $r['vlabel'],
                        'name'     => $dispName,
                        'price'    => $r['unit'],
                        'quantity' => $r['qty'],
                        'subtotal' => $r['sub'],
                        'note'     => $r['note'],
                        'status'   => 'pending',
                    ]);
                }

                foreach ($needByPid as $pid => $need) {
                    $p = $locked[$pid];
                    if ($p->track_stock && $p->stock_quantity !== null) {
                        $p->decrement('stock_quantity', $need);
                        $p->refresh();
                        if ($p->stock_quantity <= 0) {
                            $p->is_in_stock = false;
                            $p->save();
                        }
                    }
                }

                return $order;
            });
        } catch (\RuntimeException $e) {
            $msg = $e->getMessage();
            if (str_starts_with($msg, 'oos:')) {
                return response()->json(['ok' => false, 'message' => 'Şu ürün stokta değil: '.substr($msg, 4)], 422);
            }
            if (str_starts_with($msg, 'stock:')) {
                return response()->json(['ok' => false, 'message' => substr($msg, 6).' için yeterli stok kalmadı.'], 422);
            }
            throw $e;
        }

        return response()->json([
            'ok'           => true,
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'total'        => $order->total,
            'message'      => 'Siparişiniz mutfağa iletildi!',
        ]);
    }

    public function call(Request $request, string $qrToken)
    {
        $key = 'cekirdex-call:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            return response()->json(['ok' => false, 'message' => 'Çok hızlı çağırdınız.'], 429);
        }
        RateLimiter::hit($key, 30);

        $table = CekirdexTable::where('qr_token', $qrToken)->where('is_active', true)->firstOrFail();
        $restaurant = $table->restaurant;
        if (!$restaurant || !$restaurant->is_active) {
            abort(404);
        }

        $data = $request->validate([
            'call_type' => 'required|in:'.implode(',', array_keys(CekirdexCall::TYPES)),
            'message'   => 'nullable|string|max:500',
        ]);

        CekirdexCall::create([
            'cekirdex_restaurant_id' => $restaurant->id,
            'cekirdex_branch_id'     => $table->cekirdex_branch_id,
            'cekirdex_table_id'      => $table->id,
            'call_type'  => $data['call_type'],
            'message'    => $data['message'] ?? null,
            'status'     => 'pending',
            'ip_address' => $request->ip(),
        ]);

        return response()->json(['ok' => true, 'message' => 'Garsona iletildi!']);
    }

    public function status(Request $request, string $qrToken, int $orderId)
    {
        $table = CekirdexTable::where('qr_token', $qrToken)->firstOrFail();
        $order = CekirdexOrder::where('cekirdex_table_id', $table->id)
            ->where('id', $orderId)->firstOrFail();
        return response()->json([
            'ok'      => true,
            'status'  => $order->status,
            'label'   => $order->status_label,
            'total'   => $order->total,
        ]);
    }

    /**
     * Müşterinin yatırdığı id listesini alıp aktif olanların durumunu döner.
     * Body: { ids: [1, 2, 3] }
     */
    public function myOrders(Request $request, string $qrToken)
    {
        $table = CekirdexTable::where('qr_token', $qrToken)->firstOrFail();
        $ids = collect($request->input('ids', []))->filter(fn ($v) => is_numeric($v))->take(20)->values()->all();
        if (empty($ids)) {
            return response()->json(['ok' => true, 'orders' => []]);
        }
        $orders = CekirdexOrder::with('items')
            ->where('cekirdex_table_id', $table->id)
            ->whereIn('id', $ids)
            ->orderByDesc('id')
            ->get()
            ->map(fn ($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'status'       => $o->status,
                'label'        => $o->status_label,
                'total'        => (float) $o->total,
                'items'        => $o->items->map(fn ($it) => [
                    'name' => $it->name, 'quantity' => $it->quantity, 'subtotal' => (float) $it->subtotal,
                ])->values(),
                'created_at'   => $o->created_at->toIso8601String(),
                'minutes_ago'  => (int) $o->created_at->diffInMinutes(now()),
            ]);
        return response()->json(['ok' => true, 'orders' => $orders]);
    }
}
