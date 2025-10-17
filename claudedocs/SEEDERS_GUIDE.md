# Database Seeders Guide

## Overview

This guide documents all database seeders for the Insurance Admin Panel application, including existing seeders, missing seeders, and proper seeding order.

## Existing Seeders

### 1. Core System Seeders

#### RoleSeeder
**File**: `database/seeders/RoleSeeder.php`

**Purpose**: Creates default roles for the application

**Data Created**:
- Admin role (guard: web)
- User role (guard: web)

**Dependencies**: None

---

#### AdminSeeder
**File**: `database/seeders/AdminSeeder.php`

**Purpose**: Creates the default admin user

**Data Created**:
```php
Email: webmonks.in@gmail.com
Password: Webmonks239#
Name: Darshan Baraiya
Mobile: 8000071314
Role: Admin (role_id: 1)
```

**Dependencies**: RoleSeeder (must run first)

---

#### UnifiedPermissionsSeeder
**File**: `database/seeders/UnifiedPermissionsSeeder.php`

**Purpose**: Creates all permissions and assigns them to roles

**Dependencies**: RoleSeeder, AdminSeeder

---

#### AppSettingPermissionsSeeder
**File**: `database/seeders/AppSettingPermissionsSeeder.php`

**Purpose**: Creates permissions specifically for app settings management

**Dependencies**: RoleSeeder

---

### 2. Master Data Seeders

#### InsuranceCompaniesSeeder
**File**: `database/seeders/InsuranceCompaniesSeeder.php`

**Purpose**: Seeds all insurance companies in India

**Records**: 20 companies

**Sample Data**:
- CARE HEALTH INSURANCE LTD
- BAJAJ ALLIANZ GIC LTD
- TATA AIG GIC LTD
- MAGMA HDI GIC LTD
- GO DIGIT GENERAL INSURANCE LTD
- RELIANCE GIC LTD
- ICICI LOMBARD GIC LTD
- THE NEW INDIA ASSURANCE CO LTD
- HDFC ERGO GIC LTD
- LIC OF INDIA
- (10 more companies...)

**Dependencies**: None

---

#### FuelTypesSeeder
**File**: `database/seeders/FuelTypesSeeder.php`

**Purpose**: Seeds vehicle fuel types

**Records**: 4 types

**Data**:
- PETROL
- DIESEL
- CNG
- EV (Electric Vehicle)

**Dependencies**: None

---

#### PolicyTypesSeeder
**File**: `database/seeders/PolicyTypesSeeder.php`

**Purpose**: Seeds policy types (fresh, renewal, etc.)

**Records**: 3 types

**Data**:
- FRESH
- ROLLOVER
- RENEWAL

**Dependencies**: None

---

#### PremiumTypesSeeder
**File**: `database/seeders/PremiumTypesSeeder.php`

**Purpose**: Seeds all types of insurance premiums

**Records**: 35 types

**Categories**:
1. **Vehicle Insurance** (is_vehicle = 1):
   - 2 WHEELER, 2W SATP
   - 4 WHEELER, 4W SATP, 4W OD ONLY
   - 3 WHEELER, 3W SATP, 3W OD ONLY
   - COMMERCIAL VEHICLE COMP, COMMERCIAL VEHICLE SATP
   - 4W BUNDLE (1OD +3TP), 2W BUNDLE (1OD + 5TP)

2. **Life Insurance** (is_life_insurance_policies = 1):
   - TERM PLAN
   - ENDOWMENT PLANS
   - ULIP PLANS
   - LIC

3. **General Insurance** (both false):
   - HEALTH INSURANCE, TOP UP HEALTH INSURANCE
   - GMC, GPA, PERSONAL ACCIDENT
   - MARINE / TRANSIT INSURANCE
   - FIRE INSURANCE, MY HOME INSURANCE
   - TRAVEL INSURANCE
   - WC INSURANCE, TRADE CREDIT INSURANCE
   - CYBER INSURANCE
   - TRANSPORTER GODOWN INSURANCE
   - CARRIER LEGAL LIABILITY
   - BHARAT SOOKSHMA UDHYAM
   - GODOWN STOCK INSURANCE
   - BURGLARY & THEFT INSURANCE

**Dependencies**: None

---

#### AddonCoversSeeder
**File**: `database/seeders/AddonCoversSeeder.php`

**Purpose**: Seeds vehicle insurance addon covers

**Records**: 9 covers

**Data** (with order_no):
1. Zero Depreciation (order: 1)
2. Engine Protection (order: 2)
3. Consumables (order: 3)
4. Key and Lock Protect (order: 3)
5. Loss of Personal Belongings (order: 4)
6. Repair of Glass, Rubber and Plastic Part (order: 5)
7. Return to Invoice (order: 6)
8. RSA (Road Side Assistance) (order: 7)
9. Tyre Protection (order: 8)

**Dependencies**: None

---

#### CommissionTypesSeeder
**File**: `database/seeders/CommissionTypesSeeder.php`

**Purpose**: Seeds commission calculation types

**Dependencies**: None

---

#### CustomerTypesSeeder
**File**: `database/seeders/CustomerTypesSeeder.php`

**Purpose**: Seeds customer types (Retail/Corporate)

**Dependencies**: None

---

#### QuotationStatusesSeeder
**File**: `database/seeders/QuotationStatusesSeeder.php`

**Purpose**: Seeds quotation workflow statuses

**Dependencies**: None

---

### 3. Application Configuration Seeders

#### AppSettingsSeeder
**File**: `database/seeders/AppSettingsSeeder.php`

**Purpose**: Seeds 70+ application settings across 8 categories

**Categories**:
1. Company Information
2. Email Configuration
3. WhatsApp Configuration
4. Notification Settings
5. Quotation Settings
6. Claim Settings
7. Customer Portal Settings
8. System Settings

**Dependencies**: None

---

### 4. Data Migration Seeders

#### EmailCleanupSeeder
**File**: `database/seeders/EmailCleanupSeeder.php`

**Purpose**: Cleans up duplicate email addresses in existing data

**Dependencies**: All master data seeders

---

#### DataMigrationSeeder
**File**: `database/seeders/DataMigrationSeeder.php`

**Purpose**: Migrates legacy data to new schema

**Dependencies**: All master data seeders, EmailCleanupSeeder

---

## Missing Seeders

The following master data tables **do NOT have seeders** and should be created:

### 1. BranchesSeeder (HIGH PRIORITY)

**File**: `database/seeders/BranchesSeeder.php`

**Purpose**: Seed default branches for the insurance agency

**Recommended Data**:
```php
- Head Office
- Mumbai Branch
- Delhi Branch
- Bangalore Branch
- (Add more based on business requirements)
```

**Table**: `branches`

**Why Important**: Every customer_insurance record requires a branch_id

---

### 2. BrokersSeeder (HIGH PRIORITY)

**File**: `database/seeders/BrokersSeeder.php`

**Purpose**: Seed default brokers

**Recommended Data**:
```php
- Direct Sales (for non-broker sales)
- Default Broker 1
- Default Broker 2
```

**Table**: `brokers`

**Why Important**: customer_insurance records often reference brokers

---

### 3. RelationshipManagersSeeder (HIGH PRIORITY)

**File**: `database/seeders/RelationshipManagersSeeder.php`

**Purpose**: Seed default relationship managers

**Recommended Data**:
```php
- Unassigned (for policies without RM)
- Manager 1
- Manager 2
```

**Table**: `relationship_managers`

**Why Important**: customer_insurance records reference RMs

---

### 4. ReferenceUsersSeeder (MEDIUM PRIORITY)

**File**: `database/seeders/ReferenceUsersSeeder.php`

**Purpose**: Seed common reference sources

**Recommended Data**:
```php
- Walk-in Customer
- Website Inquiry
- Google Ads
- Social Media
- Referral Program
```

**Table**: `reference_users`

**Why Important**: Tracks customer acquisition sources

---

### 5. ClaimStagesSeeder (MEDIUM PRIORITY)

**File**: `database/seeders/ClaimStagesSeeder.php`

**Purpose**: Seed standard claim workflow stages

**Recommended Data**:
```php
- Claim Registered
- Documents Submitted
- Under Review
- Investigation In Progress
- Approved
- Rejected
- Payment Processed
- Closed
```

**Table**: This would need to be a master data table (currently claim_stages is transaction table)

**Why Important**: Standardizes claim workflow tracking

---

## Seeding Order

The `DatabaseSeeder.php` defines the correct seeding order:

```php
public function run()
{
    $this->call([
        // 1. Core Setup Seeders (MUST RUN FIRST)
        RoleSeeder::class,
        AdminSeeder::class,
        UnifiedPermissionsSeeder::class,

        // 2. Lookup Table Seeders (MUST RUN BEFORE DATA MIGRATION)
        CustomerTypesSeeder::class,
        CommissionTypesSeeder::class,
        QuotationStatusesSeeder::class,
        AddonCoversSeeder::class,
        PolicyTypesSeeder::class,
        PremiumTypesSeeder::class,
        FuelTypesSeeder::class,
        InsuranceCompaniesSeeder::class,

        // 3. MISSING SEEDERS (SHOULD BE ADDED HERE)
        BranchesSeeder::class,               // NEW
        BrokersSeeder::class,                // NEW
        RelationshipManagersSeeder::class,   // NEW
        ReferenceUsersSeeder::class,         // NEW

        // 4. Data Migration Seeders (MUST RUN AT THE END)
        EmailCleanupSeeder::class,
        DataMigrationSeeder::class,
    ]);
}
```

## How to Create a Proper Seeder

### Template Structure

```php
<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ExampleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing data (optional but recommended)
        DB::table('table_name')->truncate();

        // Insert data
        DB::table('table_name')->insert([
            [
                'name' => 'Example 1',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,  // Admin user
                'updated_by' => 1,
                'deleted_by' => null
            ],
            [
                'name' => 'Example 2',
                'status' => 1,
                'created_at' => now(),
                'updated_at' => now(),
                'deleted_at' => null,
                'created_by' => 1,
                'updated_by' => 1,
                'deleted_by' => null
            ],
        ]);

        // Optional: Output confirmation
        $this->command->info('Example data seeded successfully!');
    }
}
```

### Best Practices

1. **Use Truncate Carefully**: Only truncate in development, never in production
2. **Set Timestamps**: Always include created_at and updated_at
3. **Set Audit Fields**: Include created_by, updated_by (usually 1 for admin)
4. **Set Status**: Default status to true/1 (active)
5. **Null Soft Deletes**: Set deleted_at and deleted_by to null
6. **Use now()**: Use `now()` helper for consistent timestamps
7. **Add Output**: Use `$this->command->info()` to confirm success

### Common Columns Pattern

```php
[
    'name' => 'Required Name',
    'email' => 'optional@example.com',  // nullable
    'mobile_number' => '1234567890',     // nullable
    'status' => 1,                       // boolean/tinyint
    'created_at' => now(),
    'updated_at' => now(),
    'deleted_at' => null,                // soft delete
    'created_by' => 1,                   // admin user id
    'updated_by' => 1,
    'deleted_by' => null,
]
```

## Running Seeders

### Run All Seeders
```bash
php artisan db:seed
```

### Run Specific Seeder
```bash
php artisan db:seed --class=BranchesSeeder
```

### Fresh Migration with Seeding
```bash
php artisan migrate:fresh --seed
```

### Production Seeding (Careful!)
```bash
php artisan db:seed --force
```

## Seed Data Examples

### Branches Example
```php
[
    ['name' => 'Head Office - Mumbai', 'status' => 1],
    ['name' => 'Delhi Branch', 'status' => 1],
    ['name' => 'Bangalore Branch', 'status' => 1],
    ['name' => 'Chennai Branch', 'status' => 1],
    ['name' => 'Pune Branch', 'status' => 1],
]
```

### Brokers Example
```php
[
    ['name' => 'Direct Sales', 'email' => null, 'mobile_number' => null, 'status' => 1],
    ['name' => 'ABC Insurance Brokers', 'email' => 'contact@abc.com', 'mobile_number' => '9876543210', 'status' => 1],
    ['name' => 'XYZ Financial Services', 'email' => 'info@xyz.com', 'mobile_number' => '9876543211', 'status' => 1],
]
```

### Relationship Managers Example
```php
[
    ['name' => 'Unassigned', 'email' => null, 'mobile_number' => null, 'status' => 1],
    ['name' => 'Rahul Sharma', 'email' => 'rahul@company.com', 'mobile_number' => '9876543212', 'status' => 1],
    ['name' => 'Priya Patel', 'email' => 'priya@company.com', 'mobile_number' => '9876543213', 'status' => 1],
    ['name' => 'Amit Kumar', 'email' => 'amit@company.com', 'mobile_number' => '9876543214', 'status' => 1],
]
```

### Reference Users Example
```php
[
    ['name' => 'Walk-in Customer', 'email' => null, 'mobile_number' => null, 'status' => 1],
    ['name' => 'Website Inquiry', 'email' => null, 'mobile_number' => null, 'status' => 1],
    ['name' => 'Google Ads Campaign', 'email' => null, 'mobile_number' => null, 'status' => 1],
    ['name' => 'Facebook Marketing', 'email' => null, 'mobile_number' => null, 'status' => 1],
    ['name' => 'Customer Referral', 'email' => null, 'mobile_number' => null, 'status' => 1],
    ['name' => 'Partner Network', 'email' => null, 'mobile_number' => null, 'status' => 1],
]
```

## Seeder Dependencies Graph

```
RoleSeeder
    ├── AdminSeeder
    │   └── UnifiedPermissionsSeeder
    └── AppSettingPermissionsSeeder

Master Data Seeders (No dependencies, can run in parallel)
├── CustomerTypesSeeder
├── CommissionTypesSeeder
├── QuotationStatusesSeeder
├── AddonCoversSeeder
├── PolicyTypesSeeder
├── PremiumTypesSeeder
├── FuelTypesSeeder
├── InsuranceCompaniesSeeder
├── BranchesSeeder (MISSING)
├── BrokersSeeder (MISSING)
├── RelationshipManagersSeeder (MISSING)
└── ReferenceUsersSeeder (MISSING)

AppSettingsSeeder (Independent)

Data Migration (Depends on ALL above)
├── EmailCleanupSeeder
└── DataMigrationSeeder
```

## Testing Seeders

After creating seeders, verify data:

```sql
-- Check if data was inserted
SELECT COUNT(*) FROM branches;
SELECT COUNT(*) FROM brokers;
SELECT COUNT(*) FROM relationship_managers;
SELECT COUNT(*) FROM reference_users;

-- Check data quality
SELECT * FROM branches WHERE status = 1;
SELECT * FROM brokers WHERE created_by IS NULL;  -- Should be 1
SELECT * FROM relationship_managers WHERE created_at IS NULL;  -- Should be none

-- Verify foreign key readiness
SELECT COUNT(*) FROM customer_insurances WHERE branch_id IS NULL;
SELECT COUNT(*) FROM customer_insurances WHERE broker_id IS NULL;
```

## Maintenance

### Updating Existing Seeders

When updating seeders:
1. Add new records at the end to maintain existing IDs
2. Never change existing record IDs (may break foreign keys)
3. Use `updateOrCreate()` instead of `truncate()` for safer updates
4. Test on staging environment first

### Version Control

- Always commit seeder changes
- Document seed data changes in migration notes
- Keep production seed data separate from test data
- Use environment-specific seeders when needed

## Troubleshooting

### Common Issues

1. **Foreign Key Errors**: Run seeders in correct dependency order
2. **Duplicate Key Errors**: Use `truncate()` or check for existing records
3. **Null Constraint Violations**: Ensure all required fields are populated
4. **Timestamp Errors**: Use `now()` helper, not manual dates
5. **Created_by Null**: Set to 1 (admin user) for system-generated data

### Debug Commands

```bash
# Check seeder syntax
php artisan db:seed --class=BranchesSeeder --pretend

# Verbose output
php artisan db:seed --class=BranchesSeeder -v

# Check database connection
php artisan db:show

# Rollback and reseed
php artisan migrate:fresh --seed
```
