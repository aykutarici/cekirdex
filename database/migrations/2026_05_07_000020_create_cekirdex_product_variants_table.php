<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ürün varyasyonları: pizza S/M/L, kahve normal/büyük gibi.
 * Müşteri ürün eklerken bir varyasyon seçer; fiyat farkı price_adjust ile uygulanır.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_product_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_product_id')->index();
            $table->string('name', 100);
            $table->decimal('price_adjust', 8, 2)->default(0);
            $table->boolean('is_default')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_product_variants');
    }
};
