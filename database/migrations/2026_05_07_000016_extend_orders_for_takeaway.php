<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Çekirdex sipariş akışlarını masada-yemek dışına genişletir.
 * Idempotent: var olan kolonlar atlanır.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('cekirdex_orders', function (Blueprint $table) {
            $cols = [
                'order_type'        => fn ($t) => $t->string('order_type', 16)->default('dine_in')->index(),
                'public_code'       => fn ($t) => $t->string('public_code', 12)->nullable()->unique(),
                'contact_name'      => fn ($t) => $t->string('contact_name', 120)->nullable(),
                'contact_phone'     => fn ($t) => $t->string('contact_phone', 24)->nullable(),
                'contact_email'     => fn ($t) => $t->string('contact_email', 160)->nullable(),
                'delivery_address'  => fn ($t) => $t->text('delivery_address')->nullable(),
                'delivery_lat'      => fn ($t) => $t->decimal('delivery_lat', 10, 7)->nullable(),
                'delivery_lng'      => fn ($t) => $t->decimal('delivery_lng', 10, 7)->nullable(),
                'delivery_fee'      => fn ($t) => $t->decimal('delivery_fee', 8, 2)->default(0),
                'eta_minutes'       => fn ($t) => $t->unsignedSmallInteger('eta_minutes')->nullable(),
                'ready_at'          => fn ($t) => $t->timestamp('ready_at')->nullable(),
                'delivered_at'      => fn ($t) => $t->timestamp('delivered_at')->nullable(),
            ];
            foreach ($cols as $name => $build) {
                if (!Schema::hasColumn('cekirdex_orders', $name)) {
                    $build($table);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('cekirdex_orders', function (Blueprint $table) {
            foreach ([
                'order_type', 'public_code',
                'contact_name', 'contact_phone', 'contact_email',
                'delivery_address', 'delivery_lat', 'delivery_lng', 'delivery_fee',
                'eta_minutes', 'ready_at', 'delivered_at',
            ] as $col) {
                if (Schema::hasColumn('cekirdex_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
