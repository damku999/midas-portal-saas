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
        Schema::create('device_tracking', function (Blueprint $table) {
            $table->id();
            $table->string('trackable_type', 125);
            $table->unsignedBigInteger('trackable_id');
            $table->string('device_id', 125)->unique();
            $table->string('device_name', 125)->nullable();
            $table->string('device_type', 125);
            $table->string('browser', 125);
            $table->string('browser_version', 125)->nullable();
            $table->string('operating_system', 125);
            $table->string('os_version', 125)->nullable();
            $table->string('platform', 125);
            $table->json('screen_resolution')->nullable();
            $table->json('hardware_info')->nullable();
            $table->string('user_agent', 125);
            $table->json('fingerprint_data');
            $table->integer('trust_score')->default(0);
            $table->boolean('is_trusted')->default(0);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->timestamp('trusted_at')->nullable();
            $table->timestamp('trust_expires_at')->nullable();
            $table->json('location_history')->nullable();
            $table->json('ip_history')->nullable();
            $table->integer('login_count')->default(0);
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('last_failed_login_at')->nullable();
            $table->boolean('is_blocked')->default(0);
            $table->string('blocked_reason', 125)->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->timestamps();

            $table->index(['trackable_type', 'trackable_id'], 'device_tracking_trackable_idx');
            $table->index(['device_id', 'is_trusted'], 'device_tracking_device_trust_idx');
            $table->index(['trust_score', 'is_trusted'], 'device_tracking_trust_score_idx');
            $table->index(['last_seen_at', 'is_trusted'], 'device_tracking_activity_idx');
            $table->index(['is_blocked', 'blocked_at'], 'device_tracking_blocked_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('device_tracking');
    }
};
