<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Source: 2025_09_20_120001_create_additional_security_tables.php (device_security_events table)
     */
    public function up(): void
    {
        Schema::create('device_security_events', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_tracking_id');
            $table->string('event_type', 125);
            $table->string('event_severity', 125);
            $table->text('description');
            $table->json('event_data')->nullable();
            $table->string('ip_address', 125);
            $table->string('user_agent', 125);
            $table->timestamp('occurred_at');
            $table->boolean('is_resolved')->default(0);
            $table->timestamp('resolved_at')->nullable();
            $table->string('resolved_by', 125)->nullable();
            $table->text('resolution_notes')->nullable();
            $table->timestamps();

            $table->index(['device_tracking_id', 'occurred_at'], 'device_events_device_time_idx');
            $table->index(['event_type', 'event_severity'], 'device_events_type_severity_idx');
            $table->index(['is_resolved', 'occurred_at'], 'device_events_resolution_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_security_events');
    }
};
