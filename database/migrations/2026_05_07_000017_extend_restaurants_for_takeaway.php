<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Restoranın halka açık landing sayfası, paket sipariş ve rezervasyon ayarları.
 * Idempotent: sütun zaten varsa atlanır (slug erken create migration'da eklenmiş olabilir).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('cekirdex_restaurants', function (Blueprint $table) {
            $cols = [
                'slug'                     => fn ($t) => $t->string('slug', 80)->nullable()->unique(),
                'accepts_takeaway'         => fn ($t) => $t->boolean('accepts_takeaway')->default(false),
                'accepts_delivery'         => fn ($t) => $t->boolean('accepts_delivery')->default(false),
                'accepts_reservations'     => fn ($t) => $t->boolean('accepts_reservations')->default(false),
                'delivery_radius_km'       => fn ($t) => $t->decimal('delivery_radius_km', 6, 2)->default(5),
                'delivery_min_amount'      => fn ($t) => $t->decimal('delivery_min_amount', 8, 2)->default(0),
                'delivery_fee'             => fn ($t) => $t->decimal('delivery_fee', 8, 2)->default(0),
                'reservation_slot_minutes' => fn ($t) => $t->unsignedSmallInteger('reservation_slot_minutes')->default(90),
                'opening_hours'            => fn ($t) => $t->json('opening_hours')->nullable(),
                'latitude'                 => fn ($t) => $t->decimal('latitude', 10, 7)->nullable(),
                'longitude'                => fn ($t) => $t->decimal('longitude', 10, 7)->nullable(),
            ];
            foreach ($cols as $name => $build) {
                if (!Schema::hasColumn('cekirdex_restaurants', $name)) {
                    $build($table);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('cekirdex_restaurants', function (Blueprint $table) {
            foreach ([
                'slug', 'accepts_takeaway', 'accepts_delivery', 'accepts_reservations',
                'delivery_radius_km', 'delivery_min_amount', 'delivery_fee',
                'reservation_slot_minutes', 'opening_hours', 'latitude', 'longitude',
            ] as $col) {
                if (Schema::hasColumn('cekirdex_restaurants', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
