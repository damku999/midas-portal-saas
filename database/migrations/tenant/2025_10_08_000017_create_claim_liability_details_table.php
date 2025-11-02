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
        Schema::create('claim_liability_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('claim_id');
            $table->enum('claim_type', ['Cashless', 'Reimbursement']);
            $table->decimal('claim_amount', 12, 2)->nullable();
            $table->decimal('salvage_amount', 12, 2)->nullable();
            $table->decimal('less_claim_charge', 12, 2)->nullable();
            $table->decimal('amount_to_be_paid', 12, 2)->nullable();
            $table->decimal('less_salvage_amount', 12, 2)->nullable();
            $table->decimal('less_deductions', 12, 2)->nullable();
            $table->decimal('claim_amount_received', 12, 2)->nullable();
            $table->string('hospital_name', 125)->nullable();
            $table->string('hospital_address', 125)->nullable();
            $table->string('garage_name', 125)->nullable();
            $table->string('garage_address', 125)->nullable();
            $table->decimal('estimated_amount', 12, 2)->nullable();
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->index('claim_id');
            $table->index('claim_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_liability_details');
    }
};
