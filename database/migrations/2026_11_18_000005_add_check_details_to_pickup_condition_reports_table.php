<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pickup_condition_reports', function (Blueprint $table): void {
            $table->json('check_details')->nullable()->after('checks');
        });
    }

    public function down(): void
    {
        Schema::table('pickup_condition_reports', function (Blueprint $table): void {
            $table->dropColumn('check_details');
        });
    }
};
