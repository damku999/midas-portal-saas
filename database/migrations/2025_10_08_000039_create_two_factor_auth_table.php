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
     * - 2025_09_20_120000_create_two_factor_authentication_tables (two_factor_auth table)
     * - 2025_09_20_161045_update_two_factor_auth_secret_column_size
     */
    public function up(): void
    {
        Schema::create('two_factor_auth', function (Blueprint $table) {
            $table->id();
            $table->string('authenticatable_type', 125);
            $table->unsignedBigInteger('authenticatable_id');
            $table->text('secret')->nullable(); // Changed from string(255) to text
            $table->text('recovery_codes')->nullable();
            $table->timestamp('enabled_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('is_active')->default(0);
            $table->string('backup_method', 125)->nullable();
            $table->string('backup_destination', 125)->nullable();
            $table->timestamps();

            $table->index(['authenticatable_type', 'authenticatable_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('two_factor_auth');
    }
};
