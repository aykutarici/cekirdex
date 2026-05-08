<?php

namespace App\Http\Controllers\Cekirdex\Customer;

use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexOrderItem;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class TakeawayController extends Controller
{
    /**
     * Paket / adrese teslim siparişi oluştur.
     */
    public function place(Request $request, string $slug)
    {
        $key = 'cekirdex-takeaway:'.$request->ip();
        if (RateLimiter::tooManyAttempts($key, 6)) {
            return response()->json(['ok' => false, 'message' => 'Çok hızlı sipariş gönderiliyor. Bir dakika sonra tekrar deneyin.'], 429);
        }
        RateLimiter::hit($key, 60);

        $restaurant = CekirdexRestaurant::where('slug', $slug)->where('is_active', true)->firstOrFail();
        if (!$restaurant->isOpenNow()) {
            return response()->json(['ok' => false, 'message' => 'Restoran şu anda kapalı.'], 422);
        }

        $data = $request->validate([
            'order_type'      => 'required|in:takeaway,delivery',
            'contact_name'    => 'required|string|min:2|max:120',
            'contact_phone'   => 'required|string|max:24',
            'contact_email'   => 'nullable|email|max:160',
            'note'            => 'nullable|string|max:1000',
            // Adres sadece delivery için
            'delivery_address'=> 'required_if:order_type,delivery|nullable|string|max:500',
            'delivery_lat'    => 'nullable|numeric|between:-90,90',
            'delivery_lng'    => 'nullable|numeric|between:-180,180',
            // Sipariş kalemleri
            'items'                 => 'required|array|min:1|max:60',
            'items.*.product_id'    => 'required|integer',
            'items.*.quantity'      => 'required|integer|min:1|max:30',
            'items.*.variant_id'    => 'nullable|integer',
            'items.*.note'          => 'nullable|string|max:500',
        ]);

        if ($data['order_type'] === 'takeaway' && !$restaurant->accepts_takeaway) {
            return response()->json(['ok' => false, 'message' => 'Bu restoran gel-al hizmeti vermiyor.'], 422);
        }
        if ($data['order_type'] === 'delivery' && !$restaurant->accepts_delivery) {
            return response()->json(['ok' => false, 'message' => 'Bu restoran adrese teslim hizmeti vermiyor.'], 422);
        }

        // Ürünleri DB'den çek (fiyat manipülasyonuna karşı)
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
                'p' => $p, 'qty' => $qty, 'unit' => $unit,
                'sub' => round($unit * $qty, 2), 'note' => $line['note'] ?? null,
                'vid' => $r['variant']?->id, 'vlabel' => $r['variant_label'],
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

        $subtotal = 0.0;
        foreach ($resolved as $row) {
            $subtotal += $row['sub'];
        }
        $tax = round($subtotal * ((float) $restaurant->tax_rate / 100), 2);
        $svc = round($subtotal * ((float) $restaurant->service_charge_rate / 100), 2);
        $deliveryFee = $data['order_type'] === 'delivery' ? (float) $restaurant->delivery_fee : 0;
        $total = round($subtotal + $tax + $svc + $deliveryFee, 2);

        if ($data['order_type'] === 'delivery' && (float) $restaurant->delivery_min_amount > 0
            && $subtotal < (float) $restaurant->delivery_min_amount) {
            return response()->json([
                'ok' => false,
                'message' => 'Minimum sipariş tutarı: '.number_format((float) $restaurant->delivery_min_amount, 2, ',', '.').' ₺',
            ], 422);
        }

        try {
            $order = DB::transaction(function () use ($restaurant, $data, $subtotal, $tax, $svc, $deliveryFee, $total, $resolved, $needByPid, $request) {
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

                $customerUserId = Auth::guard('cekirdex_customer')->id();
                $order = CekirdexOrder::create([
                    'cekirdex_restaurant_id'    => $restaurant->id,
                    'cekirdex_branch_id'        => null,
                    'cekirdex_table_id'         => null,
                    'cekirdex_customer_user_id' => $customerUserId,
                    'order_number'   => CekirdexOrder::newOrderNumber(),
                    'public_code'    => CekirdexOrder::newPublicCode(),
                    'order_type'     => $data['order_type'],
                    'contact_name'   => $data['contact_name'],
                    'contact_phone'  => $data['contact_phone'],
                    'contact_email'  => $data['contact_email'] ?? null,
                    'delivery_address' => $data['order_type'] === 'delivery' ? $data['delivery_address'] : null,
                    'delivery_lat'   => $data['order_type'] === 'delivery' ? ($data['delivery_lat'] ?? null) : null,
                    'delivery_lng'   => $data['order_type'] === 'delivery' ? ($data['delivery_lng'] ?? null) : null,
                    'delivery_fee'   => $deliveryFee,
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

        // Müşteriye onay e-postası (best-effort)
        if (!empty($order->contact_email)) {
            $this->sendConfirmationEmail($order, $restaurant);
        }

        return response()->json([
            'ok'           => true,
            'message'      => 'Siparişiniz alındı! Restoran onaylayınca SMS/email ile bilgilendirileceksiniz.',
            'order_id'     => $order->id,
            'public_code'  => $order->public_code,
            'order_number' => $order->order_number,
            'total'        => (float) $order->total,
            'tracking_url' => url('/cekirdex/o/'.$order->public_code),
        ]);
    }

    /** Müşteri sipariş durum sayfası — public_code ile erişilir, login gerekmez. */
    public function track(string $publicCode)
    {
        $order = CekirdexOrder::where('public_code', $publicCode)
            ->whereIn('order_type', ['takeaway', 'delivery'])
            ->with(['items', 'restaurant'])
            ->firstOrFail();

        return view('cekirdex.customer.order-track', compact('order'));
    }

    /** Müşteri sipariş durumunu JSON olarak çeker (polling için). */
    public function trackFeed(string $publicCode)
    {
        $order = CekirdexOrder::where('public_code', $publicCode)->firstOrFail();
        return response()->json([
            'ok'           => true,
            'status'       => $order->status,
            'status_label' => $order->status_label,
            'eta_minutes'  => $order->eta_minutes,
            'ready_at'     => optional($order->ready_at)->toIso8601String(),
            'delivered_at' => optional($order->delivered_at)->toIso8601String(),
        ]);
    }

    private function sendConfirmationEmail(CekirdexOrder $order, CekirdexRestaurant $restaurant): void
    {
        try {
            $body = "Sayın ".$order->contact_name.",\n\n";
            $body .= $restaurant->name." sipariş özetiniz:\n\n";
            foreach ($order->items as $it) {
                $body .= "  ".$it->quantity.' × '.$it->name.'  — '.number_format($it->subtotal,2,',','.')." ₺\n";
            }
            $body .= "\n  Toplam: ".number_format((float) $order->total,2,',','.')." ₺\n\n";
            $body .= "Sipariş takip linkiniz: ".url('/cekirdex/o/'.$order->public_code)."\n";
            $body .= "Sipariş numaranız: ".$order->order_number."\n\n";
            $body .= "Restoran size en kısa sürede dönüş yapacak.\n\n";
            $body .= $restaurant->name;

            Mail::raw($body, function ($m) use ($order, $restaurant) {
                $m->to($order->contact_email)
                  ->subject($restaurant->name.' — Sipariş #'.$order->order_number);
            });
        } catch (\Throwable $e) {
            Log::warning('Çekirdex takeaway confirmation email failed: '.$e->getMessage());
        }
    }
}
