<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Consolidates migrations:
     * - 2025_09_20_120000_create_two_factor_authentication_tables (two_factor_attempts table)
     * - 2025_09_22_055533_fix_two_factor_attempts_nullable_columns
     */
    public function up(): void
    {
        Schema::create('two_factor_attempts', function (Blueprint $table) {
            $table->id();
            $table->string('authenticatable_type', 125);
            $table->unsignedBigInteger('authenticatable_id');
            $table->string('code_type', 125); // 'totp', 'recovery', 'sms', 'email'
            $table->string('ip_address', 125)->nullable();
            $table->text('user_agent')->nullable();
            $table->boolean('successful')->default(0);
            $table->string('failure_reason', 125)->nullable();
            $table->timestamp('attempted_at'); // NOT NULL
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_attempts');
    }
};
