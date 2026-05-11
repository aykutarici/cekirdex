<?php

namespace Database\Seeders;

use App\Cekirdex\Models\CekirdexCategory;
use App\Cekirdex\Models\CekirdexProduct;
use App\Cekirdex\Models\CekirdexRestaurant;
use App\Cekirdex\Models\CekirdexTable;
use App\Cekirdex\Models\CekirdexUser;
use App\Cekirdex\Models\ModelRole;
use App\Cekirdex\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CekirdexDemoSeeder extends Seeder
{
    public function run(): void
    {
        $restaurant = CekirdexRestaurant::query()->firstOrCreate(
            ['slug' => 'demir-kafe'],
            [
                'name' => 'Demir Kafe',
                'description' => 'Demo QR menü, sipariş ve rezervasyon akışları için örnek restoran.',
                'city' => 'İstanbul',
                'country' => 'TR',
                'phone' => '+90 212 000 00 00',
                'email' => 'demo@cekirdex.test',
                'currency' => 'TRY',
                'tax_rate' => 10,
                'service_charge_rate' => 0,
                'accepts_online_payment' => false,
                'accepts_takeaway' => true,
                'accepts_delivery' => false,
                'accepts_reservations' => true,
                'status' => 'active',
                'is_active' => true,
            ],
        );

        $owner = CekirdexUser::query()->firstOrCreate(
            ['email' => 'owner@cekirdex.test'],
            [
                'cekirdex_restaurant_id' => $restaurant->id,
                'role' => CekirdexUser::ROLE_OWNER,
                'name' => 'Demo Owner',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        );

        $ownerRole = Role::query()->where('key', 'restaurant.owner')->first();
        if ($ownerRole) {
            ModelRole::query()->firstOrCreate([
                'model_type' => $owner::class,
                'model_id' => $owner->id,
                'role_id' => $ownerRole->id,
                'cekirdex_restaurant_id' => $restaurant->id,
            ]);
        }

        $table = CekirdexTable::query()->firstOrCreate(
            ['qr_token' => 'demo-qr-token'],
            [
                'cekirdex_restaurant_id' => $restaurant->id,
                'name' => 'Masa 1',
                'code' => '1',
                'capacity' => 4,
                'accepts_reservations' => true,
                'is_active' => true,
            ],
        );

        $category = CekirdexCategory::query()->firstOrCreate(
            ['cekirdex_restaurant_id' => $restaurant->id, 'slug' => 'kahveler'],
            [
                'name' => 'Kahveler',
                'description' => 'Günlük taze çekirdeklerle hazırlanan içecekler.',
                'sort_order' => 1,
                'is_active' => true,
            ],
        );

        CekirdexProduct::query()->firstOrCreate(
            ['cekirdex_restaurant_id' => $restaurant->id, 'slug' => 'flat-white'],
            [
                'cekirdex_category_id' => $category->id,
                'name' => 'Flat White',
                'description' => 'Çift shot espresso ve kadifemsi süt.',
                'price' => 120,
                'preparation_minutes' => 6,
                'is_popular' => true,
                'is_new' => false,
                'is_active' => true,
                'is_in_stock' => true,
                'track_stock' => false,
                'sort_order' => 1,
            ],
        );

        CekirdexProduct::query()->firstOrCreate(
            ['cekirdex_restaurant_id' => $restaurant->id, 'slug' => 'limonlu-cheesecake'],
            [
                'cekirdex_category_id' => $category->id,
                'name' => 'Limonlu Cheesecake',
                'description' => 'Hafif limon kreması ve bisküvi tabanı.',
                'price' => 160,
                'preparation_minutes' => 3,
                'is_popular' => false,
                'is_new' => true,
                'is_active' => true,
                'is_in_stock' => true,
                'track_stock' => false,
                'sort_order' => 2,
            ],
        );

        $this->command?->info("Demo restoran hazır: {$restaurant->slug}, QR: {$table->qr_token}, owner: owner@cekirdex.test / password");
    }
}
