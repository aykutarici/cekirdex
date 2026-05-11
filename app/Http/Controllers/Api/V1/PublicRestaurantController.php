<?php

namespace App\Http\Controllers\Api\V1;

use App\Cekirdex\Models\CekirdexCall;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexOrderItem;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexReservation;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PublicRestaurantController extends Controller
{
    public function show(string $slug): JsonResponse
    {
        $restaurant = CekirdexRestaurant::query()
            ->with(['categories' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name')])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'restaurant' => $this->restaurantPayload($restaurant),
            'categories' => $restaurant->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ]),
        ]);
    }

    public function menu(string $qrToken): JsonResponse
    {
        $table = CekirdexTable::query()
            ->with([
                'restaurant.categories' => fn ($query) => $query->where('is_active', true)->orderBy('sort_order')->orderBy('name'),
                'restaurant.categories.products' => fn ($query) => $query->where('is_active', true)->with('variants')->orderBy('sort_order')->orderBy('name'),
            ])
            ->where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();

        $restaurant = $table->restaurant;

        return response()->json([
            'table' => [
                'id' => $table->id,
                'name' => $table->name,
                'code' => $table->code,
            ],
            'restaurant' => $this->restaurantPayload($restaurant),
            'categories' => $restaurant->categories->map(fn ($category) => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'products' => $category->products->map(fn ($product) => [
                    'id' => $product->id,
                    'name' => $product->name,
                    'slug' => $product->slug,
                    'description' => $product->description,
                    'price' => (float) $product->price,
                    'discount_price' => $product->discount_price ? (float) $product->discount_price : null,
                    'effective_price' => $product->effective_price,
                    'image_url' => $product->image_url,
                    'is_popular' => $product->is_popular,
                    'is_new' => $product->is_new,
                    'is_available' => $product->isAvailable(),
                    'variants' => $product->variants->map(fn ($variant) => [
                        'id' => $variant->id,
                        'name' => $variant->name,
                        'price_adjust' => (float) $variant->price_adjust,
                        'is_default' => $variant->is_default,
                    ]),
                ]),
            ]),
        ]);
    }

    public function storeOrder(Request $request, string $qrToken): JsonResponse
    {
        $table = CekirdexTable::query()
            ->with('restaurant')
            ->where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();

        $request->validate([
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer'],
            'items.*.variant_id' => ['nullable', 'integer'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.note' => ['nullable', 'string', 'max:500'],
            'guest_name' => ['nullable', 'string', 'max:120'],
            'guest_phone' => ['nullable', 'string', 'max:32'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $order = DB::transaction(function () use ($request, $table) {
            $restaurant = $table->restaurant;
            $items = collect($request->input('items'));
            $products = CekirdexProduct::query()
                ->with('variants')
                ->where('cekirdex_restaurant_id', $restaurant->id)
                ->whereIn('id', $items->pluck('product_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $resolved = [];

            foreach ($items as $line) {
                /** @var CekirdexProduct|null $product */
                $product = $products->get((int) $line['product_id']);
                if (!$product || !$product->isAvailable()) {
                    throw ValidationException::withMessages([
                        'items' => 'Sepette siparişe uygun olmayan ürün var.',
                    ]);
                }

                $quantity = (int) $line['quantity'];
                $variantId = isset($line['variant_id']) ? (int) $line['variant_id'] : null;
                $price = $product->resolveOrderLine($variantId);

                if (!($price['ok'] ?? false)) {
                    throw ValidationException::withMessages([
                        'items' => $price['message'] ?? 'Ürün seçeneği geçersiz.',
                    ]);
                }

                $lineSubtotal = round(((float) $price['unit_price']) * $quantity, 2);
                $subtotal += $lineSubtotal;

                $resolved[] = [
                    'product' => $product,
                    'variant' => $price['variant'] ?? null,
                    'variant_label' => $price['variant_label'] ?? null,
                    'unit_price' => (float) $price['unit_price'],
                    'quantity' => $quantity,
                    'note' => $line['note'] ?? null,
                    'subtotal' => $lineSubtotal,
                ];
            }

            $tax = round($subtotal * (((float) $restaurant->tax_rate) / 100), 2);
            $service = round($subtotal * (((float) $restaurant->service_charge_rate) / 100), 2);
            $total = round($subtotal + $tax + $service, 2);

            $order = CekirdexOrder::create([
                'cekirdex_restaurant_id' => $restaurant->id,
                'cekirdex_branch_id' => $table->cekirdex_branch_id,
                'cekirdex_table_id' => $table->id,
                'order_number' => CekirdexOrder::newOrderNumber(),
                'public_code' => CekirdexOrder::newPublicCode(),
                'order_type' => CekirdexOrder::TYPE_DINE_IN,
                'guest_name' => $request->input('guest_name'),
                'guest_phone' => $request->input('guest_phone'),
                'subtotal' => $subtotal,
                'tax' => $tax,
                'service_charge' => $service,
                'discount' => 0,
                'total' => $total,
                'status' => 'new',
                'payment_status' => 'pending',
                'note' => $request->input('note'),
                'ip_address' => $request->ip(),
                'user_agent' => substr((string) $request->userAgent(), 0, 500),
            ]);

            foreach ($resolved as $line) {
                /** @var CekirdexProduct $product */
                $product = $line['product'];
                CekirdexOrderItem::create([
                    'cekirdex_order_id' => $order->id,
                    'cekirdex_product_id' => $product->id,
                    'cekirdex_product_variant_id' => $line['variant']?->id,
                    'variant_label' => $line['variant_label'],
                    'name' => $product->name,
                    'price' => $line['unit_price'],
                    'quantity' => $line['quantity'],
                    'note' => $line['note'],
                    'subtotal' => $line['subtotal'],
                    'status' => 'pending',
                ]);
            }

            return $order->load('items', 'table');
        });

        return response()->json([
            'message' => 'Sipariş alındı.',
            'order' => $this->orderPayload($order),
        ], 201);
    }

    public function storeCall(Request $request, string $qrToken): JsonResponse
    {
        $table = CekirdexTable::query()
            ->where('qr_token', $qrToken)
            ->where('is_active', true)
            ->firstOrFail();

        $data = $request->validate([
            'call_type' => ['required', Rule::in(array_keys(CekirdexCall::TYPES))],
            'message' => ['nullable', 'string', 'max:500'],
        ]);

        $call = CekirdexCall::create([
            'cekirdex_restaurant_id' => $table->cekirdex_restaurant_id,
            'cekirdex_branch_id' => $table->cekirdex_branch_id,
            'cekirdex_table_id' => $table->id,
            'call_type' => $data['call_type'],
            'message' => $data['message'] ?? null,
            'status' => 'pending',
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Çağrı alındı.',
            'call' => [
                'id' => $call->id,
                'type' => $call->call_type,
                'type_label' => $call->type_label,
                'status' => $call->status,
            ],
        ], 201);
    }

    public function availability(Request $request, string $slug): JsonResponse
    {
        $data = $request->validate([
            'date' => ['nullable', 'date'],
        ]);

        $restaurant = CekirdexRestaurant::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $date = $data['date'] ?? now()->toDateString();
        $start = Carbon::parse($date)->setTime(12, 0);
        $slots = collect(range(0, 11))->map(function (int $i) use ($start) {
            $time = $start->copy()->addMinutes($i * 30);
            return [
                'time' => $time->format('H:i'),
                'datetime' => $time->toIso8601String(),
                'available' => $time->isFuture(),
            ];
        });

        return response()->json([
            'restaurant' => $this->restaurantPayload($restaurant),
            'date' => $date,
            'slots' => $slots,
        ]);
    }

    public function storeReservation(Request $request, string $slug): JsonResponse
    {
        $restaurant = CekirdexRestaurant::query()
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        $data = $request->validate([
            'contact_name' => ['required', 'string', 'max:120'],
            'contact_phone' => ['required', 'string', 'max:24'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'reserved_for' => ['required', 'date', 'after:now'],
            'party_size' => ['required', 'integer', 'min:1', 'max:60'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $reservation = CekirdexReservation::create([
            'cekirdex_restaurant_id' => $restaurant->id,
            'public_code' => CekirdexReservation::newPublicCode(),
            'contact_name' => $data['contact_name'],
            'contact_phone' => $data['contact_phone'],
            'contact_email' => $data['contact_email'] ?? null,
            'reserved_for' => $data['reserved_for'],
            'duration_minutes' => 90,
            'party_size' => $data['party_size'],
            'status' => 'pending',
            'note' => $data['note'] ?? null,
            'ip_address' => $request->ip(),
        ]);

        return response()->json([
            'message' => 'Rezervasyon talebiniz alındı.',
            'reservation' => $this->reservationPayload($reservation->load('restaurant')),
        ], 201);
    }

    public function orderTrack(string $publicCode): JsonResponse
    {
        $order = CekirdexOrder::query()
            ->with('items', 'restaurant', 'table')
            ->where('public_code', strtoupper($publicCode))
            ->firstOrFail();

        return response()->json(['order' => $this->orderPayload($order)]);
    }

    public function reservationTrack(string $publicCode): JsonResponse
    {
        $reservation = CekirdexReservation::query()
            ->with('restaurant', 'table')
            ->where('public_code', strtoupper($publicCode))
            ->firstOrFail();

        return response()->json(['reservation' => $this->reservationPayload($reservation)]);
    }

    public function cancelReservation(string $publicCode): JsonResponse
    {
        $reservation = CekirdexReservation::query()
            ->where('public_code', strtoupper($publicCode))
            ->whereNotIn('status', ['cancelled', 'completed', 'no_show'])
            ->firstOrFail();

        $reservation->forceFill([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'cancelled_by' => 'customer',
        ])->save();

        return response()->json([
            'message' => 'Rezervasyon iptal edildi.',
            'reservation' => $this->reservationPayload($reservation->load('restaurant', 'table')),
        ]);
    }

    private function restaurantPayload(CekirdexRestaurant $restaurant): array
    {
        return [
            'id' => $restaurant->id,
            'slug' => $restaurant->slug,
            'name' => $restaurant->name,
            'description' => $restaurant->description,
            'logo_url' => $restaurant->logo_url,
            'cover_image_url' => $restaurant->cover_image_url,
            'city' => $restaurant->city,
            'country' => $restaurant->country,
            'phone' => $restaurant->phone,
            'email' => $restaurant->email,
            'currency' => $restaurant->currency,
            'is_open_now' => $restaurant->isOpenNow(),
            'accepts_online_payment' => $restaurant->accepts_online_payment,
            'accepts_takeaway' => $restaurant->accepts_takeaway,
            'accepts_delivery' => $restaurant->accepts_delivery,
            'accepts_reservations' => $restaurant->accepts_reservations,
        ];
    }

    private function orderPayload(CekirdexOrder $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'public_code' => $order->public_code,
            'status' => $order->status,
            'status_label' => $order->status_label,
            'payment_status' => $order->payment_status,
            'subtotal' => (float) $order->subtotal,
            'tax' => (float) $order->tax,
            'service_charge' => (float) $order->service_charge,
            'discount' => (float) $order->discount,
            'total' => (float) $order->total,
            'table' => $order->table ? [
                'id' => $order->table->id,
                'name' => $order->table->name,
            ] : null,
            'restaurant' => $order->restaurant ? [
                'id' => $order->restaurant->id,
                'name' => $order->restaurant->name,
                'slug' => $order->restaurant->slug,
            ] : null,
            'items' => $order->items->map(fn ($item) => [
                'id' => $item->id,
                'name' => $item->name,
                'variant_label' => $item->variant_label,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
                'subtotal' => (float) $item->subtotal,
                'status' => $item->status,
            ]),
        ];
    }

    private function reservationPayload(CekirdexReservation $reservation): array
    {
        return [
            'id' => $reservation->id,
            'public_code' => $reservation->public_code,
            'contact_name' => $reservation->contact_name,
            'reserved_for' => $reservation->reserved_for?->toIso8601String(),
            'party_size' => $reservation->party_size,
            'status' => $reservation->status,
            'status_label' => $reservation->status_label,
            'restaurant' => $reservation->restaurant ? [
                'id' => $reservation->restaurant->id,
                'name' => $reservation->restaurant->name,
                'slug' => $reservation->restaurant->slug,
            ] : null,
            'table' => $reservation->table ? [
                'id' => $reservation->table->id,
                'name' => $reservation->table->name,
            ] : null,
        ];
    }
}
