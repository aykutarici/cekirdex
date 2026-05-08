<?php

namespace App\Http\Controllers\Cekirdex\Panel;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexOrderItem;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
 * Garson masada müşterinin sözlü siparişini paneline işler.
 * Yeni bir sipariş oluşturur (status=confirmed). Adisyon hesabı bu siparişe eklenir.
 */
class WaiterOrderController extends Controller
{
    private function rid(): int
    {
        return (int) Auth::guard('cekirdex')->user()->cekirdex_restaurant_id;
    }

    /** Yeni sipariş ekleme formu (masaya). */
    public function create(int $tableId)
    {
        $rid = $this->rid();
        $table = CekirdexTable::where('cekirdex_restaurant_id', $rid)->findOrFail($tableId);
        $categories = CekirdexCategory::where('cekirdex_restaurant_id', $rid)
            ->where('is_active', true)
            ->orderBy('sort_order')->orderBy('id')
            ->get();
        $products = CekirdexProduct::where('cekirdex_restaurant_id', $rid)
            ->where('is_active', true)
            ->with('variants')
            ->orderBy('cekirdex_category_id')->orderBy('sort_order')->orderBy('id')
            ->get()->groupBy('cekirdex_category_id');

        return view('cekirdex.panel.bills.waiter-order', compact('table', 'categories', 'products'));
    }

    /** Sipariş kaydet — JSON post. */
    public function store(Request $request, int $tableId)
    {
        $rid = $this->rid();
        $table = CekirdexTable::where('cekirdex_restaurant_id', $rid)->findOrFail($tableId);
        $restaurant = $table->restaurant;

        $data = $request->validate([
            'items'                 => 'required|array|min:1|max:80',
            'items.*.product_id'    => 'required|integer',
            'items.*.quantity'      => 'required|integer|min:1|max:50',
            'items.*.variant_id'    => 'nullable|integer',
            'items.*.note'          => 'nullable|string|max:500',
            'note'     => 'nullable|string|max:1000',
            'guest_name' => 'nullable|string|max:120',
        ]);

        $productIds = collect($data['items'])->pluck('product_id')->unique()->values();
        $products = CekirdexProduct::where('cekirdex_restaurant_id', $rid)
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
                return response()->json(['ok' => false, 'message' => 'Stokta değil: '.$p->name], 422);
            }
            $vid = isset($line['variant_id']) && $line['variant_id'] ? (int) $line['variant_id'] : null;
            $r = $p->resolveOrderLine($vid);
            if (!($r['ok'] ?? false)) {
                return response()->json(['ok' => false, 'message' => $r['message'] ?? 'Geçersiz seçenek'], 422);
            }
            $qty = (int) $line['quantity'];
            $unit = (float) $r['unit_price'];
            $resolved[] = [
                'p' => $p, 'qty' => $qty, 'unit' => $unit,
                'sub' => round($unit * $qty, 2),
                'note' => $line['note'] ?? null,
                'vid' => $r['variant']?->id,
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
                    'message' => $p->name.' için yeterli stok yok.',
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

                $order = CekirdexOrder::create([
                    'cekirdex_restaurant_id' => $restaurant->id,
                    'cekirdex_branch_id'     => $table->cekirdex_branch_id,
                    'cekirdex_table_id'      => $table->id,
                    'order_number'   => CekirdexOrder::newOrderNumber(),
                    'guest_name'     => $data['guest_name'] ?? 'Garson kaydı',
                    'subtotal'       => $subtotal,
                    'tax'            => $tax,
                    'service_charge' => $svc,
                    'total'          => $total,
                    'status'         => 'confirmed',
                    'payment_status' => 'pending',
                    'note'           => $data['note'] ?? null,
                    'ip_address'     => $request->ip(),
                    'user_agent'     => 'panel-waiter',
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
                return response()->json(['ok' => false, 'message' => 'Stokta değil: '.substr($msg, 4)], 422);
            }
            if (str_starts_with($msg, 'stock:')) {
                return response()->json(['ok' => false, 'message' => substr($msg, 6).' için yeterli stok yok.'], 422);
            }
            throw $e;
        }

        return response()->json([
            'ok'           => true,
            'message'      => 'Sipariş adisyona eklendi.',
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'redirect'     => route('cekirdex.panel.bills.show', $table->id),
        ]);
    }
}
