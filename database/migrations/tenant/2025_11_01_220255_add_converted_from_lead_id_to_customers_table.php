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
        Schema::table('customers', function (Blueprint $table) {
            // Add foreign key to track which lead this customer was converted from
            $table->foreignId('converted_from_lead_id')->nullable()->after('id')->constrained('leads')->onDelete('set null');
            $table->timestamp('converted_at')->nullable()->after('converted_from_lead_id');

            // Index for performance
            $table->index('converted_from_lead_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['converted_from_lead_id']);
            $table->dropColumn(['converted_from_lead_id', 'converted_at']);
        });
    }
};
