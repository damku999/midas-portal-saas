<?php

namespace Database\Seeders\Tenant;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Populates notification_types table with ALL 19 notification types used in the system:
     *
     * Customer Engagement (5):
     * - birthday_wish, customer_welcome, email_verification, password_reset, family_login_credentials
     *
     * Policy Management (7):
     * - renewal_30_days, renewal_15_days, renewal_7_days, renewal_expired, policy_created,
     *   policy_expiry_reminder
     *
     * Claims (6):
     * - claim_registered, document_request_health, document_request_vehicle, document_request_reminder,
     *   claim_stage_update, claim_closed
     *
     * Quotation (1):
     * - quotation_ready
     *
     * Marketing (1):
     * - marketing_campaign
     */
    public function run(): void
    {
        $notificationTypes = [
            // ===============================================
            // CUSTOMER ENGAGEMENT (5 types)
            // ===============================================
            [
                'name' => 'Birthday Wish',
                'code' => 'birthday_wish',
                'category' => 'customer',
                'description' => 'Birthday wishes sent to customers on their birthday via scheduled command',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => false,
                'is_active' => true,
                'order_no' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Customer Welcome Email',
                'code' => 'customer_welcome',
                'category' => 'customer',
                'description' => 'Welcome email sent when new customer is registered',
                'default_whatsapp_enabled' => false,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Email Verification',
                'code' => 'email_verification',
                'category' => 'customer',
                'description' => 'Email verification link sent to verify customer email address',
                'default_whatsapp_enabled' => false,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Password Reset',
                'code' => 'password_reset',
                'category' => 'customer',
                'description' => 'Password reset link sent when customer requests password reset',
                'default_whatsapp_enabled' => false,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Family Login Credentials',
                'code' => 'family_login_credentials',
                'category' => 'customer',
                'description' => 'Login credentials sent to family members for portal access',
                'default_whatsapp_enabled' => false,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===============================================
            // POLICY MANAGEMENT (7 types)
            // ===============================================
            [
                'name' => 'Policy Created / Insurance Added / Welcome',
                'code' => 'policy_created',
                'category' => 'policy',
                'description' => 'Notification sent when new policy is created or insurance is added to customer',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 10,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Renewal Reminder - 30 Days',
                'code' => 'renewal_30_days',
                'category' => 'policy',
                'description' => 'Reminder sent 30 days before policy expiry',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 11,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Renewal Reminder - 15 Days',
                'code' => 'renewal_15_days',
                'category' => 'policy',
                'description' => 'Reminder sent 15 days before policy expiry',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 12,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Renewal Reminder - 7 Days',
                'code' => 'renewal_7_days',
                'category' => 'policy',
                'description' => 'Reminder sent 7 days before policy expiry',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 13,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Policy Expired / Expiring Today',
                'code' => 'renewal_expired',
                'category' => 'policy',
                'description' => 'Notification sent when policy has expired or expiring today',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 14,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Policy Expiry Reminder (Event-based)',
                'code' => 'policy_expiry_reminder',
                'category' => 'policy',
                'description' => 'Event-based policy expiry reminder triggered by PolicyExpiringWarning event',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 15,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===============================================
            // QUOTATION (1 type)
            // ===============================================
            [
                'name' => 'Quotation Ready',
                'code' => 'quotation_ready',
                'category' => 'quotation',
                'description' => 'Notification sent when insurance quotation is ready with comparison of multiple companies',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 20,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===============================================
            // CLAIMS (6 types)
            // ===============================================
            [
                'name' => 'Claim Registered / Claim Number Assigned',
                'code' => 'claim_registered',
                'category' => 'claim',
                'description' => 'Notification sent when claim number is assigned to a new claim',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Document Request - Health Insurance',
                'code' => 'document_request_health',
                'category' => 'claim',
                'description' => 'List of required documents for health insurance claim',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 31,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Document Request - Vehicle/Truck Insurance',
                'code' => 'document_request_vehicle',
                'category' => 'claim',
                'description' => 'List of required documents for vehicle/truck insurance claim',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 32,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Pending Documents Reminder',
                'code' => 'document_request_reminder',
                'category' => 'claim',
                'description' => 'Reminder sent with list of pending documents for claim',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => false,
                'is_active' => true,
                'order_no' => 33,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Claim Stage Update',
                'code' => 'claim_stage_update',
                'category' => 'claim',
                'description' => 'Notification sent when claim status/stage is updated',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 34,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'name' => 'Claim Closed',
                'code' => 'claim_closed',
                'category' => 'claim',
                'description' => 'Notification sent when claim is closed/settled',
                'default_whatsapp_enabled' => false,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 35,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // ===============================================
            // MARKETING (1 type)
            // ===============================================
            [
                'name' => 'Marketing Campaign',
                'code' => 'marketing_campaign',
                'category' => 'marketing',
                'description' => 'Custom marketing messages with text or image sent to selected customers',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => false,
                'is_active' => true,
                'order_no' => 40,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('notification_types')->insert($notificationTypes);
    }
}
