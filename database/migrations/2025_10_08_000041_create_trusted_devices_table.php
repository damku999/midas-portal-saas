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
     * - 2025_09_20_120000_create_two_factor_authentication_tables (trusted_devices table)
     * - 2025_09_22_063451_fix_trusted_devices_last_used_at_column
     */
    public function up(): void
    {
        Schema::create('trusted_devices', function (Blueprint $table) {
            $table->id();
            $table->string('authenticatable_type', 125);
            $table->unsignedBigInteger('authenticatable_id');
            $table->string('device_id', 125);
            $table->string('device_name', 125);
            $table->string('device_type', 125)->nullable();
            $table->string('browser', 125)->nullable();
            $table->string('platform', 125)->nullable();
            $table->string('ip_address', 125);
            $table->string('user_agent', 125);
            $table->dateTime('last_used_at')->nullable();
            $table->timestamp('trusted_at');
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->index(['authenticatable_type', 'authenticatable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trusted_devices');
    }
};
