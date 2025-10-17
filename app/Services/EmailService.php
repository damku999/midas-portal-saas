<?php

namespace App\Services;

use App\Mail\TemplatedNotification;
use App\Models\AppSetting;
use App\Models\Claim;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\Quotation;
use App\Services\Notification\NotificationContext;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Email Service
 *
 * Handles sending templated emails using the notification template system.
 * Follows the same pattern as WhatsApp notifications for consistency.
 */
class EmailService
{
    public function __construct(
        protected TemplateService $templateService
    ) {}

    /**
     * Send templated email notification to recipient with optional attachments.
     *
     * This method orchestrates email delivery with comprehensive validation and fallback logic:
     * 1. Checks if email notifications are globally enabled in settings
     * 2. Validates recipient email address format
     * 3. Renders email content from notification template system
     * 4. Falls back to hardcoded message if template not found
     * 5. Generates appropriate subject line based on notification type
     * 6. Converts markdown-style formatting (*bold*) to HTML
     * 7. Sends email via Laravel Mailable with attachments
     * 8. Logs all operations (success/failure) for audit trail
     *
     * Returns false (not throws) on failures to prevent disrupting main workflows,
     * allowing graceful degradation when email delivery fails.
     *
     * @param  string  $to  Recipient email address
     * @param  string  $notificationTypeCode  Notification type code (e.g., 'customer_welcome', 'policy_created')
     * @param  NotificationContext  $notificationContext  Context with customer, insurance, quotation, claim, and settings
     * @param  array  $attachments  Optional file paths to attach (e.g., policy PDFs, quotation documents)
     * @return bool True on successful send, false on validation failure or sending error
     */
    public function sendTemplatedEmail(
        string $to,
        string $notificationTypeCode,
        NotificationContext $notificationContext,
        array $attachments = []
    ): bool {
        try {
            // Check if email notifications are enabled globally
            if (! is_email_notification_enabled()) {
                Log::info('Email notification skipped (disabled in settings)', [
                    'to' => $to,
                    'notification_type' => $notificationTypeCode,
                ]);

                return false;
            }

            // Validate email address
            if (! filter_var($to, FILTER_VALIDATE_EMAIL)) {
                Log::warning('Invalid email address', [
                    'to' => $to,
                    'notification_type' => $notificationTypeCode,
                ]);

                return false;
            }

            // Render email content from template
            $htmlContent = $this->templateService->render($notificationTypeCode, 'email', $notificationContext);

            // If template not found, try fallback
            if (in_array($htmlContent, [null, '', '0'], true)) {
                $htmlContent = $this->getFallbackMessage($notificationTypeCode, $notificationContext);

                if (in_array($htmlContent, [null, '', '0'], true)) {
                    Log::warning('No email template or fallback found', [
                        'notification_type' => $notificationTypeCode,
                        'to' => $to,
                    ]);

                    return false;
                }

                Log::info('Using fallback email template', [
                    'notification_type' => $notificationTypeCode,
                ]);
            }

            // Get subject from context or use default
            $subject = $this->getEmailSubject($notificationTypeCode, $notificationContext);

            // Convert markdown-style formatting to HTML
            $htmlContent = $this->formatEmailContent($htmlContent);

            // Send email using mailable
            Mail::to($to)->send(new TemplatedNotification(
                subject: $subject,
                htmlContent: $htmlContent,
                attachments: $attachments
            ));

            Log::info('Email sent successfully', [
                'to' => $to,
                'notification_type' => $notificationTypeCode,
                'subject' => $subject,
                'attachments_count' => count($attachments),
            ]);

            return true;

        } catch (\Exception $exception) {
            Log::error('Email sending failed', [
                'to' => $to,
                'notification_type' => $notificationTypeCode,
                'error' => $exception->getMessage(),
                'trace' => $exception->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Send email using customer model for context building.
     *
     * Convenience method that builds NotificationContext from customer ID,
     * validates customer has email address, loads app settings, and sends
     * templated email. Returns false if customer has no email.
     *
     * Used for customer-centric notifications: welcome messages, account updates,
     * general communications not tied to specific policies or claims.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  Customer  $customer  Customer instance with email
     * @param  array  $attachments  Optional file paths to attach
     * @return bool True on successful send, false if no email or sending fails
     */
    public function sendFromCustomer(string $notificationTypeCode, $customer, array $attachments = []): bool
    {
        try {
            if (empty($customer->email)) {
                Log::info('Email skipped - customer has no email address', [
                    'customer_id' => $customer->id,
                    'notification_type' => $notificationTypeCode,
                ]);

                return false;
            }

            $context = NotificationContext::fromCustomerId($customer->id);
            $context->settings = $this->loadSettings();

            return $this->sendTemplatedEmail($customer->email, $notificationTypeCode, $context, $attachments);

        } catch (\Exception $exception) {
            Log::error('Email from customer failed', [
                'customer_id' => $customer->id,
                'notification_type' => $notificationTypeCode,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send email using insurance policy for context building.
     *
     * Convenience method that builds NotificationContext from insurance ID,
     * loads customer from insurance relationship, validates email exists,
     * loads app settings, and sends templated email with policy details.
     *
     * Used for policy-centric notifications: policy creation confirmations,
     * renewal reminders, policy document delivery, premium updates.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  CustomerInsurance  $insurance  Insurance instance with customer
     * @param  array  $attachments  Optional file paths to attach (e.g., policy document PDF)
     * @return bool True on successful send, false if no email or sending fails
     */
    public function sendFromInsurance(string $notificationTypeCode, $insurance, array $attachments = []): bool
    {
        try {
            $customer = $insurance->customer;

            if (empty($customer->email)) {
                Log::info('Email skipped - customer has no email address', [
                    'customer_id' => $customer->id,
                    'insurance_id' => $insurance->id,
                    'notification_type' => $notificationTypeCode,
                ]);

                return false;
            }

            $context = NotificationContext::fromInsuranceId($insurance->id);
            $context->settings = $this->loadSettings();

            return $this->sendTemplatedEmail($customer->email, $notificationTypeCode, $context, $attachments);

        } catch (\Exception $exception) {
            Log::error('Email from insurance failed', [
                'insurance_id' => $insurance->id,
                'notification_type' => $notificationTypeCode,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send email using quotation for context building.
     *
     * Convenience method that builds NotificationContext from quotation ID,
     * uses quotation email if available (fallback to customer email),
     * loads app settings, and sends templated email with quotation details.
     *
     * Smart email selection: prioritizes quotation.email over customer.email,
     * supporting scenarios where quotations are sent to alternate email addresses.
     *
     * Used for quotation-centric notifications: quotation delivery, price updates,
     * comparison reports, follow-ups.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  Quotation  $quotation  Quotation instance with customer
     * @param  array  $attachments  Optional file paths to attach (e.g., quotation PDF)
     * @return bool True on successful send, false if no email or sending fails
     */
    public function sendFromQuotation(string $notificationTypeCode, $quotation, array $attachments = []): bool
    {
        try {
            $customer = $quotation->customer;

            // Use quotation email if available, otherwise customer email
            $email = $quotation->email ?? $customer->email;

            if (empty($email)) {
                Log::info('Email skipped - no email address available', [
                    'customer_id' => $customer->id,
                    'quotation_id' => $quotation->id,
                    'notification_type' => $notificationTypeCode,
                ]);

                return false;
            }

            $context = NotificationContext::fromQuotationId($quotation->id);
            $context->settings = $this->loadSettings();

            return $this->sendTemplatedEmail($email, $notificationTypeCode, $context, $attachments);

        } catch (\Exception $exception) {
            Log::error('Email from quotation failed', [
                'quotation_id' => $quotation->id,
                'notification_type' => $notificationTypeCode,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send email using claim for context building.
     *
     * Convenience method that builds NotificationContext from claim ID,
     * loads customer from claim relationship, validates email exists,
     * loads app settings, and sends templated email with claim details.
     *
     * Used for claim-centric notifications: claim submission confirmations,
     * status updates, approval/rejection notifications, settlement confirmations.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  Claim  $claim  Claim instance with customer
     * @param  array  $attachments  Optional file paths to attach
     * @return bool True on successful send, false if no email or sending fails
     */
    public function sendFromClaim(string $notificationTypeCode, $claim, array $attachments = []): bool
    {
        try {
            $customer = $claim->customer;

            if (empty($customer->email)) {
                Log::info('Email skipped - customer has no email address', [
                    'customer_id' => $customer->id,
                    'claim_id' => $claim->id,
                    'notification_type' => $notificationTypeCode,
                ]);

                return false;
            }

            $context = NotificationContext::fromClaimId($claim->id);
            $context->settings = $this->loadSettings();

            return $this->sendTemplatedEmail($customer->email, $notificationTypeCode, $context, $attachments);

        } catch (\Exception $exception) {
            Log::error('Email from claim failed', [
                'claim_id' => $claim->id,
                'notification_type' => $notificationTypeCode,
                'error' => $exception->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Generate email subject line based on notification type and context.
     *
     * Returns descriptive, context-aware subject lines for different notification types:
     * - customer_welcome: Welcome to {Company Name}
     * - policy_created: Your Insurance Policy Document - {Policy Number}
     * - quotation_ready: Your Insurance Quotation - {Quotation Number}
     * - renewal_*: Insurance Renewal Reminder - {Policy Number}
     * - claim_*: Claim status with claim number
     *
     * Extracts policy numbers, quotation numbers, claim numbers from context
     * when available, providing clear email identification for customers.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  NotificationContext  $notificationContext  Context with insurance, quotation, claim data
     * @return string Formatted email subject line
     */
    protected function getEmailSubject(string $notificationTypeCode, NotificationContext $notificationContext): string
    {
        // Default subjects based on notification type
        return match ($notificationTypeCode) {
            'customer_welcome' => 'Welcome to '.company_name(),
            'policy_created' => 'Your Insurance Policy Document - '.($notificationContext->insurance?->policy_no ?? 'Policy'),
            'quotation_ready' => 'Your Insurance Quotation - '.($notificationContext->quotation?->quotation_number ?? 'Quote'),
            'renewal_30_days', 'renewal_15_days', 'renewal_7_days' => 'Insurance Renewal Reminder - '.($notificationContext->insurance?->policy_no ?? 'Policy'),
            'renewal_expired' => 'Insurance Policy Expired - Immediate Renewal Required',
            'claim_submitted' => 'Claim Submitted Successfully - '.($notificationContext->claim?->claim_number ?? 'Claim'),
            'claim_approved' => 'Claim Approved - '.($notificationContext->claim?->claim_number ?? 'Claim'),
            'claim_rejected' => 'Claim Status Update - '.($notificationContext->claim?->claim_number ?? 'Claim'),
            default => company_name().' - Notification',
        };
    }

    /**
     * Format plain text email content to HTML with basic styling.
     *
     * Applies lightweight text-to-HTML transformations:
     * - Converts *text* to <strong>text</strong> for bold emphasis
     * - Converts line breaks (\n) to <br> tags for proper display
     * - Converts URLs to clickable links with styling
     *
     * Enables templates to use simple markdown-style syntax while rendering
     * as properly formatted HTML emails.
     *
     * @param  string  $content  Plain text content with markdown-style formatting
     * @return string HTML-formatted content ready for email body
     */
    protected function formatEmailContent(string $content): string
    {
        // Convert bold text: *text* to <strong>text</strong>
        $content = preg_replace('/\*([^\*]+)\*/', '<strong>$1</strong>', $content);

        // Convert line breaks to <br>
        $content = nl2br((string) $content);

        // Convert URLs to links
        $content = preg_replace(
            '#(https?://[^\s<]+)#i',
            '<a href="$1" style="color: #3490dc; text-decoration: none;">$1</a>',
            $content
        );

        return $content;
    }

    /**
     * Generate fallback email message when template not found.
     *
     * Provides hardcoded fallback messages for critical notification types,
     * ensuring notifications can still be sent even if templates are missing
     * or not yet created. Returns null for types without fallbacks.
     *
     * Supported fallbacks:
     * - customer_welcome: Personalized welcome message with advisor details
     * - policy_created: Policy document confirmation with expiry date
     * - quotation_ready: Quotation delivery with review prompt
     * - renewal_*: Urgency-based renewal reminders
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  NotificationContext  $notificationContext  Context for variable extraction
     * @return string|null Fallback message content or null if no fallback exists
     */
    protected function getFallbackMessage(string $notificationTypeCode, NotificationContext $notificationContext): ?string
    {
        $customer = $notificationContext->customer;
        $insurance = $notificationContext->insurance;
        $quotation = $notificationContext->quotation;

        return match ($notificationTypeCode) {
            'customer_welcome' => $this->getFallbackWelcomeMessage($customer),
            'policy_created' => $this->getFallbackPolicyCreatedMessage($insurance),
            'quotation_ready' => $this->getFallbackQuotationMessage($quotation),
            'renewal_30_days', 'renewal_15_days', 'renewal_7_days', 'renewal_expired' => $this->getFallbackRenewalMessage($insurance, $notificationTypeCode),
            default => null,
        };
    }

    /**
     * Generate fallback welcome email message.
     *
     * Creates personalized welcome message for new customers including:
     * - Personal greeting with customer name
     * - Advisor introduction and commitment statement
     * - Service offerings overview
     * - Contact encouragement
     * - Company branding (advisor name, website, title, tagline)
     *
     * Used when customer_welcome template not found.
     *
     * @param  Customer  $customer  Customer to welcome
     * @return string Formatted welcome message
     */
    protected function getFallbackWelcomeMessage($customer): string
    {
        return "Dear {$customer->name},

Welcome to the world of insurance solutions! I'm ".company_advisor_name().", your dedicated insurance advisor here to guide you through every step of your insurance journey.

Whether you're seeking protection for your loved ones, securing your assets, or planning for the future, I'm committed to providing personalized advice and finding the perfect insurance solutions tailored to your needs.

Feel free to reach out anytime with questions or concerns. Let's work together to safeguard what matters most to you!

Best regards,
".company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }

    /**
     * Generate fallback policy creation email message.
     *
     * Creates policy confirmation message including:
     * - Customer name and gratitude
     * - Policy number and type details
     * - Registration number (for vehicle insurance)
     * - Expiry date formatted as d-m-Y
     * - Contact encouragement
     * - Company branding
     *
     * Uses markdown *bold* syntax for key details (policy number, dates).
     * Used when policy_created template not found.
     *
     * @param  CustomerInsurance  $insurance  Insurance policy with customer
     * @return string Formatted policy creation message
     */
    protected function getFallbackPolicyCreatedMessage($insurance): string
    {
        $expiredDate = $insurance->expired_date ? date('d-m-Y', strtotime((string) $insurance->expired_date)) : 'N/A';
        $policyDetail = trim(($insurance->premiumType?->name ?? '').' '.($insurance->registration_no ?? ''));

        return "Dear {$insurance->customer->name},

Thank you for entrusting me with your insurance needs. Please find attached the policy document with *Policy No. {$insurance->policy_no}* of your *{$policyDetail}* which expires on *{$expiredDate}*.

If you have any questions or need further assistance, please don't hesitate to reach out.

Best regards,
".company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }

    /**
     * Generate fallback quotation email message.
     *
     * Creates quotation delivery message including:
     * - Customer name and appreciation
     * - Quotation number
     * - Multi-provider comparison mention
     * - Review and inquiry prompt
     * - Company branding
     *
     * Used when quotation_ready template not found.
     *
     * @param  Quotation  $quotation  Quotation with customer
     * @return string Formatted quotation delivery message
     */
    protected function getFallbackQuotationMessage($quotation): string
    {
        $customer = $quotation->customer;

        return "Dear {$customer->name},

Thank you for your interest in our insurance solutions. Please find attached your personalized quotation (Quotation No: {$quotation->quotation_number}).

We've compared multiple insurance providers to bring you the best options. Review the details and let us know if you have any questions.

Best regards,
".company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }

    /**
     * Generate fallback renewal reminder email message.
     *
     * Creates urgency-adapted renewal reminder including:
     * - Customer name
     * - Premium type and policy number
     * - Insurance company name
     * - Expiry date formatted as d-m-Y
     * - Urgency wording based on days until expiry:
     *   * renewal_30_days: "is due for renewal on"
     *   * renewal_15_days: "is expiring soon on"
     *   * renewal_7_days: "is expiring very soon on"
     *   * renewal_expired: "has expired on"
     * - Contact information
     * - Company branding
     *
     * Uses markdown *bold* syntax for emphasis on key details.
     * Used when renewal template not found.
     *
     * @param  CustomerInsurance  $insurance  Insurance policy with expiry date
     * @param  string  $notificationTypeCode  Specific renewal notification type
     * @return string Formatted renewal reminder message
     */
    protected function getFallbackRenewalMessage($insurance, $notificationTypeCode): string
    {
        $expiredDate = $insurance->expired_date ? date('d-m-Y', strtotime((string) $insurance->expired_date)) : 'N/A';
        $customerName = $insurance->customer->name;
        $premiumType = $insurance->premiumType?->name ?? 'Insurance';
        $policyNo = $insurance->policy_no;
        $companyName = $insurance->insuranceCompany?->name ?? 'Insurance Company';

        $urgency = match ($notificationTypeCode) {
            'renewal_30_days' => 'is due for renewal on',
            'renewal_15_days' => 'is expiring soon on',
            'renewal_7_days' => 'is expiring very soon on',
            'renewal_expired' => 'has expired on',
            default => 'is due for renewal on',
        };

        return "Dear *{$customerName}*,

Your *{$premiumType}* under Policy No *{$policyNo}* of *{$companyName}* {$urgency} *{$expiredDate}*.

To ensure continuous coverage, please renew by the due date. For assistance, contact us at ".company_phone().'.

Best regards,
'.company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }

    /**
     * Load active application settings for template variable resolution.
     *
     * Fetches all active settings and restructures them into hierarchical array:
     * - Original structure: {category: 'company', key: 'company_name', value: 'ABC'}
     * - Restructured: ['company' => ['name' => 'ABC']]
     *
     * Removes category prefix from keys for cleaner template variables:
     * - 'company_name' becomes 'name' under 'company' category
     *
     * Enables template variables like {{company.name}}, {{company.phone}}.
     *
     * @return array Hierarchical settings array indexed by category and key
     */
    protected function loadSettings(): array
    {
        $settings = AppSetting::query()->where('is_active', true)->get();

        $structured = [];
        foreach ($settings as $setting) {
            $key = $setting->key;
            $categoryPrefix = $setting->category.'_';

            if (str_starts_with((string) $key, $categoryPrefix)) {
                $key = substr((string) $key, strlen($categoryPrefix));
            }

            $structured[$setting->category][$key] = $setting->value;
        }

        return $structured;
    }
}
