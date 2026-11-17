<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->json('vehicle_categories')->nullable();
            $table->json('vehicle_transmissions')->nullable();
            $table->json('vehicle_fuel_types')->nullable();
            $table->json('vehicle_capacities')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table) {
            $table->dropColumn([
                'vehicle_categories',
                'vehicle_transmissions',
                'vehicle_fuel_types',
                'vehicle_capacities',
            ]);
        });
    }
};
