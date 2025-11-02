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
        Schema::connection('central')->create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // e.g., "Starter", "Professional", "Enterprise"
            $table->string('slug')->unique(); // e.g., "starter", "professional"
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2); // Monthly price
            $table->enum('billing_interval', ['monthly', 'yearly'])->default('monthly');
            $table->json('features')->nullable(); // JSON array of feature names

            // Limits
            $table->integer('max_users')->default(-1); // -1 = unlimited
            $table->integer('max_customers')->default(-1); // -1 = unlimited
            $table->integer('max_leads_per_month')->default(-1); // -1 = unlimited
            $table->integer('storage_limit_gb')->default(5); // Storage limit in GB

            // Metadata
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('metadata')->nullable(); // Additional custom data

            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('slug');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->dropIfExists('plans');
    }
};
