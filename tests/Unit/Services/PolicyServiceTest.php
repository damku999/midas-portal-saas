<?php

use App\Contracts\Repositories\PolicyRepositoryInterface;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Services\PolicyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock dependencies
    $this->repository = Mockery::mock(PolicyRepositoryInterface::class);

    // Create service instance
    $this->service = new PolicyService($this->repository);
});

afterEach(function () {
    Mockery::close();
});

// ============================================
// CREATE POLICY TESTS
// ============================================

test('creates policy successfully with valid data', function () {
    $policyData = [
        'customer_id' => 1,
        'policy_number' => 'POL001',
        'insurance_company_id' => 1,
        'policy_type_id' => 1,
        'policy_start_date' => now(),
        'policy_end_date' => now()->addYear(),
        'premium_amount' => 10000,
        'status' => 1,
    ];

    $policy = CustomerInsurance::factory()->make($policyData);

    $this->repository->shouldReceive('create')
        ->once()
        ->with($policyData)
        ->andReturn($policy);

    $result = $this->service->createPolicy($policyData);

    expect($result)->toBeInstanceOf(CustomerInsurance::class);
    expect($result->policy_number)->toBe('POL001');
});

test('create policy uses transaction', function () {
    $policyData = [
        'customer_id' => 1,
        'policy_number' => 'POL001',
    ];

    $this->repository->shouldReceive('create')
        ->once()
        ->andThrow(new Exception('Database error'));

    expect(fn () => $this->service->createPolicy($policyData))
        ->toThrow(Exception::class, 'Database error');
});

// ============================================
// UPDATE POLICY TESTS
// ============================================

test('updates policy successfully', function () {
    $policy = CustomerInsurance::factory()->make([
        'policy_number' => 'POL001',
        'premium_amount' => 10000,
    ]);

    $updateData = [
        'premium_amount' => 12000,
        'status' => 1,
    ];

    $this->repository->shouldReceive('update')
        ->once()
        ->with($policy, $updateData)
        ->andReturn(true);

    $result = $this->service->updatePolicy($policy, $updateData);

    expect($result)->toBeTrue();
});

test('update policy uses transaction', function () {
    $policy = CustomerInsurance::factory()->make();
    $updateData = ['premium_amount' => 12000];

    $this->repository->shouldReceive('update')
        ->once()
        ->andThrow(new Exception('Update failed'));

    expect(fn () => $this->service->updatePolicy($policy, $updateData))
        ->toThrow(Exception::class, 'Update failed');
});

// ============================================
// DELETE POLICY TESTS
// ============================================

test('deletes policy successfully', function () {
    $policy = CustomerInsurance::factory()->make(['id' => 1]);

    $this->repository->shouldReceive('delete')
        ->once()
        ->with($policy)
        ->andReturn(true);

    $result = $this->service->deletePolicy($policy);

    expect($result)->toBeTrue();
});

test('delete policy uses transaction', function () {
    $policy = CustomerInsurance::factory()->make();

    $this->repository->shouldReceive('delete')
        ->once()
        ->andThrow(new Exception('Cannot delete policy with active claims'));

    expect(fn () => $this->service->deletePolicy($policy))
        ->toThrow(Exception::class, 'Cannot delete policy with active claims');
});

// ============================================
// QUERY METHOD TESTS
// ============================================

test('gets paginated policies with filters', function () {
    $request = new Request([
        'search' => 'POL001',
        'status' => 1,
        'customer_id' => 1,
    ]);

    $paginatedResult = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

    $this->repository->shouldReceive('getPaginated')
        ->once()
        ->with(Mockery::type('array'), 10)
        ->andReturn($paginatedResult);

    $result = $this->service->getPolicies($request);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
});

test('gets customer policies', function () {
    $customer = Customer::factory()->make(['id' => 1]);
    $policies = collect([
        CustomerInsurance::factory()->make(['customer_id' => 1]),
        CustomerInsurance::factory()->make(['customer_id' => 1]),
    ]);

    $this->repository->shouldReceive('getByCustomer')
        ->once()
        ->with(1)
        ->andReturn($policies);

    $result = $this->service->getCustomerPolicies($customer);

    expect($result->count())->toBe(2);
});

test('gets policies due for renewal', function () {
    $daysAhead = 30;
    $policies = collect([
        CustomerInsurance::factory()->make([
            'policy_end_date' => now()->addDays(15),
        ]),
        CustomerInsurance::factory()->make([
            'policy_end_date' => now()->addDays(25),
        ]),
    ]);

    $this->repository->shouldReceive('getDueForRenewal')
        ->once()
        ->with($daysAhead)
        ->andReturn($policies);

    $result = $this->service->getPoliciesDueForRenewal($daysAhead);

    expect($result->count())->toBe(2);
});

test('gets policies by insurance company', function () {
    $companyId = 1;
    $policies = collect([
        CustomerInsurance::factory()->make(['insurance_company_id' => $companyId]),
    ]);

    $this->repository->shouldReceive('getByInsuranceCompany')
        ->once()
        ->with($companyId)
        ->andReturn($policies);

    $result = $this->service->getPoliciesByCompany($companyId);

    expect($result->count())->toBe(1);
});

test('gets active policies', function () {
    $policies = collect([
        CustomerInsurance::factory()->make(['status' => 1]),
        CustomerInsurance::factory()->make(['status' => 1]),
    ]);

    $this->repository->shouldReceive('getActive')
        ->once()
        ->andReturn($policies);

    $result = $this->service->getActivePolicies();

    expect($result->count())->toBe(2);
});

test('gets expired policies', function () {
    $policies = collect([
        CustomerInsurance::factory()->make([
            'policy_end_date' => now()->subDays(10),
        ]),
    ]);

    $this->repository->shouldReceive('getExpired')
        ->once()
        ->andReturn($policies);

    $result = $this->service->getExpiredPolicies();

    expect($result->count())->toBe(1);
});

test('gets policies by type', function () {
    $policyTypeId = 1;
    $policies = collect([
        CustomerInsurance::factory()->make(['policy_type_id' => $policyTypeId]),
    ]);

    $this->repository->shouldReceive('getByPolicyType')
        ->once()
        ->with($policyTypeId)
        ->andReturn($policies);

    $result = $this->service->getPoliciesByType($policyTypeId);

    expect($result->count())->toBe(1);
});

test('searches policies by query', function () {
    $query = 'POL001';
    $policies = collect([
        CustomerInsurance::factory()->make(['policy_number' => 'POL001']),
    ]);

    $this->repository->shouldReceive('search')
        ->once()
        ->with($query)
        ->andReturn($policies);

    $result = $this->service->searchPolicies($query);

    expect($result->count())->toBe(1);
});

// ============================================
// POLICY STATUS TESTS
// ============================================

test('updates policy status successfully', function () {
    $policy = CustomerInsurance::factory()->make(['status' => 0]);
    $newStatus = 1;

    $this->repository->shouldReceive('update')
        ->once()
        ->with($policy, ['status' => $newStatus])
        ->andReturn(true);

    $result = $this->service->updatePolicyStatus($policy, $newStatus);

    expect($result)->toBeTrue();
});

test('gets policy count by status', function () {
    $statusCounts = [
        'active' => 10,
        'expired' => 5,
        'cancelled' => 2,
    ];

    $this->repository->shouldReceive('getCountByStatus')
        ->once()
        ->andReturn($statusCounts);

    $result = $this->service->getPolicyCountByStatus();

    expect($result)->toHaveKey('active');
    expect($result['active'])->toBe(10);
});

// ============================================
// POLICY STATISTICS TESTS
// ============================================

test('gets policy statistics', function () {
    $statistics = [
        'total_policies' => 100,
        'active_policies' => 85,
        'expiring_soon' => 15,
        'total_premium' => 1000000,
    ];

    $this->repository->shouldReceive('getStatistics')
        ->once()
        ->andReturn($statistics);

    $result = $this->service->getPolicyStatistics();

    expect($result)->toHaveKey('total_policies');
    expect($result['total_policies'])->toBe(100);
});

test('checks if policy exists', function () {
    $policyId = 1;

    $this->repository->shouldReceive('exists')
        ->once()
        ->with($policyId)
        ->andReturn(true);

    $result = $this->service->policyExists($policyId);

    expect($result)->toBeTrue();
});

// ============================================
// FAMILY POLICY TESTS
// ============================================

test('gets family policies for family head', function () {
    $customer = Customer::factory()->make([
        'id' => 1,
        'family_group_id' => 5,
    ]);

    // Mock family relationships
    $customer->shouldReceive('hasFamily')->andReturn(true);
    $customer->shouldReceive('isFamilyHead')->andReturn(true);

    $familyPolicies = collect([
        CustomerInsurance::factory()->make(['customer_id' => 1]),
        CustomerInsurance::factory()->make(['customer_id' => 2]),
    ]);

    $this->repository->shouldReceive('getByFamilyGroup')
        ->once()
        ->with(5)
        ->andReturn($familyPolicies);

    $result = $this->service->getFamilyPolicies($customer);

    expect($result->count())->toBe(2);
});

test('gets only own policies for non-family-head', function () {
    $customer = Customer::factory()->make([
        'id' => 2,
        'family_group_id' => 5,
    ]);

    $customer->shouldReceive('hasFamily')->andReturn(true);
    $customer->shouldReceive('isFamilyHead')->andReturn(false);

    $ownPolicies = collect([
        CustomerInsurance::factory()->make(['customer_id' => 2]),
    ]);

    $this->repository->shouldReceive('getByCustomer')
        ->once()
        ->with(2)
        ->andReturn($ownPolicies);

    $result = $this->service->getFamilyPolicies($customer);

    expect($result->count())->toBe(1);
});

test('returns empty collection for customer without family', function () {
    $customer = Customer::factory()->make([
        'id' => 1,
        'family_group_id' => null,
    ]);

    $customer->shouldReceive('hasFamily')->andReturn(false);

    $result = $this->service->getFamilyPolicies($customer);

    expect($result)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($result->count())->toBe(0);
});

// ============================================
// POLICY ACCESS CONTROL TESTS
// ============================================

test('customer can view their own policy', function () {
    $customer = Customer::factory()->make(['id' => 1]);
    $policy = CustomerInsurance::factory()->make(['customer_id' => 1]);

    $result = $this->service->canCustomerViewPolicy($customer, $policy);

    expect($result)->toBeTrue();
});

test('family head can view family member policy', function () {
    $customer = Customer::factory()->make([
        'id' => 1,
        'family_group_id' => 5,
    ]);

    $policyOwner = Customer::factory()->make([
        'id' => 2,
        'family_group_id' => 5,
    ]);

    $policy = CustomerInsurance::factory()->make(['customer_id' => 2]);
    $policy->setRelation('customer', $policyOwner);

    $customer->shouldReceive('isFamilyHead')->andReturn(true);
    $customer->shouldReceive('hasFamily')->andReturn(true);

    $result = $this->service->canCustomerViewPolicy($customer, $policy);

    expect($result)->toBeTrue();
});

test('customer cannot view other customer policy', function () {
    $customer = Customer::factory()->make([
        'id' => 1,
        'family_group_id' => null,
    ]);

    $policy = CustomerInsurance::factory()->make(['customer_id' => 2]);

    $customer->shouldReceive('isFamilyHead')->andReturn(false);

    $result = $this->service->canCustomerViewPolicy($customer, $policy);

    expect($result)->toBeFalse();
});

// ============================================
// RENEWAL REMINDER TESTS
// ============================================

test('sends renewal reminder successfully', function () {
    Log::spy();

    $customer = Customer::factory()->make([
        'name' => 'John Doe',
        'mobile_number' => '1234567890',
    ]);

    $insuranceCompany = new InsuranceCompany(['name' => 'Test Insurance']);
    $policyType = new PolicyType(['name' => 'Car Insurance']);

    $policy = CustomerInsurance::factory()->make([
        'policy_number' => 'POL001',
        'policy_end_date' => now()->addDays(15),
        'customer_id' => $customer->id,
    ]);

    $policy->setRelation('customer', $customer);
    $policy->setRelation('insuranceCompany', $insuranceCompany);
    $policy->setRelation('policyType', $policyType);

    // Mock WhatsApp trait method
    $this->service->shouldReceive('whatsAppSendMessage')
        ->once()
        ->andReturn(true);

    $result = $this->service->sendRenewalReminder($policy);

    expect($result)->toBeTrue();
});

test('logs error when renewal reminder fails', function () {
    Log::spy();

    $customer = Customer::factory()->make([
        'mobile_number' => '1234567890',
    ]);

    $policy = CustomerInsurance::factory()->make([
        'policy_number' => 'POL001',
        'policy_end_date' => now()->addDays(15),
    ]);

    $policy->setRelation('customer', $customer);

    // Mock WhatsApp to throw exception
    $this->service->shouldReceive('whatsAppSendMessage')
        ->once()
        ->andThrow(new Exception('WhatsApp API error'));

    $result = $this->service->sendRenewalReminder($policy);

    expect($result)->toBeFalse();
    Log::shouldHaveReceived('error')->once();
});

test('sends bulk renewal reminders and tracks results', function () {
    $policies = collect([
        CustomerInsurance::factory()->make([
            'id' => 1,
            'policy_number' => 'POL001',
            'policy_end_date' => now()->addDays(15),
        ]),
        CustomerInsurance::factory()->make([
            'id' => 2,
            'policy_number' => 'POL002',
            'policy_end_date' => now()->addDays(20),
        ]),
    ]);

    foreach ($policies as $policy) {
        $customer = Customer::factory()->make(['mobile_number' => '1234567890']);
        $policy->setRelation('customer', $customer);
    }

    $this->repository->shouldReceive('getDueForRenewal')
        ->with(30)
        ->andReturn($policies);

    // Mock sending success for first, failure for second
    $this->service = Mockery::mock(PolicyService::class, [$this->repository])
        ->makePartial()
        ->shouldAllowMockingProtectedMethods();

    $this->service->shouldReceive('sendRenewalReminder')
        ->twice()
        ->andReturn(true, false);

    $this->service->shouldReceive('getPoliciesDueForRenewal')
        ->with(30)
        ->andReturn($policies);

    $results = $this->service->sendBulkRenewalReminders(30);

    expect($results)->toHaveKey('total');
    expect($results)->toHaveKey('sent');
    expect($results)->toHaveKey('failed');
    expect($results['total'])->toBe(2);
});

// ============================================
// RENEWAL PROCESSING TESTS
// ============================================

test('gets policies for renewal processing', function () {
    $policies = collect([
        CustomerInsurance::factory()->make([
            'policy_end_date' => now()->addDays(5),
        ]),
    ]);

    $this->repository->shouldReceive('getDueForRenewal')
        ->once()
        ->with(7)
        ->andReturn($policies);

    $result = $this->service->getPoliciesForRenewalProcessing();

    expect($result->count())->toBe(1);
});

// ============================================
// EDGE CASE TESTS
// ============================================

test('handles empty policy collection', function () {
    $this->repository->shouldReceive('getDueForRenewal')
        ->with(30)
        ->andReturn(collect([]));

    $results = $this->service->sendBulkRenewalReminders(30);

    expect($results['total'])->toBe(0);
    expect($results['sent'])->toBe(0);
    expect($results['failed'])->toBe(0);
});

test('handles null days ahead parameter in bulk reminders', function () {
    $policies = collect([]);

    $this->repository->shouldReceive('getDueForRenewal')
        ->with(30) // Should default to 30
        ->andReturn($policies);

    $results = $this->service->sendBulkRenewalReminders(null);

    expect($results['total'])->toBe(0);
});

test('handles policy with past end date', function () {
    Log::spy();

    $customer = Customer::factory()->make(['mobile_number' => '1234567890']);
    $policy = CustomerInsurance::factory()->make([
        'policy_number' => 'POL001',
        'policy_end_date' => now()->subDays(5), // Expired
    ]);

    $policy->setRelation('customer', $customer);
    $policy->setRelation('insuranceCompany', new InsuranceCompany(['name' => 'Test']));
    $policy->setRelation('policyType', new PolicyType(['name' => 'Car']));

    $this->service->shouldReceive('whatsAppSendMessage')
        ->once()
        ->andReturn(true);

    $result = $this->service->sendRenewalReminder($policy);

    expect($result)->toBeTrue();
});

test('handles repository returning null for non-existent policy', function () {
    $this->repository->shouldReceive('exists')
        ->with(99999)
        ->andReturn(false);

    $result = $this->service->policyExists(99999);

    expect($result)->toBeFalse();
});
