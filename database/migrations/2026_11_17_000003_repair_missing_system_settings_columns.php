<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            if (! Schema::hasColumn('system_settings', 'agreement_text')) {
                $table->text('agreement_text')->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'rental_location_guide')) {
                $table->text('rental_location_guide')->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'security_deposit')) {
                $table->decimal('security_deposit', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'driver_daily_fee')) {
                $table->decimal('driver_daily_fee', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'province_gas_surcharge')) {
                $table->decimal('province_gas_surcharge', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'province_diesel_surcharge')) {
                $table->decimal('province_diesel_surcharge', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'long_distance_surcharge')) {
                $table->decimal('long_distance_surcharge', 10, 2)->nullable();
            }

            if (! Schema::hasColumn('system_settings', 'admin_page_notices')) {
                $table->json('admin_page_notices')->nullable();
            }
        });
    }

    public function down(): void
    {
    }
};
