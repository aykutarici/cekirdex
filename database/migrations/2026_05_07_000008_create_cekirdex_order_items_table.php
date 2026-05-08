<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_order_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_order_id')->index();
            $table->unsignedBigInteger('cekirdex_product_id')->nullable()->index();
            $table->string('name', 160);
            $table->decimal('price', 10, 2)->default(0);
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->json('options')->nullable();
            $table->text('note')->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->string('status', 24)->default('pending'); // pending/preparing/ready/served/cancelled
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_order_items');
    }
};
