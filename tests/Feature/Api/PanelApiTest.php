<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexCategory;
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

class PanelApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'panel-restoran',
            'name'      => 'Panel Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@panel-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        // Tüm panel izinlerini bir role ekle
        $this->seedPanelPermissions();
        $this->ownerToken = $this->issueToken($this->owner);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'panel-test');
        return $token;
    }

    private function seedPanelPermissions(): void
    {
        $permissions = [
            'dashboard.view', 'orders.view', 'menu.view',
            'tables.view', 'staff.view', 'reservations.view',
            'reviews.view', 'settings.manage',
        ];

        $role = Role::create(['key' => 'restaurant.owner', 'name' => 'Sahip', 'scope' => 'restaurant']);

        foreach ($permissions as $key) {
            $perm = Permission::create(['key' => $key, 'name' => $key]);
            $role->permissions()->attach($perm->id);
        }

        ModelRole::create([
            'model_type'                => $this->owner::class,
            'model_id'                  => $this->owner->id,
            'role_id'                   => $role->id,
            'cekirdex_restaurant_id'    => $this->restaurant->id,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Kimlik doğrulama kontrolü
    // ──────────────────────────────────────────────────────────────

    public function test_panel_endpoints_require_authentication(): void
    {
        $endpoints = [
            '/api/v1/panel/dashboard',
            '/api/v1/panel/orders',
            '/api/v1/panel/menu',
            '/api/v1/panel/tables',
            '/api/v1/panel/staff',
            '/api/v1/panel/reservations',
            '/api/v1/panel/reviews',
            '/api/v1/panel/settings',
        ];

        foreach ($endpoints as $endpoint) {
            $this->getJson($endpoint)->assertStatus(401);
        }
    }

    // ──────────────────────────────────────────────────────────────
    // Dashboard
    // ──────────────────────────────────────────────────────────────

    public function test_dashboard_returns_metrics(): void
    {
        $response = $this->withToken($this->ownerToken)->getJson('/api/v1/panel/dashboard');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'metrics' => ['orders', 'tables', 'reservations', 'staff'],
                'recent_orders',
            ]);
    }

    public function test_dashboard_metrics_reflect_actual_data(): void
    {
        CekirdexTable::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'      => 'Masa 1',
            'code'      => '1',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 4,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->ownerToken)->getJson('/api/v1/panel/dashboard');

        $this->assertEquals(1, $response->json('metrics.tables'));
        $this->assertEquals(1, $response->json('metrics.staff')); // owner kendisi
    }

    // ──────────────────────────────────────────────────────────────
    // Menü
    // ──────────────────────────────────────────────────────────────

    public function test_menu_returns_categories_with_products(): void
    {
        $category = CekirdexCategory::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'       => 'Tatlılar',
            'slug'       => 'tatlilar',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        CekirdexProduct::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_category_id'   => $category->id,
            'name'      => 'Baklava',
            'slug'      => 'baklava',
            'price'     => 80.0,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->ownerToken)->getJson('/api/v1/panel/menu');

        $response->assertStatus(200)
            ->assertJsonStructure(['categories'])
            ->assertJsonCount(1, 'categories');
    }

    // ──────────────────────────────────────────────────────────────
    // Masalar
    // ──────────────────────────────────────────────────────────────

    public function test_tables_returns_restaurant_tables(): void
    {
        CekirdexTable::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'      => 'Teras 1',
            'code'      => 'T1',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 6,
            'is_active' => true,
        ]);

        $response = $this->withToken($this->ownerToken)->getJson('/api/v1/panel/tables');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data');
    }

    // ──────────────────────────────────────────────────────────────
    // Personel
    // ──────────────────────────────────────────────────────────────

    public function test_staff_returns_restaurant_users(): void
    {
        $response = $this->withToken($this->ownerToken)->getJson('/api/v1/panel/staff');

        $response->assertStatus(200)
            ->assertJsonStructure(['data'])
            ->assertJsonCount(1, 'data');
    }

    // ──────────────────────────────────────────────────────────────
    // Misafir kullanıcı panel endpointine erişemez
    // ──────────────────────────────────────────────────────────────

    public function test_guest_user_cannot_access_panel(): void
    {
        $guest = \App\Cekirdex\Models\CekirdexCustomerUser::create([
            'name'      => 'Misafir',
            'phone'     => '+905001112233',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        [$guestToken] = app(ApiTokenService::class)->issue($guest, 'guest-test', ['guest:*']);

        $this->withToken($guestToken)->getJson('/api/v1/panel/dashboard')
            ->assertStatus(403);
    }

    // ──────────────────────────────────────────────────────────────
    // Ayarlar
    // ──────────────────────────────────────────────────────────────

    public function test_settings_returns_restaurant_data(): void
    {
        $response = $this->withToken($this->ownerToken)->getJson('/api/v1/panel/settings');

        $response->assertStatus(200)
            ->assertJsonPath('restaurant.name', 'Panel Restoran');
    }
}
