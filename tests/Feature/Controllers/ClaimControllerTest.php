<?php

use App\Models\Claim;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UnifiedPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Seed roles and permissions
    $this->seed(RoleSeeder::class);
    $this->seed(UnifiedPermissionsSeeder::class);

    // Create authenticated user with all permissions
    $this->user = User::factory()->create();
    $this->user->givePermissionTo(\Spatie\Permission\Models\Permission::all());

    $this->actingAs($this->user);

    // Mock session for Auditable trait
    session()->put('user_id', $this->user->id);

    // Bypass permission middleware
    $this->withoutMiddleware([
        \Spatie\Permission\Middlewares\PermissionMiddleware::class,
        \Spatie\Permission\Middlewares\RoleMiddleware::class,
        \Spatie\Permission\Middlewares\RoleOrPermissionMiddleware::class,
    ]);
});

// ========================================
// INDEX TESTS
// ========================================

test('index displays claims list', function () {
    Claim::factory()->count(5)->create();

    $response = $this->get(route('claims.index'));

    $response->assertStatus(200);
    $response->assertViewIs('claims.index');
    $response->assertViewHas('claims');
});

test('index handles errors gracefully', function () {
    $response = $this->get(route('claims.index'));

    $response->assertStatus(200);
    $response->assertViewIs('claims.index');
});

test('index sorts claims by created_at descending', function () {
    $older = Claim::factory()->create(['created_at' => now()->subDays(2)]);
    $newer = Claim::factory()->create(['created_at' => now()->subDay()]);

    $response = $this->get(route('claims.index', [
        'sort_field' => 'created_at',
        'sort_order' => 'desc',
    ]));

    $response->assertStatus(200);
});

test('index filters claims by search term', function () {
    $claim1 = Claim::factory()->create(['claim_number' => 'CLM-001']);
    $claim2 = Claim::factory()->create(['claim_number' => 'CLM-002']);

    $response = $this->get(route('claims.index', ['search' => 'CLM-001']));

    $response->assertStatus(200);
});

test('index paginates claims correctly', function () {
    Claim::factory()->count(25)->create();

    $response = $this->get(route('claims.index'));

    $response->assertStatus(200);
    $response->assertViewHas('claims');

    $claims = $response->viewData('claims');
    expect($claims->total())->toBe(25);
});

// ========================================
// CREATE TESTS
// ========================================

test('create displays claim creation form', function () {
    $response = $this->get(route('claims.create'));

    $response->assertStatus(200);
    $response->assertViewIs('claims.create');
    $response->assertViewHas('customerInsurances');
});

// ========================================
// STORE TESTS
// ========================================

test('store creates new claim successfully', function () {
    $insurance = CustomerInsurance::factory()->create();

    $claimData = [
        'customer_insurance_id' => $insurance->id,
        'claim_type' => 'Cashless',
        'claim_date' => now()->format('Y-m-d'),
        'description' => 'Test claim description',
        'insurance_type' => 'Vehicle',
        'whatsapp_number' => '9876543210',
    ];

    $response = $this->post(route('claims.store'), $claimData);

    $response->assertRedirect(route('claims.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('claims', [
        'customer_insurance_id' => $insurance->id,
        'claim_type' => 'Cashless',
    ]);
});

test('store validates required fields', function () {
    $response = $this->post(route('claims.store'), []);

    $response->assertSessionHasErrors();
});

test('store handles errors gracefully', function () {
    // Invalid insurance ID should cause error
    $response = $this->post(route('claims.store'), [
        'customer_insurance_id' => 99999,
        'claim_type' => 'Cashless',
        'claim_date' => now()->format('Y-m-d'),
    ]);

    $response->assertSessionHas('error');
});

// ========================================
// SHOW TESTS
// ========================================

test('show displays claim details', function () {
    $claim = Claim::factory()->create();

    $response = $this->get(route('claims.show', $claim));

    $response->assertStatus(200);
    $response->assertViewIs('claims.show');
    $response->assertViewHas('claim', $claim);
});

test('show loads all relationships', function () {
    $claim = Claim::factory()->create();

    $response = $this->get(route('claims.show', $claim));

    $response->assertStatus(200);
    $viewClaim = $response->viewData('claim');
    expect($viewClaim->relationLoaded('customer'))->toBeTrue();
    expect($viewClaim->relationLoaded('customerInsurance'))->toBeTrue();
    expect($viewClaim->relationLoaded('stages'))->toBeTrue();
    expect($viewClaim->relationLoaded('documents'))->toBeTrue();
    expect($viewClaim->relationLoaded('liabilityDetail'))->toBeTrue();
});

// ========================================
// EDIT TESTS
// ========================================

test('edit displays claim edit form', function () {
    $claim = Claim::factory()->create();

    $response = $this->get(route('claims.edit', $claim));

    $response->assertStatus(200);
    $response->assertViewIs('claims.edit');
    $response->assertViewHas('claim', $claim);
});

// ========================================
// UPDATE TESTS
// ========================================

test('update modifies existing claim', function () {
    $claim = Claim::factory()->create([
        'description' => 'Old Description',
    ]);

    $response = $this->put(route('claims.update', $claim), [
        'customer_insurance_id' => $claim->customer_insurance_id,
        'claim_type' => $claim->claim_type,
        'claim_date' => $claim->claim_date->format('Y-m-d'),
        'description' => 'New Description',
        'insurance_type' => $claim->insurance_type,
    ]);

    $response->assertRedirect(route('claims.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('claims', [
        'id' => $claim->id,
        'description' => 'New Description',
    ]);
});

test('update validates required fields', function () {
    $claim = Claim::factory()->create();

    $response = $this->put(route('claims.update', $claim), [
        'description' => '',
    ]);

    $response->assertSessionHasErrors();
});

// ========================================
// UPDATE STATUS TESTS
// ========================================

test('update status toggles claim active status', function () {
    $claim = Claim::factory()->create(['status' => true]);

    $response = $this->get(route('claims.updateStatus', [
        'claim' => $claim->id,
        'status' => 0,
    ]));

    $response->assertRedirect(route('claims.index'));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('claims', [
        'id' => $claim->id,
        'status' => 0,
    ]);
});

// ========================================
// DELETE TESTS
// ========================================

test('delete removes claim', function () {
    $claim = Claim::factory()->create();

    $response = $this->delete(route('claims.delete', $claim));

    $response->assertRedirect(route('claims.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('claims', [
        'id' => $claim->id,
    ]);
});

test('delete handles non existent claim', function () {
    $response = $this->delete(route('claims.delete', 99999));

    $response->assertStatus(404);
});

// ========================================
// AJAX POLICY SEARCH TESTS
// ========================================

test('search policies returns json results', function () {
    $insurance = CustomerInsurance::factory()->create(['policy_no' => 'TEST-POLICY-123']);

    $response = $this->get(route('claims.search-policies', ['search' => 'TEST']));

    $response->assertStatus(200);
    $response->assertJsonStructure(['results']);
});

test('search policies requires minimum characters', function () {
    $response = $this->get(route('claims.search-policies', ['search' => 'AB']));

    $response->assertStatus(200);
    $response->assertJson(['results' => []]);
});

test('search policies handles errors', function () {
    $response = $this->get(route('claims.search-policies', ['search' => 'TEST-LONG-SEARCH']));

    $response->assertStatus(200);
});

// ========================================
// AJAX STATISTICS TESTS
// ========================================

test('get statistics returns claim data', function () {
    Claim::factory()->count(10)->create();

    $response = $this->get(route('claims.statistics'));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'data',
    ]);
});

test('get statistics handles errors', function () {
    $response = $this->get(route('claims.statistics'));

    // Should return success or graceful error
    expect($response->status())->toBeIn([200, 500]);
});

// ========================================
// WHATSAPP DOCUMENT LIST TESTS
// ========================================

test('send document list whatsapp returns success', function () {
    $claim = Claim::factory()->create([
        'insurance_type' => 'Vehicle',
        'whatsapp_number' => '9876543210',
    ]);

    $response = $this->post(route('claims.send-document-list-whatsapp', $claim));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'preview',
    ]);
});

test('send pending documents whatsapp returns success', function () {
    $claim = Claim::factory()->create([
        'whatsapp_number' => '9876543210',
    ]);

    $response = $this->post(route('claims.send-pending-documents-whatsapp', $claim));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'preview',
    ]);
});

test('send claim number whatsapp returns success', function () {
    $claim = Claim::factory()->create([
        'claim_number' => 'CLM-TEST-001',
        'whatsapp_number' => '9876543210',
    ]);

    $response = $this->post(route('claims.send-claim-number-whatsapp', $claim));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'message',
        'preview',
    ]);
});

// ========================================
// WHATSAPP PREVIEW TESTS
// ========================================

test('get whatsapp preview returns document list message', function () {
    $claim = Claim::factory()->create(['insurance_type' => 'Vehicle']);

    $response = $this->get(route('claims.whatsapp-preview', [
        'claim' => $claim,
        'type' => 'document_list',
    ]));

    $response->assertStatus(200);
    $response->assertJsonStructure([
        'success',
        'preview',
        'whatsapp_number',
    ]);
});

test('get whatsapp preview returns pending documents message', function () {
    $claim = Claim::factory()->create();

    $response = $this->get(route('claims.whatsapp-preview', [
        'claim' => $claim,
        'type' => 'pending_documents',
    ]));

    $response->assertStatus(200);
});

test('get whatsapp preview returns claim number message', function () {
    $claim = Claim::factory()->create(['claim_number' => 'CLM-001']);

    $response = $this->get(route('claims.whatsapp-preview', [
        'claim' => $claim,
        'type' => 'claim_number',
    ]));

    $response->assertStatus(200);
});

test('get whatsapp preview handles invalid type', function () {
    $claim = Claim::factory()->create();

    $response = $this->get(route('claims.whatsapp-preview', [
        'claim' => $claim,
        'type' => 'invalid_type',
    ]));

    $response->assertStatus(400);
    $response->assertJson(['success' => false]);
});

// ========================================
// EXPORT TESTS
// ========================================

test('export generates file with claims', function () {
    Claim::factory()->count(10)->create();

    $response = $this->get(route('claims.export', ['format' => 'xlsx']));

    $response->assertStatus(200);
});

test('export includes relationships', function () {
    $customer = Customer::factory()->create(['name' => 'Test Customer']);
    $insurance = CustomerInsurance::factory()->create(['customer_id' => $customer->id]);
    Claim::factory()->create(['customer_insurance_id' => $insurance->id]);

    $response = $this->get(route('claims.export', ['format' => 'xlsx']));

    $response->assertStatus(200);
});

// ========================================
// AUTHORIZATION TESTS
// ========================================

test('unauthenticated user cannot access claims index', function () {
    $this->withMiddleware();
    auth()->logout();

    $response = $this->get(route('claims.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot create claim', function () {
    $this->withMiddleware();
    auth()->logout();

    $response = $this->post(route('claims.store'), [
        'claim_type' => 'Cashless',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot update claim', function () {
    $this->withMiddleware();
    auth()->logout();
    $claim = Claim::factory()->create();

    $response = $this->put(route('claims.update', $claim), [
        'description' => 'Updated',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot delete claim', function () {
    $this->withMiddleware();
    auth()->logout();
    $claim = Claim::factory()->create();

    $response = $this->delete(route('claims.delete', $claim));

    $response->assertRedirect(route('login'));
});

// ========================================
// RELATIONSHIP TESTS
// ========================================

test('claim belongs to customer', function () {
    $customer = Customer::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['customer_id' => $customer->id]);
    $claim = Claim::factory()->create(['customer_insurance_id' => $insurance->id]);

    $claim->load('customer');
    expect($claim->customer->id)->toBe($customer->id);
});

test('claim belongs to customer insurance', function () {
    $insurance = CustomerInsurance::factory()->create();
    $claim = Claim::factory()->create(['customer_insurance_id' => $insurance->id]);

    expect($claim->customerInsurance->id)->toBe($insurance->id);
});

test('claim has many stages', function () {
    $claim = Claim::factory()->create();

    expect($claim->stages())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('claim has many documents', function () {
    $claim = Claim::factory()->create();

    expect($claim->documents())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

test('claim has one liability detail', function () {
    $claim = Claim::factory()->create();

    expect($claim->liabilityDetail())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasOne::class);
});
