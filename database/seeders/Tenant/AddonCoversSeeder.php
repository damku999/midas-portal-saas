<?php

namespace Database\Seeders\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddonCoversSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $now = Carbon::now();

        $addonCovers = [
            [
                'id' => 1,
                'name' => 'Zero Depreciation',
                'description' => 'Covers depreciation value of vehicle parts',
                'order_no' => 1,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 2,
                'name' => 'Consumables',
                'description' => 'Covers consumable items like nuts, bolts, oils, lubricants',
                'order_no' => 3,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 3,
                'name' => 'Engine Protection',
                'description' => 'Protects against engine damage due to water ingression',
                'order_no' => 2,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 4,
                'name' => 'Return to Invoice',
                'description' => 'Covers full invoice value in case of total loss',
                'order_no' => 6,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 5,
                'name' => 'Tyre Protection',
                'description' => 'Covers tyre and rim damage',
                'order_no' => 8,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 6,
                'name' => 'Key and Lock Protect',
                'description' => 'Key replacement and lock protection',
                'order_no' => 3,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 7,
                'name' => 'Loss of Personal Belongings',
                'description' => 'Covers personal belongings lost during accident',
                'order_no' => 4,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 8,
                'name' => 'RSA (Road Side Assistance)',
                'description' => 'Emergency roadside assistance services',
                'order_no' => 7,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 9,
                'name' => 'Repair of Glass,Rubber and Plastic Part',
                'description' => 'Covers glass, rubber and plastic parts repair',
                'order_no' => 5,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
            [
                'id' => 10,
                'name' => 'Other',
                'description' => 'Other addon covers not categorized',
                'order_no' => 10,
                'status' => true,
                'created_at' => $now,
                'updated_at' => $now,
                'created_by' => 1,
                'updated_by' => 1,
            ],
        ];

        // Truncate the table first to avoid conflicts
        DB::table('addon_covers')->truncate();

        // Insert the addon covers
        DB::table('addon_covers')->insert($addonCovers);

        $this->command->info('Addon covers seeded successfully!');
    }
}
