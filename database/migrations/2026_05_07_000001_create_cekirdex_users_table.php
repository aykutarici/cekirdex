<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Çekirdex — kendi kullanıcı tablosu (mevcut users tablosundan tamamen bağımsız).
 * Roller: super_admin, owner (restoran sahibi), manager, waiter, kitchen.
 * Müşteri uygulaması auth gerektirmez (QR ile gelen guest akışı).
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_users', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->nullable()->index();
            $table->string('role', 24)->default('owner')->index();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone', 32)->nullable();
            $table->string('avatar')->nullable();
            $table->string('locale', 8)->default('tr');
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->string('remember_token', 100)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_users');
    }
};
