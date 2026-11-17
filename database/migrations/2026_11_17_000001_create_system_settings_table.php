<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();
            $table->string('business_name')->default('Big Boss Car Rental');
            $table->string('support_name')->default('Big Boss Dispatcher');
            $table->string('support_phone', 30)->default('+63 912 345 6789');
            $table->string('messenger_url')->nullable();
            $table->string('pickup_location');
            $table->text('agreement_text');
            $table->text('rental_location_guide');
            $table->decimal('security_deposit', 10, 2)->default(1000);
            $table->unsignedSmallInteger('booking_window_days')->default(7);
            $table->unsignedSmallInteger('max_rental_days')->default(7);
            $table->unsignedSmallInteger('min_city_days')->default(1);
            $table->unsignedSmallInteger('min_province_days')->default(1);
            $table->unsignedSmallInteger('min_long_distance_days')->default(2);
            $table->decimal('driver_daily_fee', 10, 2)->default(1000);
            $table->decimal('province_gas_surcharge', 10, 2)->default(1000);
            $table->decimal('province_diesel_surcharge', 10, 2)->default(500);
            $table->decimal('long_distance_surcharge', 10, 2)->default(1500);
            $table->json('admin_page_notices')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
