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
        Schema::create('quotation_companies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('quotation_id');
            $table->unsignedBigInteger('insurance_company_id');
            $table->string('quote_number', 125)->unique();
            $table->string('policy_type', 125)->nullable();
            $table->integer('policy_tenure_years')->nullable()->default(1);
            $table->decimal('idv_vehicle', 12, 2)->nullable();
            $table->decimal('idv_trailer', 12, 2)->nullable()->default(0.00);
            $table->decimal('idv_cng_lpg_kit', 12, 2)->nullable()->default(0.00);
            $table->decimal('idv_electrical_accessories', 12, 2)->nullable()->default(0.00);
            $table->decimal('idv_non_electrical_accessories', 12, 2)->nullable()->default(0.00);
            $table->decimal('total_idv', 12, 2)->nullable();
            $table->string('plan_name', 125)->nullable(); // Manually added column
            $table->decimal('basic_od_premium', 12, 2)->nullable()->default(0.00);
            $table->decimal('tp_premium', 10, 2)->nullable();
            $table->decimal('ncb_percentage', 5, 2)->nullable()->default(0.00); // Manually added column
            $table->decimal('cng_lpg_premium', 12, 2)->nullable()->default(0.00);
            $table->decimal('total_od_premium', 12, 2)->nullable()->default(0.00);
            $table->longText('addon_covers_breakdown')->nullable();
            $table->decimal('total_addon_premium', 12, 2)->nullable()->default(0.00);
            $table->decimal('net_premium', 12, 2)->nullable()->default(0.00);
            $table->decimal('sgst_amount', 12, 2)->nullable()->default(0.00);
            $table->decimal('cgst_amount', 12, 2)->nullable()->default(0.00);
            $table->decimal('total_premium', 12, 2)->nullable()->default(0.00);
            $table->decimal('roadside_assistance', 12, 2)->nullable()->default(0.00);
            $table->decimal('final_premium', 12, 2)->nullable()->default(0.00);
            $table->boolean('is_recommended')->default(0);
            $table->text('recommendation_note')->nullable();
            $table->integer('ranking')->nullable()->default(1);
            $table->text('benefits')->nullable();
            $table->text('exclusions')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->index(['quotation_id', 'ranking']);
            $table->index('insurance_company_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotation_companies');
    }
};
