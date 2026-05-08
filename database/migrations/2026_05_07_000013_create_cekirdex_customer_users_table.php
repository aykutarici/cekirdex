<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Çekirdex müşteri (son kullanıcı) hesapları.
 *
 * Restoran panelindeki personel hesapları (cekirdex_users) ile karıştırılmamalıdır.
 * Bu tablo, QR ile menüye gelen müşterilerin opsiyonel kayıt olduğu hesapları tutar.
 * Müşteri kaydı zorunlu değildir; sadece beğen/yorum/favori için gereklidir.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_customer_users', function (Blueprint $table) {
            $table->id();
            $table->string('phone', 24)->unique();
            $table->string('name', 120);
            $table->string('email', 160)->nullable()->index();
            $table->string('password');
            $table->string('avatar')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_customer_users');
    }
};
