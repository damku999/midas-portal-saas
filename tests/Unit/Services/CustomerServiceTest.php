<?php

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Events\Customer\CustomerProfileUpdated;
use App\Events\Customer\CustomerRegistered;
use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use App\Services\CustomerService;
use App\Services\FileUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock dependencies
    $this->repository = Mockery::mock(CustomerRepositoryInterface::class);
    $this->fileUploadService = Mockery::mock(FileUploadService::class);

    // Create service instance
    $this->service = new CustomerService($this->repository, $this->fileUploadService);

    // Disable email notifications by default
    config(['app.email_notifications_enabled' => false]);

    // Fake events
    Event::fake([
        CustomerRegistered::class,
        CustomerProfileUpdated::class,
    ]);

    // Fake mail
    Mail::fake();
});

afterEach(function () {
    Mockery::close();
});

// ============================================
// CREATE CUSTOMER TESTS
// ============================================

test('creates customer successfully with valid data', function () {
    $requestData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'mobile_number' => '1234567890',
        'status' => 1,
        'type' => 'Retail',
        'pan_card_number' => 'ABCDE1234F',
    ];

    $request = StoreCustomerRequest::create('/customers', 'POST', $requestData);
    $request->setContainer(app());
    $request->validateResolved();

    $customer = Customer::factory()->make($requestData);

    $this->repository->shouldReceive('create')
        ->once()
        ->with(Mockery::subset($requestData))
        ->andReturn($customer);

    $result = $this->service->createCustomer($request);

    expect($result)->toBeInstanceOf(Customer::class);
    expect($result->name)->toBe('John Doe');
    expect($result->email)->toBe('john@example.com');
});

test('throws exception when creating customer with duplicate email', function () {
    $requestData = [
        'name' => 'John Doe',
        'email' => 'existing@example.com',
        'mobile_number' => '1234567890',
        'status' => 1,
        'type' => 'Retail',
    ];

    $request = StoreCustomerRequest::create('/customers', 'POST', $requestData);
    $request->setContainer(app());

    // Mock repository to return existing customer
    $existingCustomer = Customer::factory()->make(['email' => 'existing@example.com']);
    $this->repository->shouldReceive('findByEmail')
        ->with('existing@example.com')
        ->andReturn($existingCustomer);

    expect(fn () => $this->service->createCustomer($request))
        ->toThrow(Exception::class, 'A customer with this email address already exists');
});

test('handles customer document uploads during creation', function () {
    Storage::fake('public');

    $requestData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'mobile_number' => '1234567890',
        'status' => 1,
        'type' => 'Retail',
    ];

    $panFile = UploadedFile::fake()->create('pan.pdf', 100);
    $aadharFile = UploadedFile::fake()->create('aadhar.pdf', 100);

    $request = StoreCustomerRequest::create('/customers', 'POST', $requestData, [], [], [], [
        'pan_card_path' => $panFile,
        'aadhar_card_path' => $aadharFile,
    ]);
    $request->setContainer(app());

    $customer = Customer::factory()->make($requestData);
    $customer->id = 1;

    $this->repository->shouldReceive('create')
        ->once()
        ->andReturn($customer);

    $this->fileUploadService->shouldReceive('uploadCustomerDocument')
        ->with($panFile, 1, 'pan_card', 'John Doe')
        ->andReturn('documents/customers/1/pan.pdf');

    $this->fileUploadService->shouldReceive('uploadCustomerDocument')
        ->with($aadharFile, 1, 'aadhar_card', 'John Doe')
        ->andReturn('documents/customers/1/aadhar.pdf');

    $result = $this->service->createCustomer($request);

    expect($result)->toBeInstanceOf(Customer::class);
});

test('fires CustomerRegistered event after successful creation', function () {
    $requestData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'mobile_number' => '1234567890',
        'status' => 1,
        'type' => 'Retail',
    ];

    $request = StoreCustomerRequest::create('/customers', 'POST', $requestData);
    $request->setContainer(app());

    $customer = Customer::factory()->make($requestData);

    $this->repository->shouldReceive('create')
        ->once()
        ->andReturn($customer);

    $this->service->createCustomer($request);

    Event::assertDispatched(CustomerRegistered::class);
});

test('rollback transaction when email sending fails', function () {
    // Enable email notifications
    config(['app.email_notifications_enabled' => true]);

    $requestData = [
        'name' => 'John Doe',
        'email' => 'invalid@example.com',
        'mobile_number' => '1234567890',
        'status' => 1,
        'type' => 'Retail',
    ];

    $request = StoreCustomerRequest::create('/customers', 'POST', $requestData);
    $request->setContainer(app());

    $customer = Customer::factory()->make($requestData);
    $customer->id = 1;

    $this->repository->shouldReceive('create')
        ->once()
        ->andReturn($customer);

    // Mock Mail to throw exception
    Mail::shouldReceive('send')->andThrow(new Exception('SMTP error'));

    expect(fn () => $this->service->createCustomer($request))
        ->toThrow(Exception::class, 'Unable to send welcome email');
});

// ============================================
// UPDATE CUSTOMER TESTS
// ============================================

test('updates customer successfully with valid data', function () {
    $customer = Customer::factory()->make([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'mobile_number' => '1111111111',
        'status' => 1,
        'type' => 'Retail',
    ]);

    $updateData = [
        'name' => 'New Name',
        'email' => 'new@example.com',
        'mobile_number' => '2222222222',
        'status' => 1,
        'type' => 'Corporate',
    ];

    $request = UpdateCustomerRequest::create('/customers/1', 'PUT', $updateData);
    $request->setContainer(app());

    $this->repository->shouldReceive('update')
        ->once()
        ->with($customer, Mockery::subset($updateData))
        ->andReturn(true);

    $result = $this->service->updateCustomer($request, $customer);

    expect($result)->toBeTrue();
});

test('fires CustomerProfileUpdated event when fields change', function () {
    $customer = Customer::factory()->make([
        'name' => 'Old Name',
        'email' => 'old@example.com',
        'status' => 1,
    ]);

    $updateData = [
        'name' => 'New Name',
        'email' => 'old@example.com', // Email unchanged
        'status' => 1,
        'mobile_number' => '1234567890',
        'type' => 'Retail',
    ];

    $request = UpdateCustomerRequest::create('/customers/1', 'PUT', $updateData);
    $request->setContainer(app());

    $this->repository->shouldReceive('update')
        ->once()
        ->andReturn(true);

    $this->service->updateCustomer($request, $customer);

    Event::assertDispatched(CustomerProfileUpdated::class, function ($event) {
        return in_array('name', $event->changedFields);
    });
});

test('does not fire event when no fields change', function () {
    $customer = Customer::factory()->make([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 1,
        'type' => 'Retail',
    ]);

    $updateData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'status' => 1,
        'type' => 'Retail',
        'mobile_number' => '1234567890',
    ];

    $request = UpdateCustomerRequest::create('/customers/1', 'PUT', $updateData);
    $request->setContainer(app());

    $this->repository->shouldReceive('update')
        ->once()
        ->andReturn(true);

    $this->service->updateCustomer($request, $customer);

    Event::assertNotDispatched(CustomerProfileUpdated::class);
});

test('handles document uploads during update', function () {
    Storage::fake('public');

    $customer = Customer::factory()->make(['name' => 'John Doe']);
    $customer->id = 1;

    $updateData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'mobile_number' => '1234567890',
        'status' => 1,
        'type' => 'Retail',
    ];

    $gstFile = UploadedFile::fake()->create('gst.pdf', 100);

    $request = UpdateCustomerRequest::create('/customers/1', 'PUT', $updateData, [], [], [], [
        'gst_path' => $gstFile,
    ]);
    $request->setContainer(app());

    $this->repository->shouldReceive('update')
        ->once()
        ->andReturn(true);

    $this->fileUploadService->shouldReceive('uploadCustomerDocument')
        ->with($gstFile, 1, 'gst', 'John Doe')
        ->andReturn('documents/customers/1/gst.pdf');

    $result = $this->service->updateCustomer($request, $customer);

    expect($result)->toBeTrue();
});

// ============================================
// UPDATE CUSTOMER STATUS TESTS
// ============================================

test('updates customer status successfully', function () {
    $customerId = 1;
    $newStatus = 0;

    Customer::factory()->create(['id' => $customerId, 'status' => 1]);

    $this->repository->shouldReceive('updateStatus')
        ->once()
        ->with($customerId, $newStatus)
        ->andReturn(true);

    $result = $this->service->updateCustomerStatus($customerId, $newStatus);

    expect($result)->toBeTrue();
});

test('validates customer status input', function () {
    expect(fn () => $this->service->updateCustomerStatus(999, 5))
        ->toThrow(InvalidArgumentException::class);
});

test('validates customer exists before status update', function () {
    expect(fn () => $this->service->updateCustomerStatus(99999, 1))
        ->toThrow(InvalidArgumentException::class);
});

// ============================================
// DELETE CUSTOMER TESTS
// ============================================

test('deletes customer successfully', function () {
    $customer = Customer::factory()->make(['id' => 1]);

    $this->repository->shouldReceive('delete')
        ->once()
        ->with($customer)
        ->andReturn(true);

    $result = $this->service->deleteCustomer($customer);

    expect($result)->toBeTrue();
});

test('delete operation uses transaction', function () {
    $customer = Customer::factory()->make(['id' => 1]);

    $this->repository->shouldReceive('delete')
        ->once()
        ->andThrow(new Exception('Database error'));

    expect(fn () => $this->service->deleteCustomer($customer))
        ->toThrow(Exception::class, 'Database error');
});

// ============================================
// QUERY METHOD TESTS
// ============================================

test('gets paginated customers', function () {
    $request = new \Illuminate\Http\Request;
    $paginatedResult = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 10);

    $this->repository->shouldReceive('getPaginated')
        ->once()
        ->with($request, 10)
        ->andReturn($paginatedResult);

    $result = $this->service->getCustomers($request);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
});

test('finds customer by email', function () {
    $email = 'test@example.com';
    $customer = Customer::factory()->make(['email' => $email]);

    $this->repository->shouldReceive('findByEmail')
        ->once()
        ->with($email)
        ->andReturn($customer);

    $result = $this->service->findByEmail($email);

    expect($result)->toBeInstanceOf(Customer::class);
    expect($result->email)->toBe($email);
});

test('finds customer by mobile number', function () {
    $mobile = '1234567890';
    $customer = Customer::factory()->make(['mobile_number' => $mobile]);

    $this->repository->shouldReceive('findByMobileNumber')
        ->once()
        ->with($mobile)
        ->andReturn($customer);

    $result = $this->service->findByMobileNumber($mobile);

    expect($result)->toBeInstanceOf(Customer::class);
    expect($result->mobile_number)->toBe($mobile);
});

test('gets active customers for selection', function () {
    $customers = collect([
        Customer::factory()->make(['status' => 1]),
        Customer::factory()->make(['status' => 1]),
    ]);

    $this->repository->shouldReceive('getActive')
        ->once()
        ->andReturn($customers);

    $result = $this->service->getActiveCustomersForSelection();

    expect($result)->toBeInstanceOf(\Illuminate\Database\Eloquent\Collection::class);
    expect($result->count())->toBe(2);
});

test('gets customers by family group', function () {
    $familyGroupId = 5;
    $customers = collect([
        Customer::factory()->make(['family_group_id' => $familyGroupId]),
        Customer::factory()->make(['family_group_id' => $familyGroupId]),
    ]);

    $this->repository->shouldReceive('getByFamilyGroup')
        ->once()
        ->with($familyGroupId)
        ->andReturn($customers);

    $result = $this->service->getCustomersByFamily($familyGroupId);

    expect($result->count())->toBe(2);
});

test('gets customers by type', function () {
    $type = 'Corporate';
    $customers = collect([
        Customer::factory()->make(['type' => $type]),
    ]);

    $this->repository->shouldReceive('getByType')
        ->once()
        ->with($type)
        ->andReturn($customers);

    $result = $this->service->getCustomersByType($type);

    expect($result->count())->toBe(1);
});

test('searches customers by query', function () {
    $query = 'John';
    $customers = collect([
        Customer::factory()->make(['name' => 'John Doe']),
        Customer::factory()->make(['name' => 'Johnny Smith']),
    ]);

    $this->repository->shouldReceive('search')
        ->once()
        ->with($query)
        ->andReturn($customers);

    $result = $this->service->searchCustomers($query);

    expect($result->count())->toBe(2);
});

// ============================================
// STATISTICS TESTS
// ============================================

test('gets customer statistics', function () {
    $allCustomers = collect([
        Customer::factory()->make(['type' => 'Retail', 'status' => 1]),
        Customer::factory()->make(['type' => 'Retail', 'status' => 0]),
        Customer::factory()->make(['type' => 'Corporate', 'status' => 1]),
    ]);

    $retailCustomers = $allCustomers->where('type', 'Retail');
    $corporateCustomers = $allCustomers->where('type', 'Corporate');

    $this->repository->shouldReceive('count')
        ->once()
        ->andReturn(3);

    $this->repository->shouldReceive('getByType')
        ->with('Retail')
        ->andReturn($retailCustomers);

    $this->repository->shouldReceive('getByType')
        ->with('Corporate')
        ->andReturn($corporateCustomers);

    $result = $this->service->getCustomerStatistics();

    expect($result)->toHaveKey('total');
    expect($result)->toHaveKey('active');
    expect($result)->toHaveKey('corporate');
    expect($result['total'])->toBe(3);
});

test('checks if customer exists', function () {
    $customerId = 1;

    $this->repository->shouldReceive('exists')
        ->once()
        ->with($customerId)
        ->andReturn(true);

    $result = $this->service->customerExists($customerId);

    expect($result)->toBeTrue();
});

// ============================================
// EDGE CASE TESTS
// ============================================

test('handles null email when finding by email', function () {
    $this->repository->shouldReceive('findByEmail')
        ->with('')
        ->andReturn(null);

    $result = $this->service->findByEmail('');

    expect($result)->toBeNull();
});

test('handles transaction rollback on repository failure', function () {
    $requestData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'mobile_number' => '1234567890',
        'status' => 1,
        'type' => 'Retail',
    ];

    $request = StoreCustomerRequest::create('/customers', 'POST', $requestData);
    $request->setContainer(app());

    $this->repository->shouldReceive('create')
        ->once()
        ->andThrow(new Exception('Database constraint violation'));

    expect(fn () => $this->service->createCustomer($request))
        ->toThrow(Exception::class, 'Database constraint violation');
});

test('handles empty customer collection for statistics', function () {
    $this->repository->shouldReceive('count')
        ->andReturn(0);

    $this->repository->shouldReceive('getByType')
        ->with('Retail')
        ->andReturn(collect([]));

    $this->repository->shouldReceive('getByType')
        ->with('Corporate')
        ->andReturn(collect([]));

    $result = $this->service->getCustomerStatistics();

    expect($result['total'])->toBe(0);
    expect($result['active'])->toBe(0);
    expect($result['corporate'])->toBe(0);
});
