<?php

namespace Database\Seeders\Tenant;

use App\Models\CustomerType;
use App\Models\LeadSource;
use App\Models\LeadStatus;
use App\Models\NotificationType;
use App\Models\PolicyType;
use App\Models\PremiumType;
use Illuminate\Database\Seeder;

class DefaultTenantSeeder extends Seeder
{
    /**
     * Seed default data for a new tenant.
     */
    public function run(): void
    {
        $this->seedLeadStatuses();
        $this->seedLeadSources();
        $this->seedNotificationTypes();
        $this->seedCustomerTypes();
        $this->seedPolicyTypes();
        $this->seedPremiumTypes();
    }

    private function seedLeadStatuses(): void
    {
        $statuses = [
            [
                'name' => 'New',
                'description' => 'New lead just created',
                'color' => '#3B82F6',
                'is_active' => true,
                'is_converted' => false,
                'is_lost' => false,
                'display_order' => 1,
            ],
            [
                'name' => 'Contacted',
                'description' => 'Initial contact made with lead',
                'color' => '#8B5CF6',
                'is_active' => true,
                'is_converted' => false,
                'is_lost' => false,
                'display_order' => 2,
            ],
            [
                'name' => 'Qualified',
                'description' => 'Lead qualified for sales process',
                'color' => '#10B981',
                'is_active' => true,
                'is_converted' => false,
                'is_lost' => false,
                'display_order' => 3,
            ],
            [
                'name' => 'Proposal Sent',
                'description' => 'Quotation/proposal sent to lead',
                'color' => '#F59E0B',
                'is_active' => true,
                'is_converted' => false,
                'is_lost' => false,
                'display_order' => 4,
            ],
            [
                'name' => 'Negotiation',
                'description' => 'In negotiation phase',
                'color' => '#EF4444',
                'is_active' => true,
                'is_converted' => false,
                'is_lost' => false,
                'display_order' => 5,
            ],
            [
                'name' => 'Converted',
                'description' => 'Lead converted to customer',
                'color' => '#059669',
                'is_active' => true,
                'is_converted' => true,
                'is_lost' => false,
                'display_order' => 6,
            ],
            [
                'name' => 'Lost',
                'description' => 'Lead lost to competitor or not interested',
                'color' => '#6B7280',
                'is_active' => true,
                'is_converted' => false,
                'is_lost' => true,
                'display_order' => 7,
            ],
        ];

        foreach ($statuses as $status) {
            LeadStatus::updateOrCreate(
                ['name' => $status['name']],
                $status
            );
        }
    }

    private function seedLeadSources(): void
    {
        $sources = [
            ['name' => 'Website', 'description' => 'Lead from website inquiry', 'is_active' => true, 'display_order' => 1],
            ['name' => 'Referral', 'description' => 'Customer referral', 'is_active' => true, 'display_order' => 2],
            ['name' => 'Phone Call', 'description' => 'Inbound phone inquiry', 'is_active' => true, 'display_order' => 3],
            ['name' => 'Walk-in', 'description' => 'Walk-in customer', 'is_active' => true, 'display_order' => 4],
            ['name' => 'Email', 'description' => 'Email inquiry', 'is_active' => true, 'display_order' => 5],
            ['name' => 'Social Media', 'description' => 'Social media inquiry', 'is_active' => true, 'display_order' => 6],
            ['name' => 'Advertisement', 'description' => 'From advertising campaign', 'is_active' => true, 'display_order' => 7],
            ['name' => 'Partner', 'description' => 'Business partner referral', 'is_active' => true, 'display_order' => 8],
            ['name' => 'Other', 'description' => 'Other sources', 'is_active' => true, 'display_order' => 9],
        ];

        foreach ($sources as $source) {
            LeadSource::updateOrCreate(
                ['name' => $source['name']],
                $source
            );
        }
    }

    private function seedNotificationTypes(): void
    {
        $types = [
            [
                'name' => 'Policy Renewal Reminder',
                'code' => 'POLICY_RENEWAL',
                'category' => 'policy',
                'description' => 'Reminder for upcoming policy renewal',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 1,
            ],
            [
                'name' => 'Policy Expiry Alert',
                'code' => 'POLICY_EXPIRY',
                'category' => 'policy',
                'description' => 'Alert for policy expiration',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 2,
            ],
            [
                'name' => 'Premium Payment Due',
                'code' => 'PREMIUM_DUE',
                'category' => 'payment',
                'description' => 'Premium payment due notification',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 3,
            ],
            [
                'name' => 'Payment Confirmation',
                'code' => 'PAYMENT_CONFIRM',
                'category' => 'payment',
                'description' => 'Payment received confirmation',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 4,
            ],
            [
                'name' => 'Claim Status Update',
                'code' => 'CLAIM_STATUS',
                'category' => 'claim',
                'description' => 'Update on claim processing status',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 5,
            ],
            [
                'name' => 'Document Upload Request',
                'code' => 'DOC_REQUEST',
                'category' => 'document',
                'description' => 'Request for document upload',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 6,
            ],
            [
                'name' => 'Welcome Message',
                'code' => 'WELCOME',
                'category' => 'general',
                'description' => 'Welcome message for new customers',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => true,
                'is_active' => true,
                'order_no' => 7,
            ],
            [
                'name' => 'Birthday Wishes',
                'code' => 'BIRTHDAY',
                'category' => 'general',
                'description' => 'Birthday wishes to customers',
                'default_whatsapp_enabled' => true,
                'default_email_enabled' => false,
                'is_active' => true,
                'order_no' => 8,
            ],
        ];

        foreach ($types as $type) {
            NotificationType::updateOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }

    private function seedCustomerTypes(): void
    {
        $types = [
            ['name' => 'Individual', 'description' => 'Individual customer', 'status' => 1],
            ['name' => 'Corporate', 'description' => 'Corporate customer', 'status' => 1],
            ['name' => 'Family', 'description' => 'Family group', 'status' => 1],
            ['name' => 'Senior Citizen', 'description' => 'Senior citizen customer', 'status' => 1],
            ['name' => 'VIP', 'description' => 'VIP customer', 'status' => 1],
        ];

        foreach ($types as $type) {
            CustomerType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }

    private function seedPolicyTypes(): void
    {
        $types = [
            ['name' => 'Motor Insurance', 'description' => 'Car and bike insurance', 'is_active' => true],
            ['name' => 'Health Insurance', 'description' => 'Medical and health coverage', 'is_active' => true],
            ['name' => 'Life Insurance', 'description' => 'Life coverage', 'is_active' => true],
            ['name' => 'Travel Insurance', 'description' => 'Travel coverage', 'is_active' => true],
            ['name' => 'Home Insurance', 'description' => 'Property and home coverage', 'is_active' => true],
            ['name' => 'Business Insurance', 'description' => 'Commercial and business coverage', 'is_active' => true],
        ];

        foreach ($types as $type) {
            PolicyType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }

    private function seedPremiumTypes(): void
    {
        $types = [
            ['name' => 'Annual', 'description' => 'Yearly premium payment', 'is_active' => true],
            ['name' => 'Semi-Annual', 'description' => 'Half-yearly premium payment', 'is_active' => true],
            ['name' => 'Quarterly', 'description' => 'Quarterly premium payment', 'is_active' => true],
            ['name' => 'Monthly', 'description' => 'Monthly premium payment', 'is_active' => true],
            ['name' => 'One-Time', 'description' => 'Single premium payment', 'is_active' => true],
        ];

        foreach ($types as $type) {
            PremiumType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
