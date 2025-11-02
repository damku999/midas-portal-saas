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
        Schema::connection('central')->create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_user_id')->nullable()->constrained('tenant_users')->onDelete('set null');
            $table->string('tenant_id')->nullable(); // Foreign key to tenants table

            // Action Details
            $table->string('action'); // e.g., 'tenant.created', 'tenant.suspended', 'subscription.cancelled'
            $table->string('description'); // Human-readable description
            $table->json('details')->nullable(); // Additional action details

            // Subject (what was acted upon)
            $table->string('subject_type')->nullable(); // e.g., 'App\Models\Central\Tenant'
            $table->string('subject_id')->nullable();

            // Request Information
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('tenant_user_id');
            $table->index('tenant_id');
            $table->index('action');
            $table->index('created_at');
            $table->index(['subject_type', 'subject_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('audit_logs');
    }
};
