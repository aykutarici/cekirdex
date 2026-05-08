<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cekirdex_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('cekirdex_order_items', 'cekirdex_product_variant_id')) {
                $table->unsignedBigInteger('cekirdex_product_variant_id')->nullable()->index()->after('cekirdex_product_id');
            }
            if (!Schema::hasColumn('cekirdex_order_items', 'variant_label')) {
                $table->string('variant_label', 100)->nullable()->after('cekirdex_product_variant_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cekirdex_order_items', function (Blueprint $table) {
            foreach (['cekirdex_product_variant_id', 'variant_label'] as $col) {
                if (Schema::hasColumn('cekirdex_order_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
