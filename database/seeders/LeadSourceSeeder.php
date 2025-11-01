<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class LeadSourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sources = [
            ['name' => 'Website', 'description' => 'Leads from company website', 'is_active' => true, 'display_order' => 1],
            ['name' => 'Referral', 'description' => 'Leads from customer referrals', 'is_active' => true, 'display_order' => 2],
            ['name' => 'Social Media', 'description' => 'Leads from social media platforms', 'is_active' => true, 'display_order' => 3],
            ['name' => 'Cold Call', 'description' => 'Leads from cold calling', 'is_active' => true, 'display_order' => 4],
            ['name' => 'Walk-in', 'description' => 'Leads who walk into office', 'is_active' => true, 'display_order' => 5],
            ['name' => 'Email Campaign', 'description' => 'Leads from email marketing campaigns', 'is_active' => true, 'display_order' => 6],
            ['name' => 'Trade Show', 'description' => 'Leads from trade shows and events', 'is_active' => true, 'display_order' => 7],
            ['name' => 'Partner', 'description' => 'Leads from business partners', 'is_active' => true, 'display_order' => 8],
            ['name' => 'Existing Customer', 'description' => 'Leads from existing customers', 'is_active' => true, 'display_order' => 9],
            ['name' => 'Other', 'description' => 'Other lead sources', 'is_active' => true, 'display_order' => 10],
        ];

        foreach ($sources as $source) {
            \DB::table('lead_sources')->insert([
                'name' => $source['name'],
                'description' => $source['description'],
                'is_active' => $source['is_active'],
                'display_order' => $source['display_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
