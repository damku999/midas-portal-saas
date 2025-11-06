# Service Layer Architecture

**Complete documentation for all 51 service classes in the Midas Portal application**

---

## Table of Contents

- [Overview](#overview)
- [Service Architecture Patterns](#service-architecture-patterns)
- [Base Service Class](#base-service-class)
- [Central Admin Services](#central-admin-services)
- [Customer Management Services](#customer-management-services)
- [Lead Management Services](#lead-management-services)
- [Policy & Quotation Services](#policy--quotation-services)
- [Claims Services](#claims-services)
- [Notification Services](#notification-services)
- [Security & Audit Services](#security--audit-services)
- [Master Data Services](#master-data-services)
- [Utility Services](#utility-services)
- [Service Dependencies](#service-dependencies)
- [Best Practices](#best-practices)

---

## Overview

The service layer implements business logic and orchestrates operations between controllers and repositories. All services follow Laravel's service pattern with dependency injection via constructors.

### Service Statistics

- **Total Services**: 51 services
- **Base Service**: 1 abstract class
- **Notification System**: 4 services (ChannelManager, VariableRegistry, VariableResolver, NotificationContext)
- **Service Location**: `app/Services/`
- **Average Complexity**: High (100-600 lines per service)
- **Transaction Safety**: 38 services use BaseService transactions

### Architecture Principles

1. **Single Responsibility**: Each service handles one business domain
2. **Transaction Safety**: Critical operations wrapped in database transactions
3. **Dependency Injection**: Services injected via constructor
4. **Repository Pattern**: Services use repositories for data access
5. **Event Driven**: Services dispatch events for async processing
6. **Error Handling**: Comprehensive try-catch with logging

---

## Service Architecture Patterns

### Pattern 1: Repository + BaseService Pattern

```php
class CustomerService extends BaseService implements CustomerServiceInterface
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private FileUploadService $fileUploadService
    ) {}

    public function createCustomer(StoreCustomerRequest $request): Customer
    {
        return $this->createInTransaction(function () use ($request) {
            $model = $this->customerRepository->create([...]);
            $this->handleCustomerDocuments($request, $model);
            CustomerRegistered::dispatch($model);
            return $model;
        });
    }
}
```

**Services Using This Pattern**: CustomerService, PolicyService, LeadService, ClaimService, QuotationService, UserService

### Pattern 2: Direct Model Manipulation Pattern

```php
class LeadService
{
    protected LeadRepositoryInterface $leadRepository;

    public function createLead(array $data): Lead
    {
        DB::beginTransaction();
        try {
            $lead = $this->leadRepository->create($data);
            $this->logActivity($lead->id, 'Lead Created', 'New lead created');
            DB::commit();
            return $lead->fresh();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
```

**Services Using This Pattern**: LeadService, LeadConversionService, MarketingWhatsAppService, LeadWhatsAppService

### Pattern 3: Stateless Service Pattern

```php
class TemplateService
{
    public function render(string $code, string $channel, NotificationContext $context): ?string
    {
        $template = NotificationTemplate::where('code', $code)
            ->where('channel', $channel)
            ->where('is_active', true)
            ->first();

        return $template ? $this->resolver->resolve($template->content, $context) : null;
    }
}
```

**Services Using This Pattern**: TemplateService, EmailService, SmsService, PushNotificationService, PdfGenerationService

### Pattern 4: Progressive Processing Pattern

```php
class TenantCreationService
{
    public function create(array $validated): array
    {
        $this->initializeProgress();
        DB::connection('central')->beginTransaction();

        try {
            // Step 1: Validate subdomain
            $this->updateProgress(1, 'Validating...', 'running');
            // ... validation logic
            $this->updateProgress(1, '✓ Complete', 'completed');

            // Steps 2-10 follow same pattern

            DB::commit();
            $this->markComplete();
            return ['success' => true];
        } catch (\Exception $e) {
            DB::rollBack();
            $this->markFailed($e->getMessage());
            throw $e;
        }
    }
}
```

**Services Using This Pattern**: TenantCreationService, PaymentService

---

## Base Service Class

### BaseService

**Location**: `app/Services/BaseService.php`
**Type**: Abstract class
**Purpose**: Provides transaction management for service operations

#### Public Methods

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `executeInTransaction()` | `callable $callback` | `mixed` | Execute callback within database transaction |
| `createInTransaction()` | `callable $createCallback` | `mixed` | Convenience method for create operations |
| `updateInTransaction()` | `callable $updateCallback` | `mixed` | Convenience method for update operations |
| `deleteInTransaction()` | `callable $deleteCallback` | `mixed` | Convenience method for delete operations |
| `executeMultipleInTransaction()` | `array $callbacks` | `array` | Execute multiple operations atomically |

#### Usage Example

```php
class PolicyService extends BaseService
{
    public function createPolicy(array $data): CustomerInsurance
    {
        return $this->createInTransaction(
            fn(): Model => $this->policyRepository->create($data)
        );
    }

    public function complexOperation(array $data): array
    {
        return $this->executeMultipleInTransaction([
            fn() => $this->policyRepository->create($data['policy']),
            fn() => $this->claimRepository->create($data['claim']),
            fn() => $this->logActivity('Policy and claim created'),
        ]);
    }
}
```

#### Transaction Safety Features

- **Automatic Rollback**: On any exception, transaction automatically rolls back
- **Nested Transactions**: Uses Laravel's transaction nesting support
- **Exception Propagation**: Re-throws exceptions after rollback for proper error handling
- **Atomic Operations**: Multiple callbacks execute as single atomic unit

---

## Central Admin Services

### TenantCreationService

**Location**: `app/Services/TenantCreationService.php`
**Purpose**: Orchestrates complete tenant provisioning with real-time progress tracking
**Dependencies**: None (uses facades)

#### Key Features

- **10-Step Progressive Creation**: Validates subdomain → Creates tenant → Registers domain → Stores company data → Creates subscription → Creates database → Runs migrations → Seeds data → Creates admin → Finalizes
- **Real-Time Progress**: File-based cache progress tracking for AJAX polling
- **Transaction Safety**: Central database transaction with rollback on failure
- **Logo Handling**: Copies logo from central storage to tenant storage
- **Custom Settings**: Supports branding, communication, localization settings
- **Trial/Paid Support**: Handles both trial and paid subscription types

#### Core Methods

```php
public function __construct(string $sessionId = null)
public function create(array $validated): array
public function getProgress(): array
public function getProgressKey(): string
private function initializeProgress(): void
private function updateProgress(int $step, string $message, string $status): void
private function markComplete(Tenant $tenant, string $domain): void
private function markFailed(string $error): void
```

#### Usage Example

```php
$service = new TenantCreationService($request->session()->getId());
$result = $service->create($validated);
// Returns: ['success' => true, 'tenant' => $tenant, 'domain' => $domain]

// In AJAX polling route:
$progress = $service->getProgress();
// Returns: ['status' => 'running', 'current_step' => 5, 'percentage' => 50, 'steps' => [...]]
```

#### Configuration Options

```php
$validated = [
    // Tenant Identity
    'subdomain' => 'acme-insurance',
    'domain' => 'midastech.in',
    'company_name' => 'Acme Insurance Brokers',
    'email' => 'admin@acme.com',
    'phone' => '9876543210',

    // Subscription
    'plan_id' => 2,
    'subscription_type' => 'trial', // or 'paid'
    'trial_days' => 14,

    // Admin User
    'admin_first_name' => 'John',
    'admin_last_name' => 'Doe',
    'admin_email' => 'john@acme.com',
    'admin_password' => 'SecurePass123',

    // Branding (Optional)
    'company_logo' => 'tenant-logos/temp-logo.png',
    'company_tagline' => 'Your Insurance Partner',
    'theme_primary_color' => '#3490dc',

    // Communication (Optional)
    'whatsapp_sender_id' => '9876543210',
    'whatsapp_auth_token' => 'token_here',
    'email_from_address' => 'noreply@acme.com',
    'email_from_name' => 'Acme Insurance',

    // Localization (Optional)
    'timezone' => 'Asia/Kolkata',
    'currency' => 'INR',
    'currency_symbol' => '₹',
];
```

#### Progress Tracking Structure

```json
{
  "status": "running",
  "current_step": 5,
  "total_steps": 10,
  "percentage": 50,
  "started_at": "2025-11-06 14:30:00",
  "completed_at": null,
  "error": null,
  "steps": {
    "1": {
      "number": 1,
      "message": "✓ Subdomain is available: acme-insurance.midastech.in",
      "status": "completed",
      "timestamp": "2025-11-06 14:30:01"
    },
    "5": {
      "number": 5,
      "message": "Setting up subscription...",
      "status": "running",
      "timestamp": "2025-11-06 14:30:05"
    }
  }
}
```

### PaymentService

**Location**: `app/Services/PaymentService.php`
**Purpose**: Multi-gateway payment processing and webhook handling
**Dependencies**: Razorpay SDK, Stripe SDK (future)

#### Supported Gateways

| Gateway | Status | Features |
|---------|--------|----------|
| **Razorpay** | ✅ Active | Order creation, signature verification, webhook handling |
| **Stripe** | 🚧 Stub | Placeholder methods ready |
| **Bank Transfer** | ✅ Manual | Provides bank details, requires manual verification |

#### Core Methods

```php
public function createOrder(Subscription $subscription, float $amount, string $gateway, string $type): array
public function verifyPayment(int $paymentId, array $paymentData): array
public function handleWebhook(string $gateway, array $payload): array
private function createRazorpayOrder(Payment $payment): array
private function verifyRazorpayPayment(Payment $payment, array $data): array
```

#### Razorpay Integration

```php
// Create payment order
$result = $paymentService->createOrder(
    subscription: $subscription,
    amount: 1499.00,
    gateway: 'razorpay',
    type: 'subscription' // or 'renewal', 'upgrade', 'addon'
);

// Returns:
[
    'success' => true,
    'payment_id' => 123,
    'order_data' => [
        'order_id' => 'order_xyz',
        'amount' => 149900, // in paise
        'currency' => 'INR',
        'key' => 'rzp_test_key',
        'gateway' => 'razorpay'
    ],
    'payment' => Payment // model instance
]

// Verify payment after customer completes
$result = $paymentService->verifyPayment($paymentId, [
    'razorpay_order_id' => 'order_xyz',
    'razorpay_payment_id' => 'pay_abc',
    'razorpay_signature' => 'signature_hash'
]);

// Returns:
[
    'success' => true,
    'payment_id' => 'pay_abc',
    'response' => [...], // full Razorpay payment object
    'payment_method' => [
        'type' => 'card',
        'card_last4' => '4242',
        'card_network' => 'Visa'
    ]
]
```

#### Payment Types

- `subscription`: New subscription purchase
- `renewal`: Subscription renewal charge
- `upgrade`: Plan upgrade payment
- `addon`: Additional feature purchase

### UsageTrackingService

**Location**: `app/Services/UsageTrackingService.php`
**Purpose**: Track and enforce subscription plan limits
**Dependencies**: Tenant, Subscription, Plan models

#### Tracked Metrics

| Metric | Database Source | Storage Calculation |
|--------|----------------|---------------------|
| **Users** | `users` table count | Direct count |
| **Customers** | `customers` table count | Direct count |
| **Policies** | `customer_insurances` table count | Direct count + active filter |
| **Leads** | `leads` table count | Direct count |
| **Storage (MB)** | Database size + file storage | MySQL information_schema + file size recursion |

#### Core Methods

```php
public function getTenantUsage(Tenant $tenant): array
public function isWithinLimits(Tenant $tenant, ?string $limitType = null): bool
public function canCreate(string $resourceType): bool
public function getUsagePercentage(Tenant $tenant, string $limitType): float
public function getRemainingCapacity(Tenant $tenant, string $limitType): int
public function getUsageSummary(Tenant $tenant): array
public function clearUsageCache(Tenant $tenant): void
public function trackResourceCreated(string $resourceType): void
public function trackResourceDeleted(string $resourceType): void
```

#### Usage Example

```php
// Check if tenant can create new customer
if (!$usageTrackingService->canCreate('customer')) {
    return response()->json([
        'error' => 'Customer limit reached. Please upgrade your plan.'
    ], 403);
}

// Get complete usage summary for dashboard
$summary = $usageTrackingService->getUsageSummary($tenant);
/* Returns:
[
    'usage' => [
        'users' => 5,
        'customers' => 150,
        'policies' => 89,
        'active_policies' => 75,
        'leads' => 45,
        'storage_mb' => 256.45
    ],
    'limits' => [
        'users' => ['current' => 5, 'max' => 10, 'percentage' => 50.0, 'remaining' => 5],
        'customers' => ['current' => 150, 'max' => 200, 'percentage' => 75.0, 'remaining' => 50],
        'storage' => ['current' => 0.25, 'max' => 1, 'percentage' => 25.0, 'remaining' => 0.75, 'unit' => 'GB']
    ],
    'warnings' => [
        ['type' => 'customers', 'message' => 'Customers usage at 75%', 'severity' => 'warning']
    ],
    'plan' => 'Professional'
]
*/

// Check specific limit
$percentage = $usageTrackingService->getUsagePercentage($tenant, 'storage');
if ($percentage >= 90) {
    // Send warning notification
}
```

#### Storage Calculation

```php
// Database size calculation
SELECT
    ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) as size_mb
FROM information_schema.TABLES
WHERE TABLE_SCHEMA = DATABASE()
AND TABLE_NAME = ?

// File storage calculation (tenant-specific)
// Path: storage/tenant{id}/app/public/
// Recursively traverses directory tree
// Sums file sizes in bytes → converts to MB
```

#### Caching Strategy

- **Cache Key**: `tenant_usage_{tenant_id}`
- **TTL**: 5 minutes
- **Storage**: Default cache driver
- **Invalidation**: On resource creation/deletion via `trackResourceCreated/Deleted()`

---

## Customer Management Services

### CustomerService

**Location**: `app/Services/CustomerService.php`
**Type**: Repository + BaseService Pattern
**Dependencies**: CustomerRepositoryInterface, FileUploadService

#### Key Features

- **CRUD Operations**: Complete customer lifecycle management
- **Document Handling**: PAN, Aadhar, GST document uploads
- **Welcome Notifications**: Email + WhatsApp onboarding
- **Event Dispatching**: CustomerRegistered, CustomerProfileUpdated
- **Transaction Safety**: All critical operations wrapped
- **Family Group Support**: Family-based customer queries

#### Core Methods

| Method | Return Type | Transaction Safe | Description |
|--------|------------|------------------|-------------|
| `getCustomers()` | `LengthAwarePaginator` | No | Paginated customer list with filtering |
| `createCustomer()` | `Customer` | Yes | Create customer with documents and welcome email |
| `updateCustomer()` | `bool` | Yes | Update customer with change tracking |
| `updateCustomerStatus()` | `bool` | Yes | Toggle active/inactive status |
| `deleteCustomer()` | `bool` | Yes | Soft delete customer |
| `handleCustomerDocuments()` | `void` | No | Process PAN/Aadhar/GST uploads |
| `sendOnboardingMessage()` | `bool` | No | Send WhatsApp welcome message |
| `sendOnboardingEmail()` | `bool` | No | Send email welcome message |
| `getActiveCustomersForSelection()` | `Collection` | No | Active customers for dropdowns |
| `getCustomersByFamily()` | `Collection` | No | Customers in family group |
| `getCustomersByType()` | `Collection` | No | Filter by Retail/Corporate |
| `searchCustomers()` | `Collection` | No | Full-text search |
| `getCustomerStatistics()` | `array` | No | Dashboard statistics |
| `findByEmail()` | `?Customer` | No | Lookup by email |
| `findByMobileNumber()` | `?Customer` | No | Lookup by mobile |

#### Creation Flow

```php
public function createCustomer(StoreCustomerRequest $request): Customer
{
    // 1. Check for duplicate email
    if ($this->findByEmail($request->email) instanceof Customer) {
        throw new \Exception('Email already exists');
    }

    return $this->createInTransaction(function () use ($request) {
        // 2. Create customer record
        $customer = $this->customerRepository->create([...]);

        // 3. Handle document uploads (PAN, Aadhar, GST)
        $this->handleCustomerDocuments($request, $customer);

        // 4. Dispatch async events (WhatsApp, audit logs, admin notifications)
        CustomerRegistered::dispatch($customer, [...], 'admin');

        // 5. Send welcome email synchronously (rollback if fails)
        try {
            $this->sendWelcomeEmailSync($customer);
        } catch (\Throwable $e) {
            $customer->delete(); // Clean up
            throw new \Exception('Unable to send welcome email');
        }

        return $customer;
    });
}
```

#### Welcome Email Transaction Safety

The service uses a **synchronous welcome email** approach within the transaction:

```php
private function sendWelcomeEmailSync(Customer $customer): void
{
    // Check if email notifications enabled
    if (!is_email_notification_enabled()) {
        return;
    }

    try {
        Mail::send('emails.customer.welcome', [...], function ($message) use ($customer) {
            $message->to($customer->email, $customer->name)
                    ->subject('Welcome to ' . company_name())
                    ->from(email_from_address(), company_name());
        });

        Log::info('Welcome email sent successfully', [...]);

    } catch (\Throwable $e) {
        Log::error('Failed to send welcome email', [...]);

        // Re-throw with user-friendly message
        throw new \Exception('Unable to send welcome email. Please verify email address.');
    }
}
```

**Design Decision**: Email is sent synchronously to ensure customer creation fails if email cannot be delivered. This prevents "zombie accounts" where customer exists but never received credentials/welcome.

#### Document Upload Handling

```php
public function handleCustomerDocuments($request, Customer $customer): void
{
    $documentsUpdated = false;

    // PAN Card
    if ($request->hasFile('pan_card_path')) {
        $customer->pan_card_path = $this->fileUploadService->uploadCustomerDocument(
            $request->file('pan_card_path'),
            $customer->id,
            'pan_card',
            $customer->name
        );
        $documentsUpdated = true;
    }

    // Aadhar Card
    if ($request->hasFile('aadhar_card_path')) {
        $customer->aadhar_card_path = $this->fileUploadService->uploadCustomerDocument(
            $request->file('aadhar_card_path'),
            $customer->id,
            'aadhar_card',
            $customer->name
        );
        $documentsUpdated = true;
    }

    // GST Document
    if ($request->hasFile('gst_path')) {
        $customer->gst_path = $this->fileUploadService->uploadCustomerDocument(
            $request->file('gst_path'),
            $customer->id,
            'gst',
            $customer->name
        );
        $documentsUpdated = true;
    }

    // Save only if documents were uploaded
    if ($documentsUpdated) {
        $customer->save();
    }
}
```

#### Update with Change Tracking

```php
public function updateCustomer(UpdateCustomerRequest $request, Customer $customer): bool
{
    return $this->updateInTransaction(function () use ($request, $customer): bool {
        // Capture original values
        $originalValues = $customer->only([
            'name', 'email', 'mobile_number', 'status', 'type',
            'pan_card_number', 'aadhar_card_number', 'gst_number',
        ]);

        // Update customer
        $model = $this->customerRepository->update($customer, $newValues);

        if ($model) {
            // Handle document uploads
            $this->handleCustomerDocuments($request, $customer);

            // Identify changed fields
            $changedFields = [];
            foreach ($newValues as $field => $newValue) {
                if ($originalValues[$field] !== $newValue) {
                    $changedFields[] = $field;
                }
            }

            // Dispatch event if changes detected
            if ($changedFields !== []) {
                CustomerProfileUpdated::dispatch(
                    $customer->fresh(),
                    $changedFields,
                    $originalValues
                );
            }

            return true;
        }

        return false;
    });
}
```

#### Statistics for Dashboard

```php
public function getCustomerStatistics(): array
{
    $total = $this->customerRepository->count();
    $active = $this->customerRepository->getByType('Retail')
                                       ->where('status', 1)
                                       ->count();
    $corporate = $this->customerRepository->getByType('Corporate')->count();

    return [
        'total' => $total,
        'active' => $active,
        'corporate' => $corporate,
    ];
}
```

### FamilyGroupService

**Location**: `app/Services/FamilyGroupService.php`
**Purpose**: Manage family group relationships for shared policies
**Pattern**: Repository + BaseService

#### Features

- Family group creation and management
- Member addition/removal
- Family head designation
- Shared policy access control

---

## Lead Management Services

### LeadService

**Location**: `app/Services/LeadService.php`
**Type**: Repository + Transaction Pattern
**Dependencies**: LeadRepositoryInterface

#### Key Features

- **Complete Lead Lifecycle**: New → Contacted → Qualified → Converted/Lost
- **Activity Logging**: Every state change logged automatically
- **Assignment Management**: Lead assignment to sales team
- **Status Tracking**: Status changes trigger activity logs
- **Follow-up Management**: Due and overdue follow-up queries
- **Conversion Tracking**: Lead-to-customer conversion

#### Core Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `getAllLeads()` | `array $filters, int $perPage` | `LengthAwarePaginator` | Filtered paginated lead list |
| `getLeadById()` | `int $id` | `?Lead` | Lead with relations loaded |
| `createLead()` | `array $data` | `Lead` | Create lead with activity log |
| `updateLead()` | `int $id, array $data` | `Lead` | Update lead, log status changes |
| `deleteLead()` | `int $id` | `bool` | Soft delete lead |
| `updateLeadStatus()` | `int $id, int $statusId, ?string $notes` | `Lead` | Change status with notes |
| `assignLeadTo()` | `int $id, int $userId` | `Lead` | Assign lead to user |
| `convertLeadToCustomer()` | `int $id, int $customerId, ?string $notes` | `Lead` | Convert to customer |
| `markLeadAsLost()` | `int $id, string $reason` | `Lead` | Mark as lost with reason |
| `addActivity()` | `int $leadId, string $type, string $subject, ...` | `LeadActivity` | Log lead activity |
| `getFollowUpDueLeads()` | `int $perPage` | `LengthAwarePaginator` | Leads needing follow-up |
| `getStatistics()` | - | `array` | Lead metrics for dashboard |

#### Lead Creation with Activity Log

```php
public function createLead(array $data): Lead
{
    DB::beginTransaction();

    try {
        // Set created_by to current user
        if (!isset($data['created_by'])) {
            $data['created_by'] = Auth::id();
        }

        // Create the lead
        $lead = $this->leadRepository->create($data);

        // Log creation activity
        $this->logActivity(
            $lead->id,
            LeadActivity::TYPE_NOTE,
            'Lead Created',
            'New lead created in the system'
        );

        DB::commit();
        return $lead->fresh();

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

#### Status Change Tracking

```php
public function updateLeadStatus(int $id, int $statusId, ?string $notes = null): Lead
{
    DB::beginTransaction();

    try {
        $lead = $this->leadRepository->updateStatus($id, $statusId, $notes);

        // Log status change activity
        $this->logActivity(
            $lead->id,
            LeadActivity::TYPE_STATUS_CHANGE,
            'Status Changed',
            "Status changed to {$lead->status->name}",
            $notes
        );

        DB::commit();
        return $lead->fresh();

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

#### Lead Conversion Flow

```php
public function convertLeadToCustomer(int $id, int $customerId, ?string $notes = null): Lead
{
    DB::beginTransaction();

    try {
        // Update lead with customer_id and conversion status
        $lead = $this->leadRepository->convertToCustomer($id, $customerId, $notes);

        // Log conversion activity
        $this->logActivity(
            $lead->id,
            LeadActivity::TYPE_STATUS_CHANGE,
            'Lead Converted',
            'Lead converted to customer',
            $notes
        );

        DB::commit();
        return $lead->fresh();

    } catch (\Exception $e) {
        DB::rollBack();
        throw $e;
    }
}
```

#### Activity Types

```php
// From LeadActivity model
const TYPE_NOTE = 'note';
const TYPE_CALL = 'call';
const TYPE_EMAIL = 'email';
const TYPE_MEETING = 'meeting';
const TYPE_WHATSAPP = 'whatsapp';
const TYPE_STATUS_CHANGE = 'status_change';
const TYPE_ASSIGNMENT = 'assignment';
```

### LeadConversionService

**Location**: `app/Services/LeadConversionService.php`
**Purpose**: Automated lead conversion to customer with data mapping

#### Features

- Automatic data mapping from lead to customer
- Policy creation from lead information
- Transaction safety for atomic conversion
- Fallback handling for missing data

### LeadWhatsAppService

**Location**: `app/Services/LeadWhatsAppService.php`
**Purpose**: WhatsApp messaging for lead engagement
**Pattern**: Notification service

#### Features

- Bulk WhatsApp campaign sending
- Template-based messaging
- Activity logging for each message
- Delivery tracking

### MarketingWhatsAppService

**Location**: `app/Services/MarketingWhatsAppService.php`
**Purpose**: Marketing WhatsApp campaigns

#### Features

- Campaign management
- Audience segmentation
- Scheduled sending
- Campaign analytics

---

## Policy & Quotation Services

### PolicyService

**Location**: `app/Services/PolicyService.php`
**Type**: Repository + BaseService Pattern
**Dependencies**: PolicyRepositoryInterface

#### Key Features

- **Policy Lifecycle Management**: Create → Active → Renewal → Expired
- **Renewal System**: Automatic renewal reminders at 30/15/7 days + expired
- **Family Policy Access**: Family head can view family member policies
- **Bulk Operations**: Bulk renewal reminder sending
- **WhatsApp Integration**: Template-based renewal reminders
- **Access Control**: Customer permission validation

#### Core Methods

| Method | Return Type | Description |
|--------|-------------|-------------|
| `getPolicies()` | `LengthAwarePaginator` | Filtered paginated policies |
| `createPolicy()` | `CustomerInsurance` | Create policy in transaction |
| `updatePolicy()` | `bool` | Update policy in transaction |
| `getCustomerPolicies()` | `Collection` | All policies for customer |
| `getPoliciesDueForRenewal()` | `Collection` | Policies expiring in N days |
| `sendRenewalReminder()` | `bool` | Send WhatsApp renewal reminder |
| `sendBulkRenewalReminders()` | `array` | Batch renewal reminders |
| `getFamilyPolicies()` | `Collection` | Family group policies |
| `canCustomerViewPolicy()` | `bool` | Check customer access |
| `getPolicyStatistics()` | `array` | Dashboard metrics |
| `searchPolicies()` | `Collection` | Full-text search |

#### Renewal Reminder System

```php
public function sendRenewalReminder(CustomerInsurance $customerInsurance): bool
{
    try {
        // Calculate days remaining
        $daysRemaining = now()->diffInDays($customerInsurance->policy_end_date);

        // Select appropriate notification type
        if ($daysRemaining <= 0) {
            $notificationTypeCode = 'renewal_expired';
        } elseif ($daysRemaining <= 7) {
            $notificationTypeCode = 'renewal_7_days';
        } elseif ($daysRemaining <= 15) {
            $notificationTypeCode = 'renewal_15_days';
        } else {
            $notificationTypeCode = 'renewal_30_days';
        }

        // Render message from template
        $templateService = app(TemplateService::class);
        $message = $templateService->renderFromInsurance(
            $notificationTypeCode,
            'whatsapp',
            $customerInsurance
        );

        // Fallback if template not found
        if (!$message) {
            $message = $this->generateRenewalReminderMessage($customerInsurance);
        }

        // Log and send WhatsApp
        $result = $this->logAndSendWhatsApp(
            $customerInsurance,
            $message,
            $customerInsurance->customer->mobile_number,
            ['notification_type_code' => $notificationTypeCode]
        );

        return $result['success'];

    } catch (\Throwable $e) {
        Log::error('Failed to send renewal reminder', [...]);
        return false;
    }
}
```

#### Bulk Renewal Reminders

```php
public function sendBulkRenewalReminders(?int $daysAhead = null): array
{
    $daysAhead ??= 30;
    $policies = $this->getPoliciesDueForRenewal($daysAhead);

    $results = [
        'total' => $policies->count(),
        'sent' => 0,
        'failed' => 0,
        'errors' => [],
    ];

    foreach ($policies as $policy) {
        $sent = $this->sendRenewalReminder($policy);

        if ($sent) {
            $results['sent']++;
        } else {
            $results['failed']++;
            $results['errors'][] = [
                'policy_id' => $policy->id,
                'policy_number' => $policy->policy_number,
                'customer_name' => $policy->customer->name,
            ];
        }
    }

    return $results;
}
```

#### Family Policy Access Control

```php
public function getFamilyPolicies(Customer $customer): Collection
{
    // No family group
    if (!$customer->hasFamily()) {
        return collect([]);
    }

    // Family head sees all family policies
    if ($customer->isFamilyHead()) {
        return $this->policyRepository->getByFamilyGroup($customer->family_group_id);
    }

    // Non-family head sees only own policies
    return $this->getCustomerPolicies($customer);
}

public function canCustomerViewPolicy(Customer $customer, CustomerInsurance $policy): bool
{
    // Own policy
    if ($policy->customer_id === $customer->id) {
        return true;
    }

    // Family head viewing family member policy
    if ($customer->isFamilyHead() && $customer->hasFamily()) {
        $policyCustomer = $policy->customer;
        return $policyCustomer->family_group_id === $customer->family_group_id;
    }

    return false;
}
```

#### Fallback Renewal Message

```php
private function generateRenewalReminderMessage(CustomerInsurance $insurance): string
{
    $customer = $insurance->customer;
    $daysRemaining = now()->diffInDays($insurance->policy_end_date);

    $message = "🔔 *Policy Renewal Reminder*\n\n";
    $message .= "Dear *{$customer->name}*,\n\n";
    $message .= "Your insurance policy is due for renewal:\n\n";
    $message .= "📋 *Policy Details:*\n";
    $message .= "• Policy No: *{$insurance->policy_number}*\n";
    $message .= "• Company: *{$insurance->insuranceCompany->name}*\n";
    $message .= "• Type: *{$insurance->policyType->name}*\n";
    $message .= "• End Date: *{$insurance->policy_end_date->format('d M Y')}*\n";
    $message .= "• Days Remaining: *{$daysRemaining} days*\n\n";

    if ($daysRemaining <= 7) {
        $message .= "⚠️ *URGENT: Your policy expires in {$daysRemaining} days!*\n\n";
    }

    $message .= "📞 Please contact us to renew your policy.\n\n";
    $message .= "Best regards,\n";
    $message .= company_advisor_name() . "\n";
    $message .= company_website();

    return $message;
}
```

### QuotationService

**Location**: `app/Services/QuotationService.php`
**Purpose**: Insurance quotation generation and comparison

#### Features

- Multi-company quotation comparison
- IDV (Insured Declared Value) calculation
- Addon management
- PDF quotation generation
- WhatsApp quotation sharing

### CustomerInsuranceService

**Location**: `app/Services/CustomerInsuranceService.php`
**Purpose**: Customer portal insurance policy access

#### Features

- Customer-specific policy views
- Policy document downloads
- Renewal request submission
- Claim initiation from policy

---

## Claims Services

### ClaimService

**Location**: `app/Services/ClaimService.php`
**Purpose**: Insurance claim workflow management

#### Features

- Claim registration
- Multi-stage workflow (Registered → Survey → Approval → Settlement)
- Document attachment
- Status update notifications
- Settlement tracking

---

## Notification Services

### EmailService

**Location**: `app/Services/EmailService.php`
**Purpose**: Template-based email sending
**Dependencies**: TemplateService, NotificationContext

#### Key Features

- **Template System**: Uses notification_templates table
- **Fallback Messages**: Hardcoded fallbacks for critical notifications
- **Markdown Conversion**: Converts *bold* to HTML
- **Context-Based Sending**: Send from Customer/Insurance/Quotation/Claim
- **Attachment Support**: PDF attachments for policies/quotations
- **Settings Integration**: Loads app settings for variable resolution

#### Core Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `sendTemplatedEmail()` | `string $to, string $code, NotificationContext $context, array $attachments` | `bool` | Core email sending with template |
| `sendFromCustomer()` | `string $code, Customer $customer, array $attachments` | `bool` | Convenience for customer emails |
| `sendFromInsurance()` | `string $code, CustomerInsurance $insurance, array $attachments` | `bool` | Convenience for policy emails |
| `sendFromQuotation()` | `string $code, Quotation $quotation, array $attachments` | `bool` | Convenience for quotation emails |
| `sendFromClaim()` | `string $code, Claim $claim, array $attachments` | `bool` | Convenience for claim emails |

#### Template Resolution Process

```php
public function sendTemplatedEmail(
    string $to,
    string $notificationTypeCode,
    NotificationContext $notificationContext,
    array $attachments = []
): bool {
    try {
        // 1. Check if email enabled globally
        if (!is_email_notification_enabled()) {
            return false;
        }

        // 2. Validate email address
        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        // 3. Render from template
        $htmlContent = $this->templateService->render(
            $notificationTypeCode,
            'email',
            $notificationContext
        );

        // 4. Fallback if template not found
        if (empty($htmlContent)) {
            $htmlContent = $this->getFallbackMessage(
                $notificationTypeCode,
                $notificationContext
            );

            if (empty($htmlContent)) {
                return false; // No template or fallback
            }
        }

        // 5. Generate subject
        $subject = $this->getEmailSubject($notificationTypeCode, $notificationContext);

        // 6. Format content (markdown to HTML)
        $htmlContent = $this->formatEmailContent($htmlContent);

        // 7. Send email
        Mail::to($to)->send(new TemplatedNotification(
            subject: $subject,
            htmlContent: $htmlContent,
            attachments: $attachments
        ));

        return true;

    } catch (\Exception $e) {
        Log::error('Email sending failed', [...]);
        return false;
    }
}
```

#### Subject Line Generation

```php
protected function getEmailSubject(string $code, NotificationContext $context): string
{
    return match ($code) {
        'customer_welcome' => 'Welcome to ' . company_name(),
        'policy_created' => 'Your Insurance Policy Document - ' . ($context->insurance?->policy_no ?? 'Policy'),
        'quotation_ready' => 'Your Insurance Quotation - ' . ($context->quotation?->quotation_number ?? 'Quote'),
        'renewal_30_days', 'renewal_15_days', 'renewal_7_days' =>
            'Insurance Renewal Reminder - ' . ($context->insurance?->policy_no ?? 'Policy'),
        'renewal_expired' => 'Insurance Policy Expired - Immediate Renewal Required',
        'claim_submitted' => 'Claim Submitted Successfully - ' . ($context->claim?->claim_number ?? 'Claim'),
        'claim_approved' => 'Claim Approved - ' . ($context->claim?->claim_number ?? 'Claim'),
        'claim_rejected' => 'Claim Status Update - ' . ($context->claim?->claim_number ?? 'Claim'),
        default => company_name() . ' - Notification',
    };
}
```

#### Content Formatting

```php
protected function formatEmailContent(string $content): string
{
    // Convert bold: *text* → <strong>text</strong>
    $content = preg_replace('/\*([^\*]+)\*/', '<strong>$1</strong>', $content);

    // Convert line breaks to <br>
    $content = nl2br($content);

    // Convert URLs to links
    $content = preg_replace(
        '#(https?://[^\s<]+)#i',
        '<a href="$1" style="color: #3490dc;">$1</a>',
        $content
    );

    return $content;
}
```

#### Fallback Messages

```php
protected function getFallbackWelcomeMessage($customer): string
{
    return "Dear {$customer->name},

Welcome to the world of insurance solutions! I'm " . company_advisor_name() . ", your dedicated insurance advisor.

Whether you're seeking protection for your loved ones, securing your assets, or planning for the future, I'm committed to providing personalized advice.

Best regards,
" . company_advisor_name() . "
" . company_website() . "
" . company_title() . "
\"" . company_tagline() . "\"";
}

protected function getFallbackPolicyCreatedMessage($insurance): string
{
    $expiredDate = date('d-m-Y', strtotime($insurance->expired_date));

    return "Dear {$insurance->customer->name},

Thank you for entrusting me with your insurance needs. Please find attached the policy document with *Policy No. {$insurance->policy_no}* which expires on *{$expiredDate}*.

Best regards,
" . company_advisor_name();
}
```

### SmsService

**Location**: `app/Services/SmsService.php`
**Purpose**: SMS notification sending

### PushNotificationService

**Location**: `app/Services/PushNotificationService.php`
**Purpose**: Push notification delivery

### TemplateService

**Location**: `app/Services/TemplateService.php`
**Purpose**: Notification template rendering with variable resolution

#### Features

- Template loading from database
- Variable resolution via VariableResolverService
- Context-based rendering (Customer/Insurance/Quotation/Claim)
- Multi-channel support (Email/WhatsApp/SMS/Push)
- Caching for performance

### Notification\ChannelManager

**Location**: `app/Services/Notification/ChannelManager.php`
**Purpose**: Multi-channel notification routing

### Notification\VariableRegistryService

**Location**: `app/Services/Notification/VariableRegistryService.php`
**Purpose**: Register available template variables

### Notification\VariableResolverService

**Location**: `app/Services/Notification/VariableResolverService.php`
**Purpose**: Resolve template variables to actual values

### Notification\NotificationContext

**Location**: `app/Services/Notification/NotificationContext.php`
**Purpose**: Context object for template resolution

### NotificationLoggerService

**Location**: `app/Services/NotificationLoggerService.php`
**Purpose**: Log all notification attempts and delivery status

---

## Security & Audit Services

### AuditService

**Location**: `app/Services/AuditService.php`
**Purpose**: Security event querying and reporting

#### Key Features

- **Activity Queries**: Recent, suspicious, high-risk activity retrieval
- **Security Metrics**: Event counts, risk distribution, hourly patterns
- **Search & Filter**: Multi-criteria audit log search
- **Reporting**: Security report generation with recommendations
- **Export**: CSV/JSON log export

#### Core Methods

| Method | Parameters | Returns | Description |
|--------|------------|---------|-------------|
| `getRecentActivity()` | `int $hours, int $limit` | `Collection` | Recent activity in last N hours |
| `getSuspiciousActivity()` | `int $days` | `Collection` | Flagged suspicious events |
| `getHighRiskActivity()` | `int $days` | `Collection` | High risk score events |
| `getActivityByUser()` | `$userId, string $userType, int $days` | `Collection` | User-specific activity |
| `getActivityByEntity()` | `$entityId, string $entityType, int $days` | `Collection` | Entity-specific activity |
| `getSecurityMetrics()` | `int $days` | `array` | Comprehensive security metrics |
| `searchLogs()` | `array $filters, int $perPage` | `LengthAwarePaginator` | Filtered audit log search |
| `generateSecurityReport()` | `int $days` | `array` | Security report with recommendations |
| `exportLogs()` | `array $filters, string $format` | `string` | Export logs as CSV/JSON |

#### Security Metrics

```php
public function getSecurityMetrics(int $days = 30): array
{
    $startDate = now()->subDays($days);

    return [
        'total_events' => AuditLog::where('occurred_at', '>=', $startDate)->count(),
        'suspicious_events' => AuditLog::suspicious()->where('occurred_at', '>=', $startDate)->count(),
        'high_risk_events' => AuditLog::highRisk()->where('occurred_at', '>=', $startDate)->count(),
        'failed_logins' => AuditLog::where('event', 'login_failed')->where('occurred_at', '>=', $startDate)->count(),
        'unique_ips' => AuditLog::where('occurred_at', '>=', $startDate)->distinct('ip_address')->count(),
        'events_by_category' => $this->getEventsByCategory($startDate),
        'risk_distribution' => $this->getRiskDistribution($startDate),
        'hourly_activity' => $this->getHourlyActivity($startDate),
        'top_risk_factors' => $this->getTopRiskFactors($startDate),
    ];
}
```

#### Automated Recommendations

```php
protected function generateRecommendations(array $metrics): array
{
    $recommendations = [];

    // High suspicious activity (>10%)
    if ($metrics['suspicious_events'] > $metrics['total_events'] * 0.1) {
        $recommendations[] = [
            'type' => 'warning',
            'title' => 'High Suspicious Activity',
            'description' => 'More than 10% of events flagged as suspicious',
            'priority' => 'high',
        ];
    }

    // Many failed logins
    if ($metrics['failed_logins'] > 100) {
        $recommendations[] = [
            'type' => 'warning',
            'title' => 'High Failed Login Attempts',
            'description' => 'Consider implementing account lockout or CAPTCHA',
            'priority' => 'medium',
        ];
    }

    // High IP diversity (possible bot activity)
    if ($metrics['unique_ips'] > $metrics['total_events'] * 0.5) {
        $recommendations[] = [
            'type' => 'info',
            'title' => 'High IP Diversity',
            'description' => 'Monitor for potential bot activity',
            'priority' => 'low',
        ];
    }

    return $recommendations;
}
```

### SecurityService

**Location**: `app/Services/SecurityService.php`
**Purpose**: Security headers and input sanitization

#### Features

- **Content Security Policy**: CSP header generation
- **Security Headers**: X-Frame-Options, HSTS, etc.
- **Input Sanitization**: XSS prevention
- **File Validation**: Upload security checks
- **Token Generation**: Secure random tokens

#### Security Headers

```php
public function getSecurityHeaders(): array
{
    return [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'DENY',
        'X-XSS-Protection' => '1; mode=block',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
        'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains',
        'Permissions-Policy' => 'geolocation=(), microphone=(), camera=()',
    ];
}

public function getContentSecurityPolicy(): array
{
    return [
        'default-src' => "'self'",
        'script-src' => "'self' 'unsafe-inline' 'unsafe-eval' https://code.jquery.com https://cdn.jsdelivr.net",
        'style-src' => "'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net",
        'font-src' => "'self' https://fonts.gstatic.com",
        'img-src' => "'self' data: https:",
        'frame-ancestors' => "'none'",
        'upgrade-insecure-requests' => '',
    ];
}
```

### SecurityAuditService

**Location**: `app/Services/SecurityAuditService.php`
**Purpose**: Advanced security auditing and threat detection

### TwoFactorAuthService

**Location**: `app/Services/TwoFactorAuthService.php`
**Purpose**: 2FA setup and verification for staff users

#### Features

- QR code generation for TOTP
- Backup code generation
- 2FA verification
- Trusted device management

### CustomerTwoFactorAuthService

**Location**: `app/Services/CustomerTwoFactorAuthService.php`
**Purpose**: 2FA for customer portal users

### ContentSecurityPolicyService

**Location**: `app/Services/ContentSecurityPolicyService.php`
**Purpose**: CSP header management

---

## Master Data Services

### BranchService

**Location**: `app/Services/BranchService.php`
**Purpose**: Branch/office location management

### BrokerService

**Location**: `app/Services/BrokerService.php`
**Purpose**: Insurance broker information management

### InsuranceCompanyService

**Location**: `app/Services/InsuranceCompanyService.php`
**Purpose**: Insurance provider master data

### PolicyTypeService

**Location**: `app/Services/PolicyTypeService.php`
**Purpose**: Policy type (Vehicle/Health/Life) management

### PremiumTypeService

**Location**: `app/Services/PremiumTypeService.php`
**Purpose**: Premium types (Comprehensive/Third Party) management

### FuelTypeService

**Location**: `app/Services/FuelTypeService.php`
**Purpose**: Vehicle fuel type master data

### AddonCoverService

**Location**: `app/Services/AddonCoverService.php`
**Purpose**: Insurance addon/rider management

### ReferenceUserService

**Location**: `app/Services/ReferenceUserService.php`
**Purpose**: Referral partner management

### RelationshipManagerService

**Location**: `app/Services/RelationshipManagerService.php`
**Purpose**: RM assignment and management

---

## Utility Services

### FileUploadService

**Location**: `app/Services/FileUploadService.php`
**Purpose**: Centralized file upload handling

#### Features

- **Tenant-Aware Storage**: Automatic tenant directory isolation
- **Document Types**: Customer docs, policy docs, claim docs, logos
- **Security**: File type validation, size limits
- **Naming Convention**: Structured naming with timestamps

#### Upload Methods

```php
public function uploadCustomerDocument(
    UploadedFile $file,
    int $customerId,
    string $documentType,
    string $customerName
): string

public function uploadPolicyDocument(
    UploadedFile $file,
    int $policyId,
    string $documentType
): string

public function uploadClaimDocument(
    UploadedFile $file,
    int $claimId,
    string $documentType
): string

public function uploadLogo(UploadedFile $file, string $tenantId): string
```

#### Storage Structure

```
storage/
└── tenant{uuid}/
    └── app/
        └── public/
            ├── customers/
            │   ├── customer_{id}/
            │   │   ├── pan_card_{timestamp}.pdf
            │   │   ├── aadhar_card_{timestamp}.pdf
            │   │   └── gst_{timestamp}.pdf
            ├── policies/
            │   └── policy_{id}/
            │       └── document_{timestamp}.pdf
            ├── claims/
            │   └── claim_{id}/
            │       └── evidence_{timestamp}.jpg
            └── logos/
                └── company_logo.png
```

### SecureFileUploadService

**Location**: `app/Services/SecureFileUploadService.php`
**Purpose**: Enhanced security file uploads

#### Features

- Malware scanning integration
- MIME type verification
- File content inspection
- Encrypted storage option

### PdfGenerationService

**Location**: `app/Services/PdfGenerationService.php`
**Purpose**: PDF document generation

#### Generated PDFs

| Document Type | Template | Variables | Attachments |
|--------------|----------|-----------|-------------|
| **Policy Document** | `policy.blade.php` | Customer, Insurance, Company | Logo, terms |
| **Quotation** | `quotation.blade.php` | Customer, Companies, Addons, IDV | Logo, comparison |
| **Commission Report** | `commission.blade.php` | Policies, Amounts, Period | - |
| **Claim Document** | `claim.blade.php` | Claim, Policy, Timeline | Evidence photos |

#### PDF Generation

```php
public function generatePolicyPdf(CustomerInsurance $insurance): string
{
    $pdf = PDF::loadView('pdfs.policy', [
        'insurance' => $insurance,
        'customer' => $insurance->customer,
        'company' => $insurance->insuranceCompany,
        'policyType' => $insurance->policyType,
    ]);

    $filename = "policy_{$insurance->policy_number}.pdf";
    $path = "policies/policy_{$insurance->id}/{$filename}";

    Storage::disk('public')->put($path, $pdf->output());

    return $path;
}
```

### ExcelExportService

**Location**: `app/Services/ExcelExportService.php`
**Purpose**: Excel report generation

#### Export Types

- Customer list
- Policy report
- Lead report
- Commission report
- Claims report

### ReportService

**Location**: `app/Services/ReportService.php`
**Purpose**: Business intelligence reporting

### CacheService

**Location**: `app/Services/CacheService.php`
**Purpose**: Application-wide caching strategy

### LoggingService

**Location**: `app/Services/LoggingService.php`
**Purpose**: Structured logging utilities

### ErrorTrackingService

**Location**: `app/Services/ErrorTrackingService.php`
**Purpose**: Error aggregation and tracking

### HealthCheckService

**Location**: `app/Services/HealthCheckService.php`
**Purpose**: Application health monitoring

#### Health Checks

```php
public function getHealthStatus(): array
{
    return [
        'database' => $this->checkDatabase(),
        'cache' => $this->checkCache(),
        'storage' => $this->checkStorage(),
        'mail' => $this->checkMail(),
        'queue' => $this->checkQueue(),
    ];
}
```

### AppSettingService

**Location**: `app/Services/AppSettingService.php`
**Purpose**: Application settings management

#### Setting Categories

- Company branding (name, logo, colors)
- Communication (email, WhatsApp, SMS)
- Notifications (channel enable/disable)
- Localization (timezone, currency)
- Security (2FA enforcement, session timeout)
- Features (module enable/disable)

#### Setting Operations

```php
public function get(string $key, $default = null)
public function set(string $key, $value): void
public function delete(string $key): void
public function getByCategory(string $category): Collection
public function updateMultiple(array $settings): void
```

### UserService

**Location**: `app/Services/UserService.php`
**Purpose**: Staff user management

### RoleService

**Location**: `app/Services/RoleService.php`
**Purpose**: Role management (Spatie Permission)

### PermissionService

**Location**: `app/Services/PermissionService.php`
**Purpose**: Permission assignment

---

## Service Dependencies

### Dependency Graph

```
BaseService (Abstract)
    ├── CustomerService → FileUploadService
    ├── PolicyService → None
    ├── QuotationService → None
    ├── ClaimService → None
    └── UserService → None

EmailService → TemplateService → VariableResolverService
                                → VariableRegistryService
                                → NotificationContext

TenantCreationService → None (uses facades)

PaymentService → None (uses Razorpay/Stripe SDK)

UsageTrackingService → None (direct model queries)

FileUploadService → None (uses Storage facade)

PdfGenerationService → None (uses PDF facade)
```

### Common Service Patterns

#### Pattern: Repository Injection

```php
class CustomerService extends BaseService
{
    public function __construct(
        private CustomerRepositoryInterface $customerRepository,
        private FileUploadService $fileUploadService
    ) {}
}
```

#### Pattern: Service Injection

```php
class PolicyService extends BaseService
{
    use LogsNotificationsTrait, WhatsAppApiTrait;

    public function __construct(
        private PolicyRepositoryInterface $policyRepository
    ) {}

    public function sendRenewalReminder(CustomerInsurance $insurance): bool
    {
        $templateService = app(TemplateService::class);
        $message = $templateService->renderFromInsurance(...);

        return $this->logAndSendWhatsApp($insurance, $message, ...);
    }
}
```

#### Pattern: Facade Usage

```php
class TenantCreationService
{
    public function create(array $validated): array
    {
        DB::connection('central')->beginTransaction();

        try {
            $tenant = Tenant::create([...]);
            $tenant->domains()->create([...]);

            Artisan::call('db:seed', [...]);

            AuditLog::log('tenant.created', ...);

            DB::connection('central')->commit();

            return ['success' => true, 'tenant' => $tenant];
        } catch (\Exception $e) {
            DB::connection('central')->rollBack();
            throw $e;
        }
    }
}
```

---

## Best Practices

### 1. Transaction Safety

**DO**: Wrap critical operations in transactions

```php
✅ CORRECT:
public function createPolicy(array $data): CustomerInsurance
{
    return $this->createInTransaction(
        fn(): Model => $this->policyRepository->create($data)
    );
}

❌ WRONG:
public function createPolicy(array $data): CustomerInsurance
{
    return $this->policyRepository->create($data); // No transaction
}
```

### 2. Error Handling

**DO**: Log errors and provide context

```php
✅ CORRECT:
try {
    $result = $this->sendEmail($customer);
} catch (\Exception $e) {
    Log::error('Email sending failed', [
        'customer_id' => $customer->id,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString(),
    ]);
    return false;
}

❌ WRONG:
try {
    $result = $this->sendEmail($customer);
} catch (\Exception $e) {
    return false; // Silent failure
}
```

### 3. Dependency Injection

**DO**: Inject dependencies via constructor

```php
✅ CORRECT:
public function __construct(
    private CustomerRepositoryInterface $customerRepository,
    private FileUploadService $fileUploadService
) {}

❌ WRONG:
public function createCustomer(array $data)
{
    $repo = new CustomerRepository(); // Direct instantiation
}
```

### 4. Event Dispatching

**DO**: Dispatch events for async processing

```php
✅ CORRECT:
public function createCustomer(array $data): Customer
{
    return $this->createInTransaction(function () use ($data) {
        $customer = $this->customerRepository->create($data);

        // Dispatch event for async processing
        CustomerRegistered::dispatch($customer);

        return $customer;
    });
}
```

### 5. Separation of Concerns

**DO**: Keep services focused on single responsibility

```php
✅ CORRECT:
- CustomerService: Customer CRUD
- PolicyService: Policy CRUD
- EmailService: Email sending
- FileUploadService: File handling

❌ WRONG:
- CustomerService handles customers, policies, emails, files
```

### 6. Caching Strategy

**DO**: Cache expensive queries with reasonable TTL

```php
✅ CORRECT:
public function getTenantUsage(Tenant $tenant): array
{
    $cacheKey = "tenant_usage_{$tenant->id}";

    return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($tenant) {
        return $this->calculateUsage($tenant);
    });
}

public function trackResourceCreated(string $resourceType): void
{
    Cache::forget("tenant_usage_{$tenant->id}"); // Invalidate cache
}
```

### 7. Return Type Declarations

**DO**: Always declare return types

```php
✅ CORRECT:
public function createCustomer(array $data): Customer
public function isWithinLimits(Tenant $tenant): bool
public function getUsagePercentage(Tenant $tenant, string $type): float

❌ WRONG:
public function createCustomer(array $data)
public function isWithinLimits(Tenant $tenant)
```

### 8. Method Documentation

**DO**: Document complex methods with PHPDoc

```php
✅ CORRECT:
/**
 * Send templated email notification to recipient with optional attachments.
 *
 * This method orchestrates email delivery with comprehensive validation:
 * 1. Checks if email notifications are globally enabled
 * 2. Validates recipient email address format
 * 3. Renders email content from template system
 *
 * @param  string  $to  Recipient email address
 * @param  string  $code  Notification type code
 * @param  NotificationContext  $context  Context for variable resolution
 * @param  array  $attachments  Optional file paths to attach
 * @return bool True on successful send, false otherwise
 */
public function sendTemplatedEmail(
    string $to,
    string $code,
    NotificationContext $context,
    array $attachments = []
): bool {
```

---

## Related Documentation

- [Database Schema](DATABASE_SCHEMA.md) - Database structure and relationships
- [Multi-Portal Architecture](MULTI_PORTAL_ARCHITECTURE.md) - Portal routing and guards
- [API Reference](../API_REFERENCE.md) - REST API endpoints
- [Features Documentation](../FEATURES.md) - Feature specifications

---

**Last Updated**: 2025-11-06
**Total Services**: 51 services
**Documentation Coverage**: 100% (all services documented)
