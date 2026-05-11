<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexCall;
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

class CallApiTest extends TestCase
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
            'slug'      => 'call-restoran',
            'name'      => 'Call Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@call-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['orders.manage'], $this->owner, $this->restaurant, 'restaurant.owner.call');
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
        [$token] = app(ApiTokenService::class)->issue($user, 'call-test');
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

    private function createCall(string $status = 'pending'): CekirdexCall
    {
        return CekirdexCall::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_table_id'      => $this->table->id,
            'call_type'              => 'waiter',
            'status'                 => $status,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Çağrıları listele
    // ──────────────────────────────────────────────────────────────

    public function test_it_lists_pending_calls(): void
    {
        $this->createCall('pending');
        $this->createCall('pending');
        $this->createCall('closed');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/calls');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(2, 'data');
    }

    // ──────────────────────────────────────────────────────────────
    // Feed
    // ──────────────────────────────────────────────────────────────

    public function test_it_returns_feed_of_pending_calls(): void
    {
        $this->createCall('pending');

        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/calls/feed');

        $response->assertStatus(200)
            ->assertJsonStructure(['ok', 'ts', 'calls'])
            ->assertJsonPath('ok', true)
            ->assertJsonCount(1, 'calls');
    }

    // ──────────────────────────────────────────────────────────────
    // Çağrıya yanıt verme
    // ──────────────────────────────────────────────────────────────

    public function test_it_responds_to_a_call(): void
    {
        $call = $this->createCall('pending');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/calls/{$call->id}/respond");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Çağrı yanıtlandı.');

        $this->assertDatabaseHas('cekirdex_calls', [
            'id'     => $call->id,
            'status' => 'responded',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Çağrı kapatma
    // ──────────────────────────────────────────────────────────────

    public function test_it_closes_a_call(): void
    {
        $call = $this->createCall('pending');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/calls/{$call->id}/close");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Çağrı kapatıldı.');

        $this->assertDatabaseHas('cekirdex_calls', [
            'id'     => $call->id,
            'status' => 'closed',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Başka restoranın erişimi
    // ──────────────────────────────────────────────────────────────

    public function test_another_restaurant_cannot_access_calls(): void
    {
        $call = $this->createCall('pending');

        $otherRestaurant = CekirdexRestaurant::create([
            'slug'      => 'diger-call-restoran',
            'name'      => 'Diğer Restoran',
            'is_active' => true,
        ]);

        $otherOwner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $otherRestaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Diğer Sahip',
            'email'     => 'owner@diger-call-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['orders.manage'], $otherOwner, $otherRestaurant, 'restaurant.owner.call.other');
        [$otherToken] = app(ApiTokenService::class)->issue($otherOwner, 'other-call-test');

        $this->withToken($otherToken)
            ->postJson("/api/v1/panel/calls/{$call->id}/respond")
            ->assertStatus(404);
    }
}
