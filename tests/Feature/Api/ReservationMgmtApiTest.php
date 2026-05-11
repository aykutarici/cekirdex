<?php

namespace Tests\Feature\Api;

use App\Cekirdex\Models\CekirdexReservation;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelRole;
use App\Cekirdex\Models\Permission;
use App\Cekirdex\Models\Role;
use App\Cekirdex\Services\ApiTokenService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReservationMgmtApiTest extends TestCase
{
    use RefreshDatabase;

    private CekirdexRestaurant $restaurant;
    private CekirdexUser $owner;
    private string $ownerToken;

    protected function setUp(): void
    {
        parent::setUp();

        $this->restaurant = CekirdexRestaurant::create([
            'slug'      => 'rez-restoran',
            'name'      => 'Rezervasyon Restoran',
            'is_active' => true,
        ]);

        $this->owner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Restoran Sahibi',
            'email'     => 'owner@rez-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['reservations.manage'], $this->owner, $this->restaurant, 'restaurant.owner.reservation');
        $this->ownerToken = $this->issueToken($this->owner);
    }

    private function issueToken(CekirdexUser $user): string
    {
        [$token] = app(ApiTokenService::class)->issue($user, 'rez-test');
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

    private function createReservation(string $status = 'pending'): CekirdexReservation
    {
        return CekirdexReservation::create([
            'cekirdex_restaurant_id' => $this->restaurant->id,
            'public_code'            => CekirdexReservation::newPublicCode(),
            'contact_name'           => 'Ali Veli',
            'contact_phone'          => '+905551234567',
            'reserved_for'           => now()->addDay()->addHours(2),
            'party_size'             => 4,
            'status'                 => $status,
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Rezervasyon onaylama
    // ──────────────────────────────────────────────────────────────

    public function test_it_confirms_a_pending_reservation(): void
    {
        $reservation = $this->createReservation('pending');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/reservations/{$reservation->id}/confirm");

        $response->assertStatus(200)
            ->assertJsonPath('status', 'confirmed');

        $this->assertDatabaseHas('cekirdex_reservations', [
            'id'     => $reservation->id,
            'status' => 'confirmed',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Rezervasyon iptali
    // ──────────────────────────────────────────────────────────────

    public function test_it_cancels_a_reservation(): void
    {
        $reservation = $this->createReservation('pending');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/reservations/{$reservation->id}/cancel");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Rezervasyon iptal edildi.');

        $this->assertDatabaseHas('cekirdex_reservations', [
            'id'     => $reservation->id,
            'status' => 'cancelled',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Gelmedi işareti
    // ──────────────────────────────────────────────────────────────

    public function test_it_marks_reservation_as_no_show(): void
    {
        $reservation = $this->createReservation('confirmed');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/reservations/{$reservation->id}/no-show");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Gelmedi olarak işaretlendi.');

        $this->assertDatabaseHas('cekirdex_reservations', [
            'id'     => $reservation->id,
            'status' => 'no_show',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // Tamamlandı işareti
    // ──────────────────────────────────────────────────────────────

    public function test_it_marks_reservation_as_completed(): void
    {
        $reservation = $this->createReservation('confirmed');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/reservations/{$reservation->id}/complete");

        $response->assertStatus(200)
            ->assertJsonPath('message', 'Rezervasyon tamamlandı.');

        $this->assertDatabaseHas('cekirdex_reservations', [
            'id'     => $reservation->id,
            'status' => 'completed',
        ]);
    }

    // ──────────────────────────────────────────────────────────────
    // İptal edilmiş rezervasyon onaylanamaz
    // ──────────────────────────────────────────────────────────────

    public function test_it_cannot_confirm_a_cancelled_reservation(): void
    {
        $reservation = $this->createReservation('cancelled');

        $response = $this->withToken($this->ownerToken)
            ->postJson("/api/v1/panel/reservations/{$reservation->id}/confirm");

        $response->assertStatus(422);
    }

    // ──────────────────────────────────────────────────────────────
    // Başka restoranın erişimi
    // ──────────────────────────────────────────────────────────────

    public function test_another_restaurant_cannot_manage_reservations(): void
    {
        $reservation = $this->createReservation('pending');

        $otherRestaurant = CekirdexRestaurant::create([
            'slug'      => 'diger-rez-restoran',
            'name'      => 'Diğer Restoran',
            'is_active' => true,
        ]);

        $otherOwner = CekirdexUser::create([
            'cekirdex_restaurant_id' => $otherRestaurant->id,
            'role'      => CekirdexUser::ROLE_OWNER,
            'name'      => 'Diğer Sahip',
            'email'     => 'owner@diger-rez-restoran.com',
            'password'  => Hash::make('gizli1234'),
            'is_active' => true,
        ]);

        $this->seedPermissions(['reservations.manage'], $otherOwner, $otherRestaurant, 'restaurant.owner.reservation.other');
        [$otherToken] = app(ApiTokenService::class)->issue($otherOwner, 'other-rez-test');

        $this->withToken($otherToken)
            ->postJson("/api/v1/panel/reservations/{$reservation->id}/confirm")
            ->assertStatus(404);
    }
}
