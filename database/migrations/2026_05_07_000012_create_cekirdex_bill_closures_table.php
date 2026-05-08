<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_bill_closures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->unsignedBigInteger('cekirdex_table_id')->index();
            $table->unsignedBigInteger('closed_by_user_id');

            $table->string('delivery_method', 24); // emailed | printed | handed | none
            $table->string('recipient_email', 160)->nullable();
            $table->boolean('email_sent')->default(false);

            $table->decimal('subtotal', 10, 2);
            $table->decimal('tax', 10, 2);
            $table->decimal('service_charge', 10, 2);
            $table->decimal('discount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
            $table->decimal('paid', 10, 2);
            $table->decimal('change_returned', 10, 2)->default(0);

            $table->json('orders_snapshot');
            $table->json('items_snapshot');
            $table->json('payments_snapshot');

            $table->string('ip_address', 45)->nullable();
            $table->text('note')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_bill_closures');
    }
};
