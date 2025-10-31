<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PremiumTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data
        DB::table('premium_types')->truncate();

        // Insert premium types data (transaction types: FRESH, ROLLOVER, RENEWAL)
        DB::table('premium_types')->insert([

            // Vehicle Insurance Types
            ['name' => '2 WHEELER', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '2W SATP', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '4 WHEELER', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '4W SATP', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '3 WHEELER', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '3W SATP', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'COMMERCIAL VEHICLE COMP', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'COMMERCIAL VEHICLE SATP', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '4W OD ONLY', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '2W OD ONLY', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '3W OD ONLY', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '4W BUNDLE (1OD +3TP)', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => '2W BUNDLE (1OD + 5TP)', 'is_vehicle' => 1, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],

            // Non-Vehicle Insurance Types
            ['name' => 'HEALTH INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'GMC', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'GPA', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'PERSONAL ACCIDENT', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'MARINE / TRANSIT INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'FIRE INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'MY HOME INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'TRAVEL INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'WC INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'TRADE CREDIT INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'TOP UP HEALTH INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'CYBER INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'TRANSPORTER GODOWN INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'CARRIER LEGAL LIABILITY', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'BHARAT SOOKSHMA UDHYAM', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'GODOWN STOCK INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'BURGLARY & THEFT INSURANCE', 'is_vehicle' => 0, 'is_life_insurance_policies' => 0, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],

            // Life Insurance Types
            ['name' => 'TERM PLAN', 'is_vehicle' => 0, 'is_life_insurance_policies' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'ENDOWMENT PLANS', 'is_vehicle' => 0, 'is_life_insurance_policies' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'ULIP PLANS', 'is_vehicle' => 0, 'is_life_insurance_policies' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
            ['name' => 'LIC', 'is_vehicle' => 0, 'is_life_insurance_policies' => 1, 'status' => 1, 'created_at' => now(), 'updated_at' => now(), 'deleted_at' => null, 'created_by' => 1, 'updated_by' => 1, 'deleted_by' => null],
        ]);
    }
}