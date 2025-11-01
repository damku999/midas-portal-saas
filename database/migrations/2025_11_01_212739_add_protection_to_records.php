<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add protection columns to tables with email fields to prevent
     * deletion/modification of critical records like webmonks.in@gmail.com
     * and all *@webmonks.in domain records.
     */
    public function up(): void
    {
        // Add protection columns to users table
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('status');
            $table->string('protected_reason')->nullable()->after('is_protected');
            $table->index('is_protected');
        });

        // Add protection columns to customers table
        Schema::table('customers', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('status');
            $table->string('protected_reason')->nullable()->after('is_protected');
            $table->index('is_protected');
        });

        // Add protection columns to leads table
        Schema::table('leads', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('priority');
            $table->string('protected_reason')->nullable()->after('is_protected');
            $table->index('is_protected');
        });

        // Add protection columns to brokers table
        Schema::table('brokers', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('status');
            $table->string('protected_reason')->nullable()->after('is_protected');
            $table->index('is_protected');
        });

        // Add protection columns to branches table
        Schema::table('branches', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('status');
            $table->string('protected_reason')->nullable()->after('is_protected');
            $table->index('is_protected');
        });

        // Add protection columns to reference_users table
        Schema::table('reference_users', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('status');
            $table->string('protected_reason')->nullable()->after('is_protected');
            $table->index('is_protected');
        });

        // Add protection columns to relationship_managers table
        Schema::table('relationship_managers', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('status');
            $table->string('protected_reason')->nullable()->after('is_protected');
            $table->index('is_protected');
        });

        // Add protection columns to insurance_companies table
        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->boolean('is_protected')->default(false)->after('status');
            $table->string('protected_reason')->nullable()->after('is_protected');
            $table->index('is_protected');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['is_protected']);
            $table->dropColumn(['is_protected', 'protected_reason']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['is_protected']);
            $table->dropColumn(['is_protected', 'protected_reason']);
        });

        Schema::table('leads', function (Blueprint $table) {
            $table->dropIndex(['is_protected']);
            $table->dropColumn(['is_protected', 'protected_reason']);
        });

        Schema::table('brokers', function (Blueprint $table) {
            $table->dropIndex(['is_protected']);
            $table->dropColumn(['is_protected', 'protected_reason']);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropIndex(['is_protected']);
            $table->dropColumn(['is_protected', 'protected_reason']);
        });

        Schema::table('reference_users', function (Blueprint $table) {
            $table->dropIndex(['is_protected']);
            $table->dropColumn(['is_protected', 'protected_reason']);
        });

        Schema::table('relationship_managers', function (Blueprint $table) {
            $table->dropIndex(['is_protected']);
            $table->dropColumn(['is_protected', 'protected_reason']);
        });

        Schema::table('insurance_companies', function (Blueprint $table) {
            $table->dropIndex(['is_protected']);
            $table->dropColumn(['is_protected', 'protected_reason']);
        });
    }
};
