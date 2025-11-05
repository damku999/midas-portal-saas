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
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small businesses and startups getting started with CRM',
                'price' => 499.00,
                'billing_interval' => 'month',
                'max_users' => 3,
                'max_customers' => 100,
                'max_leads_per_month' => 50,
                'storage_limit_gb' => 5,
                'is_active' => true,
                'sort_order' => 1,
                'features' => [
                    'Up to 3 users',
                    '100 customers',
                    '50 leads per month',
                    '5GB storage',
                    'Basic email support',
                    'Standard reports',
                    'Mobile app access',
                ],
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing businesses that need advanced features and more capacity',
                'price' => 999.00,
                'billing_interval' => 'month',
                'max_users' => 10,
                'max_customers' => 500,
                'max_leads_per_month' => 250,
                'storage_limit_gb' => 20,
                'is_active' => true,
                'sort_order' => 2,
                'features' => [
                    'Up to 10 users',
                    '500 customers',
                    '250 leads per month',
                    '20GB storage',
                    'Priority email support',
                    'Advanced reports & analytics',
                    'Custom fields',
                    'API access',
                    'WhatsApp integration',
                ],
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'Unlimited power for large organizations with complex needs',
                'price' => 2499.00,
                'billing_interval' => 'month',
                'max_users' => -1, // Unlimited
                'max_customers' => -1, // Unlimited
                'max_leads_per_month' => -1, // Unlimited
                'storage_limit_gb' => 100,
                'is_active' => true,
                'sort_order' => 3,
                'features' => [
                    'Unlimited users',
                    'Unlimited customers',
                    'Unlimited leads',
                    '100GB storage',
                    '24/7 phone & email support',
                    'Dedicated account manager',
                    'Advanced reports & analytics',
                    'Custom fields',
                    'API access',
                    'WhatsApp integration',
                    'Custom integrations',
                    'White-label options',
                    'SLA guarantee',
                ],
            ],
            [
                'name' => 'Professional Annual',
                'slug' => 'professional-annual',
                'description' => 'Professional plan billed annually - Save 20%!',
                'price' => 9590.00, // ~799/month * 12 = 20% discount
                'billing_interval' => 'year',
                'max_users' => 10,
                'max_customers' => 500,
                'max_leads_per_month' => 250,
                'storage_limit_gb' => 20,
                'is_active' => true,
                'sort_order' => 4,
                'features' => [
                    'All Professional features',
                    'Billed annually',
                    'Save 20% compared to monthly',
                    'Up to 10 users',
                    '500 customers',
                    '250 leads per month',
                    '20GB storage',
                    'Priority support',
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
