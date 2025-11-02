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
        Schema::create('lead_whatsapp_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('message_template');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_type')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'active', 'completed', 'paused', 'cancelled'])->default('draft');
            $table->json('target_criteria')->nullable(); // Filters: status_id, source_id, priority, date ranges
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('total_leads')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('failed_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('read_count')->default(0);
            $table->integer('messages_per_minute')->default(100); // Throttling rate
            $table->boolean('auto_retry_failed')->default(true);
            $table->integer('max_retry_attempts')->default(3);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['status', 'created_at']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_whatsapp_campaigns');
    }
};
