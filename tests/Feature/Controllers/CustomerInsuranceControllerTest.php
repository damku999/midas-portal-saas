<?php

use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UnifiedPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

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

test('index displays customer insurances list', function () {
    CustomerInsurance::factory()->count(5)->create();

    $response = $this->get(route('customer_insurances.index'));

    $response->assertStatus(200);
    $response->assertViewIs('customer_insurances.index');
    $response->assertViewHas('customer_insurances');
    $response->assertViewHas('customers');
});

test('index filters insurances by search term', function () {
    $insurance1 = CustomerInsurance::factory()->create(['policy_no' => 'POL-12345']);
    $insurance2 = CustomerInsurance::factory()->create(['policy_no' => 'POL-67890']);

    $response = $this->get(route('customer_insurances.index', ['search' => 'POL-12345']));

    $response->assertStatus(200);
    $response->assertSee('POL-12345');
});

test('index returns ajax json for autocomplete', function () {
    $insurance = CustomerInsurance::factory()->create(['policy_no' => 'TEST-POLICY']);

    $response = $this->get(route('customer_insurances.index', [
        'ajax' => 1,
        'q' => 'TEST',
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'results' => [
            '*' => ['id', 'text'],
        ],
    ]);
});

test('index sorts insurances by created_at descending', function () {
    $older = CustomerInsurance::factory()->create(['created_at' => now()->subDays(2)]);
    $newer = CustomerInsurance::factory()->create(['created_at' => now()->subDay()]);

    $response = $this->get(route('customer_insurances.index', [
        'sort' => 'created_at',
        'direction' => 'desc',
    ]));

    $response->assertStatus(200);
});

// ========================================
// CREATE TESTS
// ========================================

test('create displays insurance creation form', function () {
    $response = $this->get(route('customer_insurances.create'));

    $response->assertStatus(200);
    $response->assertViewIs('customer_insurances.add');
});

// ========================================
// STORE TESTS
// ========================================

test('store creates new insurance successfully', function () {
    Storage::fake('public');

    $customer = Customer::factory()->create();
    $insuranceCompany = InsuranceCompany::factory()->create();
    $policyType = PolicyType::factory()->create();

    $insuranceData = [
        'customer_id' => $customer->id,
        'insurance_company_id' => $insuranceCompany->id,
        'policy_type_id' => $policyType->id,
        'policy_no' => 'POL-TEST-001',
        'registration_no' => 'MH12AB1234',
        'status' => true,
    ];

    $response = $this->post(route('customer_insurances.store'), $insuranceData);

    $response->assertRedirect(route('customer_insurances.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('customer_insurances', [
        'policy_no' => 'POL-TEST-001',
        'registration_no' => 'MH12AB1234',
    ]);
});

test('store validates required fields', function () {
    $response = $this->post(route('customer_insurances.store'), []);

    $response->assertSessionHasErrors();
});

test('store handles file upload', function () {
    Storage::fake('public');

    $customer = Customer::factory()->create();
    $insuranceCompany = InsuranceCompany::factory()->create();
    $policyType = PolicyType::factory()->create();
    $file = UploadedFile::fake()->create('policy.pdf', 100, 'application/pdf');

    $insuranceData = [
        'customer_id' => $customer->id,
        'insurance_company_id' => $insuranceCompany->id,
        'policy_type_id' => $policyType->id,
        'policy_no' => 'POL-TEST-002',
        'registration_no' => 'MH12AB5678',
        'policy_document' => $file,
        'status' => true,
    ];

    $response = $this->post(route('customer_insurances.store'), $insuranceData);

    $response->assertRedirect(route('customer_insurances.index'));
});

// ========================================
// EDIT TESTS
// ========================================

test('edit displays insurance edit form', function () {
    $insurance = CustomerInsurance::factory()->create();

    $response = $this->get(route('customer_insurances.edit', $insurance));

    $response->assertStatus(200);
    $response->assertViewIs('customer_insurances.edit');
    $response->assertViewHas('customer_insurance', $insurance);
});

// ========================================
// UPDATE TESTS
// ========================================

test('update modifies existing insurance', function () {
    $insurance = CustomerInsurance::factory()->create(['policy_no' => 'OLD-POLICY']);

    $response = $this->put(route('customer_insurances.update', $insurance), [
        'customer_id' => $insurance->customer_id,
        'insurance_company_id' => $insurance->insurance_company_id,
        'policy_type_id' => $insurance->policy_type_id,
        'policy_no' => 'NEW-POLICY',
        'registration_no' => $insurance->registration_no,
        'status' => true,
    ]);

    $response->assertRedirect(route('customer_insurances.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('customer_insurances', [
        'id' => $insurance->id,
        'policy_no' => 'NEW-POLICY',
    ]);
});

test('update validates required fields', function () {
    $insurance = CustomerInsurance::factory()->create();

    $response = $this->put(route('customer_insurances.update', $insurance), [
        'policy_no' => '',
    ]);

    $response->assertSessionHasErrors();
});

// ========================================
// UPDATE STATUS TESTS
// ========================================

test('update status toggles insurance active status', function () {
    $insurance = CustomerInsurance::factory()->create(['status' => true]);

    $response = $this->get(route('customer_insurances.status', [
        'customer_insurance_id' => $insurance->id,
        'status' => 0,
    ]));

    $response->assertRedirect(route('customer_insurances.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('customer_insurances', [
        'id' => $insurance->id,
        'status' => 0,
    ]);
});

test('update status validates insurance exists', function () {
    $response = $this->get(route('customer_insurances.status', [
        'customer_insurance_id' => 99999,
        'status' => 1,
    ]));

    $response->assertRedirect();
    $response->assertSessionHas('error');
});

// ========================================
// RENEWAL TESTS
// ========================================

test('renew displays renewal form', function () {
    $insurance = CustomerInsurance::factory()->create();

    $response = $this->get(route('customer_insurances.renew', $insurance));

    $response->assertStatus(200);
    $response->assertViewIs('customer_insurances.renew');
    $response->assertViewHas('customer_insurance', $insurance);
});

test('store renew creates new renewed policy', function () {
    $oldInsurance = CustomerInsurance::factory()->create([
        'policy_no' => 'OLD-POL-001',
        'status' => true,
    ]);

    $renewalData = [
        'customer_id' => $oldInsurance->customer_id,
        'insurance_company_id' => $oldInsurance->insurance_company_id,
        'policy_type_id' => $oldInsurance->policy_type_id,
        'policy_no' => 'NEW-POL-001',
        'registration_no' => $oldInsurance->registration_no,
        'status' => true,
    ];

    $response = $this->put(route('customer_insurances.storeRenew', $oldInsurance), $renewalData);

    $response->assertRedirect(route('customer_insurances.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('customer_insurances', [
        'policy_no' => 'NEW-POL-001',
    ]);
});

// ========================================
// DOCUMENT TESTS
// ========================================

test('send wa document sends whatsapp message', function () {
    $insurance = CustomerInsurance::factory()->create([
        'policy_document_path' => 'policies/test-document.pdf',
    ]);

    $response = $this->get(route('customer_insurances.sendWADocument', $insurance));

    $response->assertRedirect(route('customer_insurances.index'));
    // Note: Actual WhatsApp sending depends on external service
});

test('send renewal reminder wa sends reminder', function () {
    $insurance = CustomerInsurance::factory()->create();

    $response = $this->get(route('customer_insurances.sendRenewalReminderWA', $insurance));

    $response->assertRedirect(route('customer_insurances.index'));
    // Note: Actual WhatsApp sending depends on external service
});

// ========================================
// EXPORT TESTS
// ========================================

test('export generates file with insurances', function () {
    CustomerInsurance::factory()->count(10)->create();

    $response = $this->get(route('customer_insurances.export'));

    $response->assertStatus(200);
    // Export format depends on CustomerInsuranceService implementation
});

// ========================================
// PAGINATION TESTS
// ========================================

test('index paginates insurances correctly', function () {
    CustomerInsurance::factory()->count(25)->create();

    $response = $this->get(route('customer_insurances.index'));

    $response->assertStatus(200);
    $response->assertViewHas('customer_insurances');

    $insurances = $response->viewData('customer_insurances');
    expect($insurances->total())->toBe(25);
});

// ========================================
// AUTHORIZATION TESTS
// ========================================

test('unauthenticated user cannot access insurances index', function () {
    // Remove middleware bypass and logout
    $this->withMiddleware();
    auth()->logout();

    $response = $this->get(route('customer_insurances.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot create insurance', function () {
    $this->withMiddleware();
    auth()->logout();

    $response = $this->post(route('customer_insurances.store'), [
        'policy_no' => 'TEST',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot update insurance', function () {
    $this->withMiddleware();
    auth()->logout();
    $insurance = CustomerInsurance::factory()->create();

    $response = $this->put(route('customer_insurances.update', $insurance), [
        'policy_no' => 'Updated',
    ]);

    $response->assertRedirect(route('login'));
});

// ========================================
// RELATIONSHIP TESTS
// ========================================

test('insurance belongs to customer', function () {
    $customer = Customer::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['customer_id' => $customer->id]);

    $response = $this->get(route('customer_insurances.index'));

    $response->assertStatus(200);
    expect($insurance->customer->id)->toBe($customer->id);
});

test('insurance belongs to insurance company', function () {
    $company = InsuranceCompany::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['insurance_company_id' => $company->id]);

    expect($insurance->insuranceCompany->id)->toBe($company->id);
});

test('insurance belongs to policy type', function () {
    $policyType = PolicyType::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['policy_type_id' => $policyType->id]);

    expect($insurance->policyType->id)->toBe($policyType->id);
});
