<?php

namespace App\Services;

use App\Models\AppSetting;
use App\Models\Claim;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Models\Quotation;
use App\Services\Notification\NotificationContext;
use App\Services\Notification\VariableResolverService;
use Illuminate\Support\Facades\Log;

class TemplateService
{
    /**
     * Render notification template by type code and channel with variable resolution.
     *
     * This is the core template rendering method orchestrating:
     * 1. Find active NotificationType by code
     * 2. Find active NotificationTemplate for type and channel
     * 3. Resolve template variables using appropriate method:
     *    - NotificationContext: Use VariableResolverService for advanced resolution
     *    - Array: Use legacy replaceVariables() for simple string replacement
     *
     * Returns null if type/template not found, allowing caller to handle fallback logic.
     *
     * Supported channels:
     * - 'whatsapp': WhatsApp message content
     * - 'email': Email body HTML
     * - 'sms': SMS message text
     * - 'push': Push notification body
     * - 'push_title': Push notification title
     *
     * @param  string  $notificationTypeCode  Notification type code (e.g., 'customer_welcome', 'renewal_7_days')
     * @param  string  $channel  Template channel to render
     * @param  array|NotificationContext  $data  Template variables (new: NotificationContext, legacy: array)
     * @return string|null Rendered template content or null if type/template not found
     */
    public function render(string $notificationTypeCode, string $channel, array|NotificationContext $data): ?string
    {
        try {
            // Find notification type by code
            $notificationType = NotificationType::query()->where('code', $notificationTypeCode)
                ->where('is_active', true)
                ->first();

            if (! $notificationType) {
                Log::warning('Notification type not found: '.$notificationTypeCode);

                return null;
            }

            // Find active template for this type and channel
            $template = NotificationTemplate::query()->where('notification_type_id', $notificationType->id)
                ->where('channel', $channel)
                ->where('is_active', true)
                ->first();

            if (! $template) {
                Log::info(sprintf('No active template found for %s (%s)', $notificationTypeCode, $channel));

                return null;
            }

            // Render template with data - use new system if NotificationContext provided
            if ($data instanceof NotificationContext) {
                $resolver = app(VariableResolverService::class);

                return $resolver->resolveTemplate($template->template_content, $data);
            }

            // Legacy array-based replacement
            return $this->replaceVariables($template->template_content, $data);

        } catch (\Exception $exception) {
            Log::error('Template rendering failed for '.$notificationTypeCode, [
                'channel' => $channel,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Replace template variables with values using simple string replacement.
     *
     * Legacy method supporting basic variable substitution for backward compatibility.
     * Handles two placeholder formats:
     * - {{variable_name}} - Standard double-brace format (preferred)
     * - {variable_name} - Single-brace legacy format
     *
     * Simply iterates through data array and replaces all occurrences of each
     * variable placeholder with its value. No advanced features like nested
     * properties, formatters, or conditional logic.
     *
     * For new implementations, prefer NotificationContext with VariableResolverService
     * which provides nested property access, Indian currency formatting, date
     * formatting, and more.
     *
     * @param  string  $template  Template content with variable placeholders
     * @param  array  $data  Associative array of variable names → values
     * @return string Template with all variables replaced by values
     */
    protected function replaceVariables(string $template, array $data): string
    {
        $result = $template;

        foreach ($data as $key => $value) {
            // Replace {{variable}} format
            $result = str_replace('{{'.$key.'}}', (string) $value, $result);

            // Replace {variable} format for backward compatibility
            $result = str_replace('{'.$key.'}', (string) $value, $result);
        }

        return $result;
    }

    /**
     * Get list of available variables for notification template.
     *
     * Fetches the available_variables JSON field from NotificationTemplate,
     * providing documentation of which variables can be used in templates.
     *
     * Useful for:
     * - Template editing UI (show available variables)
     * - Template validation (check for undefined variables)
     * - Documentation generation
     *
     * Returns null if notification type or template not found.
     *
     * Example return value:
     * [
     *   "customer_name",
     *   "policy_number",
     *   "expiry_date",
     *   "company_name",
     *   "company_phone"
     * ]
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  string  $channel  Template channel
     * @return array|null Array of available variable names or null if not found
     */
    public function getAvailableVariables(string $notificationTypeCode, string $channel): ?array
    {
        $notificationType = NotificationType::query()->where('code', $notificationTypeCode)->first();

        if (! $notificationType) {
            return null;
        }

        $template = NotificationTemplate::query()->where('notification_type_id', $notificationType->id)
            ->where('channel', $channel)
            ->first();

        return $template?->available_variables ?? null;
    }

    /**
     * Preview template rendering with sample data without database lookup.
     *
     * Test method for template editing interface allowing users to preview
     * template output before saving. Bypasses database lookup and directly
     * renders provided template content with sample data.
     *
     * Uses legacy replaceVariables() method for simple string replacement.
     * For previewing with NotificationContext, use render() method directly.
     *
     * Useful for:
     * - Template creation UI preview pane
     * - Testing template syntax before saving
     * - Validating variable replacements
     *
     * @param  string  $templateContent  Template content to preview (not saved)
     * @param  array  $data  Sample variable values for preview
     * @return string Rendered template with sample data
     */
    public function preview(string $templateContent, array $data): string
    {
        return $this->replaceVariables($templateContent, $data);
    }

    /**
     * Render template using insurance policy for context building.
     *
     * Convenience method that builds NotificationContext from insurance ID,
     * loads app settings, and renders template. Automatically includes:
     * - Customer details from insurance relationship
     * - Insurance policy information
     * - Company/app settings for branding variables
     *
     * Used for policy-centric notifications: policy creation, renewals,
     * document delivery, premium updates.
     *
     * Returns null if template not found or rendering fails.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  string  $channel  Template channel ('whatsapp', 'email', 'sms', 'push')
     * @param  CustomerInsurance  $insurance  Insurance policy with customer
     * @return string|null Rendered template or null on failure
     */
    public function renderFromInsurance(string $notificationTypeCode, string $channel, $insurance): ?string
    {
        try {
            $context = NotificationContext::fromInsuranceId($insurance->id);
            $context->settings = $this->loadSettings();

            return $this->render($notificationTypeCode, $channel, $context);
        } catch (\Exception $exception) {
            Log::error('Template rendering from insurance failed', [
                'notification_type' => $notificationTypeCode,
                'insurance_id' => $insurance->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Render template using customer for context building.
     *
     * Convenience method that builds NotificationContext from customer ID,
     * loads app settings, and renders template. Automatically includes:
     * - Customer details (name, email, mobile, etc.)
     * - Company/app settings for branding variables
     *
     * Used for customer-centric notifications: welcome messages, account
     * updates, general communications not tied to specific policies.
     *
     * Returns null if template not found or rendering fails.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  string  $channel  Template channel ('whatsapp', 'email', 'sms', 'push')
     * @param  Customer  $customer  Customer instance
     * @return string|null Rendered template or null on failure
     */
    public function renderFromCustomer(string $notificationTypeCode, string $channel, $customer): ?string
    {
        try {
            $context = NotificationContext::fromCustomerId($customer->id);
            $context->settings = $this->loadSettings();

            return $this->render($notificationTypeCode, $channel, $context);
        } catch (\Exception $exception) {
            Log::error('Template rendering from customer failed', [
                'notification_type' => $notificationTypeCode,
                'customer_id' => $customer->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Render template using quotation for context building.
     *
     * Convenience method that builds NotificationContext from quotation ID,
     * loads app settings, and renders template. Automatically includes:
     * - Customer details from quotation relationship
     * - Quotation information (number, companies, premiums)
     * - Company/app settings for branding variables
     *
     * Used for quotation-centric notifications: quotation delivery,
     * comparison reports, price updates, follow-ups.
     *
     * Returns null if template not found or rendering fails.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  string  $channel  Template channel ('whatsapp', 'email', 'sms', 'push')
     * @param  Quotation  $quotation  Quotation instance with customer
     * @return string|null Rendered template or null on failure
     */
    public function renderFromQuotation(string $notificationTypeCode, string $channel, $quotation): ?string
    {
        try {
            $context = NotificationContext::fromQuotationId($quotation->id);
            $context->settings = $this->loadSettings();

            return $this->render($notificationTypeCode, $channel, $context);
        } catch (\Exception $exception) {
            Log::error('Template rendering from quotation failed', [
                'notification_type' => $notificationTypeCode,
                'quotation_id' => $quotation->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Render template using claim for context building.
     *
     * Convenience method that builds NotificationContext from claim's insurance,
     * adds claim details, loads app settings, and renders template.
     * Automatically includes:
     * - Customer details from claim relationship
     * - Insurance policy from claim relationship
     * - Claim information (number, status, amounts)
     * - Company/app settings for branding variables
     *
     * Used for claim-centric notifications: submission confirmations,
     * status updates, approval/rejection notifications, settlement confirmations.
     *
     * Returns null if template not found or rendering fails.
     *
     * @param  string  $notificationTypeCode  Notification type code
     * @param  string  $channel  Template channel ('whatsapp', 'email', 'sms', 'push')
     * @param  Claim  $claim  Claim instance with insurance and customer
     * @return string|null Rendered template or null on failure
     */
    public function renderFromClaim(string $notificationTypeCode, string $channel, $claim): ?string
    {
        try {
            // Build context from claim's insurance
            $context = NotificationContext::fromInsuranceId($claim->insurance->id);

            // Add claim-specific data to context
            $context->claim = $claim;
            $context->settings = $this->loadSettings();

            return $this->render($notificationTypeCode, $channel, $context);
        } catch (\Exception $exception) {
            Log::error('Template rendering from claim failed', [
                'notification_type' => $notificationTypeCode,
                'claim_id' => $claim->id,
                'error' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Load active application settings for template variable resolution.
     *
     * Fetches all active settings and restructures into hierarchical array
     * for template variable access. Removes category prefix from keys for
     * cleaner variable names in templates.
     *
     * Transformation:
     * - Database: {category: 'company', key: 'company_advisor_name', value: 'John'}
     * - Output: ['company' => ['advisor_name' => 'John']]
     *
     * Enables template variables like:
     * - {{company.name}} → Company name
     * - {{company.phone}} → Company phone
     * - {{company.advisor_name}} → Advisor name
     *
     * @return array Hierarchical settings array indexed by category and key
     */
    protected function loadSettings(): array
    {
        $settings = AppSetting::query()->where('is_active', true)->get();

        $structured = [];
        foreach ($settings as $setting) {
            // Strip category prefix from key
            // e.g., 'company_advisor_name' becomes 'advisor_name' under 'company' category
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
