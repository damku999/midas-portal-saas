<?php

namespace Database\Seeders\Central;

use App\Models\Central\Plan;
use App\Models\Central\TenantUser;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CentralAdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default super admin user
        TenantUser::updateOrCreate(
            ['email' => 'admin@midastech.in'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'), // Change this in production!
                'is_super_admin' => true,
                'is_support_admin' => false,
                'is_billing_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $this->command->info('✓ Super admin user created: admin@midastech.in / password');

        // Create default pricing plans
        $plans = [
            [
                'name' => 'Starter',
                'slug' => 'starter',
                'description' => 'Perfect for small insurance agencies getting started',
                'price' => 2999.00,
                'billing_interval' => 'month',
                'max_users' => 3,
                'max_customers' => 100,
                'max_leads_per_month' => 50,
                'storage_limit_gb' => 5,
                'features' => [
                    'Basic customer management',
                    'Policy management',
                    'Lead tracking',
                    'WhatsApp integration',
                    'SMS & Email notifications',
                    'Basic reports',
                    'Mobile app access',
                ],
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Professional',
                'slug' => 'professional',
                'description' => 'For growing agencies with advanced needs',
                'price' => 5999.00,
                'billing_interval' => 'month',
                'max_users' => 10,
                'max_customers' => 500,
                'max_leads_per_month' => 200,
                'storage_limit_gb' => 20,
                'features' => [
                    'Everything in Starter',
                    'Advanced customer segmentation',
                    'Automated workflows',
                    'API access',
                    'Advanced analytics & reports',
                    'Custom fields',
                    'Priority support',
                    'Data export',
                ],
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Enterprise',
                'slug' => 'enterprise',
                'description' => 'For large organizations with unlimited needs',
                'price' => 14999.00,
                'billing_interval' => 'month',
                'max_users' => -1, // Unlimited
                'max_customers' => -1, // Unlimited
                'max_leads_per_month' => -1, // Unlimited
                'storage_limit_gb' => 100,
                'features' => [
                    'Everything in Professional',
                    'Unlimited users & customers',
                    'Custom domain',
                    'White-label branding',
                    'Dedicated support',
                    'Custom integrations',
                    'SLA guarantee',
                    'On-premise deployment option',
                ],
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $planData) {
            Plan::updateOrCreate(
                ['slug' => $planData['slug']],
                $planData
            );
            $this->command->info("✓ Plan created: {$planData['name']}");
        }

        $this->command->info('');
        $this->command->info('==============================================');
        $this->command->info('Central Admin Seeding Completed!');
        $this->command->info('==============================================');
        $this->command->info('Super Admin Credentials:');
        $this->command->info('Email: admin@midastech.in');
        $this->command->info('Password: password');
        $this->command->info('');
        $this->command->warn('⚠️  IMPORTANT: Change the password after first login!');
        $this->command->info('');
    }
}
