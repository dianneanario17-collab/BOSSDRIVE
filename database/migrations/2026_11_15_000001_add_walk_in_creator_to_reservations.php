<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->string('walk_in_created_by_role')->nullable();
            $table->string('walk_in_created_by_name')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('reservations', function (Blueprint $table): void {
            $table->dropColumn(['walk_in_created_by_role', 'walk_in_created_by_name']);
        });
    }
};
