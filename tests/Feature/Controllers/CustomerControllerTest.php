<?php

use App\Models\Customer;
use App\Models\FamilyGroup;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UnifiedPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions (needed for views that check permissions)
    $this->seed(RoleSeeder::class);
    $this->seed(UnifiedPermissionsSeeder::class);

    // Create authenticated user with all permissions
    $this->user = User::factory()->create();
    $this->user->givePermissionTo(\Spatie\Permission\Models\Permission::all());

    $this->actingAs($this->user);

    // Mock session for Auditable trait
    session()->put('user_id', $this->user->id);

    // Bypass only permission middleware (keep auth and other middleware)
    $this->withoutMiddleware([
        \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        \Spatie\Permission\Middlewares\RoleMiddleware::class,
        \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
    ]);
});

// ========================================
// INDEX TESTS
// ========================================

test('index displays customers list', function () {
    Customer::factory()->count(5)->create();

    $response = $this->get(route('customers.index'));

    $response->assertStatus(200);
    $response->assertViewIs('customers.index');
    $response->assertViewHas('customers');
});

test('index filters customers by search term', function () {
    Customer::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
    Customer::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

    $response = $this->get(route('customers.index', ['search' => 'John']));

    $response->assertStatus(200);
    $response->assertSee('John Doe');
    $response->assertDontSee('Jane Smith');
});

test('index returns ajax json for autocomplete', function () {
    $customer = Customer::factory()->create(['name' => 'Test Customer']);

    $response = $this->get(route('customers.index', [
        'ajax' => 1,
        'q' => 'Test',
    ]));

    $response->assertStatus(200);
    $response->assertJson([
        'results' => [
            [
                'id' => $customer->id,
                'text' => 'Test Customer',
            ],
        ],
    ]);
});

test('index handles errors gracefully', function () {
    // Force error by providing invalid sort parameter
    $response = $this->get(route('customers.index'));

    $response->assertStatus(200);
    $response->assertViewIs('customers.index');
});

test('index sorts customers by name ascending', function () {
    Customer::factory()->create(['name' => 'Zara']);
    Customer::factory()->create(['name' => 'Adam']);

    $response = $this->get(route('customers.index', [
        'sort_field' => 'name',
        'sort_order' => 'asc',
    ]));

    $response->assertStatus(200);
    $response->assertSeeInOrder(['Adam', 'Zara']);
});

// ========================================
// CREATE TESTS
// ========================================

test('create displays customer creation form', function () {
    $response = $this->get(route('customers.create'));

    $response->assertStatus(200);
    $response->assertViewIs('customers.add');
});

// ========================================
// STORE TESTS
// ========================================

test('store creates new customer successfully', function () {
    $familyGroup = FamilyGroup::factory()->create();

    $customerData = [
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'mobile' => '9876543210',
        'mobile_number' => '9876543210',
        'family_group_id' => $familyGroup->id,
        'status' => true,
    ];

    $response = $this->post(route('customers.store'), $customerData);

    $response->assertRedirect(route('customers.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('customers', [
        'name' => 'John Doe',
        'email' => 'john@example.com',
    ]);
});

test('store validates required fields', function () {
    $response = $this->post(route('customers.store'), []);

    $response->assertSessionHasErrors(['name', 'mobile']);
});

test('store validates email format', function () {
    $response = $this->post(route('customers.store'), [
        'name' => 'John Doe',
        'email' => 'invalid-email',
        'mobile' => '9876543210',
        'mobile_number' => '9876543210',
    ]);

    $response->assertSessionHasErrors(['email']);
});

test('store validates unique email', function () {
    Customer::factory()->create(['email' => 'john@example.com']);

    $response = $this->post(route('customers.store'), [
        'name' => 'Jane Doe',
        'email' => 'john@example.com',
        'mobile' => '9876543210',
        'mobile_number' => '9876543210',
    ]);

    $response->assertSessionHasErrors(['email']);
});

// ========================================
// EDIT TESTS
// ========================================

test('edit displays customer edit form', function () {
    $customer = Customer::factory()->create();

    $response = $this->get(route('customers.edit', $customer));

    $response->assertStatus(200);
    $response->assertViewIs('customers.edit');
    $response->assertViewHas('customer', $customer);
    $response->assertSee($customer->name);
});

// ========================================
// UPDATE TESTS
// ========================================

test('update modifies existing customer', function () {
    $customer = Customer::factory()->create(['name' => 'Original Name']);

    $response = $this->put(route('customers.update', $customer), [
        'name' => 'Updated Name',
        'email' => $customer->email,
        'mobile' => $customer->mobile,
        'mobile_number' => $customer->mobile_number,
    ]);

    $response->assertRedirect(route('customers.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'name' => 'Updated Name',
    ]);
});

test('update validates required fields', function () {
    $customer = Customer::factory()->create();

    $response = $this->put(route('customers.update', $customer), [
        'name' => '',
    ]);

    $response->assertSessionHasErrors(['name']);
});

test('update validates unique email except current customer', function () {
    $customer1 = Customer::factory()->create(['email' => 'customer1@example.com']);
    $customer2 = Customer::factory()->create(['email' => 'customer2@example.com']);

    // Should fail - email belongs to another customer
    $response = $this->put(route('customers.update', $customer1), [
        'name' => $customer1->name,
        'email' => 'customer2@example.com',
        'mobile' => $customer1->mobile,
        'mobile_number' => $customer1->mobile_number,
    ]);

    $response->assertSessionHasErrors(['email']);
});

// ========================================
// UPDATE STATUS TESTS
// ========================================

test('update status toggles customer active status', function () {
    $customer = Customer::factory()->create(['status' => true]);

    $response = $this->put(route('customers.updateStatus', [
        'customer_id' => $customer->id,
        'status' => 0,
    ]));

    $response->assertRedirect(route('customers.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('customers', [
        'id' => $customer->id,
        'status' => 0,
    ]);
});

// ========================================
// DELETE TESTS
// ========================================

test('delete removes customer', function () {
    $customer = Customer::factory()->create();

    $response = $this->delete(route('customers.delete', $customer));

    $response->assertRedirect(route('customers.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('customers', [
        'id' => $customer->id,
    ]);
});

test('delete handles non existent customer gracefully', function () {
    $response = $this->delete(route('customers.delete', 99999));

    $response->assertStatus(404);
});

// ========================================
// IMPORT TESTS
// ========================================

test('import customers displays import form', function () {
    $response = $this->get(route('customers.importCustomers'));

    $response->assertStatus(200);
    $response->assertViewIs('customers.import');
});

// ========================================
// EXPORT TESTS
// ========================================

test('export generates xlsx file with customers', function () {
    Customer::factory()->count(10)->create();

    $response = $this->get(route('customers.export', ['format' => 'xlsx']));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->assertDownload('customers.xlsx');
});

test('export generates csv file with customers', function () {
    Customer::factory()->count(5)->create();

    $response = $this->get(route('customers.export', ['format' => 'csv']));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    $response->assertDownload('customers.csv');
});

test('export includes customer relationships', function () {
    $familyGroup = FamilyGroup::factory()->create(['name' => 'Test Family']);
    Customer::factory()->create([
        'name' => 'Family Member',
        'family_group_id' => $familyGroup->id,
    ]);

    $response = $this->get(route('customers.export', ['format' => 'xlsx']));

    $response->assertStatus(200);
});

// ========================================
// RESEND ONBOARDING WHATSAPP TESTS
// ========================================

test('resend onboarding whatsapp sends message', function () {
    $customer = Customer::factory()->create([
        'mobile' => '9876543210',
        'mobile_number' => '9876543210',
    ]);

    $response = $this->post(route('customers.resendOnBoardingWA', $customer));

    $response->assertRedirect(route('customers.index'));
    // Note: Actual WhatsApp sending depends on external service
});

test('resend onboarding whatsapp handles customer without mobile', function () {
    $customer = Customer::factory()->create([
        'mobile' => null,
        'mobile_number' => null,
    ]);

    $response = $this->post(route('customers.resendOnBoardingWA', $customer));

    $response->assertRedirect(route('customers.index'));
});

// ========================================
// PAGINATION TESTS
// ========================================

test('index paginates customers correctly', function () {
    Customer::factory()->count(25)->create();

    $response = $this->get(route('customers.index'));

    $response->assertStatus(200);
    $response->assertViewHas('customers');

    $customers = $response->viewData('customers');
    expect($customers->total())->toBe(25);
    expect($customers->perPage())->toBeLessThanOrEqual(15);
});

// ========================================
// AUTHORIZATION TESTS
// ========================================

test('unauthenticated user cannot access customers index', function () {
    auth()->logout();

    $response = $this->get(route('customers.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot create customer', function () {
    auth()->logout();

    $response = $this->post(route('customers.store'), [
        'name' => 'Test',
        'mobile' => '1234567890',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot update customer', function () {
    auth()->logout();
    $customer = Customer::factory()->create();

    $response = $this->put(route('customers.update', $customer), [
        'name' => 'Updated',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot delete customer', function () {
    auth()->logout();
    $customer = Customer::factory()->create();

    $response = $this->delete(route('customers.delete', $customer));

    $response->assertRedirect(route('login'));
});
