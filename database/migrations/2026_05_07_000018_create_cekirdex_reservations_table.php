<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_reservations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->unsignedBigInteger('cekirdex_branch_id')->nullable()->index();
            $table->unsignedBigInteger('cekirdex_table_id')->nullable()->index();
            $table->unsignedBigInteger('cekirdex_customer_user_id')->nullable()->index();

            $table->string('public_code', 12)->unique();
            $table->string('contact_name', 120);
            $table->string('contact_phone', 24);
            $table->string('contact_email', 160)->nullable();

            $table->dateTime('reserved_for')->index();
            $table->unsignedSmallInteger('duration_minutes')->default(90);
            $table->unsignedSmallInteger('party_size');

            // pending, confirmed, seated, completed, no_show, cancelled
            $table->string('status', 16)->default('pending')->index();

            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->unsignedBigInteger('confirmed_by_user_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancelled_by', 16)->nullable(); // customer / restaurant

            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_reservations');
    }
};
