<?php

namespace Database\Seeders\Tenant;

use Database\Seeders\Tenant\RoleSeeder;
use Database\Seeders\Tenant\AdminSeeder;
use Database\Seeders\Tenant\UnifiedPermissionsSeeder;
use Database\Seeders\Tenant\CustomerTypesSeeder;
use Database\Seeders\Tenant\CommissionTypesSeeder;
use Database\Seeders\Tenant\QuotationStatusesSeeder;
use Database\Seeders\Tenant\AddonCoversSeeder;
use Database\Seeders\Tenant\PolicyTypesSeeder;
use Database\Seeders\Tenant\PremiumTypesSeeder;
use Database\Seeders\Tenant\FuelTypesSeeder;
use Database\Seeders\Tenant\InsuranceCompaniesSeeder;
use Database\Seeders\Tenant\LeadSourceSeeder;
use Database\Seeders\Tenant\LeadStatusSeeder;
use Database\Seeders\Tenant\BranchesSeeder;
use Database\Seeders\Tenant\BrokersSeeder;
use Database\Seeders\Tenant\RelationshipManagersSeeder;
use Database\Seeders\Tenant\ReferenceUsersSeeder;
use Database\Seeders\Tenant\AppSettingsSeeder;
use Database\Seeders\Tenant\NotificationTypesSeeder;
use Database\Seeders\Tenant\NotificationTemplatesSeeder;
use Database\Seeders\Tenant\EmailCleanupSeeder;
use Database\Seeders\Tenant\DataMigrationSeeder;
use Illuminate\Database\Seeder;

/**
 * Tenant Database Seeder
 *
 * This seeder is used for tenant databases.
 * For central database seeding, use Central\CentralDatabaseSeeder
 */
class DatabaseSeeder extends Seeder
{
    /**
     * Seed the tenant database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // Core setup seeders
            RoleSeeder::class,
            AdminSeeder::class,
            UnifiedPermissionsSeeder::class,

            // Lookup table seeders (must run before data migration)
            CustomerTypesSeeder::class,
            CommissionTypesSeeder::class,
            QuotationStatusesSeeder::class,
            AddonCoversSeeder::class,
            PolicyTypesSeeder::class,
            PremiumTypesSeeder::class,
            FuelTypesSeeder::class,
            InsuranceCompaniesSeeder::class,

            // Lead Management master data
            LeadSourceSeeder::class,
            LeadStatusSeeder::class,

            // Master data seeders for business operations
            BranchesSeeder::class,
            BrokersSeeder::class,
            RelationshipManagersSeeder::class,
            ReferenceUsersSeeder::class,

            // Application configuration
            AppSettingsSeeder::class,
            NotificationTypesSeeder::class,
            NotificationTemplatesSeeder::class,

            // Data migration seeders (must run at the end)
            EmailCleanupSeeder::class,
            DataMigrationSeeder::class,
        ]);
    }
}