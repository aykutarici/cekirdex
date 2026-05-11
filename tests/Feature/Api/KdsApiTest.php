<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexOrderItem;
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

class KdsApiTest extends TestCase
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
            'slug'      => 'kds-restoran',
            'name'      => 'KDS Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@kds-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['orders.manage'], $this->owner, $this->restaurant, 'restaurant.owner.kds');
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
        [$token] = app(ApiTokenService::class)->issue($user, 'kds-test');
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

    private function createOrder(string $status = 'confirmed'): CekirdexOrder
    {
        return CekirdexOrder::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_table_id'      => $this->table->id,
            'order_number'           => CekirdexOrder::newOrderNumber(),
            'public_code'            => CekirdexOrder::newPublicCode(),
            'order_type'             => CekirdexOrder::TYPE_DINE_IN,
            'subtotal'               => 50.00,
            'tax'                    => 0.00,
            'service_charge'         => 0.00,
            'discount'               => 0.00,
            'total'                  => 50.00,
            'status'                 => $status,
            'payment_status'         => 'pending',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Mutfak ekranı
    // ──────────────────────────────────────────────────────────────

    public function test_it_returns_kitchen_feed(): void
    {
        $this->createOrder('confirmed');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/kds/feed');

        $response->assertStatus(200)
            ->assertJsonStructure(['ok', 'ts', 'orders'])
            ->assertJsonPath('ok', true)
            ->assertJsonCount(1, 'orders');
    }

    public function test_it_returns_kds_index(): void
    {
        $this->createOrder('confirmed');
        $this->createOrder('preparing');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/kds');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(2, 'data');
    }

    // ──────────────────────────────────────────────────────────────
    // Sipariş ilerleme
    // ──────────────────────────────────────────────────────────────

    public function test_it_advances_order_status_from_confirmed_to_preparing(): void
    {
        $order = $this->createOrder('confirmed');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/kds/{$order->id}/advance");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'preparing');

        $this->assertDatabaseHas('cekirdex_orders', [
            'id'     => $order->id,
            'status' => 'preparing',
        ]);
    }

    public function test_it_advances_order_status_from_preparing_to_ready(): void
    {
        $order = $this->createOrder('preparing');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/kds/{$order->id}/advance");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'ready');

        $this->assertDatabaseHas('cekirdex_orders', [
            'id'     => $order->id,
            'status' => 'ready',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Sipariş iptali
    // ──────────────────────────────────────────────────────────────

    public function test_it_cancels_an_order(): void
    {
        $order = $this->createOrder('confirmed');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/kds/{$order->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Sipariş iptal edildi.');

        $this->assertDatabaseHas('cekirdex_orders', [
            'id'     => $order->id,
            'status' => 'cancelled',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // İzin kontrolü
    // ──────────────────────────────────────────────────────────────

    public function test_kitchen_staff_cannot_access_without_orders_manage_permission(): void
    {
        $kitchenUser = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_KITCHEN,
            'name'      => 'Mutfak Çalışanı',
            'email'     => 'kitchen@kds-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        [$kitchenToken] = app(ApiTokenService::class)->issue($kitchenUser, 'kitchen-test');

        $this->withToken($kitchenToken)
            ->getJson('/api/v1/panel/kds')
            ->assertStatus(403);
    }
}
