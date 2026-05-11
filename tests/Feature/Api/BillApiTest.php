<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexBillClosure;
use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexOrder;
use App\Cekirdex\Models\CekirdexOrderItem;
use App\Cekirdex\Models\CekirdexPayment;
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

class BillApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;
    private CekirdexTable $table;
    private CekirdexOrder $order;
    private CekirdexProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'bill-restoran',
            'name'      => 'Bill Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@bill-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['bills.manage'], $this->owner, $this->restaurant, 'restaurant.owner.bill');
        $this->ownerToken = $this->issueToken($this->owner);

        $this->table = CekirdexTable::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'      => 'Masa 1',
            'code'      => '1',
            'qr_token'  => CekirdexTable::newQrToken(),
            'capacity'  => 4,
            'is_active' => true,
        ]);

        $category = CekirdexCategory::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'name'       => 'Ana Yemekler',
            'slug'       => 'ana-yemekler',
            'sort_order' => 1,
            'is_active'  => true,
        ]);

        $this->product = CekirdexProduct::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_category_id'   => $category->id,
            'name'      => 'Test Ürün',
            'slug'      => 'test-urun',
            'price'     => 100.00,
            'is_active' => true,
        ]);

        $this->order = CekirdexOrder::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_table_id'      => $this->table->id,
            'order_number'           => CekirdexOrder::newOrderNumber(),
            'public_code'            => CekirdexOrder::newPublicCode(),
            'order_type'             => CekirdexOrder::TYPE_DINE_IN,
            'subtotal'               => 100.00,
            'tax'                    => 0.00,
            'service_charge'         => 0.00,
            'discount'               => 0.00,
            'total'                  => 100.00,
            'status'                 => 'confirmed',
            'payment_status'         => 'pending',
        ]);

        CekirdexOrderItem::create([
            'cekirdex_order_id'   => $this->order->id,
            'cekirdex_product_id' => $this->product->id,
            'name'                => 'Test Ürün',
            'price'               => 100.00,
            'quantity'            => 1,
            'subtotal'            => 100.00,
            'status'              => 'pending',
        ]);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'bill-test');
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
    // Kimlik doğrulama
    // ──────────────────────────────────────────────────────────────

    public function test_it_lists_bills_requiring_authentication(): void
    {
        $this->getJson('/api/v1/panel/bills')->assertStatus(401);
    }

    // ──────────────────────────────────────────────────────────────
    // Hesap özeti
    // ──────────────────────────────────────────────────────────────

    public function test_it_returns_bill_summary_for_table(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->getJson("/api/v1/panel/bills/{$this->table->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(['table', 'summary'])
            ->assertJsonPath('table.id', $this->table->id);

        $this->assertEquals(100, $response->json('summary.total'));
    }

    // ──────────────────────────────────────────────────────────────
    // Ödeme kaydetme
    // ──────────────────────────────────────────────────────────────

    public function test_it_records_a_waiter_payment(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/bills/{$this->table->id}/payments", [
                'amount' => 100.00,
                'method' => 'waiter_cash',
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'payment']);

        $this->assertEquals(100, $response->json('payment.amount'));

        $this->assertDatabaseHas('cekirdex_payments', [
            'cekirdex_table_id' => $this->table->id,
            'amount'            => 100.00,
            'method'            => 'waiter_cash',
            'status'            => 'paid',
        ]);
    }

    public function test_it_fails_payment_with_invalid_method(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/bills/{$this->table->id}/payments", [
                'amount' => 50.00,
                'method' => 'gecersiz_yontem',
            ]);

        $response->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Hesap kapatma
    // ──────────────────────────────────────────────────────────────

    public function test_it_closes_a_bill_and_creates_bill_closure_record(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/bills/{$this->table->id}/close");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Hesap kapatıldı.');

        $this->assertDatabaseHas('cekirdex_bill_closures', [
            'cekirdex_table_id' => $this->table->id,
        ]);

        $this->assertDatabaseHas('cekirdex_orders', [
            'id'     => $this->order->id,
            'status' => 'closed',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Garson siparişi
    // ──────────────────────────────────────────────────────────────

    public function test_it_places_waiter_order_on_table_bill(): void
    {
        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/bills/{$this->table->id}/waiter-order", [
                'items' => [
                    ['product_id' => $this->product->id, 'quantity' => 2],
                ],
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['message', 'order_id', 'order_number', 'total']);
    }

    // ──────────────────────────────────────────────────────────────
    // Ödeme iptali
    // ──────────────────────────────────────────────────────────────

    public function test_it_cancels_a_payment(): void
    {
        $payment = CekirdexPayment::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'cekirdex_table_id'      => $this->table->id,
            'amount'                 => 50.00,
            'method'                 => 'waiter_card',
            'status'                 => 'paid',
            'split_mode'             => CekirdexPayment::SPLIT_FULL,
        ]);

        $response = $this->withToken($this->ownerToken)
            ->deleteJson("/api/v1/panel/bills/{$this->table->id}/payments/{$payment->id}");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Ödeme iptal edildi.');

        $this->assertDatabaseHas('cekirdex_payments', [
            'id'     => $payment->id,
            'status' => 'cancelled',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Başka restoranın erişimi
    // ──────────────────────────────────────────────────────────────

    public function test_another_restaurant_cannot_access_bills(): void
    {
        $otherRestaurant = CekirdexRestaurant::create([
            'slug'      => 'diger-restoran',
            'name'      => 'Diğer Restoran',
            'is_active' => true,
        ]);

        $otherOwner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $otherRestaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Diğer Sahip',
            'email'     => 'owner@diger-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['bills.manage'], $otherOwner, $otherRestaurant, 'restaurant.owner.other.bill');
        [$otherToken] = app(ApiTokenService::class)->issue($otherOwner, 'other-test');

        $this->withToken($otherToken)
            ->getJson("/api/v1/panel/bills/{$this->table->id}")
            ->assertStatus(404);
    }
}
