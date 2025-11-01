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
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete()->comment('Lead reference');
            $table->enum('activity_type', ['call', 'email', 'meeting', 'note', 'status_change', 'assignment', 'document', 'quotation'])->comment('Activity type');
            $table->string('subject', 255)->comment('Activity subject/title');
            $table->text('description')->nullable()->comment('Activity details');
            $table->string('outcome', 255)->nullable()->comment('Meeting/call outcome');
            $table->text('next_action')->nullable()->comment('Planned next steps');
            $table->timestamp('scheduled_at')->nullable()->comment('Scheduled activity time');
            $table->timestamp('completed_at')->nullable()->comment('Activity completion time');
            $table->foreignId('created_by')->constrained('users')->comment('Created by user');
            $table->timestamps();

            // Indexes for performance
            $table->index(['lead_id', 'activity_type']);
            $table->index(['lead_id', 'scheduled_at']);
            $table->index(['created_by', 'created_at']);
            $table->index('scheduled_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lead_activities');
    }
};
