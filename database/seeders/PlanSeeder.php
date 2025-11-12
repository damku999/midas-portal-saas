<?php

namespace Database\Seeders;

use App\Models\Central\Plan;
use Illuminate\Database\Seeder;

class PlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $plans = [
            [
                'name' => 'Starter-Monthly',
                'slug' => 'starter-monthly',
                'description' => 'Perfect for small insurance agencies getting started pay each month',
                'price' => 999.00,
                'billing_interval' => 'month',
                'max_users' => 3,
                'max_customers' => 100,
                'max_leads_per_month' => 50,
                'storage_limit_gb' => 1,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'Customer Management',
                    'Family Management',
                    'Customer Portal',
                    'Policy Management',
                    'Claims Management',
                    'Analytics & Reports',
                    'Commission Tracking',
                    'WhatsApp Integration (API charges extra)',
                ],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing agencies with advanced needs',
                'price' => 9999.00,
                'billing_interval' => 'year',
                'max_users' => 5,
                'max_customers' => 500,
                'max_leads_per_month' => 200,
                'storage_limit_gb' => 20,
                'is_active' => true,
                'sort_order' => 2,
                'features' => [
                    'Everything in Starter-Monthly',
                    'Quotation System',
                    'Analytics & Reports',
                    'Commission Tracking',
                    'Document Management',
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large organizations with unlimited needs',
                'price' => 11999.00,
                'billing_interval' => 'year',
                'max_users' => -1, // Unlimited
                'max_customers' => -1, // Unlimited
                'max_leads_per_month' => -1, // Unlimited
                'storage_limit_gb' => 100,
                'is_active' => true,
                'sort_order' => 3,
                'features' => [
                    'Everything in Professional',
                    'Staff & Role Management',
                    'Master Data Management',
                    'Notifications & Alerts',
                ],
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
        }

        $this->command->info('✓ Seeded ' . count($plans) . ' plans successfully');
    }
}
