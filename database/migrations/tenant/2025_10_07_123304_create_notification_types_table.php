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
        Schema::create('notification_types', function (Blueprint $table) {
            $table->id();
            $table->string('name', 125);
            $table->string('code', 50)->unique();
            $table->string('category', 50); // e.g., 'policy', 'customer', 'system'
            $table->text('description')->nullable();
            $table->boolean('default_whatsapp_enabled')->default(false);
            $table->boolean('default_email_enabled')->default(false);
            $table->boolean('is_active')->default(true);
            $table->integer('order_no')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index('category');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notification_types');
    }
};
