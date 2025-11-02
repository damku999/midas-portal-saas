<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BranchesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('branches')->truncate();

        // Insert branch data (production data)
        DB::table('branches')->insert([
            [
                'id' => 1,
                'name' => 'AHMEDABAD',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null,
            ],
        ]);

        $this->command->info('Branches seeded successfully!');
    }
}
