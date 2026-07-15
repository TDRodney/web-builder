<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('tenants')
            ->whereNull('site_setup_completed_at')
            ->update(['site_setup_completed_at' => now()]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The schema rollback removes this irreversible historical backfill.
    }
};
