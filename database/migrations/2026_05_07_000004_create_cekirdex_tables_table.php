<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * cekirdex_tables — masalar. qr_token globally unique, müşteri QR tarayınca
 * /cekirdex/m/{qr_token} ile menüye yönlendirilir.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->unsignedBigInteger('cekirdex_branch_id')->nullable()->index();
            $table->string('name', 64);
            $table->string('code', 32)->nullable();
            $table->string('qr_token', 40)->unique();
            $table->unsignedSmallInteger('capacity')->default(2);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_tables');
    }
};
