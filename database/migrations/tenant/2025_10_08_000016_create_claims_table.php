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
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->string('claim_number', 125)->unique();
            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('customer_insurance_id');
            $table->enum('insurance_type', ['Health', 'Vehicle']);
            $table->date('incident_date');
            $table->text('description')->nullable();
            $table->string('whatsapp_number', 125)->nullable();
            $table->boolean('send_email_notifications')->default(1);
            $table->boolean('status')->default(1);
            $table->timestamps();
            $table->softDeletes();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->integer('deleted_by')->nullable();

            $table->index('customer_id');
            $table->index('customer_insurance_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
