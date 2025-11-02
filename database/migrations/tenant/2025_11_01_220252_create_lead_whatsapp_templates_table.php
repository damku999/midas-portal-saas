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
        Schema::create('lead_whatsapp_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('category', ['greeting', 'follow-up', 'reminder', 'promotional', 'information', 'custom'])->default('custom');
            $table->text('message_template');
            $table->json('variables')->nullable(); // Available variables: {name}, {mobile}, {source}, etc.
            $table->string('attachment_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->index(['category', 'is_active']);
            $table->index('usage_count');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_whatsapp_templates');
    }
};
