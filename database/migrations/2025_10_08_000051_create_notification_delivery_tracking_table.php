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
        Schema::create('notification_delivery_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('notification_log_id')->constrained('notification_logs')->onDelete('cascade');
            $table->enum('status', ['sent', 'delivered', 'read', 'failed'])->default('sent');
            $table->timestamp('tracked_at');
            $table->json('provider_status')->nullable(); // API provider status
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('notification_log_id');
            $table->index('tracked_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_delivery_tracking');
    }
};
