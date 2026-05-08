<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexPayment;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Services\BillService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class BillController extends Controller
{
    public function __construct(private BillService $bills) {}

    /**
     * Masanın güncel hesap özetini döner. Müşteri "Hesap" butonuna her bastığında çağrılır.
     */
    public function show(string $qrToken)
    {
        $table = CekirdexTable::where('qr_token', $qrToken)->firstOrFail();
        $summary = $this->bills->summary($table);
        return response()->json([
            'ok'    => true,
            'table' => ['id' => $table->id, 'name' => $table->name],
            'bill'  => $summary,
        ]);
    }

    /**
     * Müşteri ödeme oluşturur (sistemden öder). Üç mod:
     *
     *  - mode=full      → kalanın tamamı
     *  - mode=amount    → istenen tutar (validation: 0 < amount <= remaining)
     *  - mode=equal     → toplam paydan biri (parts >= 2, part_index >= 1)
     *  - mode=items     → seçilen kalemlerin tutarı; kalemler hâlâ ödenmemiş quantity'den düşülür.
     *
     * Şu anda gerçek ödeme entegrasyonu yok — `provider=simulated` ile direkt `paid` durumu yazılır.
     * iyzico/PayTR eklenince burada redirect/webhook akışı çalışacak.
     */
    public function pay(Request $request, string $qrToken)
    {
        $key = 'cekirdex-pay:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 12)) {
            return response()->json(['ok' => false, 'message' => 'Çok fazla deneme. Birazdan tekrar deneyin.'], 429);
        }
        RateLimiter::hit($key, 60);

        $data = $request->validate([
            'mode'          => 'required|in:full,amount,equal,items',
            'method'        => 'required|in:online_card,online_apple_pay,online_google_pay,bank_transfer,fast,qr',
            'payer_name'    => 'nullable|string|max:120',
            'amount'        => 'nullable|numeric|min:0.01',
            'split_parts'   => 'nullable|integer|min:2|max:20',
            'split_index'   => 'nullable|integer|min:1|max:20',
            'items'         => 'nullable|array|max:80',
            'items.*.order_item_id' => 'required_with:items|integer',
            'items.*.quantity'      => 'required_with:items|integer|min:1|max:50',
        ]);

        $table = CekirdexTable::where('qr_token', $qrToken)->where('is_active', true)->firstOrFail();
        $restaurant = $table->restaurant;
        if (!$restaurant || !$restaurant->is_active) {
            return response()->json(['ok' => false, 'message' => 'Restoran şu anda ödeme alamıyor.'], 422);
        }

        $summary = $this->bills->summary($table);
        if (!$summary['has_open_orders']) {
            return response()->json(['ok' => false, 'message' => 'Açık adisyon yok.'], 422);
        }
        $remaining = (float) $summary['remaining'];
        if ($remaining <= 0) {
            return response()->json(['ok' => false, 'message' => 'Hesap zaten tamamen ödenmiş.'], 422);
        }

        // Mod'a göre tutar ve metadata hesapla
        $portion = null; $selectedItems = null; $parts = null; $partIndex = null;

        switch ($data['mode']) {
            case 'full':
                $amount = $remaining;
                $portion = 'Kalan tüm hesap';
                break;

            case 'amount':
                if (empty($data['amount'])) {
                    return response()->json(['ok' => false, 'message' => 'Tutar gerekli.'], 422);
                }
                $amount = round((float) $data['amount'], 2);
                if ($amount <= 0 || $amount > $remaining + 0.01) {
                    return response()->json(['ok' => false, 'message' => 'Tutar 0 ile kalan ('.number_format($remaining,2,',','.').' ₺) arasında olmalı.'], 422);
                }
                $portion = number_format($amount, 2, ',', '.').' ₺ kısmi ödeme';
                break;

            case 'equal':
                $parts     = (int) ($data['split_parts'] ?? 0);
                $partIndex = (int) ($data['split_index'] ?? 0);
                if ($parts < 2 || $partIndex < 1 || $partIndex > $parts) {
                    return response()->json(['ok' => false, 'message' => 'Eşit bölme için geçerli pay sayısı ve indeks gerekli.'], 422);
                }
                // Toplam hesabı parts'e böl, her pay = total / parts. Bu pay henüz ödenmemişse al.
                $partAmount = round($summary['total'] / $parts, 2);
                if ($partAmount > $remaining + 0.01) {
                    // Kalan, paydan az olabilir (başka biri kısmen ödediyse) — kalanı al
                    $partAmount = $remaining;
                }
                $amount  = $partAmount;
                $portion = $parts.' kişiden '.$partIndex.'. pay';
                break;

            case 'items':
            default:
                if (empty($data['items'])) {
                    return response()->json(['ok' => false, 'message' => 'En az bir ürün seçin.'], 422);
                }
                // Doğrula: kalemler bu masanın açık siparişlerinde mi ve kalan miktar yetiyor mu?
                $itemMap = collect($summary['items'])->keyBy('id');
                $amount = 0.0;
                $selectedItems = [];
                foreach ($data['items'] as $sel) {
                    $iid = (int) $sel['order_item_id'];
                    $qty = (int) $sel['quantity'];
                    $row = $itemMap->get($iid);
                    if (!$row) {
                        return response()->json(['ok' => false, 'message' => 'Bazı kalemler artık geçerli değil.'], 422);
                    }
                    $remainingQty = $row['quantity'] - $row['paid_quantity'];
                    if ($qty > $remainingQty) {
                        return response()->json(['ok' => false, 'message' => $row['name'].' için seçilen miktar mevcut olandan fazla.'], 422);
                    }
                    $unitNet = $row['unit_price']; // ürün net fiyatı
                    $amount += $qty * $unitNet;
                    $selectedItems[] = [
                        'order_item_id' => $iid,
                        'quantity'      => $qty,
                        'unit_price'    => $unitNet,
                        'name'          => $row['name'],
                    ];
                }
                // Items modunda subtotal'a vergi/servis oranı uygula (toplam tutar tutarlı kalsın)
                $subtotalAll = (float) $summary['subtotal'] ?: 1.0;
                $totalAll    = (float) $summary['total'];
                $ratio = $totalAll / $subtotalAll;
                $amount = round($amount * $ratio, 2);
                if ($amount > $remaining + 0.01) {
                    $amount = $remaining;
                }
                $portion = count($selectedItems).' kalem seçildi';
                break;
        }

        if ($amount <= 0) {
            return response()->json(['ok' => false, 'message' => 'Geçersiz tutar.'], 422);
        }

        // Şu an gerçek ödeme entegrasyonu yok — simulated provider ile direkt 'paid'.
        $payment = DB::transaction(function () use ($table, $restaurant, $data, $amount, $portion, $selectedItems, $parts, $partIndex, $request) {
            return CekirdexPayment::create([
                'cekirdex_restaurant_id' => $restaurant->id,
                'cekirdex_table_id'      => $table->id,
                'cekirdex_order_id'      => null,
                'amount'                 => $amount,
                'status'                 => 'paid',
                'method'                 => $data['method'],
                'provider'               => 'simulated',
                'transaction_id'         => 'SIM-'.strtoupper(\Illuminate\Support\Str::random(10)),
                'split_mode'             => $data['mode'],
                'selected_items'         => $selectedItems,
                'portion_label'          => $portion,
                'split_total_parts'      => $parts,
                'split_part_index'       => $partIndex,
                'payer_name'             => $data['payer_name'] ?? null,
                'ip_address'             => $request->ip(),
                'paid_at'                => now(),
            ]);
        });

        $newSummary = $this->bills->summary($table);

        return response()->json([
            'ok'         => true,
            'message'    => 'Ödemeniz alındı! Garson hesabı kapattığında masa kapanacak.',
            'payment_id' => $payment->id,
            'amount'     => (float) $payment->amount,
            'bill'       => $newSummary,
        ]);
    }
}
