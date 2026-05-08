<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('cekirdex_orders', function (Blueprint $table) {
            $table->unsignedBigInteger('cekirdex_customer_user_id')->nullable()->after('cekirdex_table_id')->index();
        });
    }

    public function down(): void
    {
        Schema::table('cekirdex_orders', function (Blueprint $table) {
            $table->dropColumn('cekirdex_customer_user_id');
        });
    }
};
