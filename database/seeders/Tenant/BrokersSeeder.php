<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrokersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Skips seeding by default to avoid tenant-specific production data.
     * Tenants should add their own brokers through the UI.
     *
     * Set TENANT_SEED_SAMPLE_DATA=true in .env to seed sample brokers for testing.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('brokers')->truncate();

        // Check if we should seed sample data
        $seedSampleData = config('tenant.settings.seed_sample_data', false)
            || env('TENANT_SEED_SAMPLE_DATA', false);

        if (!$seedSampleData) {
            $this->command->warn('⊘ Brokers seeding skipped (tenant-specific data). Add brokers via UI.');
            return;
        }

        // Seed sample broker data for testing/demo purposes only
        DB::table('brokers')->insert([
            [
                'id' => 1,
                'name' => 'Sample Broker 1',
                'email' => 'broker1@example.com',
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
                'name' => 'Sample Broker 2',
                'email' => 'broker2@example.com',
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

        $this->command->info('✓ Sample brokers seeded (for testing only)');
    }
}
