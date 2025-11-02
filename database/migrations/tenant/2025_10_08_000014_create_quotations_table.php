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
        Schema::create('quotations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('customer_id');
            $table->string('vehicle_number', 125)->nullable();
            $table->string('make_model_variant', 125);
            $table->string('rto_location', 125);
            $table->year('manufacturing_year');
            $table->date('date_of_registration')->nullable(); // Manually added column
            $table->integer('cubic_capacity_kw');
            $table->integer('seating_capacity');
            $table->enum('fuel_type', ['Petrol', 'Diesel', 'CNG', 'Electric', 'Hybrid']);
            $table->decimal('ncb_percentage', 5, 2)->default(0.00); // Manually added column
            $table->decimal('idv_vehicle', 12, 2)->nullable();
            $table->decimal('idv_trailer', 12, 2)->default(0.00);
            $table->decimal('idv_cng_lpg_kit', 12, 2)->default(0.00);
            $table->decimal('idv_electrical_accessories', 12, 2)->default(0.00);
            $table->decimal('idv_non_electrical_accessories', 12, 2)->default(0.00);
            $table->decimal('total_idv', 12, 2)->nullable();
            $table->json('addon_covers')->nullable();
            $table->enum('policy_type', ['Comprehensive', 'Own Damage', 'Third Party']);
            $table->integer('policy_tenure_years')->default(1);
            $table->enum('status', ['Draft', 'Generated', 'Sent', 'Accepted', 'Rejected'])->default('Draft');
            $table->timestamp('sent_at')->nullable();
            $table->string('whatsapp_number', 125)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->index('customer_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};
