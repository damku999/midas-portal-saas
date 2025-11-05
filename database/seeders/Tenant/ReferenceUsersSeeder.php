<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Skips seeding by default to avoid tenant-specific production data.
     * Tenants should add their own reference users through the UI.
     *
     * Set TENANT_SEED_SAMPLE_DATA=true in .env to seed sample reference users for testing.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('reference_users')->truncate();

        // Check if we should seed sample data
        $seedSampleData = config('tenant.settings.seed_sample_data', false)
            || env('TENANT_SEED_SAMPLE_DATA', false);

        if (!$seedSampleData) {
            $this->command->warn('⊘ Reference Users seeding skipped (tenant-specific data). Add reference users via UI.');
            return;
        }

        // Seed sample reference user data for testing/demo purposes only
        DB::table('reference_users')->insert([
            [
                'id' => 1,
                'name' => 'Sample Reference User 1',
                'email' => 'reference1@example.com',
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
                'name' => 'Sample Reference User 2',
                'email' => 'reference2@example.com',
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

        $this->command->info('✓ Sample reference users seeded (for testing only)');
    }
}
