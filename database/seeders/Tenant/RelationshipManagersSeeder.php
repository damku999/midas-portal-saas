<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RelationshipManagersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Skips seeding by default to avoid tenant-specific production data.
     * Tenants should add their own relationship managers through the UI.
     *
     * Set TENANT_SEED_SAMPLE_DATA=true in .env to seed sample RMs for testing.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('relationship_managers')->truncate();

        // Check if we should seed sample data
        $seedSampleData = config('tenant.settings.seed_sample_data', false)
            || env('TENANT_SEED_SAMPLE_DATA', false);

        if (!$seedSampleData) {
            $this->command->warn('⊘ Relationship Managers seeding skipped (tenant-specific data). Add RMs via UI.');
            return;
        }

        // Seed sample relationship manager data for testing/demo purposes only
        DB::table('relationship_managers')->insert([
            [
                'id' => 1,
                'name' => 'Sample Relationship Manager 1',
                'email' => 'rm1@example.com',
                'mobile_number' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
            ],
            [
                'id' => 2,
                'name' => 'Sample Relationship Manager 2',
                'email' => 'rm2@example.com',
                'mobile_number' => null,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
            ],
        ]);

        $this->command->info('✓ Sample relationship managers seeded (for testing only)');
    }
}
