<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('cekirdex_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cekirdex_restaurant_id')->index();
            $table->unsignedBigInteger('cekirdex_table_id')->nullable()->index();
            $table->unsignedBigInteger('cekirdex_order_id')->nullable()->index();

            // Hesap (bill) — bir masada birden çok sipariş olabilir; ödemeler sipariş-bağımsız da yapılabilir
            $table->decimal('amount', 10, 2);

            // pending → kullanıcı/garson ödeme oluşturdu, henüz onaylanmadı (nakit gibi)
            // paid    → ödeme tamamlandı
            // refunded, cancelled, failed
            $table->string('status', 24)->default('paid')->index();

            // Ödeme yöntemi
            //   online_card / online_apple_pay / online_google_pay  → müşteri sistemden ödedi
            //   waiter_card / waiter_cash                            → garson tahsilat onayladı
            //   bank_transfer / fast / qr                            → diğer
            $table->string('method', 32);

            // Provider/sağlayıcı: iyzico, paytr, manual, simulated
            $table->string('provider', 32)->default('simulated');
            $table->string('transaction_id')->nullable();

            // Bölme modu: items, equal, amount, full
            $table->string('split_mode', 16)->default('amount');
            $table->json('selected_items')->nullable();   // [{order_item_id, quantity, unit_price}]
            $table->string('portion_label', 80)->nullable();
            $table->unsignedSmallInteger('split_total_parts')->nullable(); // eşit bölmede toplam pay sayısı
            $table->unsignedSmallInteger('split_part_index')->nullable();  // bu ödemenin payı

            $table->string('payer_name', 120)->nullable();
            $table->string('ip_address', 45)->nullable();

            // Garson onayı (nakit/kart elden vs için)
            $table->unsignedBigInteger('confirmed_by_user_id')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('paid_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cekirdex_payments');
    }
};
