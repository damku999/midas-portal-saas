<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ReferenceUsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data
        DB::table('reference_users')->truncate();

        // Insert reference user/source data (production data)
        DB::table('reference_users')->insert([
            [
                'id' => 1,
                'name' => 'NARENDRA JAIN / NITESH JAIN',
                'email' => null,
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
                'name' => 'DHAVAL PANCHAL',
                'email' => null,
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

        $this->command->info('Reference users seeded successfully!');
    }
}
