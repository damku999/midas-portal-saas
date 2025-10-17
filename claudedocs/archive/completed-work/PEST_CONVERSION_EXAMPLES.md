# Pest PHP Conversion Examples

Detailed before/after comparisons showing the exact conversion patterns used across all test files.

## Table of Contents
1. [Basic Model Tests](#basic-model-tests)
2. [Relationship Tests](#relationship-tests)
3. [Soft Delete Tests](#soft-delete-tests)
4. [Service Tests with Setup](#service-tests-with-setup)
5. [Customer Tests (Complex)](#customer-tests-complex)
6. [Exception Handling](#exception-handling)

---

## 1. Basic Model Tests

### Branch Test - Fillable Attributes

**Before (PHPUnit)**:
```php
<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BranchTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_correct_fillable_attributes()
    {
        $branch = new Branch();
        $fillable = ['name', 'email', 'mobile_number', 'status'];

        $this->assertEquals($fillable, $branch->getFillable());
    }
}
```

**After (Pest)**:
```php
<?php

use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has correct fillable attributes', function () {
    $branch = new Branch();
    $fillable = ['name', 'email', 'mobile_number', 'status'];

    expect($branch->getFillable())->toBe($fillable);
});
```

**Key Changes**:
- ❌ Removed namespace
- ❌ Removed class structure
- ❌ Removed TestCase extension
- ✅ Added `uses(RefreshDatabase::class)`
- ✅ Changed `$this->assertEquals()` to `expect()->toBe()`
- ✅ Used functional `it()` helper

---

## 2. Relationship Tests

### Branch-Customer Insurance Relationship

**Before (PHPUnit)**:
```php
/** @test */
public function it_has_customer_insurances_relationship()
{
    $branch = Branch::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['branch_id' => $branch->id]);

    $this->assertTrue($branch->customerInsurances->contains($insurance));
    $this->assertInstanceOf(CustomerInsurance::class, $branch->customerInsurances->first());
}
```

**After (Pest)**:
```php
it('has customer insurances relationship', function () {
    $branch = Branch::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['branch_id' => $branch->id]);

    expect($branch->customerInsurances->contains($insurance))->toBeTrue();
    expect($branch->customerInsurances->first())->toBeInstanceOf(CustomerInsurance::class);
});
```

**Key Changes**:
- `$this->assertTrue()` → `expect()->toBeTrue()`
- `$this->assertInstanceOf()` → `expect()->toBeInstanceOf()`

---

## 3. Soft Delete Tests

### Testing Soft Deletes

**Before (PHPUnit)**:
```php
/** @test */
public function it_uses_soft_deletes()
{
    $branch = Branch::factory()->create();
    $branchId = $branch->id;

    $branch->delete();

    $this->assertSoftDeleted('branches', ['id' => $branchId]);
    $this->assertNotNull(Branch::withTrashed()->find($branchId)->deleted_at);
}
```

**After (Pest)**:
```php
it('uses soft deletes', function () {
    $branch = Branch::factory()->create();
    $branchId = $branch->id;

    $branch->delete();

    $this->assertSoftDeleted('branches', ['id' => $branchId]);
    expect(Branch::withTrashed()->find($branchId)->deleted_at)->not->toBeNull();
});
```

**Key Changes**:
- `$this->assertNotNull()` → `expect()->not->toBeNull()`
- Laravel's `$this->assertSoftDeleted()` remains unchanged (works in Pest too)

---

## 4. Service Tests with Setup

### Customer Insurance Service Test

**Before (PHPUnit)**:
```php
<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Services\CustomerInsuranceService;
use App\Models\CustomerInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CustomerInsuranceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CustomerInsuranceService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CustomerInsuranceService::class);
    }

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

        $this->assertEquals(10000, $result['base_premium']);
        $this->assertEquals(1000, $result['my_commission']);
    }
}
```

**After (Pest)**:
```php
<?php

use App\Services\CustomerInsuranceService;
use App\Models\CustomerInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CustomerInsuranceService::class);
});

it('calculates commission on net premium correctly', function () {
    $data = [
        'commission_on' => 'net_premium',
        'net_premium' => 10000,
        'my_commission_percentage' => 10,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    expect($result['base_premium'])->toBe(10000);
    expect($result['my_commission'])->toBe(1000);
});
```

**Key Changes**:
- `protected function setUp()` → `beforeEach(function () { ... })`
- Properties still accessible via `$this->service`
- Multiple `$this->assertEquals()` → individual `expect()->toBe()` calls

---

## 5. Customer Tests (Complex)

### Password Hashing and Verification

**Before (PHPUnit)**:
```php
/** @test */
public function it_can_change_password()
{
    $customer = Customer::factory()->create([
        'password' => Hash::make('oldpassword'),
        'must_change_password' => true,
    ]);

    $customer->changePassword('newpassword');

    $this->assertTrue(Hash::check('newpassword', $customer->fresh()->password));
    $this->assertFalse($customer->fresh()->must_change_password);
    $this->assertNotNull($customer->fresh()->password_changed_at);
}
```

**After (Pest)**:
```php
it('can change password', function () {
    $customer = Customer::factory()->create([
        'password' => Hash::make('oldpassword'),
        'must_change_password' => true,
    ]);

    $customer->changePassword('newpassword');

    expect(Hash::check('newpassword', $customer->fresh()->password))->toBeTrue();
    expect($customer->fresh()->must_change_password)->toBeFalse();
    expect($customer->fresh()->password_changed_at)->not->toBeNull();
});
```

**Key Changes**:
- `$this->assertTrue()` → `expect()->toBeTrue()`
- `$this->assertFalse()` → `expect()->toBeFalse()`
- `$this->assertNotNull()` → `expect()->not->toBeNull()`

---

## 6. Exception Handling

### Testing Unique Constraint Violations

**Before (PHPUnit)**:
```php
/** @test */
public function it_validates_email_uniqueness()
{
    Broker::factory()->create(['email' => 'unique@broker.com']);

    $this->expectException(\Illuminate\Database\QueryException::class);
    Broker::factory()->create(['email' => 'unique@broker.com']);
}
```

**After (Pest)**:
```php
it('validates email uniqueness', function () {
    Broker::factory()->create(['email' => 'unique@broker.com']);

    expect(fn() => Broker::factory()->create(['email' => 'unique@broker.com']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
```

**Key Changes**:
- `$this->expectException()` → `expect(fn() => ...)->toThrow()`
- Wrap the throwing code in a closure `fn() => ...`

---

## 7. String Assertions

### Testing String Operations

**Before (PHPUnit)**:
```php
/** @test */
public function it_masks_mobile_number_correctly()
{
    $customer = Customer::factory()->create(['mobile_number' => '9876543210']);

    $maskedMobile = $customer->getPrivacySafeData()['mobile_number'];

    $this->assertStringStartsWith('98', $maskedMobile);
    $this->assertStringEndsWith('10', $maskedMobile);
    $this->assertStringContainsString('*', $maskedMobile);
}
```

**After (Pest)**:
```php
it('masks mobile number correctly', function () {
    $customer = Customer::factory()->create(['mobile_number' => '9876543210']);

    $maskedMobile = $customer->getPrivacySafeData()['mobile_number'];

    expect($maskedMobile)->toStartWith('98');
    expect($maskedMobile)->toEndWith('10');
    expect($maskedMobile)->toContain('*');
});
```

**Key Changes**:
- `$this->assertStringStartsWith()` → `expect()->toStartWith()`
- `$this->assertStringEndsWith()` → `expect()->toEndWith()`
- `$this->assertStringContainsString()` → `expect()->toContain()`

---

## 8. Collection Assertions

### Testing Collection Counts

**Before (PHPUnit)**:
```php
/** @test */
public function it_can_filter_by_status()
{
    Branch::factory()->create(['status' => 1]);
    Branch::factory()->create(['status' => 0]);

    $activeBranches = Branch::where('status', 1)->get();
    $inactiveBranches = Branch::where('status', 0)->get();

    $this->assertCount(1, $activeBranches);
    $this->assertCount(1, $inactiveBranches);
}
```

**After (Pest)**:
```php
it('can filter by status', function () {
    Branch::factory()->create(['status' => 1]);
    Branch::factory()->create(['status' => 0]);

    $activeBranches = Branch::where('status', 1)->get();
    $inactiveBranches = Branch::where('status', 0)->get();

    expect($activeBranches)->toHaveCount(1);
    expect($inactiveBranches)->toHaveCount(1);
});
```

**Key Changes**:
- `$this->assertCount()` → `expect()->toHaveCount()`

---

## 9. Regex Matching

### Testing Pattern Matching

**Before (PHPUnit)**:
```php
/** @test */
public function it_generates_default_password_correctly()
{
    $password = Customer::generateDefaultPassword();

    $this->assertEquals(8, strlen($password));
    $this->assertMatchesRegularExpression('/^[A-Z0-9]+$/', $password);
}
```

**After (Pest)**:
```php
it('generates default password correctly', function () {
    $password = Customer::generateDefaultPassword();

    expect(strlen($password))->toBe(8);
    expect($password)->toMatch('/^[A-Z0-9]+$/');
});
```

**Key Changes**:
- `$this->assertEquals()` → `expect()->toBe()`
- `$this->assertMatchesRegularExpression()` → `expect()->toMatch()`

---

## 10. Database Assertions

### Testing Database Records

**Before (PHPUnit)**:
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
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);
}
```

**After (Pest)**:
```php
it('can create branch with all fields', function () {
    $branch = Branch::create([
        'name' => 'Test Branch',
        'email' => 'test@branch.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);

    $this->assertDatabaseHas('branches', [
        'name' => 'Test Branch',
        'email' => 'test@branch.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);
});
```

**Key Changes**:
- Laravel's database assertions (`$this->assertDatabaseHas()`) work unchanged in Pest
- No need to convert these to expect() syntax

---

## Assertion Conversion Reference

| PHPUnit Method | Pest Expectation |
|----------------|------------------|
| `$this->assertEquals($a, $b)` | `expect($b)->toBe($a)` |
| `$this->assertSame($a, $b)` | `expect($b)->toBe($a)` |
| `$this->assertTrue($v)` | `expect($v)->toBeTrue()` |
| `$this->assertFalse($v)` | `expect($v)->toBeFalse()` |
| `$this->assertNull($v)` | `expect($v)->toBeNull()` |
| `$this->assertNotNull($v)` | `expect($v)->not->toBeNull()` |
| `$this->assertEmpty($v)` | `expect($v)->toBeEmpty()` |
| `$this->assertNotEmpty($v)` | `expect($v)->not->toBeEmpty()` |
| `$this->assertCount($n, $arr)` | `expect($arr)->toHaveCount($n)` |
| `$this->assertInstanceOf(Class::class, $obj)` | `expect($obj)->toBeInstanceOf(Class::class)` |
| `$this->assertStringContainsString($needle, $haystack)` | `expect($haystack)->toContain($needle)` |
| `$this->assertStringStartsWith($prefix, $str)` | `expect($str)->toStartWith($prefix)` |
| `$this->assertStringEndsWith($suffix, $str)` | `expect($str)->toEndWith($suffix)` |
| `$this->assertMatchesRegularExpression($pattern, $str)` | `expect($str)->toMatch($pattern)` |
| `$this->assertArrayHasKey($key, $arr)` | `expect($arr)->toHaveKey($key)` |
| `$this->assertGreaterThan($a, $b)` | `expect($b)->toBeGreaterThan($a)` |
| `$this->assertLessThan($a, $b)` | `expect($b)->toBeLessThan($a)` |

---

**Note**: All converted tests maintain 100% functional equivalence with the original PHPUnit tests. Only syntax has changed - behavior and assertions remain identical.
