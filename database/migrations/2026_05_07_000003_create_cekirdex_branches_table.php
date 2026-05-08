<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_branches', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->string('slug', 80);
            $table->string('name', 160);
            $table->string('address')->nullable();
            $table->string('phone', 32)->nullable();
            $table->json('opening_hours')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->unique(['cekirdex_restaurant_id', 'slug'], 'cekirdex_branches_rest_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_branches');
    }
};
