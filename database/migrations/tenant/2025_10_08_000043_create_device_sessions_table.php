<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Source: 2025_09_20_120001_create_additional_security_tables.php (device_sessions table)
     */
    public function up(): void
    {
        Schema::create('device_sessions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('device_tracking_id');
            $table->string('session_id', 125);
            $table->string('ip_address', 125);
            $table->string('location_country', 125)->nullable();
            $table->string('location_city', 125)->nullable();
            $table->decimal('location_lat', 10, 8)->nullable();
            $table->decimal('location_lng', 11, 8)->nullable();
            $table->timestamp('started_at');
            $table->timestamp('last_activity_at');
            $table->timestamp('ended_at')->nullable();
            $table->integer('duration_seconds')->nullable();
            $table->json('activity_summary')->nullable();
            $table->boolean('is_suspicious')->default(0);
            $table->json('risk_factors')->nullable();
            $table->timestamps();

            $table->index(['device_tracking_id', 'started_at'], 'device_sessions_device_time_idx');
            $table->index(['session_id', 'ended_at'], 'device_sessions_session_idx');
            $table->index(['ip_address', 'started_at'], 'device_sessions_ip_time_idx');
            $table->index(['is_suspicious', 'started_at'], 'device_sessions_suspicious_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_sessions');
    }
};
