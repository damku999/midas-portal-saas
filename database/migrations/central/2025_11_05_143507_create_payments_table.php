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
        Schema::connection('central')->create('payments', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->index();
            $table->foreignId('subscription_id')->constrained('subscriptions')->onDelete('cascade');
            $table->string('payment_gateway'); // razorpay, stripe, bank_transfer
            $table->string('gateway_payment_id')->nullable()->unique(); // razorpay: pay_xxx, stripe: pi_xxx
            $table->string('gateway_order_id')->nullable(); // razorpay: order_xxx
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('INR');
            $table->enum('status', ['pending', 'processing', 'completed', 'failed', 'refunded'])->default('pending');
            $table->enum('type', ['subscription', 'renewal', 'upgrade', 'addon'])->default('subscription');
            $table->text('description')->nullable();
            $table->json('gateway_response')->nullable(); // Full response from payment gateway
            $table->json('metadata')->nullable(); // Additional custom data
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->timestamp('refunded_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['tenant_id', 'status']);
            $table->index(['subscription_id', 'status']);
            $table->index('payment_gateway');
            $table->index('created_at');

            // Foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('payments');
    }
};
