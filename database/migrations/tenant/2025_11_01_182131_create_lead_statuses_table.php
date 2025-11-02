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
        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique()->comment('Lead status name (e.g., New, Contacted, Interested)');
            $table->string('description', 255)->nullable()->comment('Lead status description');
            $table->string('color', 20)->nullable()->comment('Badge color for UI display (e.g., success, warning, danger)');
            $table->boolean('is_active')->default(true)->comment('Active status');
            $table->boolean('is_converted')->default(false)->comment('Indicates if this status means lead is converted');
            $table->boolean('is_lost')->default(false)->comment('Indicates if this status means lead is lost');
            $table->integer('display_order')->default(0)->comment('Display order for sorting');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index('is_active');
            $table->index(['is_converted', 'is_lost']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_statuses');
    }
};
