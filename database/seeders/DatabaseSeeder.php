<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            // Core setup seeders
            RoleSeeder::class,
            AdminSeeder::class,
            UnifiedPermissionsSeeder::class, // Now includes app-setting & notification permissions

            // Lookup table seeders (must run before data migration)
            CustomerTypesSeeder::class,
            CommissionTypesSeeder::class,
            QuotationStatusesSeeder::class,
            AddonCoversSeeder::class,
            PolicyTypesSeeder::class,
            PremiumTypesSeeder::class,
            FuelTypesSeeder::class,
            InsuranceCompaniesSeeder::class,

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
