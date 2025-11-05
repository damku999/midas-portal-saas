<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Intelligently seeds branch based on tenant configuration:
     * - Uses custom branch name if provided in config
     * - Falls back to company name as default branch
     * - Uses "Main Branch" as last resort
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('branches')->truncate();

        // Get custom branch name from tenant config, or use company name, or default
        $customSettings = config('tenant.settings', []);
        $companyName = $customSettings['company_name'] ?? env('APP_NAME', 'Main Branch');
        $branchName = $customSettings['branch_name'] ?? strtoupper($companyName);

        // Insert initial branch
        DB::table('branches')->insert([
            [
                'id' => 1,
                'name' => $branchName,
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
            ],
        ]);

        $this->command->info("✓ Branch seeded: {$branchName}");
    }
}
