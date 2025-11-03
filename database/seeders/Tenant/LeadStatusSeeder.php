<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;

class LeadStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $statuses = [
            ['name' => 'New', 'description' => 'New lead received', 'color' => 'info', 'is_active' => true, 'is_converted' => false, 'is_lost' => false, 'display_order' => 1],
            ['name' => 'Contacted', 'description' => 'Lead has been contacted', 'color' => 'primary', 'is_active' => true, 'is_converted' => false, 'is_lost' => false, 'display_order' => 2],
            ['name' => 'Quotation Sent', 'description' => 'Quotation has been sent to lead', 'color' => 'warning', 'is_active' => true, 'is_converted' => false, 'is_lost' => false, 'display_order' => 3],
            ['name' => 'Interested', 'description' => 'Lead is interested in purchasing', 'color' => 'success', 'is_active' => true, 'is_converted' => false, 'is_lost' => false, 'display_order' => 4],
            ['name' => 'Converted', 'description' => 'Lead converted to customer', 'color' => 'success', 'is_active' => true, 'is_converted' => true, 'is_lost' => false, 'display_order' => 5],
            ['name' => 'Lost', 'description' => 'Lead is lost or no longer interested', 'color' => 'danger', 'is_active' => true, 'is_converted' => false, 'is_lost' => true, 'display_order' => 6],
        ];

        foreach ($statuses as $status) {
            \DB::table('lead_statuses')->insert([
                'name' => $status['name'],
                'description' => $status['description'],
                'color' => $status['color'],
                'is_active' => $status['is_active'],
                'is_converted' => $status['is_converted'],
                'is_lost' => $status['is_lost'],
                'display_order' => $status['display_order'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
