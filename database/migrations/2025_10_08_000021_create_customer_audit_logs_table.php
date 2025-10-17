<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('customer_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('action', 100);
            $table->string('resource_type', 50)->nullable();
            $table->unsignedBigInteger('resource_id')->nullable();
            $table->text('description')->nullable();
            $table->text('metadata')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('session_id', 191)->nullable();
            $table->boolean('success')->default(1);
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->index(['resource_type', 'resource_id'], 'resource_idx');
            $table->index(['action', 'created_at'], 'action_created_idx');
            $table->index(['customer_id', 'action', 'created_at'], 'customer_action_created_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customer_audit_logs');
    }
};
