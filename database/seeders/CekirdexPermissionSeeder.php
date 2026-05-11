<?php

namespace Database\Seeders;

use App\Cekirdex\Models\CekirdexCustomerUser;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelRole;
use App\Cekirdex\Models\Permission;
use App\Cekirdex\Models\Role;
use Illuminate\Database\Seeder;

class CekirdexPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'dashboard.view' => ['Paneli görüntüle', 'dashboard'],
            'orders.view' => ['Siparişleri görüntüle', 'orders'],
            'orders.manage' => ['Sipariş yönet', 'orders'],
            'bills.manage' => ['Hesap yönet', 'bills'],
            'menu.view' => ['Menüyü görüntüle', 'menu'],
            'menu.manage' => ['Menü yönet', 'menu'],
            'tables.view' => ['Masaları görüntüle', 'tables'],
            'tables.manage' => ['Masa yönet', 'tables'],
            'staff.view' => ['Personeli görüntüle', 'staff'],
            'staff.manage' => ['Personel yönet', 'staff'],
            'reservations.view' => ['Rezervasyonları görüntüle', 'reservations'],
            'reservations.manage' => ['Rezervasyon yönet', 'reservations'],
            'reviews.view' => ['Yorumları görüntüle', 'reviews'],
            'reviews.manage' => ['Yorum yönet', 'reviews'],
            'settings.manage' => ['Ayar yönet', 'settings'],
            'reports.view' => ['Raporları görüntüle', 'reports'],
            'guest.profile.manage' => ['Misafir profil yönet', 'guest'],
            'guest.loyalty.view' => ['Misafir sadakat görüntüle', 'guest'],
            'system.admin' => ['Sistem yönetimi', 'system'],
        ];

        foreach ($permissions as $key => [$name, $group]) {
            Permission::query()->firstOrCreate(
                ['key' => $key],
                ['name' => $name, 'group_key' => $group]
            );
        }

        $roles = [
            'system.admin' => [
                'name' => 'Ininia Admin',
                'scope' => 'system',
                'permissions' => array_keys($permissions),
            ],
            'restaurant.owner' => [
                'name' => 'Restoran Sahibi',
                'scope' => 'restaurant',
                'permissions' => [
                    'dashboard.view', 'orders.view', 'orders.manage', 'bills.manage',
                    'menu.view', 'menu.manage', 'tables.view', 'tables.manage',
                    'staff.view', 'staff.manage', 'reservations.view', 'reservations.manage',
                    'reviews.view', 'reviews.manage', 'settings.manage', 'reports.view',
                ],
            ],
            'restaurant.manager' => [
                'name' => 'Yönetici',
                'scope' => 'restaurant',
                'permissions' => [
                    'dashboard.view', 'orders.view', 'orders.manage', 'bills.manage',
                    'menu.view', 'menu.manage', 'tables.view', 'reservations.view',
                    'reservations.manage', 'reviews.view', 'reviews.manage', 'reports.view',
                ],
            ],
            'restaurant.waiter' => [
                'name' => 'Garson',
                'scope' => 'restaurant',
                'permissions' => ['dashboard.view', 'orders.view', 'orders.manage', 'bills.manage', 'tables.view'],
            ],
            'restaurant.kitchen' => [
                'name' => 'Mutfak',
                'scope' => 'restaurant',
                'permissions' => ['dashboard.view', 'orders.view', 'orders.manage'],
            ],
            'guest.member' => [
                'name' => 'Misafir',
                'scope' => 'guest',
                'permissions' => ['guest.profile.manage', 'guest.loyalty.view'],
            ],
        ];

        foreach ($roles as $key => $definition) {
            $role = Role::query()->firstOrCreate(
                ['key' => $key],
                ['name' => $definition['name'], 'guard' => 'api', 'scope' => $definition['scope']]
            );

            $ids = Permission::query()
                ->whereIn('key', $definition['permissions'])
                ->pluck('id')
                ->all();

            $role->permissions()->sync($ids);
        }

        $legacyRoleMap = [
            CekirdexUser::ROLE_SUPER_ADMIN => 'system.admin',
            CekirdexUser::ROLE_OWNER => 'restaurant.owner',
            CekirdexUser::ROLE_MANAGER => 'restaurant.manager',
            CekirdexUser::ROLE_WAITER => 'restaurant.waiter',
            CekirdexUser::ROLE_KITCHEN => 'restaurant.kitchen',
        ];

        CekirdexUser::query()->each(function (CekirdexUser $user) use ($legacyRoleMap) {
            $roleKey = $legacyRoleMap[$user->role] ?? 'restaurant.manager';
            $role = Role::query()->where('key', $roleKey)->first();

            if ($role) {
                ModelRole::query()->firstOrCreate([
                    'model_type' => $user::class,
                    'model_id' => $user->id,
                    'role_id' => $role->id,
                    'cekirdex_restaurant_id' => $role->scope === 'restaurant' ? $user->cekirdex_restaurant_id : null,
                ]);
            }
        });

        $guestRole = Role::query()->where('key', 'guest.member')->first();
        if ($guestRole) {
            CekirdexCustomerUser::query()->each(function (CekirdexCustomerUser $user) use ($guestRole) {
                ModelRole::query()->firstOrCreate([
                    'model_type' => $user::class,
                    'model_id' => $user->id,
                    'role_id' => $guestRole->id,
                    'cekirdex_restaurant_id' => null,
                ]);
            });
        }
    }
}
