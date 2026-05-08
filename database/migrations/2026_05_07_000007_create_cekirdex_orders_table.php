<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->unsignedBigInteger('cekirdex_branch_id')->nullable()->index();
            $table->unsignedBigInteger('cekirdex_table_id')->nullable()->index();
            $table->string('order_number', 32)->unique();
            $table->string('guest_name', 120)->nullable();
            $table->string('guest_phone', 32)->nullable();
            $table->decimal('subtotal', 10, 2)->default(0);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('service_charge', 10, 2)->default(0);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2)->default(0);
            $table->string('status', 24)->default('new')->index(); // new/confirmed/preparing/ready/served/closed/cancelled
            $table->string('payment_status', 16)->default('pending'); // pending/paid/partial
            $table->string('payment_method', 24)->nullable(); // cash/card/applepay/...
            $table->text('note')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_orders');
    }
};
