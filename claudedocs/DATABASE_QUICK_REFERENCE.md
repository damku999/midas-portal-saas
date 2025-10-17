# Database Quick Reference Card

## Quick Commands

### Run All Seeders
```bash
php artisan db:seed
```

### Fresh Install (Reset Everything)
```bash
php artisan migrate:fresh --seed
```

### Run Specific Seeder
```bash
php artisan db:seed --class=BranchesSeeder
```

### Verify Seeders
```bash
# In MySQL/MariaDB console:
source claudedocs/SEEDER_VERIFICATION.sql
```

## Master Data Tables Quick Stats

| Table | Records | Purpose |
|-------|---------|---------|
| branches | 10 | Branch locations |
| brokers | 6 | Insurance brokers |
| relationship_managers | 8 | Account managers |
| reference_users | 14 | Customer sources |
| insurance_companies | 20 | Indian insurance companies |
| fuel_types | 4 | Vehicle fuel types |
| policy_types | 3 | Policy categories |
| premium_types | 35 | Insurance premium types |
| addon_covers | 9 | Vehicle insurance addons |
| customer_types | 2+ | Retail/Corporate |
| commission_types | - | Commission calculation |
| quotation_statuses | - | Quotation workflow |

## Key Relationships

### Customer Insurance Foreign Keys
```
customer_insurances
├── branch_id → branches (10 options)
├── broker_id → brokers (6 options)
├── relationship_manager_id → relationship_managers (8 options)
├── customer_id → customers
├── insurance_company_id → insurance_companies (20 options)
├── premium_type_id → premium_types (35 options)
├── policy_type_id → policy_types (3 options)
├── fuel_type_id → fuel_types (4 options)
├── commission_type_id → commission_types
└── reference_by → reference_users (14 options)
```

## Seeding Order

1. **System Setup** (Run First)
   - RoleSeeder
   - AdminSeeder
   - UnifiedPermissionsSeeder

2. **Master Data** (No Dependencies)
   - CustomerTypesSeeder
   - CommissionTypesSeeder
   - QuotationStatusesSeeder
   - AddonCoversSeeder
   - PolicyTypesSeeder
   - PremiumTypesSeeder
   - FuelTypesSeeder
   - InsuranceCompaniesSeeder
   - BranchesSeeder
   - BrokersSeeder
   - RelationshipManagersSeeder
   - ReferenceUsersSeeder

3. **Data Migration** (Run Last)
   - EmailCleanupSeeder
   - DataMigrationSeeder

## Common Queries

### Count All Master Data
```sql
SELECT
    'branches' as table_name, COUNT(*) as count FROM branches
UNION ALL
SELECT 'brokers', COUNT(*) FROM brokers
UNION ALL
SELECT 'relationship_managers', COUNT(*) FROM relationship_managers
UNION ALL
SELECT 'reference_users', COUNT(*) FROM reference_users;
```

### Check Active Records
```sql
SELECT name, status FROM branches WHERE status = 1;
SELECT name, status FROM brokers WHERE status = 1;
SELECT name, status FROM relationship_managers WHERE status = 1;
```

### Verify Foreign Key Readiness
```sql
-- Check if policies can be created with all references
SELECT
    (SELECT COUNT(*) FROM branches WHERE status = 1) as branches,
    (SELECT COUNT(*) FROM brokers WHERE status = 1) as brokers,
    (SELECT COUNT(*) FROM relationship_managers WHERE status = 1) as managers,
    (SELECT COUNT(*) FROM insurance_companies WHERE status = 1) as companies;
```

## Table Patterns

### Standard Columns (All Master Data Tables)
```php
id                  // Primary key
name                // Record name
email               // Optional contact email
mobile_number       // Optional contact phone
status              // 1 = active, 0 = inactive
created_at          // Creation timestamp
updated_at          // Last update timestamp
deleted_at          // Soft delete timestamp (nullable)
created_by          // User who created (1 = admin)
updated_by          // User who last updated
deleted_by          // User who deleted (nullable)
```

### Customer Insurance Columns (Subset)
```php
// Core Policy Info
policy_no, registration_no, rto, make_model
start_date, expired_date, tp_expiry_date

// Premium Details
od_premium, tp_premium, net_premium
gst, final_premium_with_gst

// Commission
my_commission_percentage, my_commission_amount
reference_commission_percentage, reference_commission_amount

// References
branch_id, broker_id, relationship_manager_id
customer_id, insurance_company_id
fuel_type_id, policy_type_id, premium_type_id
```

## Seed Data Examples

### Branches
- Head Office - Surat
- Mumbai, Delhi, Bangalore, Chennai, Pune
- Ahmedabad, Hyderabad, Kolkata, Jaipur

### Brokers
- Direct Sales (for non-broker sales)
- ABC Insurance Brokers
- XYZ Financial Services
- Prime Insurance Consultants

### Relationship Managers
- Unassigned (default)
- Rahul Sharma, Priya Patel, Amit Kumar
- Sneha Desai, Vikram Singh, Anjali Mehta

### Reference Users (Customer Sources)
- Walk-in Customer
- Website Inquiry
- Google Ads, Facebook, Instagram
- Customer Referral, Partner Network
- Telephone/WhatsApp Inquiry

### Insurance Companies (Partial List)
- CARE HEALTH INSURANCE LTD
- BAJAJ ALLIANZ GIC LTD
- TATA AIG GIC LTD
- ICICI LOMBARD GIC LTD
- HDFC ERGO GIC LTD
- LIC OF INDIA

### Fuel Types
- PETROL, DIESEL, CNG, EV

### Policy Types
- FRESH, ROLLOVER, RENEWAL

### Premium Types (Partial List)
- 2 WHEELER, 4 WHEELER
- HEALTH INSURANCE
- TERM PLAN, ENDOWMENT PLANS
- FIRE INSURANCE, TRAVEL INSURANCE

## Troubleshooting

### Seeder Already Run Error
```bash
# Truncate and re-run
php artisan db:seed --class=BranchesSeeder
```

### Foreign Key Constraint Error
```bash
# Run seeders in correct order
php artisan migrate:fresh --seed
```

### Permission Denied
```bash
# Check database permissions
php artisan db:show
```

### Missing Data
```bash
# Verify specific seeder ran
php artisan db:seed --class=BranchesSeeder -v
```

## Files Reference

### Documentation
- `DATABASE_DOCUMENTATION.md` - Complete schema
- `SEEDERS_GUIDE.md` - Detailed guide
- `DATABASE_SEEDER_SUMMARY.md` - Implementation summary
- `DATABASE_QUICK_REFERENCE.md` - This file

### Seeder Files (New)
- `database/seeders/BranchesSeeder.php`
- `database/seeders/BrokersSeeder.php`
- `database/seeders/RelationshipManagersSeeder.php`
- `database/seeders/ReferenceUsersSeeder.php`

### Main Orchestrator
- `database/seeders/DatabaseSeeder.php`

## Production Notes

### Before Production Seeding
1. Backup database
2. Review default admin credentials
3. Update branch names to actual locations
4. Update manager names to real employees
5. Test in staging first

### Production Command (Use with Caution)
```bash
php artisan db:seed --force
```

### Safe Production Update
```bash
# Run only new seeders
php artisan db:seed --class=BranchesSeeder --force
php artisan db:seed --class=BrokersSeeder --force
php artisan db:seed --class=RelationshipManagersSeeder --force
php artisan db:seed --class=ReferenceUsersSeeder --force
```

## Health Checks

### Quick Health Check
```sql
-- Should return all PASS
SELECT 'branches' as table_check,
       CASE WHEN COUNT(*) >= 10 THEN 'PASS' ELSE 'FAIL' END as status
FROM branches WHERE status = 1;
```

### Complete Verification
```bash
# Run full verification SQL script
mysql -u username -p database_name < claudedocs/SEEDER_VERIFICATION.sql
```

## Environment-Specific Seeding

### Development
```bash
php artisan migrate:fresh --seed
```

### Staging
```bash
php artisan migrate --force
php artisan db:seed --force
```

### Production
```bash
# Only run specific, safe seeders
php artisan db:seed --class=BranchesSeeder --force
# Verify each step manually
```

## Contact & Support

For database or seeder issues:
1. Check `DATABASE_DOCUMENTATION.md` for schema details
2. Check `SEEDERS_GUIDE.md` for seeding procedures
3. Run `SEEDER_VERIFICATION.sql` for diagnostics
4. Review Laravel logs in `storage/logs/laravel.log`
