<?php

namespace Tests\Feature;

use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelPermissionOverride;
use App\Cekirdex\Models\ModelRole;
use App\Cekirdex\Models\Permission;
use App\Cekirdex\Models\Role;
use App\Cekirdex\Services\PermissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CekirdexPermissionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_role_permissions_can_be_overridden_per_user(): void
    {
        $restaurant = CekirdexRestaurant::create([
            'slug' => 'test-restoran',
            'name' => 'Test Restoran',
        ]);

        $user = CekirdexUser::create([
            'cekirdex_restaurant_id' => $restaurant->id,
            'role' => CekirdexUser::ROLE_WAITER,
            'name' => 'Garson',
            'email' => 'garson@example.com',
            'password' => Hash::make('password'),
            'is_active' => true,
        ]);

        $ordersView = Permission::create(['key' => 'orders.view', 'name' => 'Siparişleri görüntüle']);
        $staffManage = Permission::create(['key' => 'staff.manage', 'name' => 'Personel yönet']);

        $role = Role::create(['key' => 'restaurant.waiter', 'name' => 'Garson', 'scope' => 'restaurant']);
        $role->permissions()->attach($ordersView->id);

        ModelRole::create([
            'model_type' => $user::class,
            'model_id' => $user->id,
            'role_id' => $role->id,
            'cekirdex_restaurant_id' => $restaurant->id,
        ]);

        $service = app(PermissionService::class);

        $this->assertTrue($service->can($user, 'orders.view', $restaurant->id));
        $this->assertFalse($service->can($user, 'staff.manage', $restaurant->id));

        ModelPermissionOverride::create([
            'model_type' => $user::class,
            'model_id' => $user->id,
            'permission_id' => $staffManage->id,
            'effect' => 'allow',
            'cekirdex_restaurant_id' => $restaurant->id,
        ]);

        $this->assertTrue($service->can($user, 'staff.manage', $restaurant->id));

        ModelPermissionOverride::create([
            'model_type' => $user::class,
            'model_id' => $user->id,
            'permission_id' => $ordersView->id,
            'effect' => 'deny',
            'cekirdex_restaurant_id' => $restaurant->id,
        ]);

        $this->assertFalse($service->can($user, 'orders.view', $restaurant->id));
    }
}
