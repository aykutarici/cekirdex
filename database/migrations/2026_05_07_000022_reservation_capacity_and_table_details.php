<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rezervasyon kapasitesi modları, ileri tarih günü ve masa düzeyinde rezervasyon detayı.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('cekirdex_restaurants', function (Blueprint $table) {
            if (!Schema::hasColumn('cekirdex_restaurants', 'reservation_capacity_mode')) {
                $table->string('reservation_capacity_mode', 16)->default('tables')->after('reservation_slot_minutes');
            }
            if (!Schema::hasColumn('cekirdex_restaurants', 'reservation_total_capacity')) {
                $table->unsignedSmallInteger('reservation_total_capacity')->nullable()->after('reservation_capacity_mode');
            }
            if (!Schema::hasColumn('cekirdex_restaurants', 'reservation_table_count')) {
                $table->unsignedSmallInteger('reservation_table_count')->nullable()->after('reservation_total_capacity');
            }
            if (!Schema::hasColumn('cekirdex_restaurants', 'reservation_seat_count')) {
                $table->unsignedSmallInteger('reservation_seat_count')->nullable()->after('reservation_table_count');
            }
            if (!Schema::hasColumn('cekirdex_restaurants', 'reservation_advance_days')) {
                $table->unsignedSmallInteger('reservation_advance_days')->default(30)->after('reservation_seat_count');
            }
            if (!Schema::hasColumn('cekirdex_restaurants', 'reservation_slot_interval_minutes')) {
                $table->unsignedSmallInteger('reservation_slot_interval_minutes')->default(30)->after('reservation_advance_days');
            }
        });

        Schema::table('cekirdex_tables', function (Blueprint $table) {
            if (!Schema::hasColumn('cekirdex_tables', 'photo')) {
                $table->string('photo', 255)->nullable()->after('capacity');
            }
            if (!Schema::hasColumn('cekirdex_tables', 'internal_note')) {
                $table->text('internal_note')->nullable()->after('photo');
            }
            if (!Schema::hasColumn('cekirdex_tables', 'accepts_reservations')) {
                $table->boolean('accepts_reservations')->default(true)->after('internal_note');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cekirdex_restaurants', function (Blueprint $table) {
            foreach ([
                'reservation_capacity_mode', 'reservation_total_capacity', 'reservation_table_count',
                'reservation_seat_count', 'reservation_advance_days', 'reservation_slot_interval_minutes',
            ] as $col) {
                if (Schema::hasColumn('cekirdex_restaurants', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('cekirdex_tables', function (Blueprint $table) {
            foreach (['photo', 'internal_note', 'accepts_reservations'] as $col) {
                if (Schema::hasColumn('cekirdex_tables', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
