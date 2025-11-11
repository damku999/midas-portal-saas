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
        Schema::connection('central')->create('usage_alerts', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id'); // Foreign key to tenants table
            $table->enum('resource_type', ['users', 'customers', 'storage']); // Type of resource limit
            $table->enum('threshold_level', ['warning', 'critical', 'exceeded']); // 80%, 90%, 100%
            $table->decimal('usage_percentage', 5, 2); // Actual percentage when alert triggered
            $table->integer('current_usage'); // Current count/size
            $table->integer('limit_value'); // Max allowed
            $table->enum('alert_status', ['pending', 'sent', 'acknowledged', 'resolved'])->default('pending');
            $table->timestamp('sent_at')->nullable(); // When notification was sent
            $table->timestamp('acknowledged_at')->nullable(); // When tenant acknowledged
            $table->timestamp('resolved_at')->nullable(); // When usage dropped below threshold
            $table->json('notification_channels')->nullable(); // ['email', 'dashboard'] - tracks where alert was sent
            $table->text('notes')->nullable(); // Additional context or admin notes
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('tenant_id');
            $table->index(['tenant_id', 'resource_type', 'threshold_level']); // Prevent duplicate alerts
            $table->index('alert_status');
            $table->index('created_at');

            // Foreign key to tenants table
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('usage_alerts');
    }
};
