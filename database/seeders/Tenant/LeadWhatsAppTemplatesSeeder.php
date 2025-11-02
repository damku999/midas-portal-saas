<?php

namespace Database\Seeders;

use App\Models\LeadWhatsAppTemplate;
use App\Models\User;
use Illuminate\Database\Seeder;

class LeadWhatsAppTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $adminUser = User::first(); // Get first user as creator

        $templates = [
            // 1. GREETING - Welcome Message
            [
                'name' => 'Welcome - New Lead Introduction',
                'category' => 'greeting',
                'message_template' => "Hello {name}! 👋

Thank you for your interest in {product_interest}. I'm {advisor_name} from {company_name}, and I'm excited to help you find the perfect insurance solution tailored to your needs.

We noticed your inquiry came through {source}. I'd love to schedule a brief call to understand your requirements better and provide you with the best options available.

When would be a convenient time for you to connect?

Best regards,
{advisor_name}
{company_website}
{company_phone}
\"Think of Insurance, Think of Us.\"",
                'variables' => ['name', 'product_interest', 'advisor_name', 'company_name', 'source', 'company_website', 'company_phone'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 2. GREETING - Quick Response
            [
                'name' => 'Welcome - Quick Response',
                'category' => 'greeting',
                'message_template' => "Hi {name},

Thank you for reaching out to {company_name}! 😊

I've received your inquiry about {product_interest} and I'm here to help. Our team specializes in providing comprehensive insurance solutions that protect what matters most to you.

I'll review your requirements and get back to you shortly with the best options. In the meantime, feel free to reach me at {company_phone}.

Looking forward to serving you!

{advisor_name}
{company_website}",
                'variables' => ['name', 'company_name', 'product_interest', 'company_phone', 'advisor_name', 'company_website'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 3. FOLLOW-UP - After First Contact
            [
                'name' => 'Follow-up - Post Initial Discussion',
                'category' => 'follow-up',
                'message_template' => "Dear {name},

It was great speaking with you about your {product_interest} requirements. As promised, I'm following up to ensure you have all the information you need.

Here's a quick recap:
✓ Coverage options discussed
✓ Premium estimates shared
✓ Benefits and exclusions explained

Do you have any additional questions? I'm here to help you make an informed decision. You can access detailed information at {portal_url}

Let me know when you're ready to proceed!

Warm regards,
{advisor_name}
{company_name}
{company_phone}",
                'variables' => ['name', 'product_interest', 'portal_url', 'advisor_name', 'company_name', 'company_phone'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 4. FOLLOW-UP - No Response Reminder
            [
                'name' => 'Follow-up - Gentle Reminder',
                'category' => 'follow-up',
                'message_template' => "Hello {name},

I wanted to follow up on my previous message regarding {product_interest}. I understand you're busy, and I don't want you to miss out on the opportunity to secure the best coverage.

Just a quick reminder:
📌 Your inquiry: {lead_number}
📌 Status: {status}
📌 Priority: {priority}

Is there anything holding you back? I'm happy to address any concerns or questions you might have. No pressure—just here to help!

Feel free to call me directly at {company_phone} or reply here at your convenience.

Best regards,
{advisor_name}
{company_website}",
                'variables' => ['name', 'product_interest', 'lead_number', 'status', 'priority', 'company_phone', 'advisor_name', 'company_website'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 5. PROMOTIONAL - Special Offer
            [
                'name' => 'Promotional - Limited Time Offer',
                'category' => 'promotional',
                'message_template' => "🎉 Special Offer Alert! 🎉

Dear {name},

Great news! We have an exclusive promotion on {product_interest} available for a limited time only.

🌟 *Special Benefits:*
• Discounted premiums
• Extended coverage options
• Fast-track approval
• No medical tests (conditions apply)

This offer is valid until the end of {current_date}. Don't miss out on this opportunity to secure comprehensive protection at unbeatable rates!

Want to know more? Let's schedule a quick call!

Contact me:
{advisor_name}
{company_phone}
{company_website}

\"Protect Today, Secure Tomorrow!\"",
                'variables' => ['name', 'product_interest', 'current_date', 'advisor_name', 'company_phone', 'company_website'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 6. PROMOTIONAL - Referral Bonus
            [
                'name' => 'Promotional - Referral Program',
                'category' => 'promotional',
                'message_template' => "Hi {name}! 🌟

I hope you're doing well! At {company_name}, we believe in rewarding our valued clients and prospects.

🎁 *Refer & Earn Program:*
Refer a friend or family member for {product_interest} and BOTH of you receive special benefits!

Your Benefits:
✓ Referral bonus on every successful policy
✓ Priority service for future needs
✓ Exclusive discount on renewals

Their Benefits:
✓ Special introductory rates
✓ Fast processing
✓ Personalized guidance

Share this message or call me at {company_phone} to get started!

Thank you for being part of the {company_name} family!

{advisor_name}
{company_website}",
                'variables' => ['name', 'company_name', 'product_interest', 'company_phone', 'advisor_name', 'company_website'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 7. REMINDER - Document Submission
            [
                'name' => 'Reminder - Documents Pending',
                'category' => 'reminder',
                'message_template' => "Dear {name},

This is a friendly reminder regarding your {product_interest} application ({lead_number}).

⚠️ *Action Required:*
We're awaiting the following documents to proceed with your application:
• ID Proof (Aadhaar/PAN/Passport)
• Address Proof
• Age Proof
• Recent Photographs

The sooner we receive these, the faster we can process your policy!

📧 You can upload documents at: {portal_url}
📞 Or WhatsApp them to: {company_phone}

Need help? I'm just a call away!

{advisor_name}
{company_name}",
                'variables' => ['name', 'product_interest', 'lead_number', 'portal_url', 'company_phone', 'advisor_name', 'company_name'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 8. REMINDER - Meeting/Call Scheduled
            [
                'name' => 'Reminder - Upcoming Meeting',
                'category' => 'reminder',
                'message_template' => 'Hello {name}! 👋

This is a reminder about our scheduled discussion tomorrow regarding your {product_interest} requirements.

📅 *Meeting Details:*
Date: {current_date}
Time: [Time will be confirmed]
Mode: Phone Call / Video Call

Please have the following ready:
✓ Your questions or concerns
✓ Any existing policy documents (if applicable)
✓ Preferred coverage amount and tenure

If you need to reschedule, please let me know at {company_phone}.

Looking forward to our conversation!

Best regards,
{advisor_name}
{company_name}
{company_website}',
                'variables' => ['name', 'product_interest', 'current_date', 'company_phone', 'advisor_name', 'company_name', 'company_website'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 9. INFORMATION - Information Request Response
            [
                'name' => 'Information - Information Sharing',
                'category' => 'information',
                'message_template' => "Dear {name},

Thank you for your inquiry about {product_interest}. Here's the information you requested:

📋 *Key Features:*
• Comprehensive coverage tailored to your needs
• Flexible premium payment options
• Tax benefits under Section 80C/80D
• Quick claim settlement process
• 24/7 customer support

💰 *Premium Estimate:*
We can provide personalized quotes based on your age, coverage amount, and policy term.

🔗 *Learn More:*
Visit: {company_website}
Portal: {portal_url}

Ready to get a quote? Reply to this message or call me at {company_phone}.

Your security is our priority!

{advisor_name}
{company_name}",
                'variables' => ['name', 'product_interest', 'company_website', 'portal_url', 'company_phone', 'advisor_name', 'company_name'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],

            // 10. INFORMATION - Feedback Request
            [
                'name' => 'Information - Feedback Request',
                'category' => 'information',
                'message_template' => "Hi {name},

I hope you're doing well! I wanted to reach out and see if you had any questions about {product_interest} or if there's anything else I can help you with.

Your feedback is valuable to us at {company_name}. Let me know:
• Are you satisfied with the information provided?
• Do you need any clarification?
• Is there a better way I can assist you?

I'm committed to making your insurance journey smooth and hassle-free.

Feel free to reach me anytime:
📞 {company_phone}
📧 {company_email}
🌐 {company_website}

Thank you for considering us!

Warm regards,
{advisor_name}
{company_name}",
                'variables' => ['name', 'product_interest', 'company_name', 'company_phone', 'company_email', 'company_website', 'advisor_name'],
                'is_active' => true,
                'usage_count' => 0,
                'created_by' => $adminUser?->id ?? 1,
            ],
        ];

        foreach ($templates as $template) {
            LeadWhatsAppTemplate::create($template);
        }

        $this->command->info('✅ Created '.count($templates).' WhatsApp lead marketing templates successfully!');
    }
}
