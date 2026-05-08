<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Müşteri-ürün etkileşim tabloları:
 *  - likes: ürün beğenisi (toggle, müşteri başına 1 beğeni)
 *  - favorites: kullanıcının favorisi (sonraki ziyaretinde görsün)
 *  - reviews: ürüne yorum (restoran sahibi silebilir)
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_product_likes', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_customer_user_id')->index();
            $table->unsignedBigInteger('cekirdex_product_id')->index();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->timestamps();
            $table->unique(['cekirdex_customer_user_id', 'cekirdex_product_id'], 'cekirdex_likes_unique');
        });

        Schema::create('cekirdex_product_favorites', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_customer_user_id')->index();
            $table->unsignedBigInteger('cekirdex_product_id')->index();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->timestamps();
            $table->unique(['cekirdex_customer_user_id', 'cekirdex_product_id'], 'cekirdex_favs_unique');
        });

        Schema::create('cekirdex_product_reviews', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_customer_user_id')->index();
            $table->unsignedBigInteger('cekirdex_product_id')->index();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->text('content');
            $table->unsignedTinyInteger('rating')->nullable();
            $table->boolean('is_visible')->default(true)->index();
            $table->unsignedBigInteger('hidden_by_user_id')->nullable();
            $table->timestamp('hidden_at')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_product_reviews');
        Schema::dropIfExists('cekirdex_product_favorites');
        Schema::dropIfExists('cekirdex_product_likes');
    }
};
