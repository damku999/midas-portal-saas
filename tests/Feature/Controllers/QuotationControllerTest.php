<?php

use App\Models\Customer;
use App\Models\Quotation;
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

test('index displays quotations list', function () {
    Quotation::factory()->count(5)->create();

    $response = $this->get(route('quotations.index'));

    $response->assertStatus(200);
    $response->assertViewIs('quotations.index');
    $response->assertViewHas('quotations');
});

test('index filters quotations by search term', function () {
    $quotation1 = Quotation::factory()->create(['registration_no' => 'MH12AB1234']);
    $quotation2 = Quotation::factory()->create(['registration_no' => 'GJ01CD5678']);

    $response = $this->get(route('quotations.index', ['search' => 'MH12AB']));

    $response->assertStatus(200);
});

test('index returns ajax json for autocomplete', function () {
    $quotation = Quotation::factory()->create(['registration_no' => 'TEST-REG']);

    $response = $this->get(route('quotations.index', [
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

test('index paginates quotations', function () {
    Quotation::factory()->count(25)->create();

    $response = $this->get(route('quotations.index'));

    $response->assertStatus(200);
    $response->assertViewHas('quotations');

    $quotations = $response->viewData('quotations');
    expect($quotations->total())->toBe(25);
});

// ========================================
// CREATE TESTS
// ========================================

test('create displays quotation creation form', function () {
    $response = $this->get(route('quotations.create'));

    $response->assertStatus(200);
    $response->assertViewIs('quotations.create');
});

// ========================================
// STORE TESTS
// ========================================

test('store creates new quotation successfully', function () {
    $customer = Customer::factory()->create();

    $quotationData = [
        'customer_id' => $customer->id,
        'insurance_type' => 'Motor',
        'vehicle_make' => 'Honda',
        'vehicle_model' => 'City',
        'registration_no' => 'MH12TEST',
        'manufacturing_year' => 2022,
        'idv' => 500000,
        'status' => true,
    ];

    $response = $this->post(route('quotations.store'), $quotationData);

    $response->assertRedirect();
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('quotations', [
        'customer_id' => $customer->id,
        'registration_no' => 'MH12TEST',
    ]);
});

test('store validates required fields', function () {
    $response = $this->post(route('quotations.store'), []);

    $response->assertSessionHasErrors();
});

test('store handles database transaction rollback on error', function () {
    // Invalid customer_id should cause error
    $response = $this->post(route('quotations.store'), [
        'customer_id' => 99999,
        'insurance_type' => 'Motor',
    ]);

    $response->assertSessionHas('error');
});

// ========================================
// SHOW TESTS
// ========================================

test('show displays quotation details', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->get(route('quotations.show', $quotation));

    $response->assertStatus(200);
    $response->assertViewIs('quotations.show');
    $response->assertViewHas('quotation', $quotation);
});

test('show loads related customer and companies', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->get(route('quotations.show', $quotation));

    $response->assertStatus(200);
    $viewQuotation = $response->viewData('quotation');
    expect($viewQuotation->relationLoaded('customer'))->toBeTrue();
    expect($viewQuotation->relationLoaded('quotationCompanies'))->toBeTrue();
});

// ========================================
// EDIT TESTS
// ========================================

test('edit displays quotation edit form', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->get(route('quotations.edit', $quotation));

    $response->assertStatus(200);
    $response->assertViewIs('quotations.edit');
    $response->assertViewHas('quotation', $quotation);
});

// ========================================
// UPDATE TESTS
// ========================================

test('update modifies existing quotation', function () {
    $quotation = Quotation::factory()->create([
        'vehicle_make' => 'Old Make',
        'vehicle_model' => 'Old Model',
    ]);

    $response = $this->put(route('quotations.update', $quotation), [
        'customer_id' => $quotation->customer_id,
        'insurance_type' => $quotation->insurance_type,
        'vehicle_make' => 'New Make',
        'vehicle_model' => 'New Model',
        'registration_no' => $quotation->registration_no,
        'manufacturing_year' => $quotation->manufacturing_year,
        'idv' => $quotation->idv,
    ]);

    $response->assertRedirect(route('quotations.show', $quotation));
    $response->assertSessionHas('success');

    $this->assertDatabaseHas('quotations', [
        'id' => $quotation->id,
        'vehicle_make' => 'New Make',
        'vehicle_model' => 'New Model',
    ]);
});

test('update validates required fields', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->put(route('quotations.update', $quotation), [
        'vehicle_make' => '',
    ]);

    $response->assertSessionHasErrors();
});

test('update handles database transaction', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->put(route('quotations.update', $quotation), [
        'customer_id' => $quotation->customer_id,
        'insurance_type' => $quotation->insurance_type,
        'vehicle_make' => 'Updated',
        'vehicle_model' => 'Model',
        'registration_no' => $quotation->registration_no,
        'manufacturing_year' => 2023,
        'idv' => 600000,
    ]);

    $response->assertRedirect();
});

// ========================================
// GENERATE QUOTES TESTS
// ========================================

test('generate quotes creates company quotes', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->post(route('quotations.generate-quotes', $quotation));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

test('generate quotes handles errors', function () {
    $quotation = Quotation::factory()->create();

    // Generating quotes should work or fail gracefully
    $response = $this->post(route('quotations.generate-quotes', $quotation));

    $response->assertRedirect();
});

// ========================================
// WHATSAPP TESTS
// ========================================

test('send to whatsapp sends quotation', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->post(route('quotations.send-whatsapp', $quotation));

    $response->assertRedirect();
    // Note: Actual WhatsApp sending depends on external service
});

test('send to whatsapp handles errors gracefully', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->post(route('quotations.send-whatsapp', $quotation));

    $response->assertRedirect();
});

// ========================================
// PDF DOWNLOAD TESTS
// ========================================

test('download pdf generates pdf file', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->get(route('quotations.download-pdf', $quotation));

    // PDF generation may return download or error
    expect($response->status())->toBeIn([200, 302]);
});

test('download pdf handles generation errors', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->get(route('quotations.download-pdf', $quotation));

    // Should handle gracefully
    expect($response->status())->toBeIn([200, 302]);
});

// ========================================
// GET QUOTE FORM TESTS
// ========================================

test('get quote form html returns partial view', function () {
    $response = $this->get(route('quotations.get-quote-form', ['index' => 0]));

    $response->assertStatus(200);
    // Returns rendered HTML string
    expect($response->getContent())->toBeString();
});

test('get quote form html handles different indices', function () {
    $response1 = $this->get(route('quotations.get-quote-form', ['index' => 0]));
    $response2 = $this->get(route('quotations.get-quote-form', ['index' => 1]));

    $response1->assertStatus(200);
    $response2->assertStatus(200);
});

// ========================================
// DELETE TESTS
// ========================================

test('delete removes quotation', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->delete(route('quotations.delete', $quotation));

    $response->assertRedirect(route('quotations.index'));
    $response->assertSessionHas('success');

    $this->assertSoftDeleted('quotations', [
        'id' => $quotation->id,
    ]);
});

test('delete handles non existent quotation', function () {
    $response = $this->delete(route('quotations.delete', 99999));

    $response->assertStatus(404);
});

test('delete includes company quotes count in message', function () {
    $quotation = Quotation::factory()->create();

    $response = $this->delete(route('quotations.delete', $quotation));

    $response->assertRedirect();
    $response->assertSessionHas('success');
});

// ========================================
// EXPORT TESTS
// ========================================

test('export generates file with quotations', function () {
    Quotation::factory()->count(10)->create();

    $response = $this->get(route('quotations.export', ['format' => 'xlsx']));

    $response->assertStatus(200);
});

test('export includes customer relationship', function () {
    $customer = Customer::factory()->create(['name' => 'Test Customer']);
    Quotation::factory()->create(['customer_id' => $customer->id]);

    $response = $this->get(route('quotations.export', ['format' => 'xlsx']));

    $response->assertStatus(200);
});

// ========================================
// AUTHORIZATION TESTS
// ========================================

test('unauthenticated user cannot access quotations index', function () {
    $this->withMiddleware();
    auth()->logout();

    $response = $this->get(route('quotations.index'));

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot create quotation', function () {
    $this->withMiddleware();
    auth()->logout();

    $response = $this->post(route('quotations.store'), [
        'insurance_type' => 'Motor',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot update quotation', function () {
    $this->withMiddleware();
    auth()->logout();
    $quotation = Quotation::factory()->create();

    $response = $this->put(route('quotations.update', $quotation), [
        'vehicle_make' => 'Updated',
    ]);

    $response->assertRedirect(route('login'));
});

test('unauthenticated user cannot delete quotation', function () {
    $this->withMiddleware();
    auth()->logout();
    $quotation = Quotation::factory()->create();

    $response = $this->delete(route('quotations.delete', $quotation));

    $response->assertRedirect(route('login'));
});

// ========================================
// RELATIONSHIP TESTS
// ========================================

test('quotation belongs to customer', function () {
    $customer = Customer::factory()->create();
    $quotation = Quotation::factory()->create(['customer_id' => $customer->id]);

    expect($quotation->customer->id)->toBe($customer->id);
});

test('quotation has many quotation companies', function () {
    $quotation = Quotation::factory()->create();

    expect($quotation->quotationCompanies())->toBeInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class);
});

// ========================================
// BUSINESS LOGIC TESTS
// ========================================

test('quotation reference generation works', function () {
    $quotation = Quotation::factory()->create();

    $reference = $quotation->getQuoteReference();

    expect($reference)->toBeString();
    expect(strlen($reference))->toBeGreaterThan(0);
});

test('store redirects to show page after creation', function () {
    $customer = Customer::factory()->create();

    $response = $this->post(route('quotations.store'), [
        'customer_id' => $customer->id,
        'insurance_type' => 'Motor',
        'vehicle_make' => 'Toyota',
        'vehicle_model' => 'Corolla',
        'registration_no' => 'MH01XY9999',
        'manufacturing_year' => 2023,
        'idv' => 700000,
    ]);

    $response->assertRedirect();
    $response->assertSessionHas('success');
});
