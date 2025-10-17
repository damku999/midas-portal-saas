<?php

use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Services\PolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(PolicyService::class);

    // Create required related models
    $this->customer = Customer::factory()->create();
    $this->insuranceCompany = InsuranceCompany::factory()->create();
    $this->policyType = PolicyType::factory()->create();
});

// ============================================
// CREATE POLICY TESTS
// ============================================

test('creates policy successfully with valid data', function () {
    $policyData = [
        'customer_id' => $this->customer->id,
        'policy_no' => 'POL'.time(),
        'insurance_company_id' => $this->insuranceCompany->id,
        'policy_type_id' => $this->policyType->id,
        'policy_start_date' => now()->format('Y-m-d'),
        'policy_end_date' => now()->addYear()->format('Y-m-d'),
        'final_premium_with_gst' => 10000,
        'net_premium' => 9000,
        'od_premium' => 5000,
        'tp_premium' => 4000,
        'status' => 1,
        'branch_id' => 1,
    ];

    $result = $this->service->createPolicy($policyData);

    expect($result)->toBeInstanceOf(CustomerInsurance::class);
    expect($result->policy_no)->toBe($policyData['policy_no']);
    expect($result->customer_id)->toBe($this->customer->id);
});

test('create policy uses transaction', function () {
    $policyData = [
        'customer_id' => 999999, // Invalid customer
        'policy_no' => 'POL'.time(),
        'insurance_company_id' => $this->insuranceCompany->id,
    ];

    expect(fn () => $this->service->createPolicy($policyData))
        ->toThrow(Exception::class);
});

// ============================================
// UPDATE POLICY TESTS
// ============================================

test('updates policy successfully', function () {
    $policy = CustomerInsurance::factory()->create([
        'customer_id' => $this->customer->id,
        'final_premium_with_gst' => 10000,
    ]);

    $updateData = [
        'final_premium_with_gst' => 12000,
        'status' => 1,
    ];

    $result = $this->service->updatePolicy($policy, $updateData);

    expect($result)->toBeTrue();
    expect($policy->fresh()->final_premium_with_gst)->toBe(12000);
});

test('updates policy status successfully', function () {
    $policy = CustomerInsurance::factory()->create([
        'customer_id' => $this->customer->id,
        'status' => 1,
    ]);

    $result = $this->service->updatePolicyStatus($policy, 0);

    expect($result)->toBeTrue();
    expect($policy->fresh()->status)->toBe(0);
});

// ============================================
// DELETE POLICY TESTS
// ============================================

test('deletes policy successfully', function () {
    $policy = CustomerInsurance::factory()->create([
        'customer_id' => $this->customer->id,
    ]);
    $policyId = $policy->id;

    $result = $this->service->deletePolicy($policy);

    expect($result)->toBeTrue();
    expect(CustomerInsurance::find($policyId))->toBeNull();
});

// ============================================
// QUERY METHOD TESTS
// ============================================

test('gets paginated policies', function () {
    CustomerInsurance::factory()->count(15)->create([
        'customer_id' => $this->customer->id,
    ]);

    $request = new Request;
    $result = $this->service->getPolicies($request);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    expect($result->total())->toBe(15);
});

test('gets customer policies', function () {
    CustomerInsurance::factory()->count(3)->create([
        'customer_id' => $this->customer->id,
    ]);

    // Create policies for another customer
    $otherCustomer = Customer::factory()->create();
    CustomerInsurance::factory()->count(2)->create([
        'customer_id' => $otherCustomer->id,
    ]);

    $result = $this->service->getCustomerPolicies($this->customer);

    expect($result->count())->toBe(3);
});

test('gets policies due for renewal', function () {
    // Create policies expiring in 15 days
    CustomerInsurance::factory()->count(3)->create([
        'customer_id' => $this->customer->id,
        'policy_end_date' => now()->addDays(15),
    ]);

    // Create policies expiring in 60 days
    CustomerInsurance::factory()->count(2)->create([
        'customer_id' => $this->customer->id,
        'policy_end_date' => now()->addDays(60),
    ]);

    $result = $this->service->getPoliciesDueForRenewal(30);

    expect($result->count())->toBe(3);
});

test('gets policies by insurance company', function () {
    CustomerInsurance::factory()->count(5)->create([
        'customer_id' => $this->customer->id,
        'insurance_company_id' => $this->insuranceCompany->id,
    ]);

    $otherCompany = InsuranceCompany::factory()->create();
    CustomerInsurance::factory()->count(2)->create([
        'customer_id' => $this->customer->id,
        'insurance_company_id' => $otherCompany->id,
    ]);

    $result = $this->service->getPoliciesByCompany($this->insuranceCompany->id);

    expect($result->count())->toBe(5);
});

test('gets active policies', function () {
    CustomerInsurance::factory()->count(4)->create([
        'customer_id' => $this->customer->id,
        'status' => 1,
    ]);

    CustomerInsurance::factory()->count(2)->create([
        'customer_id' => $this->customer->id,
        'status' => 0,
    ]);

    $result = $this->service->getActivePolicies();

    expect($result->count())->toBe(4);
});

test('gets expired policies', function () {
    // Create expired policies
    CustomerInsurance::factory()->count(3)->create([
        'customer_id' => $this->customer->id,
        'policy_end_date' => now()->subDays(10),
    ]);

    // Create active policies
    CustomerInsurance::factory()->count(2)->create([
        'customer_id' => $this->customer->id,
        'policy_end_date' => now()->addDays(30),
    ]);

    $result = $this->service->getExpiredPolicies();

    expect($result->count())->toBe(3);
});

test('gets policies by type', function () {
    CustomerInsurance::factory()->count(4)->create([
        'customer_id' => $this->customer->id,
        'policy_type_id' => $this->policyType->id,
    ]);

    $otherPolicyType = PolicyType::factory()->create();
    CustomerInsurance::factory()->count(2)->create([
        'customer_id' => $this->customer->id,
        'policy_type_id' => $otherPolicyType->id,
    ]);

    $result = $this->service->getPoliciesByType($this->policyType->id);

    expect($result->count())->toBe(4);
});

test('searches policies by query', function () {
    CustomerInsurance::factory()->create([
        'customer_id' => $this->customer->id,
        'policy_no' => 'POL001TEST',
    ]);

    CustomerInsurance::factory()->create([
        'customer_id' => $this->customer->id,
        'policy_no' => 'POL002TEST',
    ]);

    $result = $this->service->searchPolicies('POL001');

    expect($result->count())->toBeGreaterThanOrEqual(1);
});

// ============================================
// POLICY STATISTICS TESTS
// ============================================

test('gets policy statistics', function () {
    CustomerInsurance::factory()->count(10)->create([
        'customer_id' => $this->customer->id,
    ]);

    $result = $this->service->getPolicyStatistics();

    expect($result)->toBeArray();
    expect($result)->toHaveKey('total_policies');
});

test('checks if policy exists', function () {
    $policy = CustomerInsurance::factory()->create([
        'customer_id' => $this->customer->id,
    ]);

    $exists = $this->service->policyExists($policy->id);
    $notExists = $this->service->policyExists(99999);

    expect($exists)->toBeTrue();
    expect($notExists)->toBeFalse();
});

test('gets policy count by status', function () {
    CustomerInsurance::factory()->count(5)->create([
        'customer_id' => $this->customer->id,
        'status' => 1,
    ]);

    CustomerInsurance::factory()->count(3)->create([
        'customer_id' => $this->customer->id,
        'status' => 0,
    ]);

    $result = $this->service->getPolicyCountByStatus();

    expect($result)->toBeArray();
});

// ============================================
// FAMILY POLICY TESTS
// ============================================

test('returns empty collection for customer without family', function () {
    $customer = Customer::factory()->create(['family_group_id' => null]);

    $result = $this->service->getFamilyPolicies($customer);

    expect($result)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($result->count())->toBe(0);
});

// ============================================
// POLICY ACCESS CONTROL TESTS
// ============================================

test('customer can view their own policy', function () {
    $policy = CustomerInsurance::factory()->create([
        'customer_id' => $this->customer->id,
    ]);

    $result = $this->service->canCustomerViewPolicy($this->customer, $policy);

    expect($result)->toBeTrue();
});

test('customer cannot view other customer policy', function () {
    $otherCustomer = Customer::factory()->create();
    $policy = CustomerInsurance::factory()->create([
        'customer_id' => $otherCustomer->id,
    ]);

    $result = $this->service->canCustomerViewPolicy($this->customer, $policy);

    expect($result)->toBeFalse();
});

// ============================================
// RENEWAL PROCESSING TESTS
// ============================================

test('gets policies for renewal processing', function () {
    // Create policies expiring in 5 days (within 7-day window)
    CustomerInsurance::factory()->count(2)->create([
        'customer_id' => $this->customer->id,
        'policy_end_date' => now()->addDays(5),
    ]);

    // Create policies expiring in 15 days (outside 7-day window)
    CustomerInsurance::factory()->count(3)->create([
        'customer_id' => $this->customer->id,
        'policy_end_date' => now()->addDays(15),
    ]);

    $result = $this->service->getPoliciesForRenewalProcessing();

    expect($result->count())->toBe(2);
});

// ============================================
// EDGE CASE TESTS
// ============================================

test('handles empty policy collection', function () {
    $result = $this->service->getPoliciesDueForRenewal(30);

    expect($result)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($result->count())->toBe(0);
});

test('handles repository returning null for non-existent policy', function () {
    $result = $this->service->policyExists(99999);

    expect($result)->toBeFalse();
});

test('handles multiple policies for same customer', function () {
    CustomerInsurance::factory()->count(10)->create([
        'customer_id' => $this->customer->id,
    ]);

    $result = $this->service->getCustomerPolicies($this->customer);

    expect($result->count())->toBe(10);
});

test('handles policy filtering by date range', function () {
    $request = new Request([
        'from_date' => now()->subDays(30)->format('Y-m-d'),
        'to_date' => now()->format('Y-m-d'),
    ]);

    $result = $this->service->getPolicies($request);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
});
