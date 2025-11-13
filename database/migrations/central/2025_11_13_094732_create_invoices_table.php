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
        Schema::connection('central')->create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique(); // A00001, A00002, etc.
            $table->string('tenant_id')->nullable();
            $table->unsignedBigInteger('subscription_id')->nullable();
            $table->unsignedBigInteger('payment_id')->nullable();

            // Customer Details
            $table->string('customer_name');
            $table->text('customer_address')->nullable();
            $table->string('customer_gstin')->nullable();
            $table->string('customer_pan')->nullable();
            $table->string('customer_email')->nullable();
            $table->string('customer_phone')->nullable();

            // Invoice Details
            $table->date('invoice_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'paid', 'unpaid', 'cancelled'])->default('unpaid');

            // Line Items (stored as JSON for flexibility)
            $table->json('items'); // [{description, hsn_sac, quantity, rate, amount}]

            // Amount Breakdown
            $table->decimal('subtotal', 10, 2); // Before tax
            $table->decimal('cgst_rate', 5, 2)->default(9.00); // 9%
            $table->decimal('cgst_amount', 10, 2)->default(0);
            $table->decimal('sgst_rate', 5, 2)->default(9.00); // 9%
            $table->decimal('sgst_amount', 10, 2)->default(0);
            $table->decimal('igst_rate', 5, 2)->default(0); // For inter-state
            $table->decimal('igst_amount', 10, 2)->default(0);
            $table->decimal('total_tax', 10, 2)->default(0);
            $table->decimal('round_off', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2); // After tax

            // Payment Gateway Charges
            $table->decimal('gateway_charges', 10, 2)->default(0);
            $table->decimal('gateway_charges_gst', 10, 2)->default(0);
            $table->decimal('total_with_gateway_charges', 10, 2)->default(0);

            // Payment Tracking
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->decimal('balance_due', 10, 2)->default(0);
            $table->timestamp('paid_at')->nullable();

            // Notes & Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();

            // PDF Storage
            $table->string('pdf_path')->nullable();

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('tenant_id');
            $table->index('subscription_id');
            $table->index('payment_id');
            $table->index('invoice_date');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
