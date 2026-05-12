<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexOrder;
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

class ServiceApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;
    private CekirdexTable $table;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'servis-restoran',
            'name'      => 'Servis Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@servis-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['orders.manage'], $this->owner, $this->restaurant, 'restaurant.owner.service');
        $this->ownerToken = $this->issueToken($this->owner);

        $this->table = CekirdexTable::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'      => 'Masa 1',
            'code'      => '1',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 4,
            'is_active' => true,
        ]);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'service-test');
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

    private function createDineInOrder(string $status = 'ready'): CekirdexOrder
    {
        return CekirdexOrder::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_table_id'      => $this->table->id,
            'order_number'           => CekirdexOrder::newOrderNumber(),
            'public_code'            => CekirdexOrder::newPublicCode(),
            'order_type'             => CekirdexOrder::TYPE_DINE_IN,
            'subtotal'               => 80.00,
            'tax'                    => 0.00,
            'service_charge'         => 0.00,
            'discount'               => 0.00,
            'total'                  => 80.00,
            'status'                 => $status,
            'payment_status'         => 'pending',
            'ready_at'               => $status === 'ready' ? now() : null,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Servis ekranı listesi
    // ──────────────────────────────────────────────────────────────

    public function test_lists_ready_orders_for_service(): void
    {
        $this->createDineInOrder('ready');
        $this->createDineInOrder('ready');
        $this->createDineInOrder('confirmed');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/service');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(2, 'data');
    }

    public function test_service_index_returns_empty_when_no_ready_orders(): void
    {
        $this->createDineInOrder('confirmed');
        $this->createDineInOrder('preparing');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/service');

        $response->assertStatus(200)
            ->assertJsonCount(0, 'data');
    }

    public function test_service_index_only_returns_dine_in_orders(): void
    {
        $this->createDineInOrder('ready');

        CekirdexOrder::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'order_number'           => CekirdexOrder::newOrderNumber(),
            'public_code'            => CekirdexOrder::newPublicCode(),
            'order_type'             => CekirdexOrder::TYPE_TAKEAWAY,
            'subtotal'               => 50.00,
            'tax'                    => 0.00,
            'service_charge'         => 0.00,
            'discount'               => 0.00,
            'total'                  => 50.00,
            'status'                 => 'ready',
            'payment_status'         => 'pending',
            'ready_at'               => now(),
        ]);

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/service');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    // ──────────────────────────────────────────────────────────────
    // Servis et
    // ──────────────────────────────────────────────────────────────

    public function test_marks_order_as_served(): void
    {
        $order = $this->createDineInOrder('ready');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/service/{$order->id}/serve");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Sipariş servis edildi olarak işaretlendi.');

        $this->assertDatabaseHas('cekirdex_orders', [
            'id'     => $order->id,
            'status' => 'served',
        ]);
    }

    public function test_serve_fails_when_order_is_not_ready(): void
    {
        $order = $this->createDineInOrder('confirmed');

        $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/service/{$order->id}/serve")
            ->assertStatus(422);
    }

    public function test_serve_returns_404_for_nonexistent_order(): void
    {
        $this->withToken($this->ownerToken)
            ->postJson('/api/v1/panel/service/99999/serve')
            ->assertStatus(404);
    }

    // ──────────────────────────────────────────────────────────────
    // Servis feed
    // ──────────────────────────────────────────────────────────────

    public function test_feed_returns_ready_orders(): void
    {
        $this->createDineInOrder('ready');
        $this->createDineInOrder('served');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/service/feed');

        $response->assertStatus(200)
            ->assertJsonStructure(['ok', 'ts', 'orders'])
            ->assertJsonPath('ok', true)
            ->assertJsonCount(1, 'orders');
    }

    public function test_feed_returns_order_structure(): void
    {
        $this->createDineInOrder('ready');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/service/feed');

        $response->assertStatus(200);

        $order = $response->json('orders.0');
        $this->assertArrayHasKey('id', $order);
        $this->assertArrayHasKey('order_number', $order);
        $this->assertArrayHasKey('table', $order);
        $this->assertArrayHasKey('items', $order);
    }

    // ──────────────────────────────────────────────────────────────
    // Yeni sipariş onayla (bu endpoint mevcut değil)
    // ──────────────────────────────────────────────────────────────

    public function test_confirm_new_order(): void
    {
        $order = $this->createDineInOrder('new');

        $response = $this->withHeaders(['Authorization' => "Bearer {$this->ownerToken}"])
            ->postJson("/api/v1/panel/service/{$order->id}/confirm");

        $response->assertStatus(200);
        $this->assertDatabaseHas('cekirdex_orders', ['id' => $order->id, 'status' => 'confirmed']);
    }

    // ──────────────────────────────────────────────────────────────
    // Kimlik doğrulama kontrolü
    // ──────────────────────────────────────────────────────────────

    public function test_unauthenticated_request_fails(): void
    {
        $this->getJson('/api/v1/panel/service')
            ->assertStatus(401);
    }

    public function test_unauthenticated_feed_request_fails(): void
    {
        $this->getJson('/api/v1/panel/service/feed')
            ->assertStatus(401);
    }

    public function test_unauthenticated_serve_fails(): void
    {
        $order = $this->createDineInOrder('ready');

        $this->postJson("/api/v1/panel/service/{$order->id}/serve")
            ->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────────
    // İzin kontrolü
    // ──────────────────────────────────────────────────────────────

    public function test_user_without_permission_cannot_access_service(): void
    {
        $noPermUser = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_KITCHEN,
            'name'      => 'İzinsiz Kullanıcı',
            'email'     => 'noperm@servis-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        [$noPermToken] = app(ApiTokenService::class)->issue($noPermUser, 'noperm-test');

        $this->withToken($noPermToken)
            ->getJson('/api/v1/panel/service')
            ->assertStatus(403);
    }
}
