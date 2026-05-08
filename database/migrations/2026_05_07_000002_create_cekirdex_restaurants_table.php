<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_restaurants', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 80)->unique();
            $table->string('name', 160);
            $table->string('logo')->nullable();
            $table->string('cover_image')->nullable();
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('city', 80)->nullable();
            $table->string('country', 80)->default('Türkiye');
            $table->string('phone', 32)->nullable();
            $table->string('email')->nullable();
            $table->string('website')->nullable();
            $table->string('currency', 8)->default('TRY');
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('service_charge_rate', 5, 2)->default(0);
            $table->boolean('accepts_online_payment')->default(false);
            $table->string('primary_color', 16)->default('#7c3aed');
            $table->string('secondary_color', 16)->default('#f472b6');
            $table->string('status', 24)->default('active')->index(); // pending/active/suspended
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_restaurants');
    }
};
