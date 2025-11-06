# Policy Management

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Complete insurance policy management system for customer policies with CRUD operations, premium calculations, commission tracking, NCB management, renewal workflows, expiry reminders, and WhatsApp document sharing.

### Key Features

- **Policy CRUD**: Create, read, update, delete policy records
- **Policy Types**: Support for motor, life, health, and other insurance types
- **Premium Management**: OD premium, TP premium, GST calculation
- **Commission Tracking**: Own, transfer, and reference commission calculation
- **NCB Management**: No Claim Bonus percentage tracking
- **Renewal System**: Policy renewal workflow with link to previous policy
- **Expiry Tracking**: Monitor policy expiry dates with automated reminders
- **Document Management**: Upload and share policy documents via WhatsApp
- **Multi-Entity Support**: Branch, broker, relationship manager assignment
- **Payment Tracking**: Payment mode, cheque number, transaction details
- **Vehicle Details**: For motor insurance (registration, make/model, RTO, fuel type)
- **Life Insurance**: Plan details, term, sum insured, maturity tracking
- **Claims Integration**: Link policies to claim records
- **Export**: Excel/CSV export with full policy data

## CustomerInsurance Model

**File**: `app/Models/CustomerInsurance.php`

### Traits Used

- `HasFactory` - Factory support
- `SoftDeletes` - Soft delete support
- `LogsActivity` - Spatie Activity Log integration
- `TableRecordObserver` - Custom audit observer

### Key Attributes

**Core Policy Information**:
- `policy_no` (string) - Policy number from insurance company
- `insurance_company_id` (bigint) - Foreign key to insurance_companies
- `policy_type_id` (bigint) - Foreign key to policy_types (Motor, Life, Health, etc.)
- `premium_type_id` (bigint) - Foreign key to premium_types (Comprehensive, Third Party, etc.)
- `issue_date` (date) - Policy issue date
- `start_date` (date) - Policy start date
- `expired_date` (date) - Policy expiry date
- `status` (tinyint) - Active/inactive status (default: 1)

**Assignment**:
- `customer_id` (bigint) - Foreign key to customers
- `branch_id` (bigint, nullable) - Foreign key to branches
- `broker_id` (bigint, nullable) - Foreign key to brokers
- `relationship_manager_id` (bigint, nullable) - Foreign key to relationship_managers

**Premium & GST**:
- `od_premium` (double, nullable) - Own Damage premium
- `tp_premium` (double, nullable) - Third Party premium
- `net_premium` (double, nullable) - Net premium before GST
- `premium_amount` (double, nullable) - Total premium amount
- `gst` (double, nullable) - Total GST amount
- `final_premium_with_gst` (double, nullable) - Final premium including GST
- `sgst1/cgst1/sgst2/cgst2` (double, nullable) - GST breakdown

**Commission**:
- `commission_on` (enum: 'net_premium', 'od_premium', 'tp_premium', nullable) - Commission base
- `my_commission_percentage` (double, nullable) - Own commission %
- `my_commission_amount` (double, nullable) - Own commission amount
- `transfer_commission_percentage` (double, nullable) - Transfer commission %
- `transfer_commission_amount` (double, nullable) - Transfer commission amount
- `reference_commission_percentage` (double, nullable) - Reference commission %
- `reference_commission_amount` (double, nullable) - Reference commission amount
- `actual_earnings` (double, nullable) - Net earnings (my_commission - transfer - reference)
- `reference_by` (int, nullable) - Reference user ID

**Motor Insurance Specific**:
- `registration_no` (string, nullable) - Vehicle registration number
- `rto` (string, nullable) - RTO code
- `make_model` (string, nullable) - Vehicle make and model
- `fuel_type_id` (bigint, nullable) - Foreign key to fuel_types (Petrol, Diesel, CNG, Electric)
- `mfg_year` (string, nullable) - Manufacturing year
- `gross_vehicle_weight` (string, nullable) - GVW for commercial vehicles
- `ncb_percentage` (double, nullable) - No Claim Bonus percentage (0-50%)
- `tp_expiry_date` (date, nullable) - Third Party expiry date (can differ from OD)

**Life Insurance Specific**:
- `plan_name` (string, nullable) - Insurance plan name
- `policy_term` (string, nullable) - Policy term in years
- `premium_paying_term` (string, nullable) - Premium payment term
- `sum_insured` (string, nullable) - Sum assured amount
- `pension_amount_yearly` (string, nullable) - Yearly pension for pension plans
- `approx_maturity_amount` (string, nullable) - Approximate maturity value
- `maturity_date` (date, nullable) - Maturity date
- `life_insurance_payment_mode` (string, nullable) - Payment frequency (Monthly, Quarterly, Yearly)

**Payment**:
- `mode_of_payment` (string, nullable) - Payment mode (Cash, Cheque, Online, UPI)
- `cheque_no` (string, nullable) - Cheque number if payment by cheque

**Renewal**:
- `is_renewed` (tinyint) - Renewal status (0 = not renewed, 1 = renewed)
- `renewed_date` (datetime, nullable) - Renewal date
- `new_insurance_id` (int, nullable) - Foreign key to new policy after renewal

**Documents & Notes**:
- `policy_document_path` (string, nullable) - Policy document file path
- `remarks` (text, nullable) - Additional notes

**System**:
- `created_by/updated_by/deleted_by` - Audit trail

### Relationships

```php
// Customer & Assignment
$policy->customer(); // BelongsTo Customer
$policy->branch(); // BelongsTo Branch
$policy->broker(); // BelongsTo Broker
$policy->relationshipManager(); // BelongsTo RelationshipManager

// Insurance Details
$policy->insuranceCompany(); // BelongsTo InsuranceCompany
$policy->policyType(); // BelongsTo PolicyType (Motor, Life, Health, etc.)
$policy->premiumType(); // BelongsTo PremiumType (Comprehensive, Third Party, etc.)
$policy->fuelType(); // BelongsTo FuelType (for motor insurance)
$policy->commissionType(); // BelongsTo CommissionType

// Claims
$policy->claims(); // HasMany Claim
```

### Date Formatting Accessors

```php
// Auto-formats dates from database (Y-m-d) to UI format (d/m/Y)
$policy->issue_date_formatted; // Accessor
$policy->start_date_formatted; // Accessor
$policy->expired_date_formatted; // Accessor
$policy->tp_expiry_date_formatted; // Accessor
$policy->maturity_date_formatted; // Accessor

// Auto-converts dates from UI format (d/m/Y) to database format (Y-m-d)
$policy->issue_date = '15/10/2025'; // Mutator converts to 2025-10-15
```

## CustomerInsuranceService

**File**: `app/Services/CustomerInsuranceService.php`

### Core Methods

#### Retrieval

```php
getCustomerInsurances(Request $request): LengthAwarePaginator
// Returns paginated policies with filtering and sorting

getFormData(): array
// Returns all dropdown data needed for create/edit forms:
// - customers, branches, brokers, relationship managers
// - insurance companies, policy types, premium types, fuel types
// - commission types, payment modes

getCustomerInsuranceById(int $id): ?CustomerInsurance
// Find policy by ID

getExpiringPolicies(int $days = 30): Collection
// Get policies expiring within N days

getPoliciesByCustomer(int $customerId): Collection
// Get all policies for a customer

getPoliciesByBranch(int $branchId): Collection
// Get all policies for a branch

getPoliciesByBroker(int $brokerId): Collection
// Get all policies for a broker
```

#### Create & Update

```php
createCustomerInsurance(array $data): CustomerInsurance
// Create new policy within transaction

updateCustomerInsurance(CustomerInsurance $policy, array $data): bool
// Update existing policy within transaction

updateStatus(int $policyId, int $status): bool
// Toggle active/inactive status

prepareStorageData(Request $request): array
// Prepare validated data for storage with commission calculations
```

#### Renewal

```php
renewPolicy(CustomerInsurance $oldPolicy, array $data): CustomerInsurance
```

**Renewal Process**:
1. Create new policy with updated data
2. Copy relevant data from old policy (customer, vehicle details if applicable)
3. Mark old policy as renewed (`is_renewed = 1`, `renewed_date = now()`)
4. Link old policy to new policy (`new_insurance_id = new_policy->id`)
5. Fire PolicyRenewed event
6. Return new policy instance

#### Document Management

```php
handleFileUpload(Request $request, CustomerInsurance $policy): void
// Upload policy document PDF
// Storage path: storage/app/public/policies/{customer_id}/{policy_id}/

sendWhatsAppDocument(CustomerInsurance $policy): bool
// Send policy document via WhatsApp to customer
```

**WhatsApp Document Flow**:
1. Check if `policy_document_path` exists
2. Get full file path from storage
3. Get customer mobile number
4. Generate WhatsApp message template
5. Send document via WhatsApp API
6. Log notification in notification_logs table
7. Return success/failure boolean

```php
sendRenewalReminderWhatsApp(CustomerInsurance $policy): bool
// Send renewal reminder via WhatsApp
```

**Renewal Reminder Flow**:
1. Check if policy is expiring within threshold (30 days)
2. Generate renewal reminder message template
3. Include policy details (policy number, expiry date, premium)
4. Send via WhatsApp API
5. Log notification

#### Export

```php
exportCustomerInsurances(): BinaryFileResponse
// Export all policies to Excel/CSV
```

### Validation Rules

**Store Validation**:
```php
'customer_id' => 'required|exists:customers,id',
'insurance_company_id' => 'required|exists:insurance_companies,id',
'policy_type_id' => 'required|exists:policy_types,id',
'premium_type_id' => 'nullable|exists:premium_types,id',
'policy_no' => 'required|string|max:125',
'issue_date' => 'required|date',
'start_date' => 'required|date',
'expired_date' => 'required|date|after:start_date',
'registration_no' => 'nullable|string|max:125', // For motor insurance
'net_premium' => 'nullable|numeric|min:0',
'gst' => 'nullable|numeric|min:0',
'final_premium_with_gst' => 'nullable|numeric|min:0',
'my_commission_percentage' => 'nullable|numeric|min:0|max:100',
'ncb_percentage' => 'nullable|numeric|min:0|max:50',
'policy_document_path' => 'nullable|file|mimes:pdf|max:5120', // 5MB max
```

**Update Validation**: Same as store but allows partial updates

**Renewal Validation**: Similar to store but allows copying from previous policy

## CustomerInsuranceController

**File**: `app/Http/Controllers/CustomerInsuranceController.php`

### Routes & Actions

**List Policies**:
```php
GET /customer-insurances
public function index(Request $request)
```

**Features**:
- Standard view with pagination
- AJAX endpoint for Select2 autocomplete (`?ajax=1`)
- Search by policy number, registration number, customer name
- Sorting support
- Filter by customer, branch, broker, insurance company, policy type

**Create Policy**:
```php
GET /customer-insurances/create
public function create()

POST /customer-insurances
public function store(Request $request)
```

**Process**:
1. Validate request data
2. Prepare data with commission calculations
3. Create policy via service
4. Handle file upload (policy document PDF)
5. Send WhatsApp document if uploaded
6. Redirect with success message

**Edit Policy**:
```php
GET /customer-insurances/{customerInsurance}/edit
public function edit(CustomerInsurance $customerInsurance)

PUT /customer-insurances/{customerInsurance}
public function update(Request $request, CustomerInsurance $customerInsurance)
```

**Delete Policy**:
```php
DELETE /customer-insurances/{customerInsurance}
public function delete(CustomerInsurance $customerInsurance)
```

Soft deletes policy.

**Update Status**:
```php
POST /customer-insurances/{customer_insurance_id}/status/{status}
public function updateStatus(int $customer_insurance_id, int $status)
```

Toggles active/inactive status.

**Renew Policy**:
```php
GET /customer-insurances/{customerInsurance}/renew
public function renew(CustomerInsurance $customerInsurance)

POST /customer-insurances/{customerInsurance}/renew
public function storeRenew(Request $request, CustomerInsurance $customerInsurance)
```

**Renewal Form**: Pre-fills data from old policy for quick renewal.

**Send WhatsApp Document**:
```php
POST /customer-insurances/{customerInsurance}/send-wa-document
public function sendWADocument(CustomerInsurance $customerInsurance)
```

Manually sends policy document via WhatsApp.

**Send Renewal Reminder**:
```php
POST /customer-insurances/{customerInsurance}/send-renewal-reminder-wa
public function sendRenewalReminderWA(CustomerInsurance $customerInsurance)
```

Manually sends renewal reminder via WhatsApp.

**Import/Export**:
```php
GET /customer-insurances/import
public function importCustomerInsurances()

GET /customer-insurances/export
public function export()
```

Import view for bulk upload, export to Excel/CSV.

## Commission Calculation

### Commission Formula

```php
// Base amount based on commission_on field
$baseAmount = match($policy->commission_on) {
    'net_premium' => $policy->net_premium,
    'od_premium' => $policy->od_premium,
    'tp_premium' => $policy->tp_premium,
    default => $policy->net_premium,
};

// Own Commission
$myCommission = ($baseAmount * $policy->my_commission_percentage) / 100;

// Transfer Commission (if policy transferred)
$transferCommission = ($baseAmount * $policy->transfer_commission_percentage) / 100;

// Reference Commission (if referred)
$referenceCommission = ($baseAmount * $policy->reference_commission_percentage) / 100;

// Actual Earnings
$actualEarnings = $myCommission - $transferCommission - $referenceCommission;

$policy->update([
    'my_commission_amount' => $myCommission,
    'transfer_commission_amount' => $transferCommission,
    'reference_commission_amount' => $referenceCommission,
    'actual_earnings' => $actualEarnings,
]);
```

### Commission Types

1. **Own Commission (`my_commission_`)**: Direct earnings by the broker/agent
2. **Transfer Commission (`transfer_commission_`)**: Commission transferred to another broker/agent
3. **Reference Commission (`reference_commission_`)**: Commission paid for customer referrals

## NCB (No Claim Bonus) Management

### NCB Percentage Scale

```
Year 1 (No Claims): 20%
Year 2 (No Claims): 25%
Year 3 (No Claims): 35%
Year 4 (No Claims): 45%
Year 5+ (No Claims): 50% (maximum)
```

### NCB Application

**Motor Insurance Only**: NCB applies to Own Damage (OD) premium, not Third Party (TP) premium.

**Calculation**:
```php
$baseODPremium = 10000; // Original OD premium
$ncbPercentage = 35; // Year 3 NCB
$ncbDiscount = ($baseODPremium * $ncbPercentage) / 100; // = 3500
$finalODPremium = $baseODPremium - $ncbDiscount; // = 6500

$policy->update([
    'od_premium' => $finalODPremium,
    'ncb_percentage' => $ncbPercentage,
]);
```

**NCB Loss**: If claim is made, NCB resets to 0% on renewal.

**NCB Transfer**: NCB can be transferred when:
- Vehicle is sold and new vehicle purchased
- Policy switched between insurance companies

## Policy Renewal Workflow

### Renewal Process

**Step 1: Identify Expiring Policies**
```php
// Get policies expiring in next 30 days
$expiringPolicies = CustomerInsurance::where('expired_date', '<=', now()->addDays(30))
    ->where('expired_date', '>=', now())
    ->where('is_renewed', 0)
    ->get();

// Send renewal reminders
foreach ($expiringPolicies as $policy) {
    $service->sendRenewalReminderWhatsApp($policy);
}
```

**Step 2: Renewal Form**
- Pre-fills data from old policy
- Allows updating premium, NCB, dates
- Calculates new premium with NCB

**Step 3: Create Renewed Policy**
```php
$renewedPolicy = $service->renewPolicy($oldPolicy, [
    'issue_date' => now()->format('Y-m-d'),
    'start_date' => $oldPolicy->expired_date,
    'expired_date' => $oldPolicy->expired_date->addYear(),
    'ncb_percentage' => $newNCBPercentage, // Incremented if no claims
    'od_premium' => $newODPremium, // After NCB discount
    'policy_no' => $newPolicyNumber, // From insurance company
    // ... other updated fields
]);

// Old policy now marked as renewed
// $oldPolicy->is_renewed = 1
// $oldPolicy->renewed_date = now()
// $oldPolicy->new_insurance_id = $renewedPolicy->id
```

**Step 4: Document & Notify**
- Upload new policy document
- Send document via WhatsApp
- Update customer records

## Expiry Reminders System

### Scheduled Command

**File**: `app/Console/Commands/SendPolicyExpiryReminders.php`

**Schedule**: Daily at 9:00 AM

**Logic**:
```php
// Get policies expiring in 30, 15, 7, and 1 days
$reminders = [
    30 => CustomerInsurance::whereBetween('expired_date', [now()->addDays(29), now()->addDays(30)])->get(),
    15 => CustomerInsurance::whereBetween('expired_date', [now()->addDays(14), now()->addDays(15)])->get(),
    7 => CustomerInsurance::whereBetween('expired_date', [now()->addDays(6), now()->addDays(7)])->get(),
    1 => CustomerInsurance::whereBetween('expired_date', [now(), now()->addDay()])->get(),
];

foreach ($reminders as $days => $policies) {
    foreach ($policies as $policy) {
        // Send WhatsApp reminder
        $service->sendRenewalReminderWhatsApp($policy);

        // Log notification
        NotificationLog::create([...]);
    }
}
```

**Reminder Message Template**:
```
Dear {customer_name},

Your {policy_type} insurance policy {policy_no} for vehicle {registration_no} is expiring on {expiry_date} ({days_remaining} days remaining).

Policy Details:
- Insurance Company: {insurance_company}
- Premium: ₹{premium_amount}
- Current NCB: {ncb_percentage}%

Contact us to renew your policy and enjoy continuous coverage!

Contact: {branch_contact}
```

## Database Schema

### customer_insurances Table

```sql
CREATE TABLE customer_insurances (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Core Policy
    policy_no VARCHAR(125) NULL,
    insurance_company_id BIGINT NULL,
    policy_type_id BIGINT NULL,
    premium_type_id BIGINT NULL,
    issue_date DATE NULL,
    start_date DATE NULL,
    expired_date DATE NULL,
    status TINYINT DEFAULT 1,

    -- Assignment
    customer_id BIGINT UNSIGNED NULL,
    branch_id BIGINT UNSIGNED NULL,
    broker_id BIGINT UNSIGNED NULL,
    relationship_manager_id BIGINT UNSIGNED NULL,

    -- Premium & GST
    od_premium DOUBLE NULL,
    tp_premium DOUBLE NULL,
    net_premium DOUBLE NULL,
    premium_amount DOUBLE NULL,
    gst DOUBLE NULL,
    final_premium_with_gst DOUBLE NULL,
    sgst1 DOUBLE NULL,
    cgst1 DOUBLE NULL,
    cgst2 DOUBLE NULL,
    sgst2 DOUBLE NULL,

    -- Commission
    commission_on ENUM('net_premium', 'od_premium', 'tp_premium') NULL,
    my_commission_percentage DOUBLE NULL,
    my_commission_amount DOUBLE NULL,
    transfer_commission_percentage DOUBLE NULL,
    transfer_commission_amount DOUBLE NULL,
    reference_commission_percentage DOUBLE NULL,
    reference_commission_amount DOUBLE NULL,
    actual_earnings DOUBLE NULL,
    reference_by INT NULL,

    -- Motor Insurance
    registration_no VARCHAR(125) NULL,
    rto VARCHAR(125) NULL,
    make_model VARCHAR(125) NULL,
    fuel_type_id BIGINT NULL,
    mfg_year VARCHAR(125) NULL,
    gross_vehicle_weight VARCHAR(500) NULL,
    ncb_percentage DOUBLE NULL,
    tp_expiry_date DATE NULL,

    -- Life Insurance
    plan_name VARCHAR(150) NULL,
    policy_term VARCHAR(150) NULL,
    premium_paying_term VARCHAR(150) NULL,
    sum_insured VARCHAR(150) NULL,
    pension_amount_yearly VARCHAR(150) NULL,
    approx_maturity_amount VARCHAR(150) NULL,
    maturity_date DATE NULL,
    life_insurance_payment_mode VARCHAR(100) NULL,

    -- Payment
    mode_of_payment VARCHAR(125) NULL,
    cheque_no VARCHAR(125) NULL,

    -- Renewal
    is_renewed TINYINT DEFAULT 0,
    renewed_date DATETIME NULL,
    new_insurance_id INT NULL,

    -- Documents
    policy_document_path VARCHAR(500) NULL,
    remarks TEXT NULL,

    -- Audit
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by INT NULL,
    updated_by INT NULL,
    deleted_by INT NULL,

    INDEX idx_policy_no (policy_no),
    INDEX idx_registration (registration_no),
    INDEX idx_customer (customer_id),
    INDEX idx_expiry (expired_date),
    INDEX idx_status (status),
    INDEX idx_company (insurance_company_id),
    INDEX idx_policy_type (policy_type_id),
    INDEX idx_renewed (is_renewed),
    FOREIGN KEY (customer_id) REFERENCES customers(id),
    FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id),
    FOREIGN KEY (policy_type_id) REFERENCES policy_types(id),
    FOREIGN KEY (new_insurance_id) REFERENCES customer_insurances(id)
);
```

## Usage Examples

### Example 1: Create Motor Insurance Policy

```php
use App\Services\CustomerInsuranceService;

$service = app(CustomerInsuranceService::class);

$data = [
    'customer_id' => 10,
    'insurance_company_id' => 5, // HDFC Ergo
    'policy_type_id' => 1, // Motor Insurance
    'premium_type_id' => 1, // Comprehensive
    'fuel_type_id' => 1, // Petrol
    'policy_no' => 'HDFC/2025/12345',
    'registration_no' => 'MH01AB1234',
    'rto' => 'MH01',
    'make_model' => 'Maruti Swift VXi',
    'mfg_year' => '2020',
    'issue_date' => '2025-11-06',
    'start_date' => '2025-11-07',
    'expired_date' => '2026-11-06',
    'od_premium' => 8000,
    'tp_premium' => 2000,
    'net_premium' => 10000,
    'gst' => 1800, // 18% GST
    'final_premium_with_gst' => 11800,
    'ncb_percentage' => 35, // Year 3 NCB
    'my_commission_percentage' => 15,
    'my_commission_amount' => 1500, // 15% of 10000
    'actual_earnings' => 1500,
    'commission_on' => 'net_premium',
    'mode_of_payment' => 'Online',
    'branch_id' => 1,
    'broker_id' => 2,
];

$policy = $service->createCustomerInsurance($data);

echo "Policy created: {$policy->policy_no}";
```

### Example 2: Renew Policy with Increased NCB

```php
$oldPolicy = CustomerInsurance::where('policy_no', 'HDFC/2025/12345')->first();

// Calculate new NCB (no claims made, increase from 35% to 45%)
$newNCBPercentage = 45;
$baseODPremium = 8000; // Before NCB
$ncbDiscount = ($baseODPremium * $newNCBPercentage) / 100; // 3600
$newODPremium = $baseODPremium - $ncbDiscount; // 4400

$renewalData = [
    'issue_date' => now()->format('Y-m-d'),
    'start_date' => $oldPolicy->expired_date,
    'expired_date' => $oldPolicy->expired_date->copy()->addYear(),
    'policy_no' => 'HDFC/2026/67890', // New policy number
    'od_premium' => $newODPremium,
    'tp_premium' => 2200, // TP may increase
    'net_premium' => $newODPremium + 2200,
    'gst' => ($newODPremium + 2200) * 0.18,
    'final_premium_with_gst' => ($newODPremium + 2200) * 1.18,
    'ncb_percentage' => $newNCBPercentage,
];

$renewedPolicy = $service->renewPolicy($oldPolicy, $renewalData);

// Old policy now linked to new policy
echo "Renewed from: {$oldPolicy->policy_no} to {$renewedPolicy->policy_no}";
echo "NCB increased from {$oldPolicy->ncb_percentage}% to {$renewedPolicy->ncb_percentage}%";
```

### Example 3: Send Policy Document via WhatsApp

```php
// After policy creation or document upload
if ($request->hasFile('policy_document_path')) {
    $service->handleFileUpload($request, $policy);

    // Automatically send via WhatsApp
    $sent = $service->sendWhatsAppDocument($policy);

    if ($sent) {
        echo "Policy document sent to {$policy->customer->mobile_number}";
    }
}

// Or send manually later
$sent = $service->sendWhatsAppDocument($policy);
```

### Example 4: Expiry Reminders (Scheduled Command)

```php
// In app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    $schedule->command('policies:send-expiry-reminders')
        ->dailyAt('09:00')
        ->timezone('Asia/Kolkata');
}

// Command execution
$expiringIn30Days = CustomerInsurance::whereBetween('expired_date',
    [now()->addDays(29), now()->addDays(30)])
    ->where('is_renewed', 0)
    ->get();

foreach ($expiringIn30Days as $policy) {
    $service->sendRenewalReminderWhatsApp($policy);
}
```

### Example 5: Commission Report by Broker

```php
$brokerId = 2;
$startDate = '2025-01-01';
$endDate = '2025-11-06';

$policies = CustomerInsurance::where('broker_id', $brokerId)
    ->whereBetween('issue_date', [$startDate, $endDate])
    ->get();

$totalCommission = $policies->sum('my_commission_amount');
$actualEarnings = $policies->sum('actual_earnings');
$transferredCommission = $policies->sum('transfer_commission_amount');
$referenceCommission = $policies->sum('reference_commission_amount');

$report = [
    'broker' => Broker::find($brokerId)->name,
    'period' => "{$startDate} to {$endDate}",
    'total_policies' => $policies->count(),
    'total_commission' => $totalCommission,
    'actual_earnings' => $actualEarnings,
    'transferred' => $transferredCommission,
    'reference_paid' => $referenceCommission,
];
```

## Best Practices

1. **Always Calculate Commission**: Use prepareStorageData() to auto-calculate commissions
2. **NCB Validation**: Ensure NCB percentage is 0-50% for motor insurance
3. **Expiry Tracking**: Set up daily cron for expiry reminders
4. **Document Management**: Always upload policy documents and send via WhatsApp
5. **Renewal Linking**: Always link renewed policies to old policies via new_insurance_id
6. **Date Consistency**: Ensure start_date < expired_date
7. **GST Calculation**: Always include GST in final premium calculations
8. **Commission Base**: Specify commission_on field to clarify commission calculation base
9. **Payment Tracking**: Record payment mode and transaction details
10. **Audit Trail**: All changes automatically logged via LogsActivity trait

## Related Documentation

- **[CUSTOMER_MANAGEMENT.md](CUSTOMER_MANAGEMENT.md)** - Customer policies integration
- **[QUOTATION_SYSTEM.md](QUOTATION_SYSTEM.md)** - Converting quotations to policies
- **[CLAIMS_MANAGEMENT.md](CLAIMS_MANAGEMENT.md)** - Policy claims integration
- **[COMMISSION_TRACKING.md](../features/COMMISSION_TRACKING.md)** - Commission calculation details
- **[NOTIFICATION_SYSTEM.md](../features/NOTIFICATION_SYSTEM.md)** - WhatsApp document sharing
- **[MASTER_DATA.md](MASTER_DATA.md)** - Insurance companies, policy types, premium types
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - customer_insurances table schema

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
