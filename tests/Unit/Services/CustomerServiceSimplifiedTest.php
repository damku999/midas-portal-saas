<?php

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CustomerService::class);

    Event::fake();
});

// ============================================
// QUERY METHOD TESTS
// ============================================

test('finds customer by email', function () {
    $customer = Customer::factory()->create(['email' => 'test@example.com']);

    $result = $this->service->findByEmail('test@example.com');

    expect($result)->toBeInstanceOf(Customer::class);
    expect($result->email)->toBe('test@example.com');
});

test('returns null when customer email not found', function () {
    $result = $this->service->findByEmail('nonexistent@example.com');

    expect($result)->toBeNull();
});

test('finds customer by mobile number', function () {
    $customer = Customer::factory()->create(['mobile_number' => '1234567890']);

    $result = $this->service->findByMobileNumber('1234567890');

    expect($result)->toBeInstanceOf(Customer::class);
    expect($result->mobile_number)->toBe('1234567890');
});

test('returns null when mobile number not found', function () {
    $result = $this->service->findByMobileNumber('9999999999');

    expect($result)->toBeNull();
});

test('gets active customers for selection', function () {
    Customer::factory()->count(3)->create(['status' => 1]);
    Customer::factory()->count(2)->create(['status' => 0]);

    $result = $this->service->getActiveCustomersForSelection();

    expect($result->count())->toBe(3);
    $result->each(fn ($customer) => expect($customer->status)->toBe(1));
});

test('gets customers by family group', function () {
    $familyGroupId = 5;
    Customer::factory()->count(3)->create(['family_group_id' => $familyGroupId]);
    Customer::factory()->count(2)->create(['family_group_id' => 10]); // Different family

    $result = $this->service->getCustomersByFamily($familyGroupId);

    expect($result->count())->toBe(3);
    $result->each(fn ($customer) => expect($customer->family_group_id)->toBe($familyGroupId));
});

test('gets customers by type', function () {
    Customer::factory()->count(5)->create(['type' => 'Retail']);
    Customer::factory()->count(3)->create(['type' => 'Corporate']);

    $retailCustomers = $this->service->getCustomersByType('Retail');
    $corporateCustomers = $this->service->getCustomersByType('Corporate');

    expect($retailCustomers->count())->toBe(5);
    expect($corporateCustomers->count())->toBe(3);
});

test('searches customers by name', function () {
    Customer::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    Customer::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);
    Customer::factory()->create(['name' => 'Bob Johnson', 'email' => 'bob@example.com']);

    $results = $this->service->searchCustomers('John');

    expect($results->count())->toBeGreaterThanOrEqual(1);
    expect($results->pluck('name')->contains('John Doe'))->toBeTrue();
});

// ============================================
// STATISTICS TESTS
// ============================================

test('gets customer statistics correctly', function () {
    Customer::factory()->count(5)->create(['type' => 'Retail', 'status' => 1]);
    Customer::factory()->count(2)->create(['type' => 'Retail', 'status' => 0]);
    Customer::factory()->count(3)->create(['type' => 'Corporate', 'status' => 1]);

    $stats = $this->service->getCustomerStatistics();

    expect($stats)->toHaveKey('total');
    expect($stats)->toHaveKey('active');
    expect($stats)->toHaveKey('corporate');
    expect($stats['total'])->toBe(10);
    expect($stats['corporate'])->toBe(3);
});

test('checks if customer exists', function () {
    $customer = Customer::factory()->create();

    $exists = $this->service->customerExists($customer->id);
    $notExists = $this->service->customerExists(99999);

    expect($exists)->toBeTrue();
    expect($notExists)->toBeFalse();
});

// ============================================
// DELETE CUSTOMER TESTS
// ============================================

test('deletes customer successfully', function () {
    $customer = Customer::factory()->create();
    $customerId = $customer->id;

    $result = $this->service->deleteCustomer($customer);

    expect($result)->toBeTrue();
    expect(Customer::withTrashed()->find($customerId)->trashed())->toBeTrue();
});

// ============================================
// UPDATE STATUS TESTS
// ============================================

test('updates customer status successfully', function () {
    $customer = Customer::factory()->create(['status' => 1]);

    $result = $this->service->updateCustomerStatus($customer->id, 0);

    expect($result)->toBeTrue();
    expect($customer->fresh()->status)->toBe(0);
});

test('throws exception for invalid status value', function () {
    $customer = Customer::factory()->create();

    expect(fn () => $this->service->updateCustomerStatus($customer->id, 5))
        ->toThrow(InvalidArgumentException::class);
});

test('throws exception for non-existent customer', function () {
    expect(fn () => $this->service->updateCustomerStatus(99999, 1))
        ->toThrow(InvalidArgumentException::class);
});

// ============================================
// EDGE CASE TESTS
// ============================================

test('handles empty customer collection for statistics', function () {
    $stats = $this->service->getCustomerStatistics();

    expect($stats['total'])->toBe(0);
    expect($stats['active'])->toBe(0);
    expect($stats['corporate'])->toBe(0);
});

test('handles null email when finding', function () {
    $result = $this->service->findByEmail('');

    expect($result)->toBeNull();
});

test('handles multiple customers with same type', function () {
    Customer::factory()->count(10)->create(['type' => 'Retail']);

    $result = $this->service->getCustomersByType('Retail');

    expect($result->count())->toBe(10);
});

test('gets paginated customers with request', function () {
    Customer::factory()->count(25)->create();

    $request = new Request;
    $result = $this->service->getCustomers($request);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    expect($result->perPage())->toBe(10);
    expect($result->total())->toBe(25);
});

test('handles customer with null family group', function () {
    Customer::factory()->count(3)->create(['family_group_id' => null]);

    $result = $this->service->getCustomersByFamily(5);

    expect($result->count())->toBe(0);
});

test('differentiates between retail and corporate customers', function () {
    Customer::factory()->count(7)->create(['type' => 'Retail']);
    Customer::factory()->count(3)->create(['type' => 'Corporate']);

    $retailStats = $this->service->getCustomersByType('Retail')->count();
    $corporateStats = $this->service->getCustomersByType('Corporate')->count();

    expect($retailStats)->toBe(7);
    expect($corporateStats)->toBe(3);
});
