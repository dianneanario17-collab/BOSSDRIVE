<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->text('rental_contract_text')->nullable()->after('agreement_text');
        });

        DB::table('system_settings')
            ->whereNull('rental_contract_text')
            ->update(['rental_contract_text' => DB::raw('agreement_text')]);
    }

    public function down(): void
    {
        Schema::table('system_settings', function (Blueprint $table): void {
            $table->dropColumn('rental_contract_text');
        });
    }
};
