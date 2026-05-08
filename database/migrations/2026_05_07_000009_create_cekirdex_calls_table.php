<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_calls', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->unsignedBigInteger('cekirdex_branch_id')->nullable()->index();
            $table->unsignedBigInteger('cekirdex_table_id')->nullable()->index();
            $table->string('call_type', 32)->default('waiter'); // waiter/water/check/napkin/custom
            $table->text('message')->nullable();
            $table->string('status', 16)->default('pending')->index(); // pending/responded/closed
            $table->unsignedBigInteger('responded_by_user_id')->nullable();
            $table->timestamp('responded_at')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_calls');
    }
};
