<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Çekirdex landing'inden gelen iletişim formu — Ininia genel `contacts`
 * tablosundan tamamen ayrı; admin panelinde Çekirdex bölümünden okunur.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->string('email', 160);
            $table->string('phone', 32)->nullable();
            $table->string('restaurant_name', 160)->nullable();
            $table->string('city', 80)->nullable();
            $table->string('subject', 200)->nullable();
            $table->text('message');
            $table->string('source', 64)->default('cekirdex_landing');
            $table->string('status', 16)->default('new')->index(); // new/read/replied/closed/spam
            $table->timestamp('read_at')->nullable();
            $table->timestamp('replied_at')->nullable();
            $table->text('notes')->nullable();
            $table->ipAddress('ip_address')->nullable();
            $table->string('user_agent', 512)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_contacts');
    }
};
