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
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('notifiable_type', 100); // customer, insurance, quotation, claim
            $table->unsignedBigInteger('notifiable_id');
            $table->foreignId('notification_type_id')->nullable()->constrained('notification_types')->onDelete('set null');
            $table->foreignId('template_id')->nullable()->constrained('notification_templates')->onDelete('set null');
            $table->string('channel', 50); // whatsapp, email, sms
            $table->string('recipient', 255); // phone/email
            $table->string('subject', 500)->nullable(); // for email
            $table->text('message_content');
            $table->json('variables_used')->nullable(); // resolved variables
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->text('error_message')->nullable();
            $table->json('api_response')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamp('next_retry_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['notifiable_type', 'notifiable_id'], 'idx_notifiable');
            $table->index('channel');
            $table->index('status');
            $table->index('sent_at');
            $table->index('created_at');
            $table->index(['status', 'retry_count', 'next_retry_at'], 'idx_retry_queue');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
