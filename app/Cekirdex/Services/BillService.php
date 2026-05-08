<?php

namespace App\Cekirdex\Services;

use App\Cekirdex\Models\CekirdexPayment;
use App\Cekirdex\Models\CekirdexTable;

/**
 * Masa hesabını derler.
 *
 * Bir masada hesap kapanmamış (closed/cancelled olmayan) tüm siparişler bir
 * "açık adisyon" olarak değerlendirilir. Ödemeler bu adisyonun toplamına karşı
 * yapılır; tek tek ödemeler birikir, kalan tutar düşer.
 */
class BillService
{
    /**
     * Masanın güncel hesap özeti.
     *
     * @return array{
     *   subtotal: float,
     *   tax: float,
     *   service_charge: float,
     *   total: float,
     *   paid: float,
     *   remaining: float,
     *   currency: string,
     *   tax_rate: float,
     *   service_charge_rate: float,
     *   orders: array<int, array>,
     *   items: array<int, array>,
     *   payments: array<int, array>,
     *   has_open_orders: bool
     * }
     */
    public function summary(CekirdexTable $table): array
    {
        $orders = $table->activeOrders()->with('items')->orderBy('id')->get();

        $subtotal = 0.0; $tax = 0.0; $svc = 0.0; $total = 0.0;
        $items = [];

        foreach ($orders as $order) {
            $subtotal += (float) $order->subtotal;
            $tax      += (float) $order->tax;
            $svc      += (float) $order->service_charge;
            $total    += (float) $order->total;

            foreach ($order->items as $it) {
                // Aynı ürünü birden fazla siparişte birleştirme: kalemleri tek tek tut, ID'lerle.
                $items[] = [
                    'id'           => $it->id,
                    'order_id'     => $order->id,
                    'order_number' => $order->order_number,
                    'name'         => $it->name,
                    'quantity'     => (int) $it->quantity,
                    'unit_price'   => (float) $it->price,
                    'subtotal'     => (float) $it->subtotal,
                    'note'         => $it->note,
                    // Bu kaleme atfedilebilecek "ödenmiş quantity" — items modunda kullanılır
                    'paid_quantity' => $this->paidQuantityForItem($table->id, $it->id),
                ];
            }
        }

        $paid = (float) CekirdexPayment::where('cekirdex_table_id', $table->id)
            ->where('status', 'paid')
            ->sum('amount');

        $remaining = max(0, round($total - $paid, 2));

        $payments = CekirdexPayment::where('cekirdex_table_id', $table->id)
            ->where('status', 'paid')
            ->orderBy('id')
            ->get()
            ->map(fn ($p) => [
                'id'           => $p->id,
                'amount'       => (float) $p->amount,
                'method'       => $p->method,
                'method_label' => $p->method_label,
                'split_mode'   => $p->split_mode,
                'portion'      => $p->portion_label,
                'payer'        => $p->payer_name,
                'paid_at'      => optional($p->paid_at)->toIso8601String(),
            ])->values()->all();

        return [
            'subtotal'            => round($subtotal, 2),
            'tax'                 => round($tax, 2),
            'service_charge'      => round($svc, 2),
            'total'               => round($total, 2),
            'paid'                => round($paid, 2),
            'remaining'           => $remaining,
            'currency'            => $table->restaurant->currency ?? 'TRY',
            'tax_rate'            => (float) ($table->restaurant->tax_rate ?? 0),
            'service_charge_rate' => (float) ($table->restaurant->service_charge_rate ?? 0),
            'orders'              => $orders->map(fn ($o) => [
                'id'           => $o->id,
                'order_number' => $o->order_number,
                'status'       => $o->status,
                'status_label' => $o->status_label,
                'total'        => (float) $o->total,
                'created_at'   => $o->created_at->toIso8601String(),
            ])->values()->all(),
            'items'               => $items,
            'payments'            => $payments,
            'has_open_orders'     => $orders->isNotEmpty(),
        ];
    }

    /** Bir order_item'a karşılık ödenmiş quantity. items modu için kullanılır. */
    public function paidQuantityForItem(int $tableId, int $orderItemId): int
    {
        $payments = CekirdexPayment::where('cekirdex_table_id', $tableId)
            ->where('status', 'paid')
            ->where('split_mode', CekirdexPayment::SPLIT_ITEMS)
            ->whereNotNull('selected_items')
            ->get();
        $total = 0;
        foreach ($payments as $p) {
            foreach (($p->selected_items ?? []) as $sel) {
                if ((int) ($sel['order_item_id'] ?? 0) === $orderItemId) {
                    $total += (int) ($sel['quantity'] ?? 0);
                }
            }
        }
        return $total;
    }
}
