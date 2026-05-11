<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('guard')->default('api');
            $table->string('scope')->default('restaurant');
            $table->timestamps();
        });

        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->string('group_key')->nullable();
            $table->timestamps();
        });

        Schema::create('role_permissions', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'permission_id']);
        });

        Schema::create('model_roles', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('cekirdex_restaurant_id')->nullable()->constrained('cekirdex_restaurants')->nullOnDelete();
            $table->timestamps();
            $table->unique(['model_type', 'model_id', 'role_id', 'cekirdex_restaurant_id'], 'model_roles_unique');
        });

        Schema::create('model_permission_overrides', function (Blueprint $table) {
            $table->id();
            $table->morphs('model');
            $table->foreignId('permission_id')->constrained()->cascadeOnDelete();
            $table->enum('effect', ['allow', 'deny']);
            $table->foreignId('cekirdex_restaurant_id')->nullable()->constrained('cekirdex_restaurants')->nullOnDelete();
            $table->timestamps();
            $table->unique(['model_type', 'model_id', 'permission_id', 'cekirdex_restaurant_id'], 'model_permission_overrides_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('model_permission_overrides');
        Schema::dropIfExists('model_roles');
        Schema::dropIfExists('role_permissions');
        Schema::dropIfExists('permissions');
        Schema::dropIfExists('roles');
    }
};
