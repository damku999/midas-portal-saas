<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NotificationTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates default WhatsApp and Email templates for all 19 notification types.
     * Templates use {{variable}} syntax for variable replacement.
     */
    public function run(): void
    {
        // Get all notification types
        $notificationTypes = DB::table('notification_types')->get()->keyBy('code');

        $templates = [];

        // ==============================================
        // CUSTOMER ENGAGEMENT TEMPLATES
        // ==============================================

        // 1. Birthday Wish (WhatsApp)
        if (isset($notificationTypes['birthday_wish'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['birthday_wish']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => "🎉 *Happy Birthday, {{customer_name}}!* 🎂

Wishing you a wonderful day filled with joy, happiness, and blessings. May this year bring you good health, prosperity, and all the success you deserve.

Thank you for trusting us with your insurance needs. We're honored to be part of your journey!

Warm wishes,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
\"Think of Insurance, Think of Us.\"",
                'available_variables' => json_encode(['customer_name', 'advisor_name', 'company_website', 'company_phone', 'company_name']),
                'sample_output' => "🎉 *Happy Birthday, Raj Patel!* 🎂\n\nWishing you a wonderful day filled with joy...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 2. Customer Welcome (WhatsApp)
        if (isset($notificationTypes['customer_welcome'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['customer_welcome']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => "Dear {{customer_name}}

Welcome to the world of insurance solutions! I'm {{advisor_name}}, your dedicated insurance advisor here to guide you through every step of your insurance journey. Whether you're seeking protection for your loved ones, securing your assets, or planning for the future, I'm committed to providing personalized advice and finding the perfect insurance solutions tailored to your needs.

You can access your customer portal at: {{portal_url}}

Feel free to reach out anytime with questions or concerns. Let's work together to safeguard what matters most to you!

Best regards,
{{advisor_name}}
{{company_website}}
{{company_phone}}
Your Trusted Insurance Advisor
\"Think of Insurance, Think of Us.\"",
                'available_variables' => json_encode(['customer_name', 'advisor_name', 'company_website', 'company_phone', 'portal_url']),
                'sample_output' => "Dear Raj Patel\n\nWelcome to the world of insurance solutions!...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ==============================================
        // POLICY MANAGEMENT TEMPLATES
        // ==============================================

        // 3. Policy Created (WhatsApp)
        if (isset($notificationTypes['policy_created'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['policy_created']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => "Dear {{customer_name}}

Thank you for entrusting me with your insurance needs. Attached, you'll find the policy document with *Policy No. {{policy_no}}* of your *{{premium_type}} {{registration_no}}* which expire on *{{expired_date}}*. If you have any questions or need further assistance, please don't hesitate to reach out.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
\"Think of Insurance, Think of Us.\"",
                'available_variables' => json_encode(['customer_name', 'policy_no', 'premium_type', 'registration_no', 'expired_date', 'advisor_name', 'company_website']),
                'sample_output' => "Dear Raj Patel\n\nThank you for entrusting me with your insurance needs...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 4-7. Renewal Reminders (30, 15, 7 days, expired) - Vehicle
        $renewalCodes = ['renewal_30_days', 'renewal_15_days', 'renewal_7_days', 'renewal_expired'];
        foreach ($renewalCodes as $code) {
            if (isset($notificationTypes[$code])) {
                $templates[] = [
                    'notification_type_id' => $notificationTypes[$code]->id,
                    'channel' => 'whatsapp',
                    'subject' => null,
                    'template_content' => 'Dear *{{customer_name}}*

Your *{{policy_type}}* Under Policy No *{{policy_number}}* of *{{insurance_company}}* for Vehicle Number *{{vehicle_number}}* is due for renewal on *{{expiry_date}}*. To ensure continuous coverage, please renew by the due date.

For assistance, contact us at {{company_phone}}.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."',
                    'available_variables' => json_encode(['customer_name', 'policy_number', 'policy_type', 'insurance_company', 'expiry_date', 'days_remaining', 'premium_amount', 'vehicle_number', 'advisor_name', 'company_website', 'company_phone']),
                    'sample_output' => "Dear *Raj Patel*\n\nYour *Comprehensive* Under Policy No *POL123456* of *HDFC ERGO*...",
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // 8. Quotation Ready (WhatsApp)
        if (isset($notificationTypes['quotation_ready'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['quotation_ready']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => '🚗 *Insurance Quotation*

Dear *{{customer_name}}*,

Your insurance quotation is ready! We have compared *{{quotes_count}} insurance companies* for you.

🚙 *Vehicle Details:*
• Vehicle: *{{vehicle_make_model}}*
• Registration: *{{vehicle_number}}*
• IDV: *₹{{idv_amount}}*
• Policy: *{{policy_type}}* - {{policy_tenure}} Year(s)

💰 *Best Premium:*
• *{{best_company_name}}*
• Premium: *{{best_premium}}*

📊 *All Quotes:*
{{comparison_list}}

For detailed quotation or to proceed with purchase, please contact us at {{company_phone}}.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."',
                'available_variables' => json_encode(['customer_name', 'quotes_count', 'vehicle_make_model', 'vehicle_number', 'idv_amount', 'policy_type', 'policy_tenure', 'best_company_name', 'best_premium', 'comparison_list', 'advisor_name', 'company_phone', 'company_website']),
                'sample_output' => "🚗 *Insurance Quotation*\n\nDear *Raj Patel*,\n\nYour insurance quotation is ready!...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ==============================================
        // CLAIM TEMPLATES
        // ==============================================

        // 9. Claim Registered (WhatsApp)
        if (isset($notificationTypes['claim_registered'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['claim_registered']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => 'Dear customer,

Your Claim Number *{{claim_number}}* is registered for *{{vehicle_number}}*. We will keep you updated on the claim status.

For any assistance, please contact us.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."',
                'available_variables' => json_encode(['claim_number', 'vehicle_number', 'advisor_name', 'company_website']),
                'sample_output' => "Dear customer,\n\nYour Claim Number *CLM123456* is registered...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 10. Document Request - Health (WhatsApp)
        if (isset($notificationTypes['document_request_health'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['document_request_health']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => '🏥 *Health Insurance Claim Documents*

For health Insurance - Kindly provide below mention documents for smooth processing:

1. Original Bills and Receipts
2. Medicine Bills
3. Discharge Summary
4. Medical Case Paper
5. ID Proof (Aadhar Card)
6. Pan Card
7. Cancel Check/Bank Passbook Copy
8. Death Certificate (if applicable)
9. Post Mortem Report (if applicable)

Please submit these documents at your earliest convenience.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."',
                'available_variables' => json_encode(['advisor_name', 'company_website']),
                'sample_output' => "🏥 *Health Insurance Claim Documents*\n\nFor health Insurance...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 11. Document Request - Vehicle (WhatsApp)
        if (isset($notificationTypes['document_request_vehicle'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['document_request_vehicle']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => '🚗 *Vehicle Insurance Claim Documents*

For Vehicle/Truck Insurance - Kindly provide below mention documents:

1. RC Copy (both sides)
2. DL Copy (both sides)
3. Fitness Copy
4. Permit Copy
5. Pan Card
6. Aadhar Card
7. Cancel Check/Bank Passbook Copy
8. Insurance Policy Copy
9. FIR/Panchnama (if applicable)
10. Original Repair Bills (if applicable)
11. Original Parts Invoices (if applicable)
12. Death Certificate (if applicable)
13. Post Mortem Report (if applicable)
14. Spot Photos/Video
15. Accident Photos/Video

Please submit these documents for claim processing.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."',
                'available_variables' => json_encode(['advisor_name', 'company_website']),
                'sample_output' => "🚗 *Vehicle Insurance Claim Documents*\n\nFor Vehicle/Truck Insurance...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 12. Document Request Reminder (WhatsApp)
        if (isset($notificationTypes['document_request_reminder'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['document_request_reminder']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => '📄 *Pending Documents Reminder*

Below are the Documents pending from your side. Send it as soon as possible:

{{pending_documents_list}}

Please submit these at your earliest convenience.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."',
                'available_variables' => json_encode(['pending_documents_list', 'advisor_name', 'company_website']),
                'sample_output' => "📄 *Pending Documents Reminder*\n\nBelow are the Documents pending...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // 13. Claim Stage Update (WhatsApp)
        if (isset($notificationTypes['claim_stage_update'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['claim_stage_update']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => 'Dear {{customer_name}},

Your claim *{{claim_number}}* status has been updated to: *{{stage_name}}*

{{notes}}

For further assistance, please contact us.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."',
                'available_variables' => json_encode(['customer_name', 'claim_number', 'stage_name', 'notes', 'advisor_name', 'company_website']),
                'sample_output' => "Dear Raj Patel,\n\nYour claim *CLM123456* status has been updated...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Insert all templates
        DB::table('notification_templates')->insert($templates);
    }
}
