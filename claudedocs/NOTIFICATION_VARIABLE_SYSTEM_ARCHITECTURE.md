# Notification Variable System - Dynamic Architecture Plan

## Executive Summary

Transform the static hardcoded variable system into a dynamic, data-driven architecture that:
- Auto-discovers variables from database schema
- Resolves variables to real customer/insurance data
- Generates UI dynamically from available data sources
- Handles attachments and complex data types
- Provides real-time preview with actual data

---

## Current System Analysis

### ✅ What Works
- Basic variable replacement using `{{variable}}` format
- Simple preview with sample data
- Template storage in database
- UI for creating/editing templates

### ❌ Current Limitations
1. **Static Variables**: 28 hardcoded variables in blade templates
2. **Sample Data Only**: Preview uses fake data, not real customer data
3. **Manual Maintenance**: Adding new variables requires code changes in multiple places
4. **No Data Source Connection**: Variables not linked to actual database fields
5. **No Attachment Support**: Can't include policy documents or other files
6. **No Validation**: No way to know if variable is valid for notification type

---

## Data Source Mapping

### 1. Customer Data (`customers` table)
```php
// Direct Fields
'customer_name'           => customers.name
'customer_email'          => customers.email
'customer_mobile'         => customers.mobile_number
'customer_whatsapp'       => customers.mobile_number
'date_of_birth'           => customers.date_of_birth (formatted)
'wedding_anniversary'     => customers.wedding_anniversary_date (formatted)
'engagement_anniversary'  => customers.engagement_anniversary_date (formatted)
```

### 2. Customer Insurance Data (`customer_insurances` table)
```php
// Direct Fields
'policy_no'               => customer_insurances.policy_no
'policy_number'           => customer_insurances.policy_no (alias)
'registration_no'         => customer_insurances.registration_no
'vehicle_number'          => customer_insurances.registration_no (alias)
'vehicle_make_model'      => customer_insurances.make_model
'start_date'              => customer_insurances.start_date (formatted)
'expired_date'            => customer_insurances.expired_date (formatted)
'expiry_date'             => customer_insurances.expired_date (alias, formatted)
'premium_amount'          => customer_insurances.premium_amount (formatted with ₹)
'net_premium'             => customer_insurances.net_premium (formatted with ₹)
'ncb_percentage'          => customer_insurances.ncb_percentage (formatted with %)
'idv_amount'              => customer_insurances.sum_insured (formatted with ₹)
'rto'                     => customer_insurances.rto
'mfg_year'                => customer_insurances.mfg_year
'plan_name'               => customer_insurances.plan_name
'policy_term'             => customer_insurances.policy_term
'maturity_date'           => customer_insurances.maturity_date (formatted)

// Calculated Fields
'days_remaining'          => DATEDIFF(expired_date, NOW())
'policy_tenure'           => Year difference between start_date and expired_date
'is_expired'              => expired_date < NOW()
```

### 3. Related Data (via relationships)
```php
// Insurance Company
'insurance_company'       => insuranceCompany.name
'insurance_company_code'  => insuranceCompany.code

// Policy Type
'policy_type'             => policyType.name

// Premium Type
'premium_type'            => premiumType.name

// Fuel Type
'fuel_type'               => fuelType.name

// Branch
'branch_name'             => branch.name
'branch_address'          => branch.address

// Broker
'broker_name'             => broker.name

// Reference User
'reference_user'          => referenceUser.name
```

### 4. App Settings (`app_settings` table)
```php
// Company Category
'company_name'            => app_settings[company.name]
'company_phone'           => app_settings[company.phone]
'company_email'           => app_settings[company.email]
'company_address'         => app_settings[company.address]
'company_website'         => app_settings[company.website]
'company_logo_url'        => app_settings[company.logo_url]

// WhatsApp Category
'whatsapp_number'         => app_settings[whatsapp.sender_id]
'whatsapp_support'        => app_settings[whatsapp.support_number]

// Application Category
'portal_url'              => app_settings[application.portal_url]
'support_email'           => app_settings[application.support_email]
```

### 5. Quotation Data (`quotations` table)
```php
'quotes_count'            => Count of quotation_companies for quotation
'best_company_name'       => Quotation company with lowest premium
'best_premium'            => Lowest premium from quotation_companies (formatted with ₹)
'comparison_list'         => HTML table of all quotes
```

### 6. Claim Data (`claims` table)
```php
'claim_number'            => claims.claim_number
'claim_status'            => claims.status
'claim_amount'            => claims.claim_amount (formatted with ₹)

// Current Stage (via relationship)
'stage_name'              => currentStage.stage_name
'notes'                   => currentStage.notes
'pending_documents_list'  => HTML list of pending documents
```

### 7. System/Advisor Data
```php
'advisor_name'            => From authenticated user or app settings
'current_date'            => Current date (formatted)
'current_year'            => Current year
```

---

## Architecture Design

### Component 1: Variable Registry Service

**Purpose**: Central registry of all available variables with metadata

**Location**: `app/Services/Notification/VariableRegistryService.php`

**Features**:
```php
class VariableRegistryService
{
    // Get all registered variables
    public function getAllVariables(): Collection

    // Get variables by category
    public function getVariablesByCategory(string $category): Collection

    // Get variables by notification type
    public function getVariablesByNotificationType(string $type): Collection

    // Get variable metadata
    public function getVariableMetadata(string $variable): array

    // Register new variable
    public function registerVariable(string $key, array $metadata): void
}
```

**Variable Metadata Structure**:
```php
[
    'key' => 'customer_name',
    'label' => 'Customer Name',
    'description' => 'Full name of the customer',
    'category' => 'customer',
    'data_source' => 'customers.name',
    'data_type' => 'string',
    'format' => null, // or 'date', 'currency', 'percentage'
    'requires_context' => ['customer'], // What entities are needed
    'sample_value' => 'John Doe',
    'ui_color' => 'primary', // Bootstrap color for button
    'ui_icon' => 'fa-user',
    'available_for' => ['birthday', 'policy_created', 'renewal', ...] // Notification types
]
```

**Registry Configuration** (`config/notification_variables.php`):
```php
return [
    // Customer Variables
    'customer' => [
        'customer_name' => [
            'source' => 'customer.name',
            'label' => 'Customer Name',
            'category' => 'customer',
            'type' => 'string',
            'color' => 'primary',
            'icon' => 'fa-user',
        ],
        // ... more customer variables
    ],

    // Insurance Variables
    'insurance' => [
        'policy_number' => [
            'source' => 'insurance.policy_no',
            'label' => 'Policy Number',
            'category' => 'policy',
            'type' => 'string',
            'color' => 'success',
            'icon' => 'fa-file-contract',
        ],
        // ... more insurance variables
    ],

    // Settings Variables
    'settings' => [
        'company_name' => [
            'source' => 'setting:company.name',
            'label' => 'Company Name',
            'category' => 'company',
            'type' => 'string',
            'color' => 'info',
            'icon' => 'fa-building',
        ],
        // ... more settings variables
    ],
];
```

---

### Component 2: Variable Resolver Service

**Purpose**: Resolve variables to actual data from database

**Location**: `app/Services/Notification/VariableResolverService.php`

**Features**:
```php
class VariableResolverService
{
    public function __construct(
        protected VariableRegistryService $registry
    ) {}

    // Resolve all variables in template for specific context
    public function resolveTemplate(
        string $template,
        NotificationContext $context
    ): string

    // Resolve single variable
    public function resolveVariable(
        string $variable,
        NotificationContext $context
    ): mixed

    // Get all variable values for context
    public function resolveAllVariables(
        NotificationContext $context
    ): array

    // Validate if all variables in template can be resolved
    public function validateTemplate(
        string $template,
        string $notificationType
    ): ValidationResult
}
```

**NotificationContext Class**:
```php
class NotificationContext
{
    public ?Customer $customer = null;
    public ?CustomerInsurance $insurance = null;
    public ?Quotation $quotation = null;
    public ?Claim $claim = null;
    public array $settings = [];
    public array $customData = [];

    public function __construct(array $data = [])
    {
        // Auto-populate from array
    }

    public function hasCustomer(): bool
    public function hasInsurance(): bool
    public function hasQuotation(): bool
    public function hasClaim(): bool
}
```

**Resolution Logic**:
```php
protected function resolveBySource(string $source, NotificationContext $context): mixed
{
    // Parse source: "customer.name" or "setting:company.name"

    if (str_starts_with($source, 'setting:')) {
        return $this->resolveFromSettings($source);
    }

    if (str_starts_with($source, 'customer.')) {
        return $this->resolveFromCustomer($source, $context->customer);
    }

    if (str_starts_with($source, 'insurance.')) {
        return $this->resolveFromInsurance($source, $context->insurance);
    }

    // ... more sources
}
```

---

### Component 3: Dynamic UI Component

**Purpose**: Generate variable buttons dynamically from registry

**Location**: `resources/views/components/notification-variables.blade.php`

**Usage**:
```blade
<x-notification-variables
    :notification-type="$notificationType ?? null"
    target-field="template_content"
/>
```

**Component Features**:
- Load variables from registry via AJAX
- Group by category with color coding
- Filter by notification type
- Search/filter functionality
- Click to insert at cursor
- Show variable description on hover
- Visual feedback on insertion

**Controller Endpoint**:
```php
// app/Http/Controllers/NotificationTemplateController.php

public function getAvailableVariables(Request $request)
{
    $notificationType = $request->input('notification_type');

    $variables = app(VariableRegistryService::class)
        ->getVariablesByNotificationType($notificationType);

    return response()->json([
        'variables' => $variables->groupBy('category'),
        'categories' => $variables->pluck('category')->unique(),
    ]);
}
```

---

### Component 4: Real Data Preview System

**Purpose**: Generate preview using actual customer/insurance data

**Implementation Strategy**:

**Option A**: Sample Data Selector
```php
// User selects a real customer/insurance from dropdown
// Preview uses actual data from that selection

public function preview(Request $request)
{
    $template = $request->template_content;
    $customerId = $request->customer_id; // Optional
    $insuranceId = $request->insurance_id; // Optional

    $context = $this->buildContext($customerId, $insuranceId);

    $preview = app(VariableResolverService::class)
        ->resolveTemplate($template, $context);

    return response()->json(['preview' => $preview]);
}

protected function buildContext(?int $customerId, ?int $insuranceId): NotificationContext
{
    $context = new NotificationContext();

    if ($customerId) {
        $context->customer = Customer::find($customerId);
    } else {
        // Use first customer or create sample
        $context->customer = Customer::first() ?? $this->getSampleCustomer();
    }

    if ($insuranceId) {
        $context->insurance = CustomerInsurance::find($insuranceId);
    } elseif ($context->customer) {
        // Use first insurance of customer
        $context->insurance = $context->customer->insurance()->first();
    }

    // Load settings
    $context->settings = $this->loadSettings();

    return $context;
}
```

**Option B**: Random Real Data
```php
// Automatically use random real customer/insurance for preview
// Faster but less control

protected function buildContext(): NotificationContext
{
    $context = new NotificationContext();

    // Random active customer
    $context->customer = Customer::where('status', true)
        ->inRandomOrder()
        ->first();

    // Random active insurance
    $context->insurance = CustomerInsurance::where('status', true)
        ->inRandomOrder()
        ->first();

    $context->settings = $this->loadSettings();

    return $context;
}
```

**Recommended**: Combination approach - Default to random, allow selection

---

### Component 5: Enhanced Send Test & Actual Sending

**Send Test with Real Data**:
```php
public function sendTest(Request $request)
{
    // ... validation

    $context = $this->buildContext(
        $request->customer_id,
        $request->insurance_id
    );

    $message = app(VariableResolverService::class)
        ->resolveTemplate($request->template_content, $context);

    // Handle attachments
    $attachments = $this->resolveAttachments($context);

    if ($request->channel === 'whatsapp') {
        $this->sendWhatsAppTest($request->recipient, $message, $attachments);
    } else {
        $this->sendEmailTest($request->recipient, $request->subject, $message, $attachments);
    }
}
```

**Actual Notification Sending**:
```php
// New service: app/Services/Notification/NotificationSendingService.php

class NotificationSendingService
{
    public function sendNotification(
        NotificationTemplate $template,
        NotificationContext $context,
        string $recipient
    ): SendResult {

        $resolver = app(VariableResolverService::class);

        // Resolve template
        $message = $resolver->resolveTemplate(
            $template->template_content,
            $context
        );

        $subject = $resolver->resolveTemplate(
            $template->subject ?? '',
            $context
        );

        // Resolve attachments
        $attachments = $this->resolveAttachments($context, $template);

        // Send via appropriate channel
        return match($template->channel) {
            'whatsapp' => $this->sendWhatsApp($recipient, $message, $attachments),
            'email' => $this->sendEmail($recipient, $subject, $message, $attachments),
            'both' => $this->sendBoth($recipient, $subject, $message, $attachments),
        };
    }
}
```

---

### Component 6: Attachment Handling

**Purpose**: Handle file attachments dynamically based on variable usage

**Strategy**:

1. **Attachment Variables**: Special variables for files
```php
'{{@policy_document}}'     => Attach policy document PDF
'{{@customer_id_proof}}'   => Attach customer ID documents
'{{@claim_documents}}'     => Attach all claim-related documents
```

2. **Attachment Resolver**:
```php
class AttachmentResolver
{
    public function resolveAttachments(
        string $template,
        NotificationContext $context
    ): array {

        $attachments = [];

        // Find attachment variables in template
        preg_match_all('/\{\{@(\w+)\}\}/', $template, $matches);

        foreach ($matches[1] as $attachmentVar) {
            $file = $this->resolveAttachment($attachmentVar, $context);
            if ($file) {
                $attachments[] = $file;
            }
        }

        return $attachments;
    }

    protected function resolveAttachment(
        string $variable,
        NotificationContext $context
    ): ?string {

        return match($variable) {
            'policy_document' => $context->insurance?->policy_document_path,
            'customer_pan' => $context->customer?->pan_card_path,
            'customer_aadhar' => $context->customer?->aadhar_card_path,
            default => null,
        };
    }
}
```

3. **Email Attachments**:
```php
Mail::send('emails.generic', ['content' => $message], function($mail) use ($to, $subject, $attachments) {
    $mail->to($to)->subject($subject);

    foreach ($attachments as $attachment) {
        if (Storage::exists($attachment)) {
            $mail->attach(Storage::path($attachment));
        }
    }
});
```

4. **WhatsApp Attachments** (if API supports):
```php
// Send document via WhatsApp API
$this->whatsappClient->sendDocument([
    'receiver_id' => $phoneNumber,
    'document_url' => Storage::url($attachment),
    'caption' => $message,
]);
```

---

## Implementation Phases

### Phase 1: Foundation (Core Services)
**Duration**: 2-3 hours

1. Create config file `config/notification_variables.php` with all variable definitions
2. Implement `VariableRegistryService`
3. Implement `NotificationContext` class
4. Implement `VariableResolverService` with basic resolution logic
5. Write unit tests for resolver

**Deliverable**: Working variable resolution from config

---

### Phase 2: Dynamic UI
**Duration**: 1-2 hours

1. Create API endpoint `getAvailableVariables()`
2. Update create/edit blade templates to use dynamic loading
3. Remove hardcoded variable buttons
4. Add JavaScript for dynamic variable loading and insertion
5. Add variable search/filter functionality

**Deliverable**: UI loads variables dynamically from registry

---

### Phase 3: Real Data Preview
**Duration**: 1-2 hours

1. Update `preview()` method to use VariableResolverService
2. Add customer/insurance selector to preview section (optional)
3. Update JavaScript to send context IDs with preview request
4. Test preview with real data

**Deliverable**: Preview shows actual customer/insurance data

---

### Phase 4: Enhanced Send Test
**Duration**: 1 hour

1. Update `sendTest()` method to use VariableResolverService
2. Add customer/insurance selector to test send section
3. Test sending with real data

**Deliverable**: Test send uses actual data

---

### Phase 5: Attachment Support
**Duration**: 1-2 hours

1. Implement `AttachmentResolver`
2. Add attachment variables to registry
3. Update email sending to include attachments
4. Test attachment resolution and sending

**Deliverable**: Can attach policy documents to emails

---

### Phase 6: Production Integration
**Duration**: 1 hour

1. Create `NotificationSendingService`
2. Integrate with existing notification jobs/commands
3. Update `SendBirthdayWishes` and `SendRenewalReminders` commands
4. Test end-to-end notification flow

**Deliverable**: Production notifications use dynamic variable system

---

## Migration Strategy

### Backward Compatibility
- Keep old `generateSampleData()` method as fallback
- Support both `{{variable}}` and `{variable}` formats
- Gradual migration of existing templates

### Database Changes
**None required** - Existing schema supports this architecture

### Testing Strategy
1. Unit tests for VariableResolverService
2. Feature tests for preview endpoint
3. Feature tests for send test endpoint
4. Integration tests for actual notification sending
5. Manual testing with various notification types

---

## Configuration File Structure

```php
// config/notification_variables.php

return [

    'categories' => [
        'customer' => [
            'label' => 'Customer Information',
            'color' => 'primary',
            'icon' => 'fa-user',
        ],
        'policy' => [
            'label' => 'Policy Details',
            'color' => 'success',
            'icon' => 'fa-file-contract',
        ],
        // ... more categories
    ],

    'variables' => [

        // Customer Variables
        'customer_name' => [
            'label' => 'Customer Name',
            'description' => 'Full name of the customer',
            'category' => 'customer',
            'source' => 'customer.name',
            'type' => 'string',
            'format' => null,
            'sample' => 'John Doe',
            'requires' => ['customer'],
            'available_for' => null, // null = all notification types
        ],

        'customer_email' => [
            'label' => 'Customer Email',
            'description' => 'Email address of the customer',
            'category' => 'customer',
            'source' => 'customer.email',
            'type' => 'string',
            'sample' => 'john@example.com',
            'requires' => ['customer'],
        ],

        // ... all variables defined here

        'policy_document' => [
            'label' => 'Policy Document',
            'description' => 'Attach policy document PDF',
            'category' => 'attachments',
            'source' => 'insurance.policy_document_path',
            'type' => 'attachment',
            'sample' => '[Policy Document]',
            'requires' => ['insurance'],
            'variable_format' => '@policy_document', // Use {{@policy_document}}
        ],
    ],

    // Notification type specific variable sets
    'notification_types' => [
        'birthday_wish' => [
            'required_context' => ['customer'],
            'suggested_variables' => ['customer_name', 'date_of_birth', 'company_name'],
        ],
        'policy_created' => [
            'required_context' => ['customer', 'insurance'],
            'suggested_variables' => ['customer_name', 'policy_number', 'insurance_company', 'expiry_date'],
        ],
        'renewal_reminder' => [
            'required_context' => ['customer', 'insurance'],
            'suggested_variables' => ['customer_name', 'policy_number', 'expiry_date', 'days_remaining', 'vehicle_number'],
        ],
        // ... more notification types
    ],
];
```

---

## Benefits of This Architecture

### 1. **Maintainability**
- Single source of truth for all variables
- Add new variables by editing config only
- No code changes needed for new variables

### 2. **Flexibility**
- Variables automatically available based on data sources
- Easy to add new data sources (claims, quotations, etc.)
- Support for complex variable types (attachments, calculations)

### 3. **User Experience**
- Real-time preview with actual data
- Test sending with real customer context
- Dynamic UI shows only relevant variables

### 4. **Type Safety**
- Variable metadata includes type information
- Proper formatting (dates, currency, percentage)
- Validation before sending

### 5. **Scalability**
- Easy to extend for new notification channels (SMS, push, etc.)
- Support for conditional variables
- Support for variable transformations

---

## Advanced Features (Future)

### 1. Conditional Variables
```
{{#if days_remaining < 7}}
Urgent: Your policy expires in {{days_remaining}} days!
{{else}}
Your policy expires on {{expiry_date}}
{{/if}}
```

### 2. Variable Transformations
```
{{customer_name|uppercase}}
{{premium_amount|format:currency:INR}}
{{expiry_date|format:date:d/m/Y}}
```

### 3. Computed Variables
```php
'days_until_expiry' => [
    'source' => 'computed',
    'computation' => fn($ctx) => $ctx->insurance->expired_date->diffInDays(now()),
],
```

### 4. Variable Validation
- Validate template before saving
- Show warnings if variables missing for notification type
- Suggest variables based on notification type

### 5. Template Inheritance
- Base templates with common variables
- Notification-specific templates extending base

---

## Security Considerations

### 1. Data Access Control
- Ensure user has permission to access customer/insurance data
- Mask sensitive data in preview (partial PAN, mobile, etc.)
- Audit trail for template usage

### 2. Variable Whitelisting
- Only allow registered variables
- Prevent arbitrary code execution
- Validate variable sources

### 3. File Attachment Safety
- Validate file paths
- Check file existence and permissions
- Virus scan for uploaded documents
- Size limits for attachments

---

## Success Metrics

1. ✅ **Zero hardcoded variables** in blade templates
2. ✅ **Real data preview** working for all notification types
3. ✅ **Attachment support** for email notifications
4. ✅ **<100ms** variable resolution time
5. ✅ **100% backward compatibility** with existing templates
6. ✅ **Complete test coverage** for core services

---

## Next Steps

1. **Review this architecture** with team
2. **Approve implementation phases**
3. **Begin Phase 1** - Create config and core services
4. **Iterate based on feedback**

---

**Document Version**: 1.0
**Created**: 2025-10-07
**Author**: Claude (AI Assistant)
**Status**: Ready for Review & Implementation
