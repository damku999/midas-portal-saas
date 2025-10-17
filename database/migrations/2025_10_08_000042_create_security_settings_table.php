<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Source: 2025_09_20_120001_create_additional_security_tables.php (security_settings table)
     */
    public function up(): void
    {
        Schema::create('security_settings', function (Blueprint $table) {
            $table->id();
            $table->string('settingable_type', 125);
            $table->unsignedBigInteger('settingable_id');
            $table->boolean('two_factor_enabled')->default(0);
            $table->boolean('device_tracking_enabled')->default(1);
            $table->boolean('login_notifications')->default(1);
            $table->boolean('security_alerts')->default(1);
            $table->integer('session_timeout')->default(7200);
            $table->integer('device_trust_duration')->default(30); // 30 days
            $table->json('notification_preferences')->nullable();
            $table->timestamps();

            $table->index(['settingable_type', 'settingable_id'], 'security_settings_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('security_settings');
    }
};
