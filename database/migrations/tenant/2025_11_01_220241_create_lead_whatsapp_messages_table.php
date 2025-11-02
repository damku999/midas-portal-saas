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
        Schema::create('lead_whatsapp_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->onDelete('cascade');
            $table->text('message');
            $table->string('attachment_path')->nullable();
            $table->string('attachment_type')->nullable(); // pdf, image, document
            $table->enum('status', ['pending', 'sent', 'failed', 'delivered', 'read'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->foreignId('sent_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('campaign_id')->nullable()->constrained('lead_whatsapp_campaigns')->onDelete('set null');
            $table->text('error_message')->nullable();
            $table->json('api_response')->nullable();
            $table->timestamps();

            // Indexes for better query performance
            $table->index(['lead_id', 'status']);
            $table->index(['campaign_id', 'status']);
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_whatsapp_messages');
    }
};
