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
        Schema::connection('central')->create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id'); // Foreign key to tenants table (stancl/tenancy uses string IDs)
            $table->foreignId('plan_id')->constrained('plans')->onDelete('restrict');

            // Subscription Status
            $table->enum('status', ['trial', 'active', 'cancelled', 'expired', 'past_due', 'suspended'])->default('trial');

            // Trial Information
            $table->boolean('is_trial')->default(true);
            $table->timestamp('trial_ends_at')->nullable();

            // Billing Information
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();
            $table->timestamp('next_billing_date')->nullable();
            $table->decimal('mrr', 10, 2)->default(0); // Monthly Recurring Revenue

            // Payment Gateway Information
            $table->string('payment_gateway')->nullable(); // stripe, razorpay, manual
            $table->string('gateway_subscription_id')->nullable(); // ID from payment gateway
            $table->string('gateway_customer_id')->nullable(); // Customer ID from payment gateway
            $table->json('payment_method')->nullable(); // Card details (last 4 digits, etc.)

            // Cancellation
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason')->nullable();

            // Metadata
            $table->json('metadata')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('status');
            $table->index('trial_ends_at');
            $table->index('next_billing_date');

            // Foreign key to tenants table
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('subscriptions');
    }
};
