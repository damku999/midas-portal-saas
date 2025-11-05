<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // MySQL doesn't support direct ENUM modification, so we need to:
        // 1. First, alter the ENUM to include ALL values (old + new)
        DB::statement("ALTER TABLE plans MODIFY COLUMN billing_interval ENUM('monthly', 'yearly', 'week', 'month', 'two_month', 'quarter', 'six_month', 'year') NOT NULL DEFAULT 'monthly'");

        // 2. Then update existing values
        DB::statement("UPDATE plans SET billing_interval = 'month' WHERE billing_interval = 'monthly'");
        DB::statement("UPDATE plans SET billing_interval = 'year' WHERE billing_interval = 'yearly'");

        // 3. Finally, remove old values from ENUM
        DB::statement("ALTER TABLE plans MODIFY COLUMN billing_interval ENUM('week', 'month', 'two_month', 'quarter', 'six_month', 'year') NOT NULL DEFAULT 'month'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert back to old values
        DB::statement("UPDATE plans SET billing_interval = 'monthly' WHERE billing_interval = 'month'");
        DB::statement("UPDATE plans SET billing_interval = 'yearly' WHERE billing_interval = 'year'");

        // Revert ENUM to original values
        DB::statement("ALTER TABLE plans MODIFY COLUMN billing_interval ENUM('monthly', 'yearly') NOT NULL DEFAULT 'monthly'");
    }
};
