# Customer Management

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Comprehensive customer management system for insurance customers with full CRUD operations, family group management, document verification, authentication, and customer portal access.

### Key Features

- **Customer CRUD**: Create, read, update, delete customer records
- **Customer Types**: Retail and Corporate customer classification
- **Family Groups**: Link customers into family units for shared policies
- **Document Management**: PAN card, Aadhar card, GST document uploads
- **Authentication**: Customer portal login with email verification
- **Password Management**: Reset, change, must-change-password workflows
- **Status Management**: Active/inactive customer toggle
- **Search & Export**: Full-text search, Excel/CSV export
- **Lead Conversion**: Track customers converted from leads
- **Onboarding**: Automated welcome emails and WhatsApp messages
- **Audit Trail**: Complete activity logging with Spatie Activity Log

## Customer Model

**File**: `app/Models/Customer.php`

### Extends

`Illuminate\Foundation\Auth\User as Authenticatable` - Full authentication support

### Traits Used

- `HasApiTokens` - Laravel Sanctum API authentication
- `HasFactory` - Factory support for testing/seeding
- `HasRoles` - Spatie Permission role/permission system
- `Notifiable` - Laravel notifications
- `SoftDeletes` - Soft delete support
- `LogsActivity` - Spatie Activity Log integration
- `Auditable` - Custom audit trail
- `ProtectedRecord` - Record protection system
- `HasCustomerTwoFactorAuth` - 2FA functionality

### Key Attributes

**Personal Information**:
- `name` (string) - Customer full name
- `email` (string, nullable) - Email address
- `mobile_number` (string, nullable) - Mobile number
- `date_of_birth` (date, nullable) - Birth date
- `wedding_anniversary_date` (date, nullable) - Wedding anniversary
- `engagement_anniversary_date` (date, nullable) - Engagement anniversary

**Classification**:
- `type` (enum: 'Corporate', 'Retail', nullable) - Customer type
- `status` (boolean) - Active/inactive status (default: 1)

**Documents**:
- `pan_card_number` (string, nullable) - PAN number
- `aadhar_card_number` (string, nullable) - Aadhar number
- `gst_number` (string, nullable) - GST number
- `pan_card_path` (string, nullable) - PAN document path
- `aadhar_card_path` (string, nullable) - Aadhar document path
- `gst_path` (string, nullable) - GST document path

**Family**:
- `family_group_id` (bigint, nullable) - Foreign key to family_groups table

**Authentication**:
- `password` (string, nullable) - Hashed password for customer portal
- `email_verified_at` (timestamp, nullable) - Email verification timestamp
- `email_verification_token` (string, nullable) - Email verification token
- `password_changed_at` (timestamp, nullable) - Last password change
- `must_change_password` (boolean) - Force password change on next login
- `password_reset_token` (string, nullable) - Password reset token (64 char hex)
- `password_reset_expires_at` (timestamp, nullable) - Token expiration (1 hour)
- `password_reset_sent_at` (timestamp, nullable) - Reset email sent time

**System**:
- `notification_preferences` (json, nullable) - Notification channel preferences
- `is_protected` (boolean) - Protection from deletion
- `protected_reason` (string, nullable) - Why record is protected
- `converted_from_lead_id` (bigint, nullable) - Original lead ID if converted
- `converted_at` (timestamp, nullable) - Conversion timestamp
- `created_by/updated_by/deleted_by` - Audit trail

### Relationships

```php
// Insurance Policies
$customer->insurance(); // HasMany CustomerInsurance

// Quotations
$customer->quotations(); // HasMany Quotation

// Claims
$customer->claims(); // HasMany Claim

// Family Management
$customer->familyGroup(); // BelongsTo FamilyGroup
$customer->familyMember(); // HasOne FamilyMember (relationship record)
$customer->familyMembers(); // HasMany FamilyMember (all family members if in a group)

// Lead Conversion
$customer->originalLead(); // BelongsTo Lead

// Customer Classification
$customer->customerType(); // BelongsTo CustomerType

// Device & Notifications
$customer->devices(); // HasMany CustomerDevice (push notification devices)
$customer->activeDevices(); // HasMany CustomerDevice (active only)
$customer->notificationLogs(); // HasMany NotificationLog

// 2FA & Security
$customer->customerTwoFactorAuth(); // HasOne CustomerTwoFactorAuth
$customer->customerTrustedDevices(); // HasMany CustomerTrustedDevice
$customer->customerSecuritySettings(); // HasOne CustomerSecuritySettings

// Audit
$customer->auditLogs(); // HasMany CustomerAuditLog
$customer->activities(); // HasMany Activity (Spatie Activity Log)
```

### Key Methods

#### Status Checks

```php
$customer->isActive(): bool // Check if customer is active
$customer->isRetailCustomer(): bool // Check if retail customer
$customer->isCorporateCustomer(): bool // Check if corporate customer
```

#### Family Management

```php
$customer->hasFamily(): bool // Check if part of a family group
$customer->isFamilyHead(): bool // Check if designated family head
$customer->isInSameFamilyAs(Customer $customer): bool // Check family relationship
$customer->canViewSensitiveDataOf(Customer $customer): bool // Permission check
$customer->getViewableInsurance() // Get policies customer can view (own + family if head)
$customer->familyInsurance() // HasMany relationship for family policies
```

#### Privacy & Masking

```php
$customer->getPrivacySafeData(): array // Masked data for family viewing
$customer->getMaskedPanNumber(): ?string // PAN with stars (CFD*****8P)
protected maskEmail(string $email): ?string // Email masking (jo****@example.com)
protected maskMobile(string $mobile): ?string // Mobile masking (98****21)
```

#### Password Management

```php
$customer->setDefaultPassword(?string $password = null): string // Generate/set random password
$customer->setCustomPassword(string $password, bool $forceChange = true): string // Set custom password
$customer->changePassword(string $newPassword): void // User password change
$customer->needsPasswordChange(): bool // Check must_change_password flag
$customer->checkPassword(string $password): bool // Verify password
Customer::generateDefaultPassword(): string // Static: generate 8-char password
```

#### Email Verification

```php
$customer->hasVerifiedEmail(): bool // Check if email verified
$customer->generateEmailVerificationToken(): string // Generate 60-char token
$customer->verifyEmail(string $token): bool // Verify and mark verified
```

#### Password Reset

```php
$customer->generatePasswordResetToken(): string // Generate secure 64-char token with 1-hour expiry
$customer->verifyPasswordResetToken(string $token): bool // Verify token and check expiry
$customer->clearPasswordResetToken(): void // Clear after successful reset
```

#### Date Formatting

```php
$customer->date_of_birth_formatted // Accessor: UI format (d/m/Y)
$customer->wedding_anniversary_date_formatted // Accessor: UI format (d/m/Y)
$customer->engagement_anniversary_date_formatted // Accessor: UI format (d/m/Y)
```

#### Document Accessors

```php
$customer->pan_card_path // Accessor: Returns asset('storage/'.$value) or null
$customer->aadhar_card_path // Accessor: Returns asset('storage/'.$value) or null
$customer->gst_path // Accessor: Returns asset('storage/'.$value) or null
```

## CustomerService

**File**: `app/Services/CustomerService.php`

### Core Methods

#### Retrieval

```php
getCustomers(Request $request): LengthAwarePaginator
// Returns paginated customers (10 per page)
// Supports filtering and search via CustomerRepository

getActiveCustomersForSelection(): Collection
// Returns only active customers for dropdowns

getCustomersByFamily(int $familyGroupId): Collection
// Get all customers in a family group

getCustomersByType(string $type): Collection
// Filter by 'Retail' or 'Corporate'

searchCustomers(string $query): Collection
// Full-text search across name, email, mobile

findByEmail(string $email): ?Customer
// Find customer by email address

findByMobileNumber(string $mobileNumber): ?Customer
// Find customer by mobile number

customerExists(int $customerId): bool
// Check if customer exists

getCustomerStatistics(): array
// Returns ['total', 'active', 'corporate'] counts
```

#### Create

```php
createCustomer(StoreCustomerRequest $request): Customer
```

**Transaction Flow**:
1. Check email uniqueness (throws exception if exists)
2. Create customer record
3. Handle document uploads (PAN, Aadhar, GST)
4. Fire `CustomerRegistered` event (async - WhatsApp, audit, admin notifications)
5. Send welcome email **synchronously** (throws exception on failure → rollback)
6. Return customer instance

**Important**: Email send failure causes complete transaction rollback and customer deletion.

**Throws**:
- `\Exception` - Email already exists
- `\Exception` - Email send failure (user-friendly message)

#### Update

```php
updateCustomer(UpdateCustomerRequest $request, Customer $customer): bool
```

**Transaction Flow**:
1. Capture original values for change tracking
2. Update customer data
3. Handle document uploads
4. Identify changed fields
5. Fire `CustomerProfileUpdated` event with changes
6. Return success boolean

#### Status Management

```php
updateCustomerStatus(int $customerId, int $status): bool
```

**Validation**:
- `customer_id` - Required, must exist in database
- `status` - Required, must be 0 (inactive) or 1 (active)

**Throws**: `\InvalidArgumentException` on validation failure

#### Delete

```php
deleteCustomer(Customer $customer): bool
```

Soft deletes customer within transaction.

### Document Management

```php
handleCustomerDocuments(
    StoreCustomerRequest|UpdateCustomerRequest $request,
    Customer $customer
): void
```

**Handles**:
- `pan_card_path` - PAN card document
- `aadhar_card_path` - Aadhar card document
- `gst_path` - GST certificate

**Process**:
1. Check if each file exists in request
2. Upload via `FileUploadService->uploadCustomerDocument()`
3. Update customer paths
4. Save customer record if any documents uploaded

**Storage Path**: `storage/app/public/customers/{customer_id}/{document_type}/`

### Onboarding

#### WhatsApp Onboarding

```php
sendOnboardingMessage(Customer $customer): bool
```

**Flow**:
1. Generate message from template or fallback
2. Log notification before sending
3. Send via `whatsAppSendMessage()` (WhatsAppApiTrait)
4. Update notification log status
5. Return success boolean

**Non-blocking**: Logs errors but returns false, doesn't throw exceptions.

#### Email Onboarding

```php
sendOnboardingEmail(Customer $customer): bool
```

**Flow**:
1. Get EmailService instance
2. Send using `sendFromCustomer('customer_welcome', $customer)`
3. Return success boolean

**Non-blocking**: Logs errors but returns false.

**Synchronous Welcome Email**:
```php
private sendWelcomeEmailSync(Customer $customer): void
```

Used during customer creation. **Throws exception on failure** to trigger transaction rollback.

**Template**: `emails.customer.welcome`
**Subject**: "Welcome to {Company} - Your Customer Account is Ready!"

## CustomerController

**File**: `app/Http/Controllers/CustomerController.php`

**Extends**: `AbstractBaseCrudController`

### Routes & Actions

**List Customers**:
```php
GET /customers
public function index(Request $request)
```

**Features**:
- Standard view with pagination
- AJAX endpoint for Select2 autocomplete (`?ajax=1` or `?q=search`)
- Sorting support (`sort_field`, `sort_order`)
- Search across name, email, mobile
- Error handling with empty paginator fallback

**Create Customer Form**:
```php
GET /customers/create
public function create(): View
```

Returns `customers.add` view.

**Store Customer**:
```php
POST /customers
public function store(StoreCustomerRequest $request): RedirectResponse
```

- Validates via `StoreCustomerRequest`
- Creates via `CustomerService->createCustomer()`
- Redirects to `customers.index` with success message
- Returns error with input on failure

**Show Customer** (redirects to edit):
```php
GET /customers/{customer}
public function show(Customer $customer): RedirectResponse
```

Redirects to `customers.edit` route.

**Edit Customer Form**:
```php
GET /customers/{customer}/edit
public function edit(Customer $customer): View
```

Returns `customers.edit` view with:
- `$customer` - Customer instance
- `$customer_insurances` - Related insurance policies

**Update Customer**:
```php
PUT /customers/{customer}
public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
```

- Validates via `UpdateCustomerRequest`
- Updates via `CustomerService->updateCustomer()`
- Redirects with success/error message

**Update Status**:
```php
POST /customers/{customer_id}/status/{status}
public function updateStatus(int $customer_id, int $status): RedirectResponse
```

Toggles active/inactive status via `CustomerService->updateCustomerStatus()`.

**Delete Customer**:
```php
DELETE /customers/{customer}
public function delete(Customer $customer): RedirectResponse
```

Soft deletes customer via `CustomerService->deleteCustomer()`.

**Import Customers**:
```php
GET /customers/import
public function importCustomers(): View
```

Returns `customers.import` view for bulk import.

**Resend Onboarding WhatsApp**:
```php
POST /customers/{customer}/resend-onboarding-wa
public function resendOnBoardingWA(Customer $customer): RedirectResponse
```

Manually resends onboarding WhatsApp message.

### Export Configuration

**Trait**: `ExportableTrait`

**Export Config**:
```php
protected function getExportConfig(Request $request): array
{
    return [
        'format' => 'xlsx', // or 'csv'
        'filename' => 'customers',
        'with_headings' => true,
        'auto_size' => true,
        'relations' => ['familyGroup'],
        'headings' => ['ID', 'Name', 'Email', 'Mobile', 'Status', 'Family Group', 'Created Date'],
        'mapping' => fn($customer) => [
            $customer->id,
            $customer->name,
            $customer->email,
            $customer->mobile_number,
            ucfirst($customer->status),
            $customer->familyGroup ? $customer->familyGroup->name : 'Individual',
            $customer->created_at->format('Y-m-d H:i:s'),
        ],
    ];
}
```

**Searchable Fields**: `['name', 'email', 'mobile_number']`

## Customer Portal Authentication

### CustomerAuthController

**File**: `app/Http/Controllers/Auth/CustomerAuthController.php`

**Login Flow**:
1. Validate email/mobile + password
2. Authenticate via `Auth::guard('customer')->attempt()`
3. Check `must_change_password` flag
4. Redirect to change password or dashboard

**Registration Flow**:
1. Validate customer data
2. Create customer via `CustomerService`
3. Generate email verification token
4. Send verification email
5. Auto-login or redirect to verify email

**Password Reset Flow**:
1. Request reset with email/mobile
2. Generate secure token (`generatePasswordResetToken()`)
3. Send reset link via email
4. Verify token + expiry
5. Update password + clear token

**Email Verification Flow**:
1. Send verification email with token link
2. Click link → verify token
3. Mark `email_verified_at` timestamp
4. Clear `email_verification_token`

## Customer Portal Features

### Dashboard

- View own insurance policies
- View family policies (if family head)
- Policy details and documents
- Claims tracking
- Profile management

### Family Access

**Family Head Permissions**:
- View all family members
- View all family insurance policies
- Cannot view raw personal documents (privacy-safe data only)

**Regular Members**:
- View own policies only
- View masked family member info

**Privacy Protection**:
```php
$customer->canViewSensitiveDataOf($otherCustomer); // Permission check
$customer->getPrivacySafeData(); // Masked data for family viewing
```

### Profile Management

- Update personal information
- Upload/update documents
- Change password
- Manage notification preferences
- Email verification

## Database Schema

### customers Table

```sql
CREATE TABLE customers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(125) NOT NULL,
    email VARCHAR(125) NULL,
    mobile_number VARCHAR(125) NULL,
    date_of_birth DATE NULL,
    wedding_anniversary_date DATE NULL,
    engagement_anniversary_date DATE NULL,
    type ENUM('Corporate', 'Retail') NULL,
    status TINYINT DEFAULT 1,

    -- Documents
    pan_card_number VARCHAR(50) NULL,
    aadhar_card_number VARCHAR(50) NULL,
    gst_number VARCHAR(50) NULL,
    pan_card_path VARCHAR(150) NULL,
    aadhar_card_path VARCHAR(150) NULL,
    gst_path VARCHAR(150) NULL,

    -- Family
    family_group_id BIGINT UNSIGNED NULL COMMENT 'Family group this customer belongs to',

    -- Authentication
    password VARCHAR(255) NULL COMMENT 'Password for customer login',
    password_changed_at TIMESTAMP NULL,
    must_change_password BOOLEAN DEFAULT 0,
    email_verified_at TIMESTAMP NULL COMMENT 'Email verification timestamp',
    email_verification_token VARCHAR(255) NULL,
    password_reset_sent_at TIMESTAMP NULL,
    password_reset_token VARCHAR(255) NULL,
    password_reset_expires_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,

    -- System
    notification_preferences JSON NULL,
    is_protected BOOLEAN DEFAULT 0,
    protected_reason TEXT NULL,
    converted_from_lead_id BIGINT UNSIGNED NULL,
    converted_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,
    deleted_by INT NULL,

    INDEX idx_email (email),
    INDEX idx_mobile (mobile_number),
    INDEX idx_status (status),
    INDEX idx_type (type),
    INDEX idx_family_group (family_group_id),
    FOREIGN KEY (family_group_id) REFERENCES family_groups(id),
    FOREIGN KEY (converted_from_lead_id) REFERENCES leads(id)
);
```

## Events

### CustomerRegistered

**File**: `app/Events/Customer/CustomerRegistered.php`

**Dispatched**: After customer creation (before email)

**Payload**:
- `Customer $customer`
- `array $metadata` - Request data, document flags
- `string $createdBy` - 'admin' or 'self'

**Listeners**:
- Send WhatsApp onboarding message
- Create audit log entry
- Notify administrators (for admin-created customers)
- Update statistics

### CustomerProfileUpdated

**File**: `app/Events/Customer/CustomerProfileUpdated.php`

**Dispatched**: After customer update with changes

**Payload**:
- `Customer $customer` - Fresh instance
- `array $changedFields` - Field names that changed
- `array $originalValues` - Old values before update

**Listeners**:
- Create audit log entry
- Send change notifications
- Update related records

## Validation Rules

### StoreCustomerRequest

```php
'name' => 'required|string|max:125',
'email' => 'nullable|email|max:125|unique:customers,email',
'mobile_number' => 'nullable|string|max:125',
'date_of_birth' => 'nullable|date',
'wedding_anniversary_date' => 'nullable|date',
'engagement_anniversary_date' => 'nullable|date',
'type' => 'nullable|in:Corporate,Retail',
'status' => 'required|boolean',
'pan_card_number' => 'nullable|string|max:50',
'aadhar_card_number' => 'nullable|string|max:50',
'gst_number' => 'nullable|string|max:50',
'pan_card_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
'aadhar_card_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
'gst_path' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
'family_group_id' => 'nullable|exists:family_groups,id',
```

### UpdateCustomerRequest

Same as StoreCustomerRequest but:
- `email` - unique:customers,email,{customer_id}
- `status` - optional (not required)

## Usage Examples

### Example 1: Create Customer with Documents

```php
use App\Services\CustomerService;
use App\Http\Requests\StoreCustomerRequest;

$customerService = app(CustomerService::class);

// Prepare request data
$requestData = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'mobile_number' => '+919876543210',
    'type' => 'Retail',
    'status' => true,
    'date_of_birth' => '1990-05-15',
    'pan_card_number' => 'ABCDE1234F',
];

// Handle file uploads
if ($request->hasFile('pan_card_path')) {
    $requestData['pan_card_path'] = $request->file('pan_card_path');
}

$request = StoreCustomerRequest::create($requestData);

try {
    $customer = $customerService->createCustomer($request);

    // Customer created successfully
    // - Record saved to database
    // - Documents uploaded
    // - WhatsApp message sent (async)
    // - Welcome email sent (sync)
    // - Audit log created

    echo "Customer created: {$customer->name} (ID: {$customer->id})";
} catch (\Exception $e) {
    // Handle errors (email send failure, duplicate email, etc.)
    echo "Error: {$e->getMessage()}";
}
```

### Example 2: Update Customer with Change Tracking

```php
$customer = Customer::find($customerId);

$request = UpdateCustomerRequest::create([
    'name' => 'John Doe Updated',
    'mobile_number' => '+919876543211', // Changed
    'status' => true,
]);

$updated = $customerService->updateCustomer($request, $customer);

if ($updated) {
    // CustomerProfileUpdated event fired with:
    // - changedFields: ['mobile_number']
    // - originalValues: ['mobile_number' => '+919876543210']

    echo "Customer updated successfully";
}
```

### Example 3: Family Group Management

```php
// Check if customer has family
if ($customer->hasFamily()) {
    // Check if family head
    if ($customer->isFamilyHead()) {
        // Family head can view all family policies
        $policies = $customer->getViewableInsurance()->get();

        foreach ($policies as $policy) {
            echo "Policy: {$policy->policy_no} - Customer: {$policy->customer->name}\n";
        }
    } else {
        // Regular member sees own policies only
        $policies = $customer->insurance;
    }

    // Get all family members
    $familyMembers = $customer->familyMembers;

    foreach ($familyMembers as $member) {
        // Get privacy-safe data
        $safeData = $member->customer->getPrivacySafeData();
        echo "Member: {$safeData['name']} - {$safeData['email']}\n"; // Email is masked
    }
}
```

### Example 4: Password Management

```php
// Set default password (admin action)
$plainPassword = $customer->setDefaultPassword();
// Password is auto-generated (8 chars)
// must_change_password = true
// email_verified_at = null
// email_verification_token = generated

echo "Password: {$plainPassword}"; // Send to customer via secure channel

// Set custom password (admin action)
$plainPassword = $customer->setCustomPassword('SecureP@ss123', true);
// must_change_password = true (force change)

// Customer changes password (after login)
$customer->changePassword('NewP@ssword456');
// must_change_password = false
// password_changed_at = now()
// email_verified_at = now()

// Check if password change required
if ($customer->needsPasswordChange()) {
    return redirect()->route('customer.change-password');
}
```

### Example 5: Password Reset Flow

```php
// Step 1: Generate reset token
$token = $customer->generatePasswordResetToken();
// Token: 64-character hex string (cryptographically secure)
// Expires: 1 hour from now

// Step 2: Send reset link
$resetLink = url("/customer/password-reset?token={$token}&email={$customer->email}");
Mail::to($customer->email)->send(new PasswordResetMail($resetLink));

// Step 3: Verify token (in reset form handler)
if ($customer->verifyPasswordResetToken($token)) {
    // Token is valid and not expired
    $customer->changePassword($newPassword);
    $customer->clearPasswordResetToken();

    echo "Password reset successful";
} else {
    // Token invalid or expired
    echo "Password reset link has expired or is invalid";
}
```

### Example 6: Search and Export Customers

```php
// Search customers
$query = "john";
$customers = $customerService->searchCustomers($query);
// Searches name, email, mobile_number

foreach ($customers as $customer) {
    echo "{$customer->name} - {$customer->email}\n";
}

// Get customers by type
$retailCustomers = $customerService->getCustomersByType('Retail');
$corporateCustomers = $customerService->getCustomersByType('Corporate');

// Get family customers
$familyCustomers = $customerService->getCustomersByFamily($familyGroupId);

// Export to Excel
$controller = app(CustomerController::class);
return $controller->export(request()); // Returns Excel download
```

### Example 7: Customer Portal Authentication

```php
// Login
$credentials = [
    'email' => 'john@example.com',
    'password' => 'password123',
];

if (Auth::guard('customer')->attempt($credentials)) {
    $customer = Auth::guard('customer')->user();

    if ($customer->needsPasswordChange()) {
        return redirect()->route('customer.change-password');
    }

    if (!$customer->hasVerifiedEmail()) {
        return redirect()->route('customer.verify-email');
    }

    return redirect()->route('customer.dashboard');
}

// Logout
Auth::guard('customer')->logout();
```

### Example 8: Document Upload & Verification

```php
// Upload documents
$request = UpdateCustomerRequest::create([
    'pan_card_path' => $uploadedFile, // UploadedFile instance
    'aadhar_card_path' => $uploadedFile,
    'gst_path' => $uploadedFile,
]);

$customerService->handleCustomerDocuments($request, $customer);
// Documents uploaded to: storage/app/public/customers/{customer_id}/{type}/

// Access document URLs
echo $customer->pan_card_path; // Returns asset URL: http://domain.com/storage/customers/1/pan_card/...
echo $customer->aadhar_card_path;
echo $customer->gst_path;

// Get masked PAN for display
echo $customer->getMaskedPanNumber(); // Returns: CFD*****8P
```

## Security Features

### Protection System

**ProtectedRecord Trait**:
```php
$customer->is_protected = true;
$customer->protected_reason = 'VIP customer with active policies';
$customer->save();

// Attempting to delete throws exception
try {
    $customer->delete();
} catch (\Exception $e) {
    echo "Cannot delete: {$e->getMessage()}";
}
```

### SQL Injection Protection

**Family Group ID Validation**:
```php
protected function validateFamilyGroupId($familyGroupId): int
{
    // Validates:
    // 1. Not null
    // 2. Numeric
    // 3. Positive integer
    // 4. Exists in family_groups table
    // 5. Status is active

    // Throws InvalidArgumentException on failure
}
```

Used in:
- `familyInsurance()` relationship
- `getViewableInsurance()` method

### Password Security

**Password Reset Token**:
- Cryptographically secure: `bin2hex(random_bytes(32))` - 64 characters
- 1-hour expiration
- Constant-time comparison: `hash_equals()`
- Auto-clears on expiry verification

**Password Hashing**:
- Laravel's `Hash::make()` - bcrypt with cost factor
- `Hash::check()` for verification

### Multi-Guard Authentication

**Boot Method**:
```php
protected static function boot()
{
    parent::boot();

    // Check both 'customer' and 'web' guards for audit trail
    static::creating(function ($model) {
        if (Auth::guard('customer')->check()) {
            $model->created_by = Auth::guard('customer')->id();
        } elseif (Auth::guard('web')->check()) {
            $model->created_by = Auth::guard('web')->id();
        } else {
            $model->created_by = 0; // System/API
        }
    });

    // Same for updating, deleting
}
```

## Best Practices

1. **Always Use Service Layer**: Never create/update customers directly via model
2. **Transaction Safety**: All write operations use transactions (handled by service)
3. **Event-Driven**: Leverage events for side effects (notifications, audit logs)
4. **Family Privacy**: Use `canViewSensitiveDataOf()` before showing personal data
5. **Document Security**: Documents stored in tenant-isolated storage
6. **Password Reset Security**: Always verify token expiry + use constant-time comparison
7. **Search Performance**: Use indexed fields (email, mobile, status, type)
8. **Export Limits**: Add pagination for large exports to avoid memory issues
9. **Audit Trail**: All changes automatically logged via Spatie Activity Log
10. **Lead Tracking**: Always set `converted_from_lead_id` when converting leads

## Related Documentation

- **[FAMILY_GROUP_MANAGEMENT.md](../features/FAMILY_GROUP_MANAGEMENT.md)** - Family groups and member management
- **[TWO_FACTOR_AUTHENTICATION.md](../features/TWO_FACTOR_AUTHENTICATION.md)** - Customer 2FA system
- **[DEVICE_TRACKING.md](../features/DEVICE_TRACKING.md)** - Device fingerprinting and trusted devices
- **[AUDIT_LOGGING.md](../features/AUDIT_LOGGING.md)** - Customer audit logs
- **[NOTIFICATION_SYSTEM.md](../features/NOTIFICATION_SYSTEM.md)** - Customer notifications
- **[LEAD_MANAGEMENT.md](LEAD_MANAGEMENT.md)** - Lead to customer conversion
- **[POLICY_MANAGEMENT.md](POLICY_MANAGEMENT.md)** - Customer insurance policies
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - customers table schema

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
