<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Müşteri QR özelliklerine yönelik testler.
 *
 * NOT: Çoğu endpoint (müşteri kimlik doğrulama, adisyon, ürün tepkileri,
 * yorumlar vb.) henüz uygulanmadığından ilgili testler markTestIncomplete()
 * ile işaretlenmiştir.
 */
class CustomerQrApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexTable $table;
    private CekirdexCategory $category;
    private CekirdexProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'musteri-restoran',
            'name'      => 'Müşteri Restoran',
            'is_active' => true,
        ]);

        $this->table = CekirdexTable::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'      => 'Masa 1',
            'code'      => '1',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 4,
            'is_active' => true,
        ]);

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
            'price'      => 50.00,
            'is_active'  => true,
        ]);
    }

    private function createCustomerUser(array $overrides = []): CekirdexCustomerUser
    {
        return CekirdexCustomerUser::create(array_merge([
            'name'      => 'Test Müşteri',
            'phone'     => CekirdexCustomerUser::normalizePhone('+905001112233'),
            'email'     => 'musteri@example.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ], $overrides));
    }

    private function bearerToken(CekirdexCustomerUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'customer-test');
        return $token;
    }

    // ──────────────────────────────────────────────────────────────
    // Müşteri kayıt (QR masaya bağlı)
    // ──────────────────────────────────────────────────────────────

    public function test_customer_can_register_at_qr_table(): void
    {
        $response = $this->postJson("/api/v1/tables/{$this->table->qr_token}/auth/register", [
            'name'  => 'Yeni Müşteri',
            'phone' => '+905009998877',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token_type', 'access_token', 'actor']);

        $this->assertDatabaseHas('cekirdex_customer_users', ['name' => 'Yeni Müşteri']);
    }

    // ──────────────────────────────────────────────────────────────
    // Müşteri telefon ile giriş
    // ──────────────────────────────────────────────────────────────

    public function test_customer_can_login_with_phone(): void
    {
        $customer = $this->createCustomerUser(['phone' => CekirdexCustomerUser::normalizePhone('+905009998866')]);

        $response = $this->postJson("/api/v1/tables/{$this->table->qr_token}/auth/login", [
            'phone' => '+905009998866',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token_type', 'access_token', 'actor']);
    }

    // ──────────────────────────────────────────────────────────────
    // Adisyon görüntüle
    // ──────────────────────────────────────────────────────────────

    public function test_customer_can_get_bill(): void
    {
        $response = $this->getJson("/api/v1/tables/{$this->table->qr_token}/bill");

        // Returns either a summary or 200 with empty bill
        $response->assertStatus(200)
            ->assertJsonStructure(['data' => ['table_id', 'total', 'has_open_orders']]);
    }

    // ──────────────────────────────────────────────────────────────
    // Ürün tepkileri görüntüle
    // ──────────────────────────────────────────────────────────────

    public function test_customer_can_view_product_reactions(): void
    {
        $response = $this->getJson(
            "/api/v1/tables/{$this->table->qr_token}/products/{$this->product->id}/reactions"
        );

        $response->assertStatus(200)
            ->assertJsonStructure(['like_count', 'favorite_count', 'liked', 'favorited']);
    }

    // ──────────────────────────────────────────────────────────────
    // Ürün beğeni
    // ──────────────────────────────────────────────────────────────

    public function test_authenticated_customer_can_like_product(): void
    {
        $customer = $this->createCustomerUser();
        $token = $this->bearerToken($customer);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/v1/tables/{$this->table->qr_token}/products/{$this->product->id}/like");

        $response->assertStatus(200)
            ->assertJsonStructure(['liked', 'like_count']);
    }

    // ──────────────────────────────────────────────────────────────
    // Ürün yorum ekle
    // ──────────────────────────────────────────────────────────────

    public function test_authenticated_customer_can_add_review(): void
    {
        $customer = $this->createCustomerUser();
        $token = $this->bearerToken($customer);

        $response = $this->withHeaders(['Authorization' => "Bearer $token"])
            ->postJson("/api/v1/tables/{$this->table->qr_token}/products/{$this->product->id}/reviews", [
                'rating'  => 5,
                'content' => 'Harika bir ürün!',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['review' => ['rating', 'content']]);
    }

    // ──────────────────────────────────────────────────────────────
    // Ürün yorumlarını listele
    // ──────────────────────────────────────────────────────────────

    public function test_can_list_product_reviews(): void
    {
        $response = $this->getJson(
            "/api/v1/tables/{$this->table->qr_token}/products/{$this->product->id}/reviews"
        );

        $response->assertStatus(200)
            ->assertJsonStructure(['reviews']);
    }

    // ──────────────────────────────────────────────────────────────
    // Mevcut menü endpoint'i (var olduğu doğrulandı)
    // ──────────────────────────────────────────────────────────────

    public function test_customer_can_view_menu_via_qr_token(): void
    {
        $response = $this->getJson("/api/v1/tables/{$this->table->qr_token}/menu");

        $response->assertStatus(200)
            ->assertJsonPath('table.name', 'Masa 1')
            ->assertJsonPath('restaurant.name', 'Müşteri Restoran')
            ->assertJsonStructure(['table', 'restaurant', 'categories']);
    }

    public function test_menu_returns_404_for_invalid_qr_token(): void
    {
        $this->getJson('/api/v1/tables/gecersiz-qr-token/menu')
            ->assertStatus(404);
    }

    public function test_menu_returns_404_for_inactive_table(): void
    {
        $inactiveTable = CekirdexTable::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'      => 'Pasif Masa',
            'code'      => '99',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 2,
            'is_active' => false,
        ]);

        $this->getJson("/api/v1/tables/{$inactiveTable->qr_token}/menu")
            ->assertStatus(404);
    }
}
