<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelRole;
use App\Cekirdex\Models\Permission;
use App\Cekirdex\Models\Role;
use App\Cekirdex\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TakeawayOrderApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;
    private CekirdexCategory $category;
    private CekirdexProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'               => 'takeaway-restoran',
            'name'               => 'Takeaway Restoran',
            'is_active'          => true,
            'accepts_takeaway'   => true,
            'accepts_delivery'   => true,
            'tax_rate'           => 0,
            'service_charge_rate' => 0,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@takeaway-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['orders.manage'], $this->owner, $this->restaurant, 'restaurant.owner.takeaway');
        $this->ownerToken = $this->issueToken($this->owner);

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
            'price'      => 100.00,
            'is_active'  => true,
        ]);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'takeaway-test');
        return $token;
    }

    private function seedPermissions(array $permissionKeys, CekirdexUser $user, CekirdexRestaurant $restaurant, string $roleKey): void
    {
        $role = Role::create(['key' => $roleKey, 'name' => 'Sahip', 'scope' => 'restaurant']);

        foreach ($permissionKeys as $key) {
            $perm = Permission::firstOrCreate(['key' => $key], ['name' => $key]);
            $role->permissions()->attach($perm->id);
        }

        ModelRole::create([
            'model_type'             => $user::class,
            'model_id'               => $user->id,
            'role_id'                => $role->id,
            'cekirdex_restaurant_id' => $restaurant->id,
        ]);
    }

    private function createTakeawayOrder(string $status = 'new', string $type = CekirdexOrder::TYPE_TAKEAWAY): CekirdexOrder
    {
        return CekirdexOrder::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'order_number'           => CekirdexOrder::newOrderNumber(),
            'public_code'            => CekirdexOrder::newPublicCode(),
            'order_type'             => $type,
            'contact_name'           => 'Test Müşteri',
            'contact_phone'          => '+905551234567',
            'delivery_address'       => $type === CekirdexOrder::TYPE_DELIVERY ? 'Test Sok. No:1' : null,
            'subtotal'               => 100.00,
            'tax'                    => 0.00,
            'service_charge'         => 0.00,
            'discount'               => 0.00,
            'total'                  => 100.00,
            'status'                 => $status,
            'payment_status'         => 'pending',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Herkese açık takeaway sipariş endpoint'i (henüz uygulanmadı)
    // ──────────────────────────────────────────────────────────────

    public function test_creates_takeaway_order_successfully(): void
    {
        $response = $this->postJson("/api/v1/restaurants/{$this->restaurant->slug}/order", [
            'order_type'    => CekirdexOrder::TYPE_TAKEAWAY,
            'items'         => [['product_id' => $this->product->id, 'quantity' => 1]],
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551112233',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('order.status', 'new');

        $this->assertDatabaseHas('cekirdex_orders', [
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'order_type' => CekirdexOrder::TYPE_TAKEAWAY,
            'status'     => 'new',
        ]);
    }

    public function test_creates_delivery_order_with_address(): void
    {
        $response = $this->postJson("/api/v1/restaurants/{$this->restaurant->slug}/order", [
            'order_type'       => CekirdexOrder::TYPE_DELIVERY,
            'items'            => [['product_id' => $this->product->id, 'quantity' => 2]],
            'contact_name'     => 'Ali Veli',
            'contact_phone'    => '+905551112233',
            'delivery_address' => 'Örnek Mah. Test Sok. No:1',
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('order.status', 'new');

        $this->assertDatabaseHas('cekirdex_orders', [
            'order_type'       => CekirdexOrder::TYPE_DELIVERY,
            'delivery_address' => 'Örnek Mah. Test Sok. No:1',
        ]);
    }

    public function test_requires_delivery_address_for_delivery_type(): void
    {
        $response = $this->postJson("/api/v1/restaurants/{$this->restaurant->slug}/order", [
            'order_type'    => CekirdexOrder::TYPE_DELIVERY,
            'items'         => [['product_id' => $this->product->id, 'quantity' => 1]],
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551112233',
            // delivery_address deliberately omitted
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['delivery_address']);
    }

    public function test_validates_products_exist(): void
    {
        $response = $this->postJson("/api/v1/restaurants/{$this->restaurant->slug}/order", [
            'order_type'    => CekirdexOrder::TYPE_TAKEAWAY,
            'items'         => [['product_id' => 99999, 'quantity' => 1]],
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551112233',
        ]);

        $response->assertStatus(422);
    }

    public function test_calculates_order_total_correctly(): void
    {
        $response = $this->postJson("/api/v1/restaurants/{$this->restaurant->slug}/order", [
            'order_type'    => CekirdexOrder::TYPE_TAKEAWAY,
            'items'         => [['product_id' => $this->product->id, 'quantity' => 3]],
            'contact_name'  => 'Ali Veli',
            'contact_phone' => '+905551112233',
        ]);

        $response->assertStatus(201);

        // product price is 100, quantity 3 → total should be 300 (tax_rate=0)
        $this->assertEquals(300.0, $response->json('order.total'));
    }

    // ──────────────────────────────────────────────────────────────
    // Panel: Takeaway listesi
    // ──────────────────────────────────────────────────────────────

    public function test_lists_takeaway_orders(): void
    {
        $this->createTakeawayOrder('new', CekirdexOrder::TYPE_TAKEAWAY);
        $this->createTakeawayOrder('confirmed', CekirdexOrder::TYPE_DELIVERY);

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/takeaway');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'counts'])
            ->assertJsonCount(2, 'data');
    }

    public function test_lists_takeaway_orders_by_tab(): void
    {
        $this->createTakeawayOrder('new');
        $this->createTakeawayOrder('delivered');

        $active = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/takeaway?tab=active');
        $active->assertStatus(200)->assertJsonCount(1, 'data');

        $completed = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/takeaway?tab=completed');
        $completed->assertStatus(200)->assertJsonCount(1, 'data');
    }

    // ──────────────────────────────────────────────────────────────
    // Panel: Takeaway detay
    // ──────────────────────────────────────────────────────────────

    public function test_shows_takeaway_order_detail(): void
    {
        $order = $this->createTakeawayOrder('new');

        $response = $this->withToken($this->ownerToken)
            ->getJson("/api/v1/panel/takeaway/{$order->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonPath('data.id', $order->id)
            ->assertJsonPath('data.order_type', CekirdexOrder::TYPE_TAKEAWAY);
    }

    public function test_shows_takeaway_order_returns_404_for_dine_in(): void
    {
        $dineInOrder = CekirdexOrder::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_table_id'      => CekirdexTable::create([
                'cekirdex_restaurant_id' => $this->restaurant->id,
                'name'      => 'Masa 1',
                'code'      => '1',
                'qr_token'  => CekirdexTable::newQrToken(),
                'capacity'  => 4,
                'is_active' => true,
            ])->id,
            'order_number'   => CekirdexOrder::newOrderNumber(),
            'public_code'    => CekirdexOrder::newPublicCode(),
            'order_type'     => CekirdexOrder::TYPE_DINE_IN,
            'subtotal'       => 50.00,
            'tax'            => 0.00,
            'service_charge' => 0.00,
            'discount'       => 0.00,
            'total'          => 50.00,
            'status'         => 'new',
            'payment_status' => 'pending',
        ]);

        $this->withToken($this->ownerToken)
            ->getJson("/api/v1/panel/takeaway/{$dineInOrder->id}")
            ->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // Panel: Takeaway onayla
    // ──────────────────────────────────────────────────────────────

    public function test_confirms_takeaway_order(): void
    {
        $order = $this->createTakeawayOrder('new');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/confirm", [
                'eta_minutes' => 30,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', 'confirmed');

        $this->assertDatabaseHas('cekirdex_orders', [
            'id'          => $order->id,
            'status'      => 'confirmed',
            'eta_minutes' => 30,
        ]);
    }

    public function test_confirm_fails_when_order_is_not_new(): void
    {
        $order = $this->createTakeawayOrder('confirmed');

        $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/confirm", [
                'eta_minutes' => 30,
            ])
            ->assertStatus(422);
    }

    public function test_confirm_requires_eta_minutes(): void
    {
        $order = $this->createTakeawayOrder('new');

        $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/confirm", [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['eta_minutes']);
    }

    // ──────────────────────────────────────────────────────────────
    // Panel: Takeaway durumu ilerlet
    // ──────────────────────────────────────────────────────────────

    public function test_advances_takeaway_order_status(): void
    {
        $order = $this->createTakeawayOrder('confirmed');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/advance");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'preparing');

        $this->assertDatabaseHas('cekirdex_orders', [
            'id'     => $order->id,
            'status' => 'preparing',
        ]);
    }

    public function test_advances_takeaway_order_from_preparing_to_ready(): void
    {
        $order = $this->createTakeawayOrder('preparing');

        $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/advance")
            ->assertStatus(200)
            ->assertJsonPath('status', 'ready');
    }

    public function test_advances_takeaway_order_from_ready_to_delivered(): void
    {
        $order = $this->createTakeawayOrder('ready');

        $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/advance")
            ->assertStatus(200)
            ->assertJsonPath('status', 'delivered');
    }

    public function test_advance_fails_when_order_is_already_delivered(): void
    {
        $order = $this->createTakeawayOrder('delivered');

        $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/advance")
            ->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Panel: Takeaway iptal
    // ──────────────────────────────────────────────────────────────

    public function test_cancels_takeaway_order(): void
    {
        $order = $this->createTakeawayOrder('new');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/cancel", [
                'reason' => 'Stok yok',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Sipariş iptal edildi.');

        $this->assertDatabaseHas('cekirdex_orders', [
            'id'     => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancel_fails_when_order_already_cancelled(): void
    {
        $order = $this->createTakeawayOrder('cancelled');

        $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/takeaway/{$order->id}/cancel")
            ->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Panel: Takeaway feed
    // ──────────────────────────────────────────────────────────────

    public function test_takeaway_feed_returns_active_orders(): void
    {
        $this->createTakeawayOrder('new');
        $this->createTakeawayOrder('cancelled');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/takeaway/feed');

        $response->assertStatus(200)
            ->assertJsonStructure(['ok', 'ts', 'orders'])
            ->assertJsonPath('ok', true)
            ->assertJsonCount(1, 'orders');
    }

    // ──────────────────────────────────────────────────────────────
    // Kimlik doğrulama kontrolü
    // ──────────────────────────────────────────────────────────────

    public function test_unauthenticated_request_fails(): void
    {
        $this->getJson('/api/v1/panel/takeaway')
            ->assertStatus(401);
    }

    public function test_unauthenticated_request_fails_on_detail(): void
    {
        $order = $this->createTakeawayOrder('new');

        $this->getJson("/api/v1/panel/takeaway/{$order->id}")
            ->assertStatus(401);
    }
}
