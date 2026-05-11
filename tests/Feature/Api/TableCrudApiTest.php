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

class TableCrudApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'table-restoran',
            'name'      => 'Masa Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@table-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['tables.manage'], $this->owner, $this->restaurant, 'restaurant.owner.table');
        $this->ownerToken = $this->issueToken($this->owner);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'table-test');
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

    private function createTable(array $overrides = []): CekirdexTable
    {
        return CekirdexTable::create(array_merge([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'      => 'Test Masa',
            'code'      => 'TM',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 4,
            'is_active' => true,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────────
    // Masa oluşturma
    // ──────────────────────────────────────────────────────────────

    public function test_it_creates_a_table(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->postJson('/api/v1/panel/tables', [
                'name'     => 'Teras 1',
                'code'     => 'T1',
                'capacity' => 6,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data'])
            ->assertJsonPath('data.name', 'Teras 1');

        $this->assertDatabaseHas('cekirdex_tables', [
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'                   => 'Teras 1',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Masa güncelleme
    // ──────────────────────────────────────────────────────────────

    public function test_it_updates_a_table(): void
    {
        $table = $this->createTable(['name' => 'Eski Masa']);

        $response = $this->withToken($this->ownerToken)
            ->putJson("/api/v1/panel/tables/{$table->id}", [
                'name'      => 'Yeni Masa',
                'capacity'  => 8,
                'is_active' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Yeni Masa');
    }

    // ──────────────────────────────────────────────────────────────
    // Masa silme
    // ──────────────────────────────────────────────────────────────

    public function test_it_deletes_an_inactive_table(): void
    {
        $table = $this->createTable(['is_active' => false]);

        $response = $this->withToken($this->ownerToken)
            ->deleteJson("/api/v1/panel/tables/{$table->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('cekirdex_tables', ['id' => $table->id]);
    }

    public function test_it_cannot_delete_table_with_active_orders(): void
    {
        $table = $this->createTable();

        CekirdexOrder::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_table_id'      => $table->id,
            'order_number'           => CekirdexOrder::newOrderNumber(),
            'public_code'            => CekirdexOrder::newPublicCode(),
            'order_type'             => CekirdexOrder::TYPE_DINE_IN,
            'subtotal'               => 50.00,
            'tax'                    => 0.00,
            'service_charge'         => 0.00,
            'discount'               => 0.00,
            'total'                  => 50.00,
            'status'                 => 'confirmed',
            'payment_status'         => 'pending',
        ]);

        $response = $this->withToken($this->ownerToken)
            ->deleteJson("/api/v1/panel/tables/{$table->id}");

        $response->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // QR token yenileme
    // ──────────────────────────────────────────────────────────────

    public function test_it_regenerates_qr_token(): void
    {
        $table        = $this->createTable();
        $oldQrToken   = $table->qr_token;

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/tables/{$table->id}/regenerate-qr");

        $response->assertStatus(200)
            ->assertJsonStructure(['message', 'qr_token', 'menu_url']);

        $newQrToken = $response->json('qr_token');
        $this->assertNotEquals($oldQrToken, $newQrToken);

        $this->assertDatabaseHas('cekirdex_tables', [
            'id'       => $table->id,
            'qr_token' => $newQrToken,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Başka restoranın erişimi
    // ──────────────────────────────────────────────────────────────

    public function test_another_restaurant_cannot_manage_tables(): void
    {
        $table = $this->createTable();

        $otherRestaurant = CekirdexRestaurant::create([
            'slug'      => 'diger-table-restoran',
            'name'      => 'Diğer Restoran',
            'is_active' => true,
        ]);

        $otherOwner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $otherRestaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Diğer Sahip',
            'email'     => 'owner@diger-table-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['tables.manage'], $otherOwner, $otherRestaurant, 'restaurant.owner.table.other');
        [$otherToken] = app(ApiTokenService::class)->issue($otherOwner, 'other-table-test');

        $this->withToken($otherToken)
            ->putJson("/api/v1/panel/tables/{$table->id}", [
                'name'      => 'Ele Geçirilmiş Masa',
                'is_active' => true,
            ])
            ->assertStatus(404);
    }
}
