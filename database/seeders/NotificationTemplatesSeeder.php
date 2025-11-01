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
     *
     * This seeder is idempotent - it will NOT create duplicates.
     * It uses updateOrCreate to ensure templates are updated if they exist.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding notification templates...');

        // Get all notification types
        $notificationTypes = DB::table('notification_types')->get()->keyBy('code');

        $created = 0;
        $updated = 0;
        $skipped = 0;

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

        // 14. Claim Closed (WhatsApp)
        if (isset($notificationTypes['claim_closed'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['claim_closed']->id,
                'channel' => 'whatsapp',
                'subject' => null,
                'template_content' => 'Dear {{customer_name}},

We are pleased to inform you that your claim *{{claim_number}}* has been successfully closed.

*Settlement Amount:* ₹{{settlement_amount}}
*Settlement Date:* {{settlement_date}}

The payment will be credited to your registered bank account within 3-5 working days.

Thank you for your patience throughout this process.

Best regards,
{{advisor_name}}
{{company_website}}
Your Trusted Insurance Advisor
"Think of Insurance, Think of Us."',
                'available_variables' => json_encode(['customer_name', 'claim_number', 'settlement_amount', 'settlement_date', 'advisor_name', 'company_website']),
                'sample_output' => "Dear Raj Patel,\n\nWe are pleased to inform you that your claim...",
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // ==============================================
        // EMAIL TEMPLATES FOR CRITICAL NOTIFICATIONS
        // ==============================================

        // Customer Welcome (Email)
        if (isset($notificationTypes['customer_welcome'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['customer_welcome']->id,
                'channel' => 'email',
                'subject' => 'Welcome to {{company_name}} - Your Insurance Journey Begins!',
                'template_content' => '<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
        <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #2c5aa0; margin-bottom: 20px;">Welcome to {{company_name}}!</h2>

            <p>Dear <strong>{{customer_name}}</strong>,</p>

            <p>Welcome to the world of insurance solutions! I\'m <strong>{{advisor_name}}</strong>, your dedicated insurance advisor here to guide you through every step of your insurance journey.</p>

            <p>Whether you\'re seeking protection for your loved ones, securing your assets, or planning for the future, I\'m committed to providing personalized advice and finding the perfect insurance solutions tailored to your needs.</p>

            <div style="background-color: #e3f2fd; padding: 15px; border-left: 4px solid #2c5aa0; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #2c5aa0;">Your Customer Portal</h3>
                <p style="margin-bottom: 5px;">Access your policies, claims, and documents anytime:</p>
                <p><a href="{{portal_url}}" style="color: #2c5aa0; font-weight: bold;">{{portal_url}}</a></p>
            </div>

            <p>Feel free to reach out anytime with questions or concerns. Let\'s work together to safeguard what matters most to you!</p>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p style="margin-bottom: 5px;"><strong>Best regards,</strong></p>
                <p style="margin: 5px 0;">{{advisor_name}}</p>
                <p style="margin: 5px 0; color: #666;">{{company_website}}</p>
                <p style="margin: 5px 0; color: #666;">{{company_phone}}</p>
                <p style="margin: 10px 0; font-style: italic; color: #888;">"Think of Insurance, Think of Us."</p>
            </div>
        </div>
    </div>
</body>
</html>',
                'available_variables' => json_encode(['customer_name', 'advisor_name', 'company_name', 'company_website', 'company_phone', 'portal_url']),
                'sample_output' => '<html>...</html>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Policy Created (Email)
        if (isset($notificationTypes['policy_created'])) {
            $templates[] = [
                'notification_type_id' => $notificationTypes['policy_created']->id,
                'channel' => 'email',
                'subject' => 'Policy Document - {{policy_no}} | {{company_name}}',
                'template_content' => '<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
        <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #28a745; margin-bottom: 20px;">✓ Your Insurance Policy is Ready!</h2>

            <p>Dear <strong>{{customer_name}}</strong>,</p>

            <p>Thank you for entrusting me with your insurance needs. Please find your policy document attached to this email.</p>

            <div style="background-color: #e8f5e9; padding: 20px; border-radius: 8px; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #2e7d32;">Policy Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; width: 40%;">Policy Number:</td>
                        <td style="padding: 8px 0;">{{policy_no}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Policy Type:</td>
                        <td style="padding: 8px 0;">{{premium_type}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Vehicle/Asset:</td>
                        <td style="padding: 8px 0;">{{registration_no}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Expiry Date:</td>
                        <td style="padding: 8px 0; color: #d32f2f; font-weight: bold;">{{expired_date}}</td>
                    </tr>
                </table>
            </div>

            <p>Please keep this policy document safely for future reference. If you have any questions or need further assistance, please don\'t hesitate to reach out.</p>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p style="margin-bottom: 5px;"><strong>Best regards,</strong></p>
                <p style="margin: 5px 0;">{{advisor_name}}</p>
                <p style="margin: 5px 0; color: #666;">{{company_website}}</p>
                <p style="margin: 10px 0; font-style: italic; color: #888;">"Think of Insurance, Think of Us."</p>
            </div>
        </div>
    </div>
</body>
</html>',
                'available_variables' => json_encode(['customer_name', 'policy_no', 'premium_type', 'registration_no', 'expired_date', 'advisor_name', 'company_name', 'company_website']),
                'sample_output' => '<html>...</html>',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        // Renewal Reminder (Email) - Generic for all renewal periods
        foreach ($renewalCodes as $code) {
            if (isset($notificationTypes[$code])) {
                $urgency = match ($code) {
                    'renewal_30_days' => '30 days',
                    'renewal_15_days' => '15 days',
                    'renewal_7_days' => '7 days - URGENT',
                    'renewal_expired' => 'EXPIRED',
                    default => ''
                };

                $templates[] = [
                    'notification_type_id' => $notificationTypes[$code]->id,
                    'channel' => 'email',
                    'subject' => 'Policy Renewal Reminder - '.$urgency.' | {{policy_number}}',
                    'template_content' => '<html>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; background-color: #f8f9fa;">
        <div style="background-color: #ffffff; padding: 30px; border-radius: 10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
            <h2 style="color: #ff9800; margin-bottom: 20px;">⚠ Policy Renewal Reminder</h2>

            <p>Dear <strong>{{customer_name}}</strong>,</p>

            <p>This is a friendly reminder that your insurance policy is due for renewal.</p>

            <div style="background-color: #fff3e0; padding: 20px; border-left: 4px solid #ff9800; margin: 20px 0;">
                <h3 style="margin-top: 0; color: #e65100;">Policy Details</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold; width: 40%;">Policy Number:</td>
                        <td style="padding: 8px 0;">{{policy_number}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Policy Type:</td>
                        <td style="padding: 8px 0;">{{policy_type}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Insurance Company:</td>
                        <td style="padding: 8px 0;">{{insurance_company}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Vehicle Number:</td>
                        <td style="padding: 8px 0;">{{vehicle_number}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Expiry Date:</td>
                        <td style="padding: 8px 0; color: #d32f2f; font-weight: bold; font-size: 16px;">{{expiry_date}}</td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; font-weight: bold;">Days Remaining:</td>
                        <td style="padding: 8px 0; color: #d32f2f; font-weight: bold;">{{days_remaining}} days</td>
                    </tr>
                </table>
            </div>

            <p><strong>To ensure continuous coverage, please renew your policy by the due date.</strong></p>

            <p>For assistance with renewal or to get a new quotation, please contact us at <strong>{{company_phone}}</strong>.</p>

            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd;">
                <p style="margin-bottom: 5px;"><strong>Best regards,</strong></p>
                <p style="margin: 5px 0;">{{advisor_name}}</p>
                <p style="margin: 5px 0; color: #666;">{{company_website}}</p>
                <p style="margin: 5px 0; color: #666;">{{company_phone}}</p>
                <p style="margin: 10px 0; font-style: italic; color: #888;">"Think of Insurance, Think of Us."</p>
            </div>
        </div>
    </div>
</body>
</html>',
                    'available_variables' => json_encode(['customer_name', 'policy_number', 'policy_type', 'insurance_company', 'vehicle_number', 'expiry_date', 'days_remaining', 'premium_amount', 'advisor_name', 'company_website', 'company_phone']),
                    'sample_output' => '<html>...</html>',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insert or update all templates (prevent duplicates)
        if (! empty($templates)) {
            $templatesCount = count($templates);
            $this->command->info("Processing {$templatesCount} templates...");

            foreach ($templates as $template) {
                // Check if template already exists
                $existing = DB::table('notification_templates')
                    ->where('notification_type_id', $template['notification_type_id'])
                    ->where('channel', $template['channel'])
                    ->first();

                if ($existing) {
                    // Update existing template
                    DB::table('notification_templates')
                        ->where('id', $existing->id)
                        ->update([
                            'subject' => $template['subject'],
                            'template_content' => $template['template_content'],
                            'available_variables' => $template['available_variables'],
                            'sample_output' => $template['sample_output'],
                            'is_active' => $template['is_active'],
                            'updated_at' => now(),
                        ]);
                    $updated++;

                    // Get notification type name for logging
                    $notificationType = DB::table('notification_types')
                        ->where('id', $template['notification_type_id'])
                        ->value('code');

                    $this->command->line("  ✓ Updated: {$notificationType} ({$template['channel']})");
                } else {
                    // Create new template
                    DB::table('notification_templates')->insert($template);
                    $created++;

                    // Get notification type name for logging
                    $notificationType = DB::table('notification_types')
                        ->where('id', $template['notification_type_id'])
                        ->value('code');

                    $this->command->info("  + Created: {$notificationType} ({$template['channel']})");
                }
            }

            $this->command->newLine();
            $this->command->info('✅ Seeding completed successfully!');
            $this->command->table(
                ['Action', 'Count'],
                [
                    ['Templates Created', $created],
                    ['Templates Updated', $updated],
                    ['Total Processed', $created + $updated],
                ]
            );
        } else {
            $this->command->warn('⚠️  No templates to seed. All notification types may be inactive.');
        }
    }
}
