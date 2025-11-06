# Master Data Management

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Centralized master data tables for reference data management across the insurance SaaS platform. All master data models use soft deletes, activity logging, and status-based filtering.

### Key Features

- **Insurance Companies**: Manage insurance provider data (ICICI Lombard, HDFC ERGO, Bajaj Allianz, etc.)
- **Policy Types**: Define policy categories (Motor Comprehensive, Health, Life, etc.)
- **Addon Covers**: Configure available addon covers with smart ordering system
- **Fuel Types**: Vehicle fuel type options (Petrol, Diesel, CNG, Electric, Hybrid)
- **Status Management**: Active/Inactive (1/0) toggles for all master data
- **Soft Deletes**: Preserve historical data integrity
- **Activity Logging**: Full audit trail via Spatie Activity Log
- **Tenant-Scoped**: All master data tenant-specific (multi-tenancy support)

## InsuranceCompany Model

**File**: `app/Models/InsuranceCompany.php`

### Purpose

Stores insurance provider information for quotation generation, policy management, and commission tracking.

### Attributes

- `name` (string) - Company name (e.g., "ICICI Lombard", "HDFC ERGO")
- `email` (string, nullable) - Company contact email
- `mobile_number` (string, nullable) - Company contact number
- `status` (integer) - Active (1) / Inactive (0)
- `created_at`, `updated_at`, `deleted_at` - Soft delete timestamps
- `created_by`, `updated_by`, `deleted_by` - Audit trail

### Relationships

```php
public function customerInsurances(): HasMany
{
    return $this->hasMany(CustomerInsurance::class, 'insurance_company_id');
}
```

### Common Insurance Companies

| Name | Type | Common Products |
|------|------|-----------------|
| ICICI Lombard | General | Motor, Health, Travel |
| HDFC ERGO | General | Motor, Health, Home |
| Bajaj Allianz | General | Motor, Health, Travel |
| TATA AIG | General | Motor, Health, Travel |
| Reliance General | General | Motor, Health, Property |
| HDFC Life | Life | Term, Endowment, ULIP |
| SBI Life | Life | Term, Endowment, Pension |
| LIC | Life | Term, Endowment, Pension |

### Usage

```php
// Get active insurance companies
$companies = InsuranceCompany::where('status', 1)->orderBy('name')->get();

// Create insurance company
InsuranceCompany::create([
    'name' => 'ICICI Lombard',
    'email' => 'support@icicilombard.com',
    'mobile_number' => '1800-266-7766',
    'status' => 1,
]);

// Get company's policies count
$company = InsuranceCompany::find(1);
$policyCount = $company->customerInsurances()->count();
```

## PolicyType Model

**File**: `app/Models/PolicyType.php`

### Purpose

Define policy categories for insurance products. Used in quotation and policy management to categorize insurance offerings.

### Attributes

- `name` (string) - Policy type name
- `status` (integer) - Active (1) / Inactive (0)
- `is_life_insurance_policies` (boolean) - Indicates life insurance vs general insurance
- `is_vehicle` (boolean) - Indicates vehicle/motor insurance
- `description` (text, nullable) - Policy type description
- `created_at`, `updated_at`, `deleted_at` - Soft delete timestamps
- `created_by`, `updated_by`, `deleted_by` - Audit trail

### Relationships

```php
public function customerInsurances(): HasMany
{
    return $this->hasMany(CustomerInsurance::class, 'policy_type_id');
}
```

### Common Policy Types

**Motor/Vehicle Insurance**:
- Motor Comprehensive (Own Damage + Third Party)
- Motor Third Party Only
- Motor Own Damage Only
- Two Wheeler Comprehensive
- Commercial Vehicle

**Health Insurance**:
- Individual Health
- Family Floater Health
- Senior Citizen Health
- Critical Illness
- Personal Accident

**Life Insurance**:
- Term Life
- Endowment
- ULIP (Unit Linked Insurance Plan)
- Pension/Retirement
- Money Back

**Other**:
- Home Insurance
- Travel Insurance
- Fire Insurance
- Marine Insurance

### Usage

```php
// Get active policy types
$policyTypes = PolicyType::where('status', 1)->orderBy('name')->get();

// Get only vehicle policy types
$vehiclePolicyTypes = PolicyType::where('status', 1)
    ->where('is_vehicle', true)
    ->orderBy('name')
    ->get();

// Get only life insurance types
$lifePolicyTypes = PolicyType::where('status', 1)
    ->where('is_life_insurance_policies', true)
    ->orderBy('name')
    ->get();

// Create policy type
PolicyType::create([
    'name' => 'Motor Comprehensive',
    'is_vehicle' => true,
    'is_life_insurance_policies' => false,
    'description' => 'Comprehensive motor vehicle insurance covering own damage and third party liability',
    'status' => 1,
]);

// Get policies count by type
$policyType = PolicyType::find(1);
$count = $policyType->customerInsurances()->count();
```

## AddonCover Model

**File**: `app/Models/AddonCover.php`

### Purpose

Manage available addon covers for insurance policies with intelligent ordering system. Used in quotation and policy premium calculations.

### Attributes

- `name` (string) - Addon cover name
- `description` (text, nullable) - Cover description
- `order_no` (integer) - Display order (smart ordering with auto-assignment and conflict resolution)
- `status` (boolean) - Active (true) / Inactive (false)
- `created_at`, `updated_at`, `deleted_at` - Soft delete timestamps
- `created_by`, `updated_by`, `deleted_by` - Audit trail

### Smart Ordering System

**Auto-Assignment**: `order_no = 0` → Auto-assign next available number

**Conflict Resolution**: Duplicate order numbers automatically shift existing records

**Order Update Logic**:
- Moving to higher position → Close gap from original position
- Moving to lower position → Shift others up
- Inserting at specific position → Shift existing records down

```php
protected static function booted()
{
    static::saving(function($addonCover) {
        // Auto-assign if order_no = 0
        if ($addonCover->order_no == 0) {
            $addonCover->order_no = self::getNextAvailableOrder();
            return;
        }

        // Handle duplicate order numbers
        $existingCover = static::where('order_no', $addonCover->order_no)
            ->where('id', '!=', $addonCover->id ?? 0)
            ->first();

        if ($existingCover) {
            // Shift others and insert at desired position
            static::where('order_no', '>=', $addonCover->order_no)
                ->where('id', '!=', $addonCover->id ?? 0)
                ->increment('order_no');
        }
    });
}
```

### Helper Methods

```php
// Get ordered addon covers (active only)
public static function getOrdered($status = 1)
{
    return static::where('status', $status)
        ->orderBy('order_no', 'asc')
        ->orderBy('name', 'asc')
        ->get();
}

// Get next available order number
private static function getNextAvailableOrder(): int
{
    $lastOrder = static::max('order_no') ?? 0;
    return $lastOrder + 1;
}
```

### Common Addon Covers

| Name | Description | Typical Premium | Order |
|------|-------------|-----------------|-------|
| Zero Depreciation | No depreciation on claim settlement | 0.4% of IDV | 1 |
| Engine Protection | Engine and gearbox damage coverage | 0.1% of IDV | 2 |
| NCB Protection | Protects No Claim Bonus on claim | 0.05% of IDV | 3 |
| Road Side Assistance | 24/7 roadside assistance services | ₹180 (fixed) | 4 |
| Invoice Protection | Return to invoice value on total loss | 0.23% of IDV | 5 |
| Key Replacement | Lost/stolen key replacement | ₹425 (fixed) | 6 |
| Personal Accident | Owner-driver personal accident cover | ₹450 (fixed) | 7 |
| Tyre Protection | Tyre damage coverage | 0.18% of IDV | 8 |
| Consumables | Oil, nuts, bolts coverage | 0.06% of IDV | 9 |

### Usage

```php
// Get ordered addon covers for display
$addonCovers = AddonCover::getOrdered(1);

foreach ($addonCovers as $addon) {
    echo "{$addon->order_no}. {$addon->name}\n";
}

// Create addon cover with auto-ordering
AddonCover::create([
    'name' => 'Zero Depreciation',
    'description' => 'No depreciation deduction on claim settlement',
    'order_no' => 0, // Auto-assign next available
    'status' => true,
]);

// Create addon cover at specific position
AddonCover::create([
    'name' => 'Engine Protection',
    'description' => 'Covers engine and gearbox damage',
    'order_no' => 2, // Insert at position 2, shifts others down
    'status' => true,
]);

// Update order (smart ordering handles conflicts)
$addon = AddonCover::find(5);
$addon->update(['order_no' => 1]); // Moves to position 1, shifts others

// Reorder multiple addons
$addons = AddonCover::getOrdered();
foreach ($addons as $index => $addon) {
    $addon->update(['order_no' => $index + 1]);
}
```

## FuelType Model

**File**: `app/Models/FuelType.php`

### Purpose

Store fuel type options for vehicle insurance policies. Used in policy and quotation forms.

### Attributes

- `name` (string, nullable) - Fuel type name
- `status` (integer) - Active (1) / Inactive (0)
- `created_at`, `updated_at`, `deleted_at` - Soft delete timestamps
- `created_by`, `updated_by`, `deleted_by` - Audit trail

### Relationships

```php
public function customerInsurances(): HasMany
{
    return $this->hasMany(CustomerInsurance::class, 'fuel_type_id');
}
```

### Common Fuel Types

| Name | Applicable To | CNG/LPG Premium |
|------|---------------|-----------------|
| Petrol | Cars, Two Wheelers | Not applicable |
| Diesel | Cars, Commercial Vehicles | Not applicable |
| CNG | Cars, Commercial Vehicles | 5% of CNG kit IDV |
| Electric | Electric Vehicles | Not applicable |
| Hybrid | Hybrid Vehicles | 5% of CNG/LPG kit IDV (if applicable) |
| LPG | Cars | 5% of LPG kit IDV |

### Usage

```php
// Get active fuel types
$fuelTypes = FuelType::where('status', 1)->orderBy('name')->get();

// Create fuel type
FuelType::create([
    'name' => 'Petrol',
    'status' => 1,
]);

// Get policies count by fuel type
$fuelType = FuelType::find(1);
$count = $fuelType->customerInsurances()->count();

// Check if fuel type requires CNG/LPG premium
$requiresCngLpgPremium = in_array($quotation->fuel_type, ['CNG', 'Hybrid', 'LPG']);
if ($requiresCngLpgPremium && $quotation->idv_cng_lpg_kit > 0) {
    $cngLpgPremium = ($quotation->idv_cng_lpg_kit * 0.05) * $companyFactor;
}
```

## Database Schemas

### insurance_companies Table

```sql
CREATE TABLE insurance_companies (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    mobile_number VARCHAR(20) NULL,
    status INTEGER DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_name (name),
    INDEX idx_status (status)
);
```

### policy_types Table

```sql
CREATE TABLE policy_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    status INTEGER DEFAULT 1,
    is_life_insurance_policies BOOLEAN DEFAULT FALSE,
    is_vehicle BOOLEAN DEFAULT FALSE,
    description TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_name (name),
    INDEX idx_status (status),
    INDEX idx_is_vehicle (is_vehicle),
    INDEX idx_is_life (is_life_insurance_policies)
);
```

### addon_covers Table

```sql
CREATE TABLE addon_covers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    order_no INTEGER DEFAULT 0,
    status BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_name (name),
    INDEX idx_status (status),
    INDEX idx_order_no (order_no),
    UNIQUE KEY unique_order_no (order_no, deleted_at)
);
```

### fuel_types Table

```sql
CREATE TABLE fuel_types (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NULL,
    status INTEGER DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_name (name),
    INDEX idx_status (status)
);
```

## Seeders

### InsuranceCompanySeeder

```php
// database/seeders/tenant/InsuranceCompanySeeder.php
public function run(): void
{
    $companies = [
        ['name' => 'ICICI Lombard', 'email' => 'support@icicilombard.com', 'mobile_number' => '1800-266-7766'],
        ['name' => 'HDFC ERGO', 'email' => 'support@hdfcergo.com', 'mobile_number' => '1800-266-0700'],
        ['name' => 'Bajaj Allianz', 'email' => 'bagichelp@bajajallianz.co.in', 'mobile_number' => '1800-209-5858'],
        ['name' => 'TATA AIG', 'email' => 'customer.care@tataaig.com', 'mobile_number' => '1800-266-7780'],
        ['name' => 'Reliance General', 'email' => 'customercare@reliancegeneral.co.in', 'mobile_number' => '1800-3009-3009'],
        ['name' => 'HDFC Life', 'email' => 'query@hdfclife.com', 'mobile_number' => '1800-266-9970'],
        ['name' => 'SBI Life', 'email' => 'customer.service@sbilife.co.in', 'mobile_number' => '1800-267-9090'],
        ['name' => 'LIC', 'email' => 'customerservice@licindia.com', 'mobile_number' => '1800-123-4000'],
    ];

    foreach ($companies as $company) {
        InsuranceCompany::updateOrCreate(
            ['name' => $company['name']],
            ['email' => $company['email'], 'mobile_number' => $company['mobile_number'], 'status' => 1]
        );
    }
}
```

### PolicyTypeSeeder

```php
// database/seeders/tenant/PolicyTypeSeeder.php
public function run(): void
{
    $policyTypes = [
        ['name' => 'Motor Comprehensive', 'is_vehicle' => true, 'is_life_insurance_policies' => false],
        ['name' => 'Motor Third Party', 'is_vehicle' => true, 'is_life_insurance_policies' => false],
        ['name' => 'Motor Own Damage', 'is_vehicle' => true, 'is_life_insurance_policies' => false],
        ['name' => 'Two Wheeler Comprehensive', 'is_vehicle' => true, 'is_life_insurance_policies' => false],
        ['name' => 'Commercial Vehicle', 'is_vehicle' => true, 'is_life_insurance_policies' => false],
        ['name' => 'Individual Health', 'is_vehicle' => false, 'is_life_insurance_policies' => false],
        ['name' => 'Family Floater Health', 'is_vehicle' => false, 'is_life_insurance_policies' => false],
        ['name' => 'Term Life', 'is_vehicle' => false, 'is_life_insurance_policies' => true],
        ['name' => 'Endowment', 'is_vehicle' => false, 'is_life_insurance_policies' => true],
        ['name' => 'ULIP', 'is_vehicle' => false, 'is_life_insurance_policies' => true],
        ['name' => 'Home Insurance', 'is_vehicle' => false, 'is_life_insurance_policies' => false],
        ['name' => 'Travel Insurance', 'is_vehicle' => false, 'is_life_insurance_policies' => false],
    ];

    foreach ($policyTypes as $type) {
        PolicyType::updateOrCreate(
            ['name' => $type['name']],
            [
                'is_vehicle' => $type['is_vehicle'],
                'is_life_insurance_policies' => $type['is_life_insurance_policies'],
                'status' => 1
            ]
        );
    }
}
```

### AddonCoverSeeder

```php
// database/seeders/tenant/AddonCoverSeeder.php
public function run(): void
{
    $addonCovers = [
        ['name' => 'Zero Depreciation', 'description' => 'No depreciation on claim settlement', 'order_no' => 1],
        ['name' => 'Engine Protection', 'description' => 'Engine and gearbox damage coverage', 'order_no' => 2],
        ['name' => 'NCB Protection', 'description' => 'Protects No Claim Bonus on claim', 'order_no' => 3],
        ['name' => 'Road Side Assistance', 'description' => '24/7 roadside assistance', 'order_no' => 4],
        ['name' => 'Invoice Protection', 'description' => 'Return to invoice value on total loss', 'order_no' => 5],
        ['name' => 'Key Replacement', 'description' => 'Lost/stolen key replacement', 'order_no' => 6],
        ['name' => 'Personal Accident', 'description' => 'Owner-driver personal accident cover', 'order_no' => 7],
        ['name' => 'Tyre Protection', 'description' => 'Tyre damage coverage', 'order_no' => 8],
        ['name' => 'Consumables', 'description' => 'Oil, nuts, bolts coverage', 'order_no' => 9],
    ];

    foreach ($addonCovers as $addon) {
        AddonCover::updateOrCreate(
            ['name' => $addon['name']],
            [
                'description' => $addon['description'],
                'order_no' => $addon['order_no'],
                'status' => true
            ]
        );
    }
}
```

### FuelTypeSeeder

```php
// database/seeders/tenant/FuelTypeSeeder.php
public function run(): void
{
    $fuelTypes = ['Petrol', 'Diesel', 'CNG', 'Electric', 'Hybrid', 'LPG'];

    foreach ($fuelTypes as $fuelType) {
        FuelType::updateOrCreate(
            ['name' => $fuelType],
            ['status' => 1]
        );
    }
}
```

## Related Documentation

- **[QUOTATION_SYSTEM.md](QUOTATION_SYSTEM.md)** - Uses insurance companies and addon covers
- **[POLICY_MANAGEMENT.md](POLICY_MANAGEMENT.md)** - Uses policy types and insurance companies
- **[MULTI_TENANCY.md](../core/MULTI_TENANCY.md)** - Master data tenant scoping
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - Complete schema reference

## Best Practices

1. **Always Use Status Checks**: Filter by `status = 1` to show only active records
2. **Soft Delete Never Hard Delete**: Preserve data integrity with soft deletes
3. **Seed Master Data**: Use seeders for consistent data across environments
4. **Order Addon Covers**: Use `AddonCover::getOrdered()` for consistent display order
5. **Cache Master Data**: Cache frequently accessed master data to reduce database queries
6. **Validate References**: Always validate foreign keys before assignment
7. **Activity Logging**: All master data changes logged automatically via Spatie Activity Log

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
