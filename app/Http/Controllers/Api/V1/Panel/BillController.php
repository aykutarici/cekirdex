<?php

namespace App\Http\Controllers\Api\V1\Panel;

use App\Cekirdex\Models\CekirdexBillClosure;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexOrderItem;
use App\Cekirdex\Models\CekirdexPayment;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Services\BillService;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BillController extends Controller
{
    public function __construct(private readonly BillService $billService) {}

    public function index(Request $request): JsonResponse
    {
        $restaurantId = $this->restaurantId($request);

        $tables = CekirdexTable::query()
            ->where('cekirdex_restaurant_id', $restaurantId)
            ->where('is_active', true)
            ->with(['activeOrders'])
            ->orderBy('name')
            ->get();

        $data = $tables->filter(fn (CekirdexTable $t) => $t->activeOrders->isNotEmpty())
            ->map(function (CekirdexTable $table) {
                $summary = $this->billService->summary($table);
                return [
                    'table_id'   => $table->id,
                    'table_name' => $table->name,
                    'total'      => $summary['total'],
                    'paid'       => $summary['paid'],
                    'remaining'  => $summary['remaining'],
                    'currency'   => $summary['currency'],
                    'order_count' => count($summary['orders']),
                ];
            })
            ->values();

        return response()->json(['data' => $data]);
    }

    public function show(Request $request, int $tableId): JsonResponse
    {
        $table = $this->findTable($request, $tableId);
        $summary = $this->billService->summary($table);

        return response()->json([
            'data' => [
                'table_id'    => $table->id,
                'table_name'  => $table->name,
                'subtotal'    => $summary['subtotal'],
                'tax'         => $summary['tax'],
                'service_charge' => $summary['service_charge'],
                'total'       => $summary['total'],
                'paid'        => $summary['paid'],
                'remaining'   => $summary['remaining'],
                'currency'    => $summary['currency'],
                'orders'      => $summary['orders'],
                'items'       => $summary['items'],
                'payments'    => $summary['payments'],
                'has_open_orders' => $summary['has_open_orders'],
            ],
        ]);
    }

    public function addPayment(Request $request, int $tableId): JsonResponse
    {
        $actor = $this->actor($request);
        $table = $this->findTable($request, $tableId);

        $data = $request->validate([
            'amount'     => 'required|numeric|min:0.01',
            'method'     => 'required|in:waiter_card,waiter_cash,bank_transfer',
            'payer_name' => 'nullable|string|max:120',
        ]);

        $payment = CekirdexPayment::create([
            'cekirdex_restaurant_id' => $table->cekirdex_restaurant_id,
            'cekirdex_table_id'      => $table->id,
            'amount'                 => $data['amount'],
            'method'                 => $data['method'],
            'status'                 => 'paid',
            'split_mode'             => CekirdexPayment::SPLIT_FULL,
            'payer_name'             => $data['payer_name'] ?? null,
            'confirmed_by_user_id'   => $actor->id,
            'confirmed_at'           => now(),
            'paid_at'                => now(),
        ]);

        return response()->json([
            'message' => 'Ödeme kaydedildi.',
            'payment' => ['id' => $payment->id, 'amount' => (float) $payment->amount, 'method' => $payment->method],
        ], 201);
    }

    public function cancelPayment(Request $request, int $tableId, int $paymentId): JsonResponse
    {
        $table = $this->findTable($request, $tableId);

        $payment = CekirdexPayment::query()
            ->where('cekirdex_table_id', $table->id)
            ->where('id', $paymentId)
            ->firstOrFail();

        $payment->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Ödeme iptal edildi.']);
    }

    public function closeBill(Request $request, int $tableId): JsonResponse
    {
        $actor  = $this->actor($request);
        $table  = $this->findTable($request, $tableId);

        DB::transaction(function () use ($actor, $table) {
            $summary = $this->billService->summary($table);

            CekirdexOrder::query()
                ->where('cekirdex_table_id', $table->id)
                ->whereNotIn('status', ['cancelled', 'closed'])
                ->update(['status' => 'closed']);

            CekirdexBillClosure::create([
                'cekirdex_restaurant_id' => $table->cekirdex_restaurant_id,
                'cekirdex_table_id'      => $table->id,
                'closed_by_user_id'      => $actor->id,
                'delivery_method'        => 'none',
                'subtotal'               => $summary['subtotal'],
                'tax'                    => $summary['tax'],
                'service_charge'         => $summary['service_charge'],
                'discount'               => 0,
                'total'                  => $summary['total'],
                'paid'                   => $summary['paid'],
                'change_returned'        => max(0, $summary['paid'] - $summary['total']),
                'orders_snapshot'        => $summary['orders'],
                'items_snapshot'         => $summary['items'],
                'payments_snapshot'      => $summary['payments'],
                'ip_address'             => request()->ip(),
            ]);
        });

        return response()->json(['message' => 'Hesap kapatıldı.']);
    }

    public function waiterOrder(Request $request, int $tableId): JsonResponse
    {
        $actor = $this->actor($request);
        $table = $this->findTable($request, $tableId);

        $data = $request->validate([
            'items'              => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.variant_id' => 'nullable|integer',
            'items.*.quantity'   => 'required|integer|min:1',
            'items.*.note'       => 'nullable|string|max:500',
            'note'               => 'nullable|string|max:1000',
        ]);

        $order = DB::transaction(function () use ($request, $actor, $table, $data) {
            $restaurant = $table->restaurant;
            $itemsInput = collect($data['items']);

            $products = CekirdexProduct::query()
                ->with('variants')
                ->where('cekirdex_restaurant_id', $restaurant->id)
                ->whereIn('id', $itemsInput->pluck('product_id')->all())
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0.0;
            $resolved = [];

            foreach ($itemsInput as $line) {
                /** @var CekirdexProduct|null $product */
                $product = $products->get((int) $line['product_id']);
                if (!$product) {
                    throw ValidationException::withMessages(['items' => 'Geçersiz ürün ID.']);
                }

                $quantity  = (int) $line['quantity'];
                $variantId = isset($line['variant_id']) ? (int) $line['variant_id'] : null;
                $price     = $product->resolveOrderLine($variantId);

                if (!($price['ok'] ?? false)) {
                    throw ValidationException::withMessages(['items' => $price['message'] ?? 'Ürün seçeneği geçersiz.']);
                }

                $lineSubtotal  = round(((float) $price['unit_price']) * $quantity, 2);
                $subtotal     += $lineSubtotal;

                $resolved[] = [
                    'product'       => $product,
                    'variant'       => $price['variant'] ?? null,
                    'variant_label' => $price['variant_label'] ?? null,
                    'unit_price'    => (float) $price['unit_price'],
                    'quantity'      => $quantity,
                    'note'          => $line['note'] ?? null,
                    'subtotal'      => $lineSubtotal,
                ];
            }

            $taxRate = (float) ($restaurant->tax_rate ?? 0);
            $svcRate = (float) ($restaurant->service_charge_rate ?? 0);
            $tax     = round($subtotal * ($taxRate / 100), 2);
            $service = round($subtotal * ($svcRate / 100), 2);
            $total   = round($subtotal + $tax + $service, 2);

            $order = CekirdexOrder::create([
                'cekirdex_restaurant_id' => $restaurant->id,
                'cekirdex_branch_id'     => $table->cekirdex_branch_id,
                'cekirdex_table_id'      => $table->id,
                'order_number'           => CekirdexOrder::newOrderNumber(),
                'public_code'            => CekirdexOrder::newPublicCode(),
                'order_type'             => CekirdexOrder::TYPE_DINE_IN,
                'subtotal'               => $subtotal,
                'tax'                    => $tax,
                'service_charge'         => $service,
                'discount'               => 0,
                'total'                  => $total,
                'status'                 => 'confirmed',
                'payment_status'         => 'pending',
                'note'                   => $data['note'] ?? null,
                'ip_address'             => $request->ip(),
            ]);

            foreach ($resolved as $line) {
                CekirdexOrderItem::create([
                    'cekirdex_order_id'          => $order->id,
                    'cekirdex_product_id'         => $line['product']->id,
                    'cekirdex_product_variant_id' => $line['variant']?->id,
                    'variant_label'              => $line['variant_label'],
                    'name'                       => $line['product']->name,
                    'price'                      => $line['unit_price'],
                    'quantity'                   => $line['quantity'],
                    'note'                       => $line['note'],
                    'subtotal'                   => $line['subtotal'],
                    'status'                     => 'pending',
                ]);
            }

            return $order->load('items', 'table');
        });

        return response()->json([
            'message'      => 'Sipariş oluşturuldu.',
            'order_id'     => $order->id,
            'order_number' => $order->order_number,
            'total'        => (float) $order->total,
        ], 201);
    }

    private function findTable(Request $request, int $tableId): CekirdexTable
    {
        return CekirdexTable::query()
            ->where('cekirdex_restaurant_id', $this->restaurantId($request))
            ->findOrFail($tableId);
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
