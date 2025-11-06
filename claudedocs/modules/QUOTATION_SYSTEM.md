# Quotation System

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Multi-company insurance quote comparison system with automated premium calculations, addon covers, PDF generation, and WhatsApp/email delivery.

### Key Features

- **Multi-Company Comparison**: Generate quotes from up to 5 insurance companies simultaneously
- **IDV Breakdown**: Comprehensive Insured Declared Value calculation with 5 components
- **Automated Premium Calculation**: OD + TP + NCB + Addons + GST = Final premium
- **Addon Covers**: 9 addon types with dynamic pricing (Zero Depreciation, Engine Protection, etc.)
- **Quote Reference**: Auto-generated format QT/YY/00000001
- **PDF Generation**: Multi-company comparison PDF via DomPDF
- **WhatsApp/Email Delivery**: Send quotations with PDF attachments
- **Ranking & Recommendations**: Auto-rank by lowest premium with recommendation indicators
- **Quote-to-Policy Conversion**: Seamless conversion workflow

## Quotation Model

**File**: `app/Models/Quotation.php`

### Attributes

**Customer & Vehicle Details**
- `customer_id` (bigint) - Customer reference (foreign key to customers table)
- `quotation_number` (string) - System-generated quote number
- `vehicle_number` (string) - Registration number
- `make_model_variant` (string) - Full vehicle description
- `manufacturing_year` (integer) - Year of manufacture
- `fuel_type` (string) - Petrol, Diesel, CNG, Electric, Hybrid
- `cc` (integer) - Engine cubic capacity

**IDV Components** (All decimal/float)
- `idv_vehicle` - Base vehicle insured value
- `idv_trailer` - Trailer value (if applicable)
- `idv_cng_lpg_kit` - CNG/LPG conversion kit value
- `idv_electrical_accessories` - Electrical accessories value
- `idv_non_electrical_accessories` - Non-electrical accessories value
- `total_idv` - Auto-calculated sum of all IDV components

**Policy Configuration**
- `policy_type` (string) - Comprehensive, Third Party, Own Damage
- `policy_tenure_years` (integer) - Policy duration (1-3 years)
- `addon_covers` (array) - Selected addon covers

**Contact & Status**
- `mobile_number` (string) - Customer mobile
- `whatsapp_number` (string) - WhatsApp contact
- `email` (string, nullable) - Email address
- `status` (string) - Draft, Sent, Approved, Converted, Expired
- `sent_at` (datetime, nullable) - WhatsApp/Email sent timestamp

**Tenant & Audit**
- `tenant_id` (string) - Multi-tenancy identifier
- `created_at`, `updated_at` - Timestamps

### Relationships

```php
// Parent quotation → multiple company quotes
public function quotationCompanies(): HasMany
{
    return $this->hasMany(QuotationCompany::class);
}

// Quotation belongs to customer
public function customer(): BelongsTo
{
    return $this->belongsTo(Customer::class);
}
```

### Helper Methods

**Quote Reference Generation**:
```php
public function getQuoteReference(): string
{
    // Format: QT/YY/00000001
    return 'QT/' . date('y') . '/' . str_pad($this->id, 8, '0', STR_PAD_LEFT);
}

// Usage: $quotation->getQuoteReference() → "QT/25/00000123"
```

**Quote Analysis**:
```php
// Get recommended company quote
public function recommendedQuote(): ?QuotationCompany
{
    return $this->quotationCompanies()->where('is_recommended', true)->first();
}

// Get cheapest quote
public function bestQuote(): ?QuotationCompany
{
    return $this->quotationCompanies()->orderBy('final_premium')->first();
}
```

### Boot Method - Cascade Delete

```php
protected static function boot(): void
{
    parent::boot();

    static::deleting(static function (Quotation $quotation): void {
        // Delete all company quotes
        $quotation->quotationCompanies()->delete();

        // Delete activity logs for quotation
        Activity::query()
            ->where('subject_type', Quotation::class)
            ->where('subject_id', $quotation->id)
            ->delete();

        // Delete activity logs for company quotes
        Activity::query()
            ->where('subject_type', QuotationCompany::class)
            ->whereIn('subject_id', $quotation->quotationCompanies()->pluck('id'))
            ->delete();
    });
}
```

## QuotationCompany Model

**File**: `app/Models/QuotationCompany.php`

### Attributes

**Identification**
- `quotation_id` (bigint) - Parent quotation reference
- `insurance_company_id` (bigint) - Insurance company reference
- `quote_number` (string) - Company-specific quote number (QT/YY/####CC########)

**Policy & IDV Details**
- `policy_type` (string) - Comprehensive, Third Party, Own Damage
- `policy_tenure_years` (integer) - 1-3 years
- `idv_vehicle`, `idv_trailer`, `idv_cng_lpg_kit`, `idv_electrical_accessories`, `idv_non_electrical_accessories`, `total_idv` - Same as Quotation model

**Premium Breakdown** (All decimal/float)
- `basic_od_premium` - Base Own Damage premium (1.2%-3.0% of IDV based on vehicle age)
- `tp_premium` - Third Party liability premium
- `cng_lpg_premium` - CNG/LPG kit premium (5% of kit IDV)
- `total_od_premium` - Basic OD + CNG/LPG premium
- `addon_covers_breakdown` (JSON) - Addon name → premium mapping `{'Zero Depreciation': 4500, 'Engine Protection': 1200}`
- `total_addon_premium` - Sum of all addon premiums
- `net_premium` - Total OD + Total Addon premium
- `sgst_amount` - 9% State GST on net premium
- `cgst_amount` - 9% Central GST on net premium
- `total_premium` - Net premium + SGST + CGST
- `roadside_assistance` - Fixed charge (₹136.88)
- `final_premium` - Total premium + Roadside assistance

**Recommendation & Ranking**
- `is_recommended` (boolean) - Recommended quote indicator (lowest premium)
- `recommendation_note` (text, nullable) - Custom recommendation reason
- `ranking` (integer) - Premium ranking (1 = cheapest)

**Coverage Details**
- `benefits` (text, nullable) - Coverage benefits description
- `exclusions` (text, nullable) - Policy exclusions description

### Relationships

```php
// Company quote belongs to quotation
public function quotation(): BelongsTo
{
    return $this->belongsTo(Quotation::class);
}

// Company quote belongs to insurance company
public function insuranceCompany(): BelongsTo
{
    return $this->belongsTo(InsuranceCompany::class);
}
```

### Helper Methods

**Savings Calculation**:
```php
public function calculateSavings(?QuotationCompany $quotationCompany = null): float
{
    if (!$quotationCompany instanceof \App\Models\QuotationCompany) return 0;
    return $quotationCompany->final_premium - $this->final_premium;
}

// Usage: Calculate savings vs another quote
$savings = $bestQuote->calculateSavings($expensiveQuote); // ₹5,400
```

**Formatted Premium**:
```php
public function getFormattedPremium(): string
{
    return '₹ ' . number_format($this->final_premium, 0);
}

// Usage: $quote->getFormattedPremium() → "₹ 12,450"
```

## QuotationService

**File**: `app/Services/QuotationService.php`

### Core Methods

#### Create Quotation with Company Quotes

```php
public function createQuotation(array $data): Quotation
```

**Purpose**: Create quotation with IDV calculation and optional company quotes

**Process**:
1. Calculate total IDV from 5 components
2. Extract company data from `$data['companies']` array
3. Create Quotation record within transaction
4. Create company quotes if provided
5. Dispatch QuotationGenerated event
6. Return created quotation

**Usage**:
```php
$data = [
    'customer_id' => 123,
    'vehicle_number' => 'GJ01AB1234',
    'make_model_variant' => 'Maruti Suzuki Swift VXI',
    'manufacturing_year' => 2020,
    'fuel_type' => 'Petrol',
    'cc' => 1197,
    'idv_vehicle' => 450000,
    'idv_trailer' => 0,
    'idv_cng_lpg_kit' => 0,
    'idv_electrical_accessories' => 5000,
    'idv_non_electrical_accessories' => 3000,
    // total_idv calculated automatically → 458000
    'policy_type' => 'Comprehensive',
    'policy_tenure_years' => 1,
    'addon_covers' => ['Zero Depreciation', 'Engine Protection'],
    'mobile_number' => '9876543210',
    'whatsapp_number' => '9876543210',
    'email' => 'customer@example.com',
    'status' => 'Draft'
];

$quotation = $quotationService->createQuotation($data);
```

#### Generate Company Quotes (Auto)

```php
public function generateCompanyQuotes(Quotation $quotation): void
```

**Purpose**: Auto-generate quotes from up to 5 active insurance companies

**Process**:
1. Fetch up to 5 active insurance companies
2. Generate quote for each company with premium calculations
3. Set rankings by final premium (lowest to highest)
4. Mark cheapest quote as recommended

**Premium Calculation Formula**:
```
Basic OD Premium = (IDV × OD Rate% based on vehicle age) × Company Factor
CNG/LPG Premium = (CNG Kit IDV × 5%) × Company Factor
Total OD Premium = Basic OD + CNG/LPG
Addon Premium = Sum of all selected addons
Net Premium = Total OD + Addon Premium
SGST = Net Premium × 9%
CGST = Net Premium × 9%
Total Premium = Net Premium + SGST + CGST
Final Premium = Total Premium + Roadside Assistance (₹136.88)
```

**OD Rate by Vehicle Age**:
- 0-1 year: 1.2%
- 2-3 years: 1.8%
- 4-5 years: 2.4%
- 5+ years: 3.0%

**Company Rating Factors**:
- TATA AIG: 1.0 (baseline)
- HDFC ERGO: 0.95 (5% cheaper)
- ICICI Lombard: 1.05 (5% costlier)
- Bajaj Allianz: 0.98 (2% cheaper)
- Reliance General: 0.92 (8% cheaper)
- Default: 1.0

**Addon Rates** (as % of IDV or fixed):
- Zero Depreciation: 0.4% of IDV
- Engine Protection: 0.1% of IDV
- NCB Protection: 0.05% of IDV
- Invoice Protection: 0.23% of IDV
- Tyre Protection: 0.18% of IDV
- Consumables: 0.06% of IDV
- Road Side Assistance: ₹180 (fixed)
- Key Replacement: ₹425 (fixed)
- Personal Accident: ₹450 (fixed)

**Usage**:
```php
$quotationService->generateCompanyQuotes($quotation);

// Result: Creates 5 QuotationCompany records with:
// - TATA AIG: ₹12,450 (Rank 3)
// - HDFC ERGO: ₹11,828 (Rank 1) ⭐ Recommended
// - ICICI Lombard: ₹13,073 (Rank 5)
// - Bajaj Allianz: ₹12,201 (Rank 2)
// - Reliance General: ₹11,454 (Rank 4)
```

#### Generate Quotes for Selected Companies

```php
public function generateQuotesForSelectedCompanies(Quotation $quotation, array $companyIds): void
```

**Purpose**: Generate quotes for specific insurance companies only

**Usage**:
```php
// Customer wants quotes from specific companies only
$companyIds = [1, 3, 5]; // TATA AIG, ICICI Lombard, Reliance General
$quotationService->generateQuotesForSelectedCompanies($quotation, $companyIds);
```

#### Create Manual Company Quotes

```php
public function createManualCompanyQuotes(Quotation $quotation, array $companies): void
```

**Purpose**: Create company quotes from manually entered premium data (useful for offline quotes)

**Process**:
1. Process each company data array
2. Calculate total addon premium from breakdown
3. Detect and skip exact duplicates
4. Create QuotationCompany records
5. Set rankings by final premium

**Usage**:
```php
$companies = [
    [
        'insurance_company_id' => 1,
        'quote_number' => 'TATA/2025/001234',
        'basic_od_premium' => 5500,
        'tp_premium' => 3200,
        'cng_lpg_premium' => 0,
        'total_od_premium' => 5500,
        'addon_covers_breakdown' => [
            'Zero Depreciation' => ['price' => 1800, 'note' => ''],
            'Engine Protection' => ['price' => 450, 'note' => 'Covers engine damage']
        ],
        'total_addon_premium' => 2250,
        'net_premium' => 7750,
        'sgst_amount' => 697.50,
        'cgst_amount' => 697.50,
        'total_premium' => 9145,
        'roadside_assistance' => 136.88,
        'final_premium' => 9281.88,
        'is_recommended' => false,
        'ranking' => 1
    ],
    // ... more companies
];

$quotationService->createManualCompanyQuotes($quotation, $companies);
```

#### Send Quotation via WhatsApp

```php
public function sendQuotationViaWhatsApp(Quotation $quotation): void
```

**Purpose**: Generate PDF and send quotation to customer via WhatsApp

**Process**:
1. Generate WhatsApp message with quote comparison
2. Generate PDF comparison using PdfGenerationService
3. Send WhatsApp message with PDF attachment using WhatsAppApiTrait
4. Update quotation status to "Sent" with sent_at timestamp
5. Clean up temporary PDF file
6. Log all steps with detailed context

**WhatsApp Message Format**:
```
🚗 *Insurance Quotation*

Dear *{Customer Name}*,

Your insurance quotation is ready! We have compared *5 insurance companies* for you.

🚙 *Vehicle Details:*
• Vehicle: *Maruti Suzuki Swift VXI*
• Registration: *GJ01AB1234*
• IDV: *₹4,58,000*
• Policy: *Comprehensive* - 1 Year(s)

💰 *Best Premium:*
• *HDFC ERGO*
• Premium: *₹11,828*

📊 *Premium Comparison:*
⭐ *HDFC ERGO*: ₹11,828 _(Recommended)_
2. *Bajaj Allianz*: ₹12,201
3. *TATA AIG*: ₹12,450
4. *Reliance General*: ₹11,454
5. *ICICI Lombard*: ₹13,073

💵 *You can save up to ₹1,619*

📎 *Detailed PDF comparison attached*

📞 For any queries or to proceed with purchase:

Best regards,
{Advisor Name}
{Company Website}
{Company Title}

"{Company Tagline}"
```

**Usage**:
```php
$quotationService->sendQuotationViaWhatsApp($quotation);

// Logs:
// - Starting WhatsApp send (quotation_id, customer, number)
// - Template fetch (quotation_ready)
// - PDF generation
// - WhatsApp API call
// - Success/failure with detailed context
// - PDF cleanup
```

#### Send Quotation via Email

```php
public function sendQuotationViaEmail(Quotation $quotation): void
```

**Purpose**: Send quotation comparison PDF via email

**Process**:
1. Generate PDF using PdfGenerationService
2. Send email using EmailService with notification template
3. Update status to "Sent" if not already sent
4. Clean up temporary PDF
5. Log all operations

**Usage**:
```php
$quotationService->sendQuotationViaEmail($quotation);
```

#### Generate PDF for Download

```php
public function generatePdf(Quotation $quotation)
```

**Purpose**: Generate quotation comparison PDF for browser download/view

**Returns**: Response object with PDF stream

**Usage**:
```php
// In controller
public function downloadPdf(Quotation $quotation)
{
    return $this->quotationService->generatePdf($quotation);
}
```

#### Update Quotation with Companies

```php
public function updateQuotationWithCompanies(Quotation $quotation, array $data): void
```

**Purpose**: Update quotation and regenerate all company quotes

**Process**:
1. Calculate new total IDV
2. Update quotation master data
3. Delete all existing company quotes
4. Create new company quotes from updated data
5. Re-rank quotes

**Usage**:
```php
// When vehicle IDV or details change
$data = [
    'idv_vehicle' => 460000, // Increased from 450000
    'addon_covers' => ['Zero Depreciation', 'Engine Protection', 'NCB Protection'],
    'companies' => [...] // New company quote data
];

$quotationService->updateQuotationWithCompanies($quotation, $data);
```

#### Delete Quotation

```php
public function deleteQuotation(Quotation $quotation): bool
```

**Purpose**: Delete quotation with cascade to company quotes (transaction-safe)

**Usage**:
```php
$quotationService->deleteQuotation($quotation);
// Deletes quotation + all company quotes + activity logs
```

#### Get Quotation Form Data

```php
public function getQuotationFormData(): array
```

**Purpose**: Fetch all reference data for quotation form

**Returns**:
```php
[
    'customers' => Collection, // Active customers ordered by name
    'insuranceCompanies' => Collection, // Active companies ordered by name
    'addonCovers' => Collection // Available addon covers ordered
]
```

## QuotationController

**File**: `app/Http/Controllers/QuotationController.php`

### Key Routes

| Method | Route | Action | Description |
|--------|-------|--------|-------------|
| GET | `/quotations` | `index()` | List all quotations with filters |
| GET | `/quotations/create` | `create()` | Show quotation creation form |
| POST | `/quotations` | `store()` | Create new quotation |
| GET | `/quotations/{id}` | `show()` | View quotation details |
| GET | `/quotations/{id}/edit` | `edit()` | Edit quotation form |
| PUT | `/quotations/{id}` | `update()` | Update quotation |
| DELETE | `/quotations/{id}` | `destroy()` | Delete quotation |
| POST | `/quotations/{id}/generate-quotes` | `generateQuotes()` | Generate company quotes |
| POST | `/quotations/{id}/send-whatsapp` | `sendToWhatsApp()` | Send via WhatsApp |
| POST | `/quotations/{id}/send-email` | `sendToEmail()` | Send via email |
| GET | `/quotations/{id}/download-pdf` | `downloadPdf()` | Download PDF |
| GET | `/quotations/export` | `export()` | Export quotations to Excel/CSV |

### AJAX Autocomplete Support

**Endpoint**: `/quotations/search` (for Select2 integration)

**Search Fields**:
- `registration_no` - Vehicle registration number
- `vehicle_make` - Make/model/variant
- `vehicle_model` - Model variant
- `customer.name` - Customer name

## Database Schema

### quotations Table

```sql
CREATE TABLE quotations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(255) NOT NULL,
    customer_id BIGINT UNSIGNED NOT NULL,
    quotation_number VARCHAR(255) UNIQUE,

    -- Vehicle details
    vehicle_number VARCHAR(255),
    make_model_variant VARCHAR(255),
    manufacturing_year INTEGER,
    fuel_type VARCHAR(50),
    cc INTEGER,

    -- IDV breakdown
    idv_vehicle DECIMAL(10,2) DEFAULT 0,
    idv_trailer DECIMAL(10,2) DEFAULT 0,
    idv_cng_lpg_kit DECIMAL(10,2) DEFAULT 0,
    idv_electrical_accessories DECIMAL(10,2) DEFAULT 0,
    idv_non_electrical_accessories DECIMAL(10,2) DEFAULT 0,
    total_idv DECIMAL(10,2) DEFAULT 0,

    -- Policy configuration
    policy_type VARCHAR(50) DEFAULT 'Comprehensive',
    policy_tenure_years INTEGER DEFAULT 1,
    addon_covers JSON,

    -- Contact & status
    mobile_number VARCHAR(20),
    whatsapp_number VARCHAR(20),
    email VARCHAR(255),
    status VARCHAR(50) DEFAULT 'Draft',
    sent_at TIMESTAMP NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_tenant (tenant_id),
    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
```

### quotation_companies Table

```sql
CREATE TABLE quotation_companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    quotation_id BIGINT UNSIGNED NOT NULL,
    insurance_company_id BIGINT UNSIGNED NOT NULL,
    quote_number VARCHAR(255) UNIQUE,

    -- Policy & IDV
    policy_type VARCHAR(50) DEFAULT 'Comprehensive',
    policy_tenure_years INTEGER DEFAULT 1,
    idv_vehicle DECIMAL(10,2) DEFAULT 0,
    idv_trailer DECIMAL(10,2) DEFAULT 0,
    idv_cng_lpg_kit DECIMAL(10,2) DEFAULT 0,
    idv_electrical_accessories DECIMAL(10,2) DEFAULT 0,
    idv_non_electrical_accessories DECIMAL(10,2) DEFAULT 0,
    total_idv DECIMAL(10,2) DEFAULT 0,

    -- Premium breakdown
    basic_od_premium DECIMAL(10,2) DEFAULT 0,
    tp_premium DECIMAL(10,2) DEFAULT 0,
    cng_lpg_premium DECIMAL(10,2) DEFAULT 0,
    total_od_premium DECIMAL(10,2) DEFAULT 0,
    addon_covers_breakdown JSON,
    total_addon_premium DECIMAL(10,2) DEFAULT 0,
    net_premium DECIMAL(10,2) DEFAULT 0,
    sgst_amount DECIMAL(10,2) DEFAULT 0,
    cgst_amount DECIMAL(10,2) DEFAULT 0,
    total_premium DECIMAL(10,2) DEFAULT 0,
    roadside_assistance DECIMAL(10,2) DEFAULT 0,
    final_premium DECIMAL(10,2) DEFAULT 0,

    -- Recommendation
    is_recommended BOOLEAN DEFAULT FALSE,
    recommendation_note TEXT,
    ranking INTEGER DEFAULT 1,

    -- Coverage
    benefits TEXT,
    exclusions TEXT,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_quotation (quotation_id),
    INDEX idx_company (insurance_company_id),
    INDEX idx_ranking (ranking),
    FOREIGN KEY (quotation_id) REFERENCES quotations(id) ON DELETE CASCADE,
    FOREIGN KEY (insurance_company_id) REFERENCES insurance_companies(id) ON DELETE CASCADE
);
```

## Usage Examples

### Example 1: Create Quotation with Auto-Generated Quotes

```php
use App\Services\QuotationService;

$quotationService = app(QuotationService::class);

// Step 1: Create quotation
$data = [
    'customer_id' => 123,
    'vehicle_number' => 'MH02AB1234',
    'make_model_variant' => 'Honda City ZX CVT',
    'manufacturing_year' => 2021,
    'fuel_type' => 'Petrol',
    'cc' => 1498,
    'idv_vehicle' => 750000,
    'idv_electrical_accessories' => 10000,
    'idv_non_electrical_accessories' => 5000,
    // total_idv = 765000 (auto-calculated)
    'policy_type' => 'Comprehensive',
    'policy_tenure_years' => 1,
    'addon_covers' => [
        'Zero Depreciation',
        'Engine Protection',
        'NCB Protection',
        'Road Side Assistance'
    ],
    'mobile_number' => '9876543210',
    'whatsapp_number' => '9876543210',
    'status' => 'Draft'
];

$quotation = $quotationService->createQuotation($data);

// Step 2: Generate company quotes automatically
$quotationService->generateCompanyQuotes($quotation);

// Result: Quotation with 5 company quotes ranked by premium
echo $quotation->getQuoteReference(); // "QT/25/00000045"
echo $quotation->quotationCompanies()->count(); // 5
echo $quotation->bestQuote()->insuranceCompany->name; // "Reliance General"
echo $quotation->bestQuote()->getFormattedPremium(); // "₹ 18,234"
```

### Example 2: Generate Selected Company Quotes

```php
// Create quotation
$quotation = $quotationService->createQuotation($data);

// Customer wants quotes from specific companies only
$selectedCompanies = [1, 3, 5]; // TATA AIG, ICICI Lombard, Reliance General

$quotationService->generateQuotesForSelectedCompanies($quotation, $selectedCompanies);

// Result: Only 3 company quotes created
$quotes = $quotation->quotationCompanies()->orderBy('ranking')->get();

foreach ($quotes as $quote) {
    echo "{$quote->ranking}. {$quote->insuranceCompany->name}: {$quote->getFormattedPremium()}\n";
}
// Output:
// 1. Reliance General: ₹ 18,234
// 2. TATA AIG: ₹ 19,580
// 3. ICICI Lombard: ₹ 20,559
```

### Example 3: Create Manual Company Quotes

```php
// Useful when entering offline quotes received from insurance agents
$quotation = $quotationService->createQuotation($basicData);

$manualQuotes = [
    [
        'insurance_company_id' => 1,
        'quote_number' => 'TATA/2025/Q123456',
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'total_idv' => 765000,
        'basic_od_premium' => 9180, // 1.2% of IDV
        'tp_premium' => 2400,
        'total_od_premium' => 9180,
        'addon_covers_breakdown' => [
            'Zero Depreciation' => ['price' => 3060, 'note' => ''],
            'Engine Protection' => ['price' => 765, 'note' => 'Up to ₹50,000 coverage'],
            'NCB Protection' => ['price' => 383, 'note' => ''],
            'Road Side Assistance' => ['price' => 180, 'note' => '24/7 service']
        ],
        'total_addon_premium' => 4388,
        'net_premium' => 13568, // 9180 + 4388
        'sgst_amount' => 1221.12, // 9%
        'cgst_amount' => 1221.12, // 9%
        'total_premium' => 16010.24,
        'roadside_assistance' => 136.88,
        'final_premium' => 16147.12,
        'is_recommended' => false,
        'benefits' => '24/7 customer support, cashless garages nationwide',
        'exclusions' => 'Pre-existing damage, wear and tear'
    ],
    [
        'insurance_company_id' => 2,
        'quote_number' => 'HDFC/2025/987654',
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'total_idv' => 765000,
        'basic_od_premium' => 8721, // Lower due to company factor
        'tp_premium' => 2400,
        'total_od_premium' => 8721,
        'addon_covers_breakdown' => [
            'Zero Depreciation' => ['price' => 2907, 'note' => ''],
            'Engine Protection' => ['price' => 727, 'note' => ''],
            'NCB Protection' => ['price' => 364, 'note' => ''],
            'Road Side Assistance' => ['price' => 171, 'note' => '']
        ],
        'total_addon_premium' => 4169,
        'net_premium' => 12890,
        'sgst_amount' => 1160.10,
        'cgst_amount' => 1160.10,
        'total_premium' => 15210.20,
        'roadside_assistance' => 136.88,
        'final_premium' => 15347.08,
        'is_recommended' => true,
        'recommendation_note' => 'Best premium with comprehensive coverage'
    ]
];

$quotationService->createManualCompanyQuotes($quotation, $manualQuotes);

// Rankings set automatically: HDFC (Rank 1), TATA (Rank 2)
```

### Example 4: Send Quotation via WhatsApp

```php
$quotation = Quotation::find(45);

// Ensure company quotes exist
if ($quotation->quotationCompanies()->count() === 0) {
    $quotationService->generateCompanyQuotes($quotation);
}

// Send via WhatsApp with PDF
try {
    $quotationService->sendQuotationViaWhatsApp($quotation);

    echo "Quotation sent successfully!\n";
    echo "Status: {$quotation->fresh()->status}\n"; // "Sent"
    echo "Sent at: {$quotation->fresh()->sent_at}\n"; // "2025-11-06 14:30:15"

} catch (\Exception $e) {
    Log::error('WhatsApp send failed', ['error' => $e->getMessage()]);
    echo "Failed to send: {$e->getMessage()}";
}
```

### Example 5: Compare Company Quotes

```php
$quotation = Quotation::with('quotationCompanies.insuranceCompany')->find(45);

// Get all quotes sorted by ranking
$quotes = $quotation->quotationCompanies()->orderBy('ranking')->get();

echo "Quotation: {$quotation->getQuoteReference()}\n";
echo "Vehicle: {$quotation->make_model_variant}\n";
echo "IDV: ₹" . number_format($quotation->total_idv) . "\n\n";

echo "Company Comparison:\n";
echo str_repeat('-', 60) . "\n";

foreach ($quotes as $quote) {
    $icon = $quote->is_recommended ? '⭐' : $quote->ranking;
    $tag = $quote->is_recommended ? ' (RECOMMENDED)' : '';

    echo "{$icon}. {$quote->insuranceCompany->name}{$tag}\n";
    echo "   Premium: {$quote->getFormattedPremium()}\n";
    echo "   OD: ₹" . number_format($quote->total_od_premium);
    echo " + Addons: ₹" . number_format($quote->total_addon_premium);
    echo " + GST: ₹" . number_format($quote->sgst_amount + $quote->cgst_amount) . "\n";

    if ($quote->recommendation_note) {
        echo "   Note: {$quote->recommendation_note}\n";
    }
    echo "\n";
}

// Calculate savings
$bestQuote = $quotes->first();
$highestQuote = $quotes->last();
$savings = $bestQuote->calculateSavings($highestQuote);

echo "Potential Savings: ₹" . number_format($savings) . "\n";

// Output:
// Quotation: QT/25/00000045
// Vehicle: Honda City ZX CVT
// IDV: ₹765,000
//
// Company Comparison:
// ------------------------------------------------------------
// ⭐. HDFC ERGO (RECOMMENDED)
//    Premium: ₹ 15,347
//    OD: ₹8,721 + Addons: ₹4,169 + GST: ₹2,320
//    Note: Best premium with comprehensive coverage
//
// 2. Reliance General
//    Premium: ₹ 15,503
//    OD: ₹8,438 + Addons: ₹4,438 + GST: ₹2,359
//
// 3. Bajaj Allianz
//    Premium: ₹ 15,840
//    OD: ₹8,996 + Addons: ₹4,340 + GST: ₹2,400
//
// 4. TATA AIG
//    Premium: ₹ 16,147
//    OD: ₹9,180 + Addons: ₹4,388 + GST: ₹2,442
//
// 5. ICICI Lombard
//    Premium: ₹ 16,955
//    OD: ₹9,639 + Addons: ₹4,606 + GST: ₹2,564
//
// Potential Savings: ₹1,608
```

### Example 6: Update Quotation and Regenerate Quotes

```php
// Customer wants to update vehicle IDV and addons
$quotation = Quotation::find(45);

$updatedData = [
    'idv_vehicle' => 780000, // Increased from 750000
    'idv_electrical_accessories' => 15000, // Increased from 10000
    'addon_covers' => [
        'Zero Depreciation',
        'Engine Protection',
        'NCB Protection',
        'Road Side Assistance',
        'Invoice Protection', // New addon
        'Personal Accident'   // New addon
    ],
    'companies' => [] // Empty to regenerate automatically
];

// Update and regenerate quotes
$quotationService->updateQuotationWithCompanies($quotation, $updatedData);

// Regenerate company quotes with new data
$quotationService->generateCompanyQuotes($quotation->fresh());

echo "Updated IDV: ₹" . number_format($quotation->fresh()->total_idv) . "\n"; // ₹795,000
echo "Total Addons: " . count($quotation->fresh()->addon_covers) . "\n"; // 6
echo "New Best Premium: {$quotation->fresh()->bestQuote()->getFormattedPremium()}\n"; // Higher due to increased IDV
```

### Example 7: Export Quotations

```php
// In controller
public function export(Request $request)
{
    $quotations = $quotationService->getQuotations($request);

    return Excel::download(
        new QuotationsExport($quotations),
        'quotations_' . date('Y-m-d') . '.xlsx'
    );
}

// Export configuration (app/Exports/QuotationsExport.php)
public function map($quotation): array
{
    return [
        $quotation->id,
        $quotation->getQuoteReference(),
        $quotation->customer->name,
        $quotation->vehicle_number,
        $quotation->make_model_variant,
        '₹' . number_format($quotation->total_idv),
        $quotation->policy_type,
        $quotation->quotationCompanies()->count(),
        $quotation->bestQuote() ? $quotation->bestQuote()->getFormattedPremium() : 'N/A',
        $quotation->status,
        formatDateForUi($quotation->created_at)
    ];
}
```

### Example 8: Quote-to-Policy Conversion

```php
// Customer selects a quote to purchase
$quotation = Quotation::find(45);
$selectedQuote = $quotation->quotationCompanies()->where('id', 12)->first();

// Convert to policy (in CustomerInsuranceService)
$policyData = [
    'customer_id' => $quotation->customer_id,
    'quotation_id' => $quotation->id,
    'insurance_company_id' => $selectedQuote->insurance_company_id,

    // Copy vehicle details
    'vehicle_number' => $quotation->vehicle_number,
    'make_model_variant' => $quotation->make_model_variant,
    'manufacturing_year' => $quotation->manufacturing_year,
    'fuel_type' => $quotation->fuel_type,
    'cc' => $quotation->cc,

    // Copy IDV breakdown
    'idv_vehicle' => $selectedQuote->idv_vehicle,
    'idv_trailer' => $selectedQuote->idv_trailer,
    'idv_cng_lpg_kit' => $selectedQuote->idv_cng_lpg_kit,
    'idv_electrical_accessories' => $selectedQuote->idv_electrical_accessories,
    'idv_non_electrical_accessories' => $selectedQuote->idv_non_electrical_accessories,
    'total_idv' => $selectedQuote->total_idv,

    // Copy premium breakdown
    'od_premium' => $selectedQuote->total_od_premium,
    'tp_premium' => $selectedQuote->tp_premium,
    'net_premium' => $selectedQuote->net_premium,
    'gst' => $selectedQuote->sgst_amount + $selectedQuote->cgst_amount,
    'final_premium' => $selectedQuote->final_premium,

    // Policy dates
    'issue_date' => now(),
    'start_date' => now(),
    'end_date' => now()->addYear($selectedQuote->policy_tenure_years),
    'policy_type' => $selectedQuote->policy_type,

    // Add policy number and other required fields
    'policy_number' => generatePolicyNumber(),
    'status' => 'Active'
];

$policy = CustomerInsurance::create($policyData);

// Update quotation status
$quotation->update(['status' => 'Converted']);

echo "Policy created: {$policy->policy_number}\n";
echo "Quotation status: {$quotation->fresh()->status}\n"; // "Converted"
```

## Premium Calculation Deep Dive

### Complete Calculation Example

**Vehicle**: Maruti Suzuki Swift VXI (2020 model - 5 years old)
**Company**: HDFC ERGO (Factor: 0.95)

**Step 1: IDV Calculation**
```
IDV Vehicle:                ₹4,50,000
IDV Electrical Accessories: ₹    5,000
IDV Non-Electrical:         ₹    3,000
                           -----------
Total IDV:                  ₹4,58,000
```

**Step 2: Basic OD Premium**
```
Vehicle Age: 5 years
OD Rate: 2.4% (for 4-5 year old vehicles)
Company Factor: 0.95 (HDFC ERGO)

Basic OD = (458000 × 2.4 / 100) × 0.95
         = 10,992 × 0.95
         = ₹10,442.40
```

**Step 3: Addon Premiums**
```
Zero Depreciation: (458000 × 0.4 / 100) × 0.95 = ₹1,740.40
Engine Protection:  (458000 × 0.1 / 100) × 0.95 = ₹   435.10
NCB Protection:     (458000 × 0.05 / 100) × 0.95 = ₹   217.55
Road Side Assist:   180 × 0.95 = ₹   171.00
                                                  -----------
Total Addon Premium:                              ₹2,564.05
```

**Step 4: Net Premium**
```
Total OD Premium:    ₹10,442.40
Total Addon Premium: ₹ 2,564.05
                    -----------
Net Premium:         ₹13,006.45
```

**Step 5: GST Calculation**
```
SGST (9%):  13,006.45 × 0.09 = ₹1,170.58
CGST (9%):  13,006.45 × 0.09 = ₹1,170.58
                               ----------
Total GST:                     ₹2,341.16
```

**Step 6: Final Premium**
```
Net Premium:         ₹13,006.45
GST:                 ₹ 2,341.16
                    -----------
Total Premium:       ₹15,347.61
Roadside Assistance: ₹   136.88
                    -----------
FINAL PREMIUM:       ₹15,484.49
```

## Status Workflow

**Quotation Status Flow**:
```
Draft → Sent → Approved → Converted
  ↓       ↓       ↓
  ↓       ↓       └─→ Expired
  ↓       └─→ Expired
  └─→ Expired
```

**Status Descriptions**:
- **Draft**: Quotation created, not yet sent to customer
- **Sent**: Quotation sent via WhatsApp/Email with PDF
- **Approved**: Customer approved/selected a specific quote
- **Converted**: Quotation converted to active policy
- **Expired**: Quotation validity expired (typically 30 days)

## Related Documentation

- **[POLICY_MANAGEMENT.md](POLICY_MANAGEMENT.md)** - Policy management and quote-to-policy conversion
- **[CUSTOMER_MANAGEMENT.md](CUSTOMER_MANAGEMENT.md)** - Customer data and relationships
- **[PDF_GENERATION.md](../features/PDF_GENERATION.md)** - PDF quotation generation
- **[NOTIFICATION_SYSTEM.md](../features/NOTIFICATION_SYSTEM.md)** - WhatsApp/Email delivery
- **[COMMISSION_TRACKING.md](../features/COMMISSION_TRACKING.md)** - Commission calculations

## API Integration Points

### Events Dispatched

**QuotationGenerated** - Fired after quotation creation
```php
QuotationGenerated::dispatch($quotation);
// Listeners: Send notification to sales team, create activity log
```

### Traits Used

**WhatsAppApiTrait** - WhatsApp message sending
```php
$this->logAndSendWhatsAppWithAttachment($quotation, $message, $number, $pdfPath, $metadata);
```

**LogsNotificationsTrait** - Notification logging
```php
// Automatically logs all WhatsApp/Email notifications with NotificationLog records
```

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
