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
        Schema::connection('central')->table('payments', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['tenant_id']);

            // Make tenant_id nullable
            $table->string('tenant_id')->nullable()->change();

            // Make subscription_id nullable too for test payments
            $table->dropForeign(['subscription_id']);
            $table->foreignId('subscription_id')->nullable()->change();

            // Re-add foreign keys with nullable support
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('central')->table('payments', function (Blueprint $table) {
            // Drop foreign keys
            $table->dropForeign(['tenant_id']);
            $table->dropForeign(['subscription_id']);

            // Make fields non-nullable again
            $table->string('tenant_id')->nullable(false)->change();
            $table->foreignId('subscription_id')->nullable(false)->change();

            // Re-add foreign keys
            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->foreign('subscription_id')->references('id')->on('subscriptions')->onDelete('cascade');
        });
    }
};
