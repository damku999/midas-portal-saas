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
        Schema::create('notification_template_versions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('template_id')->constrained('notification_templates')->onDelete('cascade');
            $table->integer('version_number')->default(1);
            $table->string('channel', 20);
            $table->string('subject')->nullable();
            $table->text('template_content');
            $table->json('available_variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('changed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('change_type', 50)->default('update'); // create, update, restore
            $table->text('change_notes')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            // Index for faster queries
            $table->index(['template_id', 'version_number']);
            $table->index('changed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_template_versions');
    }
};
