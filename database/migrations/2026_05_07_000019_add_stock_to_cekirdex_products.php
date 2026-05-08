<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ürün başına stok yönetimi.
 *
 * - is_in_stock: garson/owner anlık "Bugün yok" toggle'ı (boolean)
 * - stock_quantity: opsiyonel sayısal stok (null = takip edilmiyor, sınırsız)
 * - track_stock: stok adedi takip ediliyor mu? (false ise sadece is_in_stock kullanılır)
 *
 * Idempotent: var olan kolon varsa atlanır.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('cekirdex_products', function (Blueprint $table) {
            if (!Schema::hasColumn('cekirdex_products', 'is_in_stock')) {
                $table->boolean('is_in_stock')->default(true)->index();
            }
            if (!Schema::hasColumn('cekirdex_products', 'track_stock')) {
                $table->boolean('track_stock')->default(false);
            }
            if (!Schema::hasColumn('cekirdex_products', 'stock_quantity')) {
                $table->integer('stock_quantity')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cekirdex_products', function (Blueprint $table) {
            foreach (['is_in_stock', 'track_stock', 'stock_quantity'] as $col) {
                if (Schema::hasColumn('cekirdex_products', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
