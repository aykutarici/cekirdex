<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->unsignedBigInteger('cekirdex_category_id')->index();
            $table->string('name', 160);
            $table->string('slug', 160);
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('discount_price', 10, 2)->nullable();
            $table->unsignedSmallInteger('preparation_minutes')->default(0);
            $table->boolean('is_popular')->default(false)->index();
            $table->boolean('is_new')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->json('options')->nullable();   // varyasyonlar/ek malzemeler
            $table->json('allergens')->nullable();
            $table->unsignedSmallInteger('sort_order')->default(0)->index();
            $table->timestamps();

            $table->unique(['cekirdex_restaurant_id', 'slug'], 'cekirdex_prod_rest_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_products');
    }
};
