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
     * - 2025_09_22_054514_create_audit_logs_table
     * - 2025_09_22_054820_add_missing_columns_to_audit_logs_table
     * - 2025_09_22_060651_fix_audit_logs_risk_score_column
     * - 2025_09_22_060849_fix_audit_logs_action_column_default
     */
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('auditable_type', 125);
            $table->unsignedBigInteger('auditable_id');
            $table->string('actor_type', 125)->nullable();
            $table->unsignedBigInteger('actor_id')->nullable();
            $table->string('action', 125)->nullable();
            $table->string('event', 125);
            $table->string('event_category', 125);
            $table->string('target_type', 125)->nullable();
            $table->unsignedBigInteger('target_id')->nullable();
            $table->json('properties')->nullable();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->json('metadata')->nullable();
            $table->string('ip_address', 125)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 125)->nullable();
            $table->string('request_id', 125)->nullable();
            $table->timestamp('occurred_at');
            $table->string('severity', 125)->default('info');
            $table->decimal('risk_score', 5, 2)->nullable();
            $table->string('risk_level', 125)->nullable();
            $table->json('risk_factors')->nullable();
            $table->boolean('is_suspicious')->default(0);
            $table->string('category', 125)->nullable();
            $table->timestamps();

            $table->index(['auditable_type', 'auditable_id']);
            $table->index(['actor_type', 'actor_id']);
            $table->index('action');
            $table->index('event');
            $table->index('event_category');
            $table->index('ip_address');
            $table->index('occurred_at');
            $table->index('severity');
            $table->index('risk_level');
            $table->index('is_suspicious');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
