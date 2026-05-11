<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexReservation;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRestaurantApiTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────
    // Yardımcı fabrika metodları
    // ──────────────────────────────────────────────────────────────

    private function createRestaurant(array $overrides = []): CekirdexRestaurant
    {
        return CekirdexRestaurant::create(array_merge([
            'slug'      => 'test-restoran',
            'name'      => 'Test Restoran',
            'is_active' => true,
        ], $overrides));
    }

    private function createTable(CekirdexRestaurant $restaurant, array $overrides = []): CekirdexTable
    {
        return CekirdexTable::create(array_merge([
            'cekirdex_restaurant_id' => $restaurant->id,
            'name'      => 'Masa 1',
            'code'      => '1',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 4,
            'is_active' => true,
        ], $overrides));
    }

    private function createCategory(CekirdexRestaurant $restaurant, array $overrides = []): CekirdexCategory
    {
        return CekirdexCategory::create(array_merge([
            'cekirdex_restaurant_id' => $restaurant->id,
            'name'       => 'Ana Yemekler',
            'slug'       => 'ana-yemekler',
            'sort_order' => 1,
            'is_active'  => true,
        ], $overrides));
    }

    private function createProduct(CekirdexCategory $category, CekirdexRestaurant $restaurant, array $overrides = []): CekirdexProduct
    {
        return CekirdexProduct::create(array_merge([
            'cekirdex_restaurant_id' => $restaurant->id,
            'cekirdex_category_id'   => $category->id,
            'name'       => 'Test Ürün',
            'slug'       => 'test-urun',
            'price'      => 50.00,
            'is_active'  => true,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────────
    // Restoran göster (show)
    // ──────────────────────────────────────────────────────────────

    public function test_show_returns_active_restaurant(): void
    {
        $this->createRestaurant();

        $response = $this->getJson('/api/v1/restaurants/test-restoran');

        $response->assertStatus(200)
            ->assertJsonPath('restaurant.name', 'Test Restoran')
            ->assertJsonPath('restaurant.slug', 'test-restoran')
            ->assertJsonStructure(['restaurant', 'categories']);
    }

    public function test_show_returns_404_for_inactive_restaurant(): void
    {
        $this->createRestaurant(['is_active' => false]);

        $this->getJson('/api/v1/restaurants/test-restoran')->assertStatus(404);
    }

    public function test_show_returns_404_for_nonexistent_restaurant(): void
    {
        $this->getJson('/api/v1/restaurants/yok-restoran')->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // QR Menü
    // ──────────────────────────────────────────────────────────────

    public function test_menu_returns_table_and_categories(): void
    {
        $restaurant = $this->createRestaurant();
        $table = $this->createTable($restaurant);
        $category = $this->createCategory($restaurant);
        $this->createProduct($category, $restaurant);

        $response = $this->getJson("/api/v1/tables/{$table->qr_token}/menu");

        $response->assertStatus(200)
            ->assertJsonPath('table.name', 'Masa 1')
            ->assertJsonPath('restaurant.name', 'Test Restoran')
            ->assertJsonStructure(['table', 'restaurant', 'categories']);

        $this->assertCount(1, $response->json('categories'));
    }

    public function test_menu_returns_404_for_inactive_table(): void
    {
        $restaurant = $this->createRestaurant();
        $table = $this->createTable($restaurant, ['is_active' => false]);

        $this->getJson("/api/v1/tables/{$table->qr_token}/menu")->assertStatus(404);
    }

    public function test_menu_returns_404_for_invalid_qr_token(): void
    {
        $this->getJson('/api/v1/tables/gecersiz-token/menu')->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // Sipariş oluştur
    // ──────────────────────────────────────────────────────────────

    public function test_store_order_creates_order_and_items(): void
    {
        $restaurant = $this->createRestaurant();
        $table = $this->createTable($restaurant);
        $category = $this->createCategory($restaurant);
        $product = $this->createProduct($category, $restaurant);

        $response = $this->postJson("/api/v1/tables/{$table->qr_token}/orders", [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'order'])
            ->assertJsonPath('order.status', 'new');

        $this->assertDatabaseHas('cekirdex_orders', ['cekirdex_table_id' => $table->id]);
        $this->assertDatabaseHas('cekirdex_order_items', ['cekirdex_product_id' => $product->id, 'quantity' => 2]);
    }

    public function test_store_order_calculates_total_correctly(): void
    {
        $restaurant = $this->createRestaurant(['tax_rate' => 0, 'service_charge_rate' => 0]);
        $table = $this->createTable($restaurant);
        $category = $this->createCategory($restaurant);
        $product = $this->createProduct($category, $restaurant, ['price' => 100.00]);

        $response = $this->postJson("/api/v1/tables/{$table->qr_token}/orders", [
            'items' => [['product_id' => $product->id, 'quantity' => 3]],
        ]);

        $response->assertStatus(201);
        $this->assertEquals(300, $response->json('order.subtotal'));
        $this->assertEquals(300, $response->json('order.total'));
    }

    public function test_store_order_fails_with_invalid_product(): void
    {
        $restaurant = $this->createRestaurant();
        $table = $this->createTable($restaurant);

        $response = $this->postJson("/api/v1/tables/{$table->qr_token}/orders", [
            'items' => [['product_id' => 99999, 'quantity' => 1]],
        ]);

        $response->assertStatus(422);
    }

    public function test_store_order_fails_with_empty_items(): void
    {
        $restaurant = $this->createRestaurant();
        $table = $this->createTable($restaurant);

        $this->postJson("/api/v1/tables/{$table->qr_token}/orders", ['items' => []])
            ->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Garson çağrısı
    // ──────────────────────────────────────────────────────────────

    public function test_store_call_creates_waiter_call(): void
    {
        $restaurant = $this->createRestaurant();
        $table = $this->createTable($restaurant);

        $response = $this->postJson("/api/v1/tables/{$table->qr_token}/calls", [
            'call_type' => 'waiter',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('call.type', 'waiter');

        $this->assertDatabaseHas('cekirdex_calls', [
            'cekirdex_table_id' => $table->id,
            'call_type'         => 'waiter',
        ]);
    }

    public function test_store_call_fails_with_invalid_call_type(): void
    {
        $restaurant = $this->createRestaurant();
        $table = $this->createTable($restaurant);

        $this->postJson("/api/v1/tables/{$table->qr_token}/calls", [
            'call_type' => 'gecersiz_tur',
        ])->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Müsaitlik
    // ──────────────────────────────────────────────────────────────

    public function test_availability_returns_time_slots(): void
    {
        $this->createRestaurant();

        $response = $this->getJson('/api/v1/restaurants/test-restoran/availability?date=' . now()->addDay()->toDateString());

        $response->assertStatus(200)
            ->assertJsonStructure(['restaurant', 'date', 'slots'])
            ->assertJsonCount(12, 'slots');
    }

    // ──────────────────────────────────────────────────────────────
    // Rezervasyon oluştur
    // ──────────────────────────────────────────────────────────────

    public function test_store_reservation_creates_reservation(): void
    {
        $this->createRestaurant();

        $response = $this->postJson('/api/v1/restaurants/test-restoran/reservations', [
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551234567',
            'reserved_for'  => now()->addDay()->addHours(2)->toIso8601String(),
            'party_size'    => 4,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'reservation'])
            ->assertJsonPath('reservation.status', 'pending');

        $this->assertDatabaseHas('cekirdex_reservations', ['contact_name' => 'Ali Veli']);
    }

    public function test_store_reservation_fails_with_past_date(): void
    {
        $this->createRestaurant();

        $this->postJson('/api/v1/restaurants/test-restoran/reservations', [
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551234567',
            'reserved_for'  => now()->subDay()->toIso8601String(),
            'party_size'    => 2,
        ])->assertStatus(422);
    }

    public function test_store_reservation_fails_with_missing_fields(): void
    {
        $this->createRestaurant();

        $this->postJson('/api/v1/restaurants/test-restoran/reservations', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['contact_name', 'contact_phone', 'reserved_for', 'party_size']);
    }

    // ──────────────────────────────────────────────────────────────
    // Sipariş takip
    // ──────────────────────────────────────────────────────────────

    public function test_order_track_returns_order(): void
    {
        $restaurant = $this->createRestaurant();
        $table = $this->createTable($restaurant);
        $category = $this->createCategory($restaurant);
        $product = $this->createProduct($category, $restaurant);

        $orderResponse = $this->postJson("/api/v1/tables/{$table->qr_token}/orders", [
            'items' => [['product_id' => $product->id, 'quantity' => 1]],
        ]);

        $publicCode = $orderResponse->json('order.public_code');

        $this->getJson("/api/v1/orders/{$publicCode}")
            ->assertStatus(200)
            ->assertJsonPath('order.public_code', $publicCode);
    }

    public function test_order_track_returns_404_for_invalid_code(): void
    {
        $this->getJson('/api/v1/orders/GECERSIZ123')->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // Rezervasyon takip & iptal
    // ──────────────────────────────────────────────────────────────

    public function test_reservation_track_returns_reservation(): void
    {
        $restaurant = $this->createRestaurant();

        $rsv = $this->postJson('/api/v1/restaurants/test-restoran/reservations', [
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551234567',
            'reserved_for'  => now()->addDay()->addHours(3)->toIso8601String(),
            'party_size'    => 2,
        ])->json('reservation');

        $this->getJson("/api/v1/reservations/{$rsv['public_code']}")
            ->assertStatus(200)
            ->assertJsonPath('reservation.public_code', $rsv['public_code']);
    }

    public function test_cancel_reservation_changes_status_to_cancelled(): void
    {
        $this->createRestaurant();

        $rsv = $this->postJson('/api/v1/restaurants/test-restoran/reservations', [
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551234567',
            'reserved_for'  => now()->addDay()->addHours(3)->toIso8601String(),
            'party_size'    => 2,
        ])->json('reservation');

        $this->postJson("/api/v1/reservations/{$rsv['public_code']}/cancel")
            ->assertStatus(200)
            ->assertJsonPath('reservation.status', 'cancelled');

        $this->assertDatabaseHas('cekirdex_reservations', [
            'public_code' => $rsv['public_code'],
            'status'      => 'cancelled',
        ]);
    }

    public function test_cancel_reservation_returns_404_for_already_cancelled(): void
    {
        $this->createRestaurant();

        $rsv = $this->postJson('/api/v1/restaurants/test-restoran/reservations', [
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551234567',
            'reserved_for'  => now()->addDay()->addHours(3)->toIso8601String(),
            'party_size'    => 2,
        ])->json('reservation');

        $this->postJson("/api/v1/reservations/{$rsv['public_code']}/cancel");
        $this->postJson("/api/v1/reservations/{$rsv['public_code']}/cancel")->assertStatus(404);
    }
}
