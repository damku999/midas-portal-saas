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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number', 50)->unique()->comment('Auto-generated lead number (LD-YYYYMM-XXXX)');
            $table->string('name', 255)->comment('Lead full name');
            $table->string('email', 255)->nullable()->comment('Email address');
            $table->string('mobile_number', 20)->comment('Primary mobile number');
            $table->string('alternate_mobile', 20)->nullable()->comment('Alternate mobile number');
            $table->string('city', 100)->nullable()->comment('City');
            $table->string('state', 100)->nullable()->comment('State');
            $table->string('pincode', 10)->nullable()->comment('PIN code');
            $table->text('address')->nullable()->comment('Full address');
            $table->date('date_of_birth')->nullable()->comment('Date of birth');
            $table->integer('age')->nullable()->comment('Age (calculated field)');
            $table->string('occupation', 255)->nullable()->comment('Occupation');

            // Foreign keys
            $table->foreignId('source_id')->constrained('lead_sources')->comment('Lead source');
            $table->string('product_interest', 255)->nullable()->comment('Product interest (Vehicle/Life/Health)');
            $table->foreignId('status_id')->constrained('lead_statuses')->comment('Current lead status');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium')->comment('Lead priority');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete()->comment('Assigned user');
            $table->foreignId('relationship_manager_id')->nullable()->constrained('relationship_managers')->nullOnDelete()->comment('Relationship manager');
            $table->foreignId('reference_user_id')->nullable()->constrained('reference_users')->nullOnDelete()->comment('Reference user');

            // Follow-up and tracking
            $table->date('next_follow_up_date')->nullable()->comment('Next follow-up date');
            $table->text('remarks')->nullable()->comment('Internal notes and remarks');

            // Conversion tracking
            $table->foreignId('converted_customer_id')->nullable()->constrained('customers')->nullOnDelete()->comment('Converted customer ID');
            $table->timestamp('converted_at')->nullable()->comment('Conversion timestamp');
            $table->text('conversion_notes')->nullable()->comment('Conversion details and notes');

            // Loss tracking
            $table->text('lost_reason')->nullable()->comment('Reason for marking lead as lost');
            $table->timestamp('lost_at')->nullable()->comment('Loss timestamp');

            // Audit fields
            $table->foreignId('created_by')->constrained('users')->comment('Created by user');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete()->comment('Updated by user');
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('lead_number');
            // Note: Email index removed due to MySQL key length limitation (varchar 255)
            $table->index('mobile_number');
            $table->index(['source_id', 'status_id']);
            $table->index(['assigned_to', 'status_id']);
            $table->index('next_follow_up_date');
            $table->index('converted_customer_id');
            $table->index(['created_at', 'status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
