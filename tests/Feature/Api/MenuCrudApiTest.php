<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelRole;
use App\Cekirdex\Models\Permission;
use App\Cekirdex\Models\Role;
use App\Cekirdex\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MenuCrudApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'menu-restoran',
            'name'      => 'Menü Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@menu-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['menu.manage'], $this->owner, $this->restaurant, 'restaurant.owner.menu');
        $this->ownerToken = $this->issueToken($this->owner);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'menu-test');
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

    private function createCategory(array $overrides = []): CekirdexCategory
    {
        return CekirdexCategory::create(array_merge([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'       => 'Test Kategori',
            'slug'       => 'test-kategori-' . uniqid(),
            'sort_order' => 1,
            'is_active'  => true,
        ], $overrides));
    }

    private function createProduct(CekirdexCategory $category, array $overrides = []): CekirdexProduct
    {
        return CekirdexProduct::create(array_merge([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_category_id'   => $category->id,
            'name'      => 'Test Ürün',
            'slug'      => 'test-urun-' . uniqid(),
            'price'     => 50.00,
            'is_active' => true,
        ], $overrides));
    }

    // ──────────────────────────────────────────────────────────────
    // Kategori oluşturma
    // ──────────────────────────────────────────────────────────────

    public function test_it_creates_a_category(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->postJson('/api/v1/panel/menu/categories', [
                'name'       => 'Tatlılar',
                'sort_order' => 1,
                'is_active'  => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data'])
            ->assertJsonPath('data.name', 'Tatlılar');

        $this->assertDatabaseHas('cekirdex_categories', [
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'                   => 'Tatlılar',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Kategori güncelleme
    // ──────────────────────────────────────────────────────────────

    public function test_it_updates_a_category(): void
    {
        $category = $this->createCategory(['name' => 'Eski Ad', 'slug' => 'eski-ad']);

        $response = $this->withToken($this->ownerToken)
            ->putJson("/api/v1/panel/menu/categories/{$category->id}", [
                'name'      => 'Yeni Ad',
                'is_active' => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Yeni Ad');
    }

    // ──────────────────────────────────────────────────────────────
    // Kategori silme
    // ──────────────────────────────────────────────────────────────

    public function test_it_deletes_an_empty_category(): void
    {
        $category = $this->createCategory();

        $response = $this->withToken($this->ownerToken)
            ->deleteJson("/api/v1/panel/menu/categories/{$category->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('cekirdex_categories', ['id' => $category->id]);
    }

    public function test_it_cannot_delete_category_with_products(): void
    {
        $category = $this->createCategory();
        $this->createProduct($category);

        $response = $this->withToken($this->ownerToken)
            ->deleteJson("/api/v1/panel/menu/categories/{$category->id}");

        $response->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Ürün oluşturma
    // ──────────────────────────────────────────────────────────────

    public function test_it_creates_a_product(): void
    {
        $category = $this->createCategory();

        $response = $this->withToken($this->ownerToken)
            ->postJson('/api/v1/panel/menu/products', [
                'cekirdex_category_id' => $category->id,
                'name'                 => 'Yeni Ürün',
                'price'                => 75.00,
                'is_active'            => true,
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'data'])
            ->assertJsonPath('data.name', 'Yeni Ürün');

        $this->assertDatabaseHas('cekirdex_products', [
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'                   => 'Yeni Ürün',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Ürün güncelleme
    // ──────────────────────────────────────────────────────────────

    public function test_it_updates_a_product(): void
    {
        $category = $this->createCategory();
        $product  = $this->createProduct($category, ['name' => 'Eski Ürün']);

        $response = $this->withToken($this->ownerToken)
            ->putJson("/api/v1/panel/menu/products/{$product->id}", [
                'cekirdex_category_id' => $category->id,
                'name'                 => 'Güncellenmiş Ürün',
                'price'                => 90.00,
                'is_active'            => true,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Güncellenmiş Ürün');
    }

    // ──────────────────────────────────────────────────────────────
    // Stok durumu
    // ──────────────────────────────────────────────────────────────

    public function test_it_toggles_product_stock(): void
    {
        $category = $this->createCategory();
        $product  = $this->createProduct($category);

        $initialStock = $product->is_in_stock;

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/menu/products/{$product->id}/toggle-stock");

        $response->assertStatus(200)
            ->assertJsonPath('is_in_stock', !$initialStock);
    }

    // ──────────────────────────────────────────────────────────────
    // Aktiflik durumu
    // ──────────────────────────────────────────────────────────────

    public function test_it_toggles_product_active_status(): void
    {
        $category = $this->createCategory();
        $product  = $this->createProduct($category, ['is_active' => true]);

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/menu/products/{$product->id}/toggle-active");

        $response->assertStatus(200)
            ->assertJsonPath('is_active', false);
    }

    // ──────────────────────────────────────────────────────────────
    // Ürün silme
    // ──────────────────────────────────────────────────────────────

    public function test_it_deletes_a_product(): void
    {
        $category = $this->createCategory();
        $product  = $this->createProduct($category);

        $response = $this->withToken($this->ownerToken)
            ->deleteJson("/api/v1/panel/menu/products/{$product->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('cekirdex_products', ['id' => $product->id]);
    }

    // ──────────────────────────────────────────────────────────────
    // Başka restoranın erişimi
    // ──────────────────────────────────────────────────────────────

    public function test_another_restaurant_cannot_manage_menu(): void
    {
        $category = $this->createCategory();

        $otherRestaurant = CekirdexRestaurant::create([
            'slug'      => 'diger-menu-restoran',
            'name'      => 'Diğer Restoran',
            'is_active' => true,
        ]);

        $otherOwner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $otherRestaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Diğer Sahip',
            'email'     => 'owner@diger-menu-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['menu.manage'], $otherOwner, $otherRestaurant, 'restaurant.owner.menu.other');
        [$otherToken] = app(ApiTokenService::class)->issue($otherOwner, 'other-menu-test');

        $this->withToken($otherToken)
            ->putJson("/api/v1/panel/menu/categories/{$category->id}", [
                'name'      => 'Ele Geçirilmiş Kategori',
                'is_active' => true,
            ])
            ->assertStatus(404);
    }
}
