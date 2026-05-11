<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\ApiAccessToken;
use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    // ──────────────────────────────────────────────────────────────
    // Yardımcı fabrika metodları
    // ──────────────────────────────────────────────────────────────

    private function createRestaurant(array $overrides = []): CekirdexRestaurant
    {
        return CekirdexRestaurant::create(array_merge([
            'slug'      => 'test-restoran',
            'name'      => 'Test Restoran',
            'is_active' => true,
        ], $overrides));
    }

    private function createStaffUser(CekirdexRestaurant $restaurant, array $overrides = []): CekirdexUser
    {
        return CekirdexUser::create(array_merge([
            'cekirdex_restaurant_id' => $restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Test Kullanıcı',
            'email'     => 'staff@example.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ], $overrides));
    }

    private function createGuestUser(array $overrides = []): CekirdexCustomerUser
    {
        return CekirdexCustomerUser::create(array_merge([
            'name'      => 'Misafir Kullanıcı',
            // Telefon normalizePhone() çıktısıyla kaydedilmeli (kayıt akışında olduğu gibi)
            'phone'     => CekirdexCustomerUser::normalizePhone('+905001112233'),
            'email'     => 'guest@example.com',
            'password'  => Hash::make('password123'),
            'is_active' => true,
        ], $overrides));
    }

    private function bearerToken($actor, string $name = 'test'): string
    {
        [$token] = app(ApiTokenService::class)->issue($actor, $name);
        return $token;
    }

    // ──────────────────────────────────────────────────────────────
    // Personel giriş
    // ──────────────────────────────────────────────────────────────

    public function test_staff_login_succeeds_with_valid_credentials(): void
    {
        $restaurant = $this->createRestaurant();
        $this->createStaffUser($restaurant);

        $response = $this->postJson('/api/v1/auth/staff/login', [
            'email'    => 'staff@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token_type', 'access_token', 'actor'])
            ->assertJsonPath('token_type', 'Bearer')
            ->assertJsonPath('actor.account_type', 'staff');
    }

    public function test_staff_login_fails_with_wrong_password(): void
    {
        $restaurant = $this->createRestaurant();
        $this->createStaffUser($restaurant);

        $response = $this->postJson('/api/v1/auth/staff/login', [
            'email'    => 'staff@example.com',
            'password' => 'yanlis_sifre',
        ]);

        $response->assertStatus(422)
            ->assertJsonPath('message', 'Giriş bilgileri hatalı veya hesap aktif değil.');
    }

    public function test_staff_login_fails_when_user_is_inactive(): void
    {
        $restaurant = $this->createRestaurant();
        $this->createStaffUser($restaurant, ['is_active' => false]);

        $response = $this->postJson('/api/v1/auth/staff/login', [
            'email'    => 'staff@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_staff_login_fails_with_missing_fields(): void
    {
        $response = $this->postJson('/api/v1/auth/staff/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    // ──────────────────────────────────────────────────────────────
    // Personel kayıt
    // ──────────────────────────────────────────────────────────────

    public function test_staff_register_creates_restaurant_and_user(): void
    {
        $response = $this->postJson('/api/v1/auth/staff/register', [
            'restaurant_name' => 'Yeni Kafe',
            'city'            => 'İstanbul',
            'name'            => 'Kafe Sahibi',
            'email'           => 'owner@yeni-kafe.com',
            'password'        => 'gizli1234',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['token_type', 'access_token', 'restaurant', 'actor'])
            ->assertJsonPath('actor.account_type', 'staff');

        $this->assertDatabaseHas('cekirdex_restaurants', ['name' => 'Yeni Kafe']);
        $this->assertDatabaseHas('cekirdex_users', ['email' => 'owner@yeni-kafe.com']);
    }

    public function test_staff_register_creates_default_categories_and_tables(): void
    {
        $this->postJson('/api/v1/auth/staff/register', [
            'restaurant_name' => 'Demo Restoran',
            'name'            => 'Sahibi',
            'email'           => 'sahibi@demo.com',
            'password'        => 'gizli1234',
        ])->assertStatus(201);

        $restaurant = CekirdexRestaurant::where('name', 'Demo Restoran')->first();

        $this->assertNotNull($restaurant);
        $this->assertDatabaseHas('cekirdex_categories', ['cekirdex_restaurant_id' => $restaurant->id, 'name' => 'Ana Yemekler']);
        $this->assertDatabaseHas('cekirdex_tables', ['cekirdex_restaurant_id' => $restaurant->id, 'name' => 'Masa 1']);
    }

    public function test_staff_register_fails_with_duplicate_email(): void
    {
        $restaurant = $this->createRestaurant();
        $this->createStaffUser($restaurant);

        $response = $this->postJson('/api/v1/auth/staff/register', [
            'restaurant_name' => 'Başka Restoran',
            'name'            => 'Başka Kişi',
            'email'           => 'staff@example.com',
            'password'        => 'gizli1234',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    // ──────────────────────────────────────────────────────────────
    // Misafir giriş
    // ──────────────────────────────────────────────────────────────

    public function test_guest_login_succeeds_with_email(): void
    {
        $this->createGuestUser();

        $response = $this->postJson('/api/v1/auth/guest/login', [
            'login'    => 'guest@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('actor.account_type', 'guest');
    }

    public function test_guest_login_succeeds_with_phone(): void
    {
        $this->createGuestUser();

        $response = $this->postJson('/api/v1/auth/guest/login', [
            'login'    => '+905001112233',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('actor.account_type', 'guest');
    }

    public function test_guest_login_fails_with_wrong_password(): void
    {
        $this->createGuestUser();

        $response = $this->postJson('/api/v1/auth/guest/login', [
            'login'    => 'guest@example.com',
            'password' => 'yanlis_sifre',
        ]);

        $response->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Misafir kayıt
    // ──────────────────────────────────────────────────────────────

    public function test_guest_register_creates_customer_user(): void
    {
        $response = $this->postJson('/api/v1/auth/guest/register', [
            'name'     => 'Yeni Misafir',
            'phone'    => '+905559876543',
            'email'    => 'yeni@example.com',
            'password' => 'gizli1234',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['access_token', 'actor'])
            ->assertJsonPath('actor.account_type', 'guest');

        $this->assertDatabaseHas('cekirdex_customer_users', ['email' => 'yeni@example.com']);
    }

    public function test_guest_register_fails_with_duplicate_phone(): void
    {
        $this->createGuestUser();

        $response = $this->postJson('/api/v1/auth/guest/register', [
            'name'     => 'Başka Misafir',
            'phone'    => '+905001112233',
            'email'    => 'baska@example.com',
            'password' => 'gizli1234',
        ]);

        $response->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Me & Logout
    // ──────────────────────────────────────────────────────────────

    public function test_me_returns_authenticated_actor(): void
    {
        $restaurant = $this->createRestaurant();
        $user = $this->createStaffUser($restaurant);
        $token = $this->bearerToken($user);

        $response = $this->withToken($token)->getJson('/api/v1/auth/me');

        $response->assertStatus(200)
            ->assertJsonPath('actor.account_type', 'staff')
            ->assertJsonPath('actor.email', 'staff@example.com');
    }

    public function test_me_returns_401_without_token(): void
    {
        $this->getJson('/api/v1/auth/me')->assertStatus(401);
    }

    public function test_logout_deletes_token(): void
    {
        $restaurant = $this->createRestaurant();
        $user = $this->createStaffUser($restaurant);
        $token = $this->bearerToken($user);

        $this->assertDatabaseCount('api_access_tokens', 1);

        $this->withToken($token)->postJson('/api/v1/auth/logout')
            ->assertStatus(200);

        $this->assertDatabaseCount('api_access_tokens', 0);
    }

    public function test_logout_returns_401_without_token(): void
    {
        $this->postJson('/api/v1/auth/logout')->assertStatus(401);
    }
}
