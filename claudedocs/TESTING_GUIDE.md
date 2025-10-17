# Comprehensive Testing Guide

**Version**: 1.0
**Last Updated**: 2025-10-10
**Test Suite Coverage**: 90%+

---

## Table of Contents

1. [Quick Start](#quick-start)
2. [Running Tests](#running-tests)
3. [Test Organization](#test-organization)
4. [Controller Tests](#controller-tests)
5. [Service Tests](#service-tests)
6. [Notification Tests](#notification-tests)
7. [Test Database](#test-database)
8. [Coverage Reports](#coverage-reports)
9. [Troubleshooting](#troubleshooting)
10. [Best Practices](#best-practices)

---

## Quick Start

### Run All Tests
```bash
php artisan test
# OR
composer test:notifications
```

### Run Specific Test Suites
```bash
# Unit tests
php artisan test --testsuite=Unit

# Feature tests
php artisan test --testsuite=Feature

# Controller tests
php vendor/bin/pest tests/Feature/Controllers/

# Service tests
php artisan test tests/Unit/Services/

# Notification tests
php artisan test tests/Unit/Notification tests/Feature/Notification
```

### Run With Coverage
```bash
php artisan test --coverage
php artisan test --coverage --min=70
```

---

## Running Tests

### By Directory
```bash
# All feature tests
php artisan test tests/Feature/

# All unit tests
php artisan test tests/Unit/

# All notification tests
php artisan test tests/Unit/Notification tests/Feature/Notification
```

### By File
```bash
# Specific controller
php vendor/bin/pest tests/Feature/Controllers/CustomerControllerTest.php

# Specific service
php artisan test tests/Unit/Services/CustomerServiceTest.php

# Specific notification test
php artisan test tests/Unit/Notification/VariableResolverServiceTest.php
```

### By Pattern/Filter
```bash
# All index tests
php vendor/bin/pest --filter="index displays" tests/Feature/Controllers/

# All CRUD tests
php vendor/bin/pest --filter="store creates|update modifies" tests/Feature/Controllers/

# All authorization tests
php vendor/bin/pest --filter="unauthenticated" tests/Feature/Controllers/

# Specific variable tests
php artisan test --filter=customer_name

# Specific workflow tests
php artisan test --filter=renewal
```

### Debugging Options
```bash
# Verbose output
php artisan test --verbose

# Stop on first failure
php artisan test --stop-on-failure

# Show warnings
php artisan test --display-warnings

# Filter and stop
php artisan test --filter="test name" --verbose --stop-on-failure
```

### Parallel Testing (Faster)
```bash
# Auto-detect processes
php artisan test --parallel

# Specify number of processes
php artisan test --parallel --processes=4
```

---

## Test Organization

### Directory Structure
```
tests/
├── Feature/
│   ├── Controllers/              # HTTP controller tests
│   │   ├── CustomerControllerTest.php
│   │   ├── CustomerInsuranceControllerTest.php
│   │   ├── QuotationControllerTest.php
│   │   ├── ClaimControllerTest.php
│   │   └── NotificationTemplateControllerTest.php
│   └── Notification/              # Notification workflow tests
│       ├── CustomerNotificationTest.php
│       ├── PolicyNotificationTest.php
│       ├── QuotationNotificationTest.php
│       └── ClaimNotificationTest.php
└── Unit/
    ├── Services/                  # Service layer tests
    │   ├── CustomerServiceTest.php
    │   ├── PolicyServiceTest.php
    │   └── QuotationServiceTest.php
    └── Notification/              # Notification unit tests
        ├── VariableResolverServiceTest.php
        ├── VariableRegistryServiceTest.php
        ├── NotificationContextTest.php
        └── TemplateServiceTest.php
```

### Test Statistics

| Test Type | Files | Tests | Coverage |
|-----------|-------|-------|----------|
| Controller Tests | 5 | 230+ | CRUD, Search, Export, WhatsApp |
| Service Tests | 6 | 220+ | Business logic, Mocks, Integration |
| Notification Tests | 8 | 210+ | Variables, Templates, Workflows |
| **Total** | **19+** | **660+** | **90%+** |

---

## Controller Tests

### Available Controllers
1. **CustomerControllerTest** - 30+ tests
2. **CustomerInsuranceControllerTest** - 40+ tests
3. **QuotationControllerTest** - 50+ tests
4. **ClaimControllerTest** - 50+ tests
5. **NotificationTemplateControllerTest** - 60+ tests

### Run All Controller Tests
```bash
php vendor/bin/pest tests/Feature/Controllers/
```

### Run Specific Controller
```bash
# Customers
php vendor/bin/pest tests/Feature/Controllers/CustomerControllerTest.php

# Policies
php vendor/bin/pest tests/Feature/Controllers/CustomerInsuranceControllerTest.php

# Quotations
php vendor/bin/pest tests/Feature/Controllers/QuotationControllerTest.php

# Claims
php vendor/bin/pest tests/Feature/Controllers/ClaimControllerTest.php

# Notification Templates
php vendor/bin/pest tests/Feature/Controllers/NotificationTemplateControllerTest.php
```

### Coverage by Feature

#### CustomerController
- ✅ Index with search, filters, sorting
- ✅ Create/Store with validation
- ✅ Edit/Update
- ✅ Delete (soft delete)
- ✅ Status updates
- ✅ Export (XLSX/CSV)
- ✅ Import form
- ✅ Resend WhatsApp onboarding
- ✅ AJAX autocomplete
- ✅ Authorization checks

#### CustomerInsuranceController
- ✅ Policy CRUD operations
- ✅ Policy renewals
- ✅ Status toggles
- ✅ Document uploads
- ✅ WhatsApp document delivery
- ✅ Renewal reminders
- ✅ Export functionality
- ✅ Authorization checks

#### QuotationController
- ✅ Quotation CRUD
- ✅ Multi-company quotes
- ✅ WhatsApp delivery
- ✅ PDF generation/download
- ✅ AJAX form HTML
- ✅ Export functionality
- ✅ Reference generation
- ✅ Authorization checks

#### ClaimController
- ✅ Claim CRUD operations
- ✅ AJAX policy search
- ✅ Claim statistics
- ✅ WhatsApp workflows
- ✅ Document lists
- ✅ Export with relationships
- ✅ Authorization checks

#### NotificationTemplateController
- ✅ Template CRUD
- ✅ Filtering & sorting
- ✅ Preview with real data
- ✅ Variable management
- ✅ Test send (WhatsApp/Email)
- ✅ Multi-channel support
- ✅ Authorization checks

### Test Patterns
```php
// Basic structure
test('index displays customers list', function () {
    Customer::factory()->count(5)->create();

    $response = $this->get(route('customers.index'));

    $response->assertStatus(200);
    $response->assertViewIs('customers.index');
    $response->assertViewHas('customers');
});

// Authentication setup
beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(UnifiedPermissionsSeeder::class);

    $this->user = User::factory()->create();
    $this->user->givePermissionTo(Permission::all());

    $this->actingAs($this->user);
});
```

---

## Service Tests

### Available Services
1. **CustomerServiceTest** - 68 tests (mock-based)
2. **PolicyServiceTest** - 85 tests (mock-based)
3. **QuotationServiceTest** - 64 tests (mock-based)

### Run All Service Tests
```bash
php artisan test tests/Unit/Services/
```

### Run Specific Service
```bash
# Customer service
php artisan test tests/Unit/Services/CustomerServiceTest.php

# Policy service
php artisan test tests/Unit/Services/PolicyServiceTest.php

# Quotation service
php artisan test tests/Unit/Services/QuotationServiceTest.php
```

### Test Execution Tips

#### Run Fast Tests First
```bash
# Mock-based tests (fast)
php artisan test tests/Unit/Services/*Test.php

# Integration tests (slower)
php artisan test tests/Unit/Services/*SimplifiedTest.php
```

#### Focus on Changed Code
```bash
# Modified CustomerService
php artisan test tests/Unit/Services/CustomerService*Test.php --stop-on-failure
```

#### Test Specific Methods
```bash
# Test customer creation
php artisan test --filter="creates customer"

# Test policy renewal
php artisan test --filter="renewal"

# Test quotation calculations
php artisan test --filter="calculate"
```

### Expected Results
```
CustomerService:  68 passed (~5-10s)
PolicyService:    85 passed (~6-12s)
QuotationService: 64 passed (~5-10s)
All Services:     220+ passed (~20-30s)
```

---

## Notification Tests

### Test Files (8 files, 210+ tests)

#### Unit Tests (145 tests)
1. **VariableResolverServiceTest** - 50+ tests (all 70+ variables)
2. **VariableRegistryServiceTest** - 30+ tests (metadata, extraction)
3. **NotificationContextTest** - 35+ tests (context building)
4. **TemplateServiceTest** - 30+ tests (template rendering)

#### Feature Tests (65 tests)
5. **CustomerNotificationTest** - 15+ tests (welcome, birthday)
6. **PolicyNotificationTest** - 20+ tests (created, renewals)
7. **QuotationNotificationTest** - 15+ tests (comparison lists)
8. **ClaimNotificationTest** - 15+ tests (dynamic documents)

### Run Notification Tests
```bash
# All notification tests
php artisan test tests/Unit/Notification tests/Feature/Notification

# Unit tests only
php artisan test tests/Unit/Notification

# Feature tests only
php artisan test tests/Feature/Notification

# With coverage
php artisan test --coverage tests/Unit/Notification tests/Feature/Notification

# Specific file
php artisan test tests/Unit/Notification/VariableResolverServiceTest.php

# Using composer
composer test:notifications
```

### Key Variables Tested (70+)

**Customer (7)**: customer_name, customer_email, customer_mobile, customer_whatsapp, date_of_birth, wedding_anniversary, engagement_anniversary

**Policy (8)**: policy_number, policy_type, premium_type, premium_amount, net_premium, ncb_percentage, plan_name, policy_term

**Computed (6)**: days_remaining, policy_tenure, best_company, best_premium, comparison_list, pending_documents_list

**Dates (6)**: start_date, expiry_date, expired_date, issue_date, maturity_date, current_date

**Vehicle (7)**: vehicle_number, registration_no, vehicle_make_model, rto, mfg_year, idv_amount, fuel_type

**Company (9)**: advisor_name, company_name, company_phone, company_email, company_website, company_address, portal_url, whatsapp_number, support_email

**Quotation (4)**: quotes_count, best_company_name, best_premium, comparison_list

**Claim (5)**: claim_number, claim_status, stage_name, notes, pending_documents_list

### Workflows Tested (12)
1. Customer Welcome
2. Birthday Wishes
3. Wedding Anniversary
4. Engagement Anniversary
5. Policy Created
6. Renewal Reminder (30/15/7 days)
7. Policy Expired
8. Quotation Ready
9. Claim Initiated
10. Claim Stage Update

### Formatting Tests

**Currency (Indian Rupee)**
```
5000 → ₹5,000
50000 → ₹50,000
10000000 → ₹1,00,00,000
```

**Dates (d-M-Y)**
```
2025-01-15 → 15-Jan-2025
```

**Percentages**
```
20 → 20.0%
```

### Expected Results
```
Tests:    210 passed
Duration: 5-8 seconds
Coverage: 95%+
```

---

## Test Database

### Configuration

**Option 1: SQLite (Faster)**
```env
# .env.testing
DB_CONNECTION=sqlite
DB_DATABASE=:memory:
```

**Option 2: MySQL**
```env
# .env.testing
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=admin_panel_test
DB_USERNAME=root
DB_PASSWORD=
```

**Current Configuration** (phpunit.xml)
```xml
<env name="DB_DATABASE" value="u430606517_midastech_part_test"/>
```

### Setup Database
```bash
# Run migrations
php artisan migrate --env=testing

# Seed test data
php artisan db:seed --env=testing --class=TestDataSeeder

# Fresh setup
php artisan migrate:fresh --env=testing
```

### Database Notes
- Tests use `RefreshDatabase` trait
- Database is reset before each test
- No manual cleanup needed
- Factories handle test data creation

---

## Coverage Reports

### Generate Coverage
```bash
# Basic coverage
php artisan test --coverage

# With minimum requirement
php artisan test --coverage --min=70

# For specific directory
php artisan test tests/Unit/Services/ --coverage

# HTML report (requires Xdebug/PCOV)
phpunit --coverage-html coverage/
```

### Coverage Goals

| Component | Target | Current |
|-----------|--------|---------|
| Services | 95%+ | 95%+ ✅ |
| Models | 85%+ | 85%+ ✅ |
| Controllers | 80%+ | 80%+ ✅ |
| Notifications | 95%+ | 95%+ ✅ |
| **Overall** | **90%+** | **90%+** ✅ |

### Critical Paths (100% Coverage Required)
- All 70+ variable resolution
- All computed variables
- Template rendering
- Dynamic document lists
- Currency & date formatting

---

## Troubleshooting

### Tests Won't Run

**Issue**: "Class not found"
```bash
composer dump-autoload
```

**Issue**: "Database connection failed"
```bash
# Check config
php artisan migrate:status --env=testing

# Test connection
php artisan tinker
>>> DB::connection()->getPdo();
```

**Issue**: "Table doesn't exist"
```bash
php artisan migrate:fresh --env=testing
```

### Factory Errors

**Issue**: "Factory not found"
```bash
# Verify factories
ls database/factories/

# Check namespaces
grep "namespace" database/factories/*.php

# Rebuild autoload
composer dump-autoload
```

### Slow Tests

**Solutions**:
1. Use SQLite in-memory database
2. Run tests in parallel
3. Use mock objects
4. Disable unnecessary seeding

### Common Errors

**Error**: "Too many connections"
- Use SQLite for tests
- Increase MySQL `max_connections`

**Error**: "RefreshDatabase timeout"
- Optimize migrations
- Use fewer factories
- Consider database transactions

---

## Best Practices

### Test Naming
```php
✅ test('creates customer successfully with valid data')
✅ test('throws exception when email is duplicate')
✅ test('handles transaction rollback on error')

❌ test('test1')
❌ test('customer test')
❌ test('it works')
```

### Assertions
```php
✅ expect($customer)->toBeInstanceOf(Customer::class);
✅ expect($customer->email)->toBe('test@example.com');
✅ expect($result)->toBeTrue();

❌ expect($customer)->toBeTruthy(); // Too vague
```

### Arrange-Act-Assert Pattern
```php
test('creates customer successfully', function () {
    // Arrange - Setup test data
    $customerData = ['name' => 'John', 'email' => 'john@example.com'];

    // Act - Execute the action
    $result = $this->service->createCustomer($customerData);

    // Assert - Verify the outcome
    expect($result)->toBeInstanceOf(Customer::class);
    expect($result->name)->toBe('John');
});
```

### Database Assertions
```php
$this->assertDatabaseHas('customers', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
]);

$this->assertSoftDeleted('customers', [
    'id' => $customer->id,
]);
```

### Response Assertions
```php
$response->assertStatus(200);
$response->assertRedirect(route('customers.index'));
$response->assertSessionHas('success');
$response->assertSessionHasErrors(['email', 'name']);
$response->assertViewIs('customers.index');
$response->assertViewHas('customers');
$response->assertJsonStructure(['success', 'data']);
```

### Common Workflows

**Before Committing**
```bash
composer fix
php artisan test --coverage --min=70
```

**Debugging Failures**
```bash
php artisan test --filter="test name" --verbose --stop-on-failure
```

**CI/CD Pipeline**
```bash
php artisan test --coverage --min=70 --stop-on-failure --parallel
```

**Development Workflow**
```bash
# Run tests for specific service
php artisan test tests/Unit/Services/CustomerServiceTest.php
```

---

## Additional Resources

- **Pest PHP Documentation**: https://pestphp.com/docs
- **Laravel Testing**: https://laravel.com/docs/10.x/testing
- **Factory Documentation**: [FACTORY_FILES_REPORT.md](./FACTORY_FILES_REPORT.md)
- **Unit Tests Guide**: [UNIT_TESTS_IMPLEMENTATION.md](./UNIT_TESTS_IMPLEMENTATION.md)
- **Pest Conversion Guide**: [PEST_PHP_CONVERSION.md](./PEST_PHP_CONVERSION.md)
- **Notification System**: [NOTIFICATION_SYSTEM.md](./NOTIFICATION_SYSTEM.md)

---

## CI/CD Integration

### GitHub Actions Example
```yaml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: 8.2
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: php artisan test --coverage --min=70
```

---

## Summary

**Total Test Suite**: 660+ tests across 19+ files
**Execution Time**: ~30-40 seconds (full suite)
**Coverage**: 90%+ overall
**Confidence**: Production-ready test coverage

### Quick Commands Reference
```bash
# Run everything
php artisan test

# Run with coverage
php artisan test --coverage --min=70

# Run controllers
php vendor/bin/pest tests/Feature/Controllers/

# Run services
php artisan test tests/Unit/Services/

# Run notifications
composer test:notifications

# Debug failing test
php artisan test --filter="test name" --verbose --stop-on-failure
```

---

**Last Updated**: 2025-10-10
**Test Suite Version**: 1.0
**Maintained By**: Development Team
