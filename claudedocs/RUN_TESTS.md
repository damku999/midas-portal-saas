# Quick Start - Running Tests

## Prerequisites

Ensure you have:
1. PHP 8.1+ installed
2. Composer dependencies installed
3. SQLite extension enabled

## Installation

```bash
# Install dependencies (if not already done)
composer install

# Generate application key (if needed)
php artisan key:generate
```

## Running Tests

### Run All Tests
```bash
php artisan test
```

### Run Only Unit Tests
```bash
php artisan test --testsuite=Unit
```

### Run Specific Test File
```bash
# Branch tests
php artisan test tests/Unit/Models/BranchTest.php

# Customer tests
php artisan test tests/Unit/Models/CustomerTest.php

# Service tests
php artisan test tests/Unit/Services/CustomerInsuranceServiceTest.php
```

### Run With Coverage Report
```bash
# Simple coverage summary
php artisan test --coverage

# Detailed HTML coverage report
php artisan test --coverage-html coverage

# Then open: coverage/index.html in your browser
```

### Run Specific Test Method
```bash
php artisan test --filter="it_has_correct_fillable_attributes"
php artisan test --filter="BranchTest::it_has_correct_fillable_attributes"
```

### Run Tests with More Details
```bash
# Verbose output
php artisan test --verbose

# Stop on first failure
php artisan test --stop-on-failure

# Parallel execution (faster)
php artisan test --parallel
```

## Expected Output

```
PASS  Tests\Unit\Models\BranchTest
✓ it has correct fillable attributes
✓ it can create branch with all fields
✓ it has customer insurances relationship
✓ it uses soft deletes
✓ it can filter by status
✓ it can retrieve only active branches
✓ it stores audit trail fields

PASS  Tests\Unit\Models\CustomerTest
✓ it has correct fillable attributes
✓ it can create customer with basic fields
... (14 more tests)

PASS  Tests\Unit\Services\CustomerInsuranceServiceTest
✓ it calculates commission on net premium correctly
... (9 more tests)

Tests:  58 passed
Time:   2.34s
```

## Test Files Created

### Model Tests
- `tests/Unit/Models/BranchTest.php` (7 tests)
- `tests/Unit/Models/BrokerTest.php` (6 tests)
- `tests/Unit/Models/ReferenceUserTest.php` (5 tests)
- `tests/Unit/Models/CustomerTest.php` (16 tests)
- `tests/Unit/Models/CustomerInsuranceTest.php` (14 tests)

### Service Tests
- `tests/Unit/Services/CustomerInsuranceServiceTest.php` (10 tests)

### Factories
- `database/factories/BranchFactory.php`
- `database/factories/BrokerFactory.php`
- `database/factories/ReferenceUserFactory.php`

## Troubleshooting

### Issue: "SQLite extension not found"
**Solution**:
```bash
# Enable in php.ini
extension=sqlite3
extension=pdo_sqlite

# Restart PHP
```

### Issue: "Class not found" errors
**Solution**:
```bash
# Regenerate autoload files
composer dump-autoload

# Clear caches
php artisan config:clear
php artisan cache:clear
```

### Issue: Tests fail with database errors
**Solution**:
```bash
# Ensure test environment is set up
php artisan migrate:fresh --env=testing

# Or let RefreshDatabase handle it automatically
```

### Issue: "Memory limit exhausted"
**Solution**: Add to `phpunit.xml`:
```xml
<php>
    <ini name="memory_limit" value="512M"/>
</php>
```

## Documentation

For detailed information, see:
- **UNIT_TESTS_IMPLEMENTATION.md** - Complete test documentation
- **TESTING_SUITE_SUMMARY.md** - Quick summary and statistics

## Quick Test Examples

### Test Branch Creation
```php
/** @test */
public function it_can_create_branch_with_all_fields()
{
    $branch = Branch::create([
        'name' => 'Test Branch',
        'email' => 'test@branch.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);

    $this->assertDatabaseHas('branches', [
        'name' => 'Test Branch',
        'email' => 'test@branch.com',
    ]);
}
```

### Test Commission Calculation
```php
/** @test */
public function it_calculates_commission_on_net_premium_correctly()
{
    $data = [
        'commission_on' => 'net_premium',
        'net_premium' => 10000,
        'my_commission_percentage' => 10,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    $this->assertEquals(1000, $result['my_commission']);
}
```

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
      - name: Install Dependencies
        run: composer install
      - name: Run Tests
        run: php artisan test --coverage --min=65
```

### GitLab CI Example
```yaml
test:
  script:
    - composer install
    - php artisan test --coverage --min=65
```

## Test Statistics

- **Total Tests**: 58
- **Test Files**: 6 classes
- **Models Covered**: 5
- **Services Covered**: 1
- **Factories**: 3
- **Expected Runtime**: 2-5 seconds

## Coverage Goals

- Models: 80%+
- Services: 70%+
- Overall: 65%+

---

**Last Updated**: 2025-10-07
**Laravel Version**: 10.49.0
**PHPUnit**: Via Laravel Testing
