<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexOrderItem;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderFeedApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexTable $table;
    private CekirdexCategory $category;
    private CekirdexProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'               => 'feed-restoran',
            'name'               => 'Feed Restoran',
            'is_active'          => true,
            'tax_rate'           => 0,
            'service_charge_rate' => 0,
        ]);

        $this->table = CekirdexTable::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'      => 'Masa 1',
            'code'      => '1',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 4,
            'is_active' => true,
        ]);

        $this->category = CekirdexCategory::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'       => 'Ana Yemekler',
            'slug'       => 'ana-yemekler',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        $this->product = CekirdexProduct::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_category_id'   => $this->category->id,
            'name'       => 'Test Ürün',
            'slug'       => 'test-urun',
            'price'      => 75.00,
            'is_active'  => true,
        ]);
    }

    private function createOrderViaApi(): array
    {
        $response = $this->postJson("/api/v1/tables/{$this->table->qr_token}/orders", [
            'items' => [
                ['product_id' => $this->product->id, 'quantity' => 1],
            ],
        ]);

        $response->assertStatus(201);
        return $response->json('order');
    }

    private function createOrder(string $status = 'new'): CekirdexOrder
    {
        return CekirdexOrder::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_table_id'      => $this->table->id,
            'order_number'           => CekirdexOrder::newOrderNumber(),
            'public_code'            => CekirdexOrder::newPublicCode(),
            'order_type'             => CekirdexOrder::TYPE_DINE_IN,
            'subtotal'               => 75.00,
            'tax'                    => 0.00,
            'service_charge'         => 0.00,
            'discount'               => 0.00,
            'total'                  => 75.00,
            'status'                 => $status,
            'payment_status'         => 'pending',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Sipariş takip (GET /api/v1/orders/{publicCode})
    // ──────────────────────────────────────────────────────────────

    public function test_order_track_returns_order_status(): void
    {
        $order = $this->createOrder('new');

        $response = $this->getJson("/api/v1/orders/{$order->public_code}");

        $response->assertStatus(200)
            ->assertJsonStructure(['order'])
            ->assertJsonPath('order.public_code', $order->public_code)
            ->assertJsonPath('order.status', 'new');
    }

    public function test_order_track_returns_order_via_api_creation(): void
    {
        $orderData = $this->createOrderViaApi();
        $publicCode = $orderData['public_code'];

        $response = $this->getJson("/api/v1/orders/{$publicCode}");

        $response->assertStatus(200)
            ->assertJsonPath('order.public_code', $publicCode)
            ->assertJsonStructure([
                'order' => [
                    'id',
                    'order_number',
                    'public_code',
                    'status',
                    'subtotal',
                    'total',
                    'items',
                ],
            ]);
    }

    public function test_order_track_public_code_is_case_insensitive(): void
    {
        $order = $this->createOrder('new');
        $lowerCode = strtolower($order->public_code);

        $this->getJson("/api/v1/orders/{$lowerCode}")
            ->assertStatus(200)
            ->assertJsonPath('order.public_code', $order->public_code);
    }

    // ──────────────────────────────────────────────────────────────
    // Sipariş takip - geçersiz kod
    // ──────────────────────────────────────────────────────────────

    public function test_order_track_with_invalid_code_returns_404(): void
    {
        $this->getJson('/api/v1/orders/GECERSIZ123')
            ->assertStatus(404);
    }

    public function test_order_track_with_empty_code_returns_404(): void
    {
        $this->getJson('/api/v1/orders/XXXXXXXXXX')
            ->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // Sipariş feed (GET /api/v1/orders/{publicCode}/feed)
    // ──────────────────────────────────────────────────────────────

    public function test_order_feed_returns_status(): void
    {
        $order = $this->createOrder('new');

        $response = $this->getJson("/api/v1/orders/{$order->public_code}/feed");

        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'status_label', 'items', 'total', 'updated_at'])
            ->assertJsonPath('status', 'new');
    }

    public function test_order_feed_with_invalid_code_returns_404(): void
    {
        $this->getJson('/api/v1/orders/INVALIDCODE999/feed')
            ->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // Sipariş içerik doğrulama
    // ──────────────────────────────────────────────────────────────

    public function test_order_track_includes_items(): void
    {
        $order = $this->createOrder('new');
        CekirdexOrderItem::create([
            'cekirdex_order_id'   => $order->id,
            'cekirdex_product_id' => $this->product->id,
            'name'                => $this->product->name,
            'price'               => 75.00,
            'quantity'            => 2,
            'subtotal'            => 150.00,
            'status'              => 'pending',
        ]);

        $response = $this->getJson("/api/v1/orders/{$order->public_code}");

        $response->assertStatus(200);
        $this->assertCount(1, $response->json('order.items'));
        $this->assertEquals(2, $response->json('order.items.0.quantity'));
    }

    public function test_order_track_includes_restaurant_info(): void
    {
        $order = $this->createOrder('new');

        $response = $this->getJson("/api/v1/orders/{$order->public_code}");

        $response->assertStatus(200)
            ->assertJsonPath('order.restaurant.name', 'Feed Restoran')
            ->assertJsonPath('order.restaurant.slug', 'feed-restoran');
    }

    public function test_order_track_shows_different_statuses(): void
    {
        $statuses = ['new', 'confirmed', 'preparing', 'ready', 'served'];

        foreach ($statuses as $status) {
            $order = $this->createOrder($status);

            $this->getJson("/api/v1/orders/{$order->public_code}")
                ->assertStatus(200)
                ->assertJsonPath('order.status', $status);
        }
    }
}
