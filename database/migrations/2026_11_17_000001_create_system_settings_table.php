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
            $table->decimal('security_deposit', 10, 2)->default(1000);
            $table->unsignedSmallInteger('booking_window_days')->default(7);
            $table->unsignedSmallInteger('max_rental_days')->default(7);
            $table->unsignedSmallInteger('min_city_days')->default(1);
            $table->unsignedSmallInteger('min_province_days')->default(1);
            $table->unsignedSmallInteger('min_long_distance_days')->default(2);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
