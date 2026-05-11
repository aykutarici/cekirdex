<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelRole;
use App\Cekirdex\Models\Permission;
use App\Cekirdex\Models\Role;
use App\Cekirdex\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class StaffCrudApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'staff-restoran',
            'name'      => 'Personel Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@staff-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['staff.manage'], $this->owner, $this->restaurant, 'restaurant.owner.staff');
        $this->ownerToken = $this->issueToken($this->owner);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'staff-test');
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

    private function createStaff(array $overrides = []): CekirdexUser
    {
        static $counter = 0;
        $counter++;

        return CekirdexUser::create(array_merge([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_WAITER,
            'name'      => 'Garson ' . $counter,
            'email'     => 'garson' . $counter . '@staff-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────────
    // Personel oluşturma
    // ──────────────────────────────────────────────────────────────

    public function test_it_creates_a_staff_member(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->postJson('/api/v1/panel/staff', [
                'name'     => 'Yeni Garson',
                'email'    => 'yeni@staff-restoran.com',
                'role'     => 'waiter',
                'password' => 'gizli1234',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data'])
            ->assertJsonPath('data.name', 'Yeni Garson');

        $this->assertDatabaseHas('cekirdex_users', [
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'email'                  => 'yeni@staff-restoran.com',
            'role'                   => 'waiter',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Personel güncelleme
    // ──────────────────────────────────────────────────────────────

    public function test_it_updates_a_staff_member(): void
    {
        $staff = $this->createStaff(['name' => 'Eski İsim']);

        $response = $this->withToken($this->ownerToken)
            ->putJson("/api/v1/panel/staff/{$staff->id}", [
                'name'      => 'Yeni İsim',
                'email'     => $staff->email,
                'role'      => 'waiter',
                'is_active' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Yeni İsim');
    }

    // ──────────────────────────────────────────────────────────────
    // Personel silme
    // ──────────────────────────────────────────────────────────────

    public function test_it_deletes_a_staff_member(): void
    {
        $staff = $this->createStaff();

        $response = $this->withToken($this->ownerToken)
            ->deleteJson("/api/v1/panel/staff/{$staff->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('cekirdex_users', ['id' => $staff->id]);
    }

    public function test_it_cannot_delete_self(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->deleteJson("/api/v1/panel/staff/{$this->owner->id}");

        $response->assertStatus(422);

        $this->assertDatabaseHas('cekirdex_users', ['id' => $this->owner->id]);
    }

    // ──────────────────────────────────────────────────────────────
    // Başka restoranın erişimi
    // ──────────────────────────────────────────────────────────────

    public function test_another_restaurant_cannot_manage_staff(): void
    {
        $staff = $this->createStaff();

        $otherRestaurant = CekirdexRestaurant::create([
            'slug'      => 'diger-staff-restoran',
            'name'      => 'Diğer Restoran',
            'is_active' => true,
        ]);

        $otherOwner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $otherRestaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Diğer Sahip',
            'email'     => 'owner@diger-staff-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['staff.manage'], $otherOwner, $otherRestaurant, 'restaurant.owner.staff.other');
        [$otherToken] = app(ApiTokenService::class)->issue($otherOwner, 'other-staff-test');

        $this->withToken($otherToken)
            ->deleteJson("/api/v1/panel/staff/{$staff->id}")
            ->assertStatus(404);
    }
}
