# Database Seeders Quick Reference

**Last Updated:** 2025-10-06

---

## Production Data Summary

### AddonCovers (10 records)
```
1  - Zero Depreciation
2  - Consumables
3  - Engine Protection
4  - Return to Invoice
5  - Tyre Protection
6  - Key and Lock Protect
7  - Loss of Personal Belongings
8  - RSA (Road Side Assistance)
9  - Repair of Glass,Rubber and Plastic Part
10 - Other
```

### FuelTypes (4 records)
```
1 - PETROL
2 - DIESEL
3 - CNG
4 - EV
```

### PremiumTypes (3 records - Transaction Types)
```
1 - FRESH
2 - ROLLOVER
3 - RENEWAL
```

### PolicyTypes (34 records - Insurance Types)

**Vehicle Insurance (13):**
```
2 WHEELER, 2W SATP, 4 WHEELER, 4W SATP
3 WHEELER, 3W SATP
COMMERCIAL VEHICLE COMP, COMMERCIAL VEHICLE SATP
4W OD ONLY, 2W OD ONLY, 3W OD ONLY
4W BUNDLE (1OD +3TP), 2W BUNDLE (1OD + 5TP)
```

**Non-Vehicle Insurance (17):**
```
HEALTH INSURANCE, GMC, GPA, PERSONAL ACCIDENT
MARINE / TRANSIT INSURANCE, FIRE INSURANCE, MY HOME INSURANCE
TRAVEL INSURANCE, WC INSURANCE, TRADE CREDIT INSURANCE
TOP UP HEALTH INSURANCE, CYBER INSURANCE
TRANSPORTER GODOWN INSURANCE, CARRIER LEGAL LIABILITY
BHARAT SOOKSHMA UDHYAM, GODOWN STOCK INSURANCE
BURGLARY & THEFT INSURANCE
```

**Life Insurance (4):**
```
TERM PLAN, ENDOWMENT PLANS, ULIP PLANS, LIC
```

### InsuranceCompanies (20 records)
```
1  - CARE HEALTH INSURANCE LTD
2  - BAJAJ ALLIANZ GIC LTD
3  - TATA AIG GIC LTD
4  - MAGMA HDI GIC LTD
5  - GO DIGIT GENERAL INSURANCE LTD
6  - RELIANCE GIC LTD
7  - ICICI LOMBARD GIC LTD
8  - THE NEW INDIA ASSURANCE CO LTD
9  - TATA AIA LIFE INSURANCE CO LTD
10 - ICICI PRU LIFE INSURANCE CO LTD
11 - HDFC ERGO GIC LTD
12 - LIBERTY GENERAL INSURANCE LTD
13 - ZUNO GENERAL INSURANCE LTD
14 - LIC OF INDIA
15 - CHOLA MS GIC
16 - ROYAL SUNDARAM GIC LTD
17 - THE ORIENTAL INSURANCE COMPANY LIMITED
18 - ADITYA BIRLA HEALTH INSURANCE CO LTD
19 - KOTAK GIC LTD
20 - SBI GIC LTD
```

### Branches (1 record)
```
1 - AHMEDABAD
```

### Brokers (5 records)
```
1 - NARENDRA JAIN / NITESH JAIN
2 - PARTH RAWAL
3 - PRITESH THAKKAR
4 - PARTH - NITESH
5 - ROHAN GAJJAR
```

### RelationshipManagers (10 records)
```
1  - RELITRADE INSURANCE BROKING PVT LTD
2  - PARTH RAWAL
3  - RATNAAFIN INSURANCE BROKING PVT LTD
4  - VINIT - LANDMARK INSURANCE BROKING PVT LTD
5  - MOIN - LANDMARK INSURANCE BROKING PVT LTD
6  - GNR - LANDMARK INSURANCE BROKING PVT LTD
7  - ROHAN GAJJAR ERICSON TPA
8  - GEO FINTECH
9  - SAMTA JAIN
10 - TANUSHI JAIN
```

### ReferenceUsers (2 records)
```
1 - NARENDRA JAIN / NITESH JAIN
2 - DHAVAL PANCHAL
```

---

## Seeding Commands

### Seed All
```bash
php artisan db:seed
```

### Seed Specific Seeder
```bash
php artisan db:seed --class=PolicyTypesSeeder
php artisan db:seed --class=PremiumTypesSeeder
php artisan db:seed --class=AddonCoversSeeder
php artisan db:seed --class=BranchesSeeder
php artisan db:seed --class=BrokersSeeder
php artisan db:seed --class=RelationshipManagersSeeder
php artisan db:seed --class=ReferenceUsersSeeder
```

### Fresh Migration + Seed
```bash
php artisan migrate:fresh --seed
```

---

## Important Notes

1. **PolicyTypes vs PremiumTypes:**
   - PolicyTypes = Insurance product types (34 types)
   - PremiumTypes = Transaction types (FRESH/ROLLOVER/RENEWAL)

2. **AddonCovers ID 10:**
   - Must exist for "Other" categorization
   - Added in latest cleanup

3. **Master Data:**
   - All branches, brokers, RMs, and reference users now use PRODUCTION data
   - No more fake placeholder data

4. **IDs:**
   - All seeders now use explicit IDs for consistency
   - Safe for production data references

---

## Seeder Execution Order

```
1. Core Setup
   → RoleSeeder
   → AdminSeeder
   → UnifiedPermissionsSeeder

2. Lookup Tables (must run before master data)
   → CustomerTypesSeeder
   → CommissionTypesSeeder
   → QuotationStatusesSeeder
   → AddonCoversSeeder
   → PolicyTypesSeeder
   → PremiumTypesSeeder
   → FuelTypesSeeder
   → InsuranceCompaniesSeeder

3. Master Data (depends on lookup tables)
   → BranchesSeeder
   → BrokersSeeder
   → RelationshipManagersSeeder
   → ReferenceUsersSeeder

4. Data Migration (must run last)
   → EmailCleanupSeeder
   → DataMigrationSeeder
```

---

## Verification Queries

```sql
-- Check record counts
SELECT 'addon_covers' as table_name, COUNT(*) as count FROM addon_covers
UNION ALL SELECT 'fuel_types', COUNT(*) FROM fuel_types
UNION ALL SELECT 'premium_types', COUNT(*) FROM premium_types
UNION ALL SELECT 'policy_types', COUNT(*) FROM policy_types
UNION ALL SELECT 'insurance_companies', COUNT(*) FROM insurance_companies
UNION ALL SELECT 'branches', COUNT(*) FROM branches
UNION ALL SELECT 'brokers', COUNT(*) FROM brokers
UNION ALL SELECT 'relationship_managers', COUNT(*) FROM relationship_managers
UNION ALL SELECT 'reference_users', COUNT(*) FROM reference_users;

-- Expected counts:
-- addon_covers: 10
-- fuel_types: 4
-- premium_types: 3
-- policy_types: 34
-- insurance_companies: 20
-- branches: 1
-- brokers: 5
-- relationship_managers: 10
-- reference_users: 2
```

---

**Reference Document** | See SEEDERS_ANALYSIS.md for detailed changes
