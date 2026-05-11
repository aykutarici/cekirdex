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

class SettingsApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'settings-restoran',
            'name'      => 'Ayarlar Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@settings-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['settings.manage'], $this->owner, $this->restaurant, 'restaurant.owner.settings');
        $this->ownerToken = $this->issueToken($this->owner);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'settings-test');
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

    // ──────────────────────────────────────────────────────────────
    // Ayarları getirme
    // ──────────────────────────────────────────────────────────────

    public function test_it_returns_settings(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->getJson('/api/v1/panel/settings');

        $response->assertStatus(200)
            ->assertJsonPath('restaurant.name', 'Ayarlar Restoran');
    }

    // ──────────────────────────────────────────────────────────────
    // Ayarları güncelleme
    // ──────────────────────────────────────────────────────────────

    public function test_it_updates_settings(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->putJson('/api/v1/panel/settings', [
                'name'     => 'Güncellenmiş Restoran',
                'currency' => 'TRY',
                'tax_rate' => 8,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Güncellenmiş Restoran');

        $this->assertDatabaseHas('cekirdex_restaurants', [
            'id'   => $this->restaurant->id,
            'name' => 'Güncellenmiş Restoran',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Şifre güncelleme
    // ──────────────────────────────────────────────────────────────

    public function test_it_updates_password(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->putJson('/api/v1/panel/settings/password', [
                'current_password'      => 'gizli1234',
                'password'              => 'yeni_sifre123',
                'password_confirmation' => 'yeni_sifre123',
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Şifreniz güncellendi.');
    }

    public function test_it_fails_with_wrong_current_password(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->putJson('/api/v1/panel/settings/password', [
                'current_password'      => 'yanlis_sifre',
                'password'              => 'yeni_sifre123',
                'password_confirmation' => 'yeni_sifre123',
            ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Mevcut şifre hatalı.');
    }

    // ──────────────────────────────────────────────────────────────
    // İzin kontrolü
    // ──────────────────────────────────────────────────────────────

    public function test_only_owner_and_manager_can_update_settings(): void
    {
        $waiter = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_WAITER,
            'name'      => 'Garson',
            'email'     => 'garson@settings-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        [$waiterToken] = app(ApiTokenService::class)->issue($waiter, 'waiter-settings-test');

        $this->withToken($waiterToken)
            ->putJson('/api/v1/panel/settings', ['name' => 'Ele Geçirilmiş'])
            ->assertStatus(403);
    }
}
