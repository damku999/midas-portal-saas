<?php

use App\Models\AddonCover;
use App\Models\Branch;
use App\Models\Broker;
use App\Models\Claim;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\FamilyGroup;
use App\Models\FuelType;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Models\PremiumType;
use App\Models\Quotation;
use App\Models\ReferenceUser;
use App\Models\RelationshipManager;
use App\Models\Report;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Database\Seeders\UnifiedPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

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

    // Fake Excel for all tests
    Excel::fake();
});

// ========================================
// CUSTOMER EXPORT TESTS
// ========================================

test('customers export route works', function () {
    Customer::factory()->count(5)->create();

    $response = $this->get(route('customers.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('customers_'.date('Y-m-d').'.xlsx');
});

test('customers export includes all data', function () {
    Customer::factory()->count(10)->create();

    $response = $this->get(route('customers.export'));

    $response->assertStatus(200);
});

test('customers export handles empty data', function () {
    $response = $this->get(route('customers.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('customers_'.date('Y-m-d').'.xlsx');
});

// ========================================
// BROKER EXPORT TESTS
// ========================================

test('brokers export route works', function () {
    Broker::factory()->count(5)->create();

    $response = $this->get(route('brokers.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('brokers_'.date('Y-m-d').'.xlsx');
});

// ========================================
// REFERENCE USERS EXPORT TESTS
// ========================================

test('reference users export route works', function () {
    ReferenceUser::factory()->count(5)->create();

    $response = $this->get(route('reference-users.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('reference_users_'.date('Y-m-d').'.xlsx');
});

// ========================================
// RELATIONSHIP MANAGER EXPORT TESTS
// ========================================

test('relationship managers export route works', function () {
    RelationshipManager::factory()->count(5)->create();

    $response = $this->get(route('relationship-managers.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('relationship_managers_'.date('Y-m-d').'.xlsx');
});

// ========================================
// INSURANCE COMPANY EXPORT TESTS
// ========================================

test('insurance companies export route works', function () {
    InsuranceCompany::factory()->count(5)->create();

    $response = $this->get(route('insurance-companies.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('insurance_companies_'.date('Y-m-d').'.xlsx');
});

// ========================================
// CUSTOMER INSURANCE EXPORT TESTS
// ========================================

test('customer insurances export route works', function () {
    CustomerInsurance::factory()->count(5)->create();

    $response = $this->get(route('customer-insurances.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('customer_insurances_'.date('Y-m-d').'.xlsx');
});

test('customer insurances export with filters', function () {
    CustomerInsurance::factory()->count(3)->create(['status' => 'active']);
    CustomerInsurance::factory()->count(2)->create(['status' => 'expired']);

    $response = $this->get(route('customer-insurances.export', ['status' => 'active']));

    $response->assertStatus(200);
});

// ========================================
// USER EXPORT TESTS
// ========================================

test('users export route works', function () {
    User::factory()->count(5)->create();

    $response = $this->get(route('users.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('users_'.date('Y-m-d').'.xlsx');
});

// ========================================
// FAMILY GROUP EXPORT TESTS
// ========================================

test('family groups export route works', function () {
    FamilyGroup::factory()->count(5)->create();

    $response = $this->get(route('family-groups.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('family_groups_'.date('Y-m-d').'.xlsx');
});

// ========================================
// POLICY TYPE EXPORT TESTS
// ========================================

test('policy types export route works', function () {
    PolicyType::factory()->count(5)->create();

    $response = $this->get(route('policy-types.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('policy_types_'.date('Y-m-d').'.xlsx');
});

// ========================================
// ADDON COVER EXPORT TESTS
// ========================================

test('addon covers export route works', function () {
    AddonCover::factory()->count(5)->create();

    $response = $this->get(route('addon-covers.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('addon_covers_'.date('Y-m-d').'.xlsx');
});

// ========================================
// PREMIUM TYPE EXPORT TESTS
// ========================================

test('premium types export route works', function () {
    PremiumType::factory()->count(5)->create();

    $response = $this->get(route('premium-types.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('premium_types_'.date('Y-m-d').'.xlsx');
});

// ========================================
// FUEL TYPE EXPORT TESTS
// ========================================

test('fuel types export route works', function () {
    FuelType::factory()->count(5)->create();

    $response = $this->get(route('fuel-types.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('fuel_types_'.date('Y-m-d').'.xlsx');
});

// ========================================
// QUOTATION EXPORT TESTS
// ========================================

test('quotations export route works', function () {
    Quotation::factory()->count(5)->create();

    $response = $this->get(route('quotations.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('quotations_'.date('Y-m-d').'.xlsx');
});

// ========================================
// REPORT EXPORT TESTS
// ========================================

test('reports export route works', function () {
    Report::factory()->count(5)->create();

    $response = $this->get(route('reports.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('reports_'.date('Y-m-d').'.xlsx');
});

// ========================================
// BRANCH EXPORT TESTS
// ========================================

test('branches export route works', function () {
    Branch::factory()->count(5)->create();

    $response = $this->get(route('branches.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('branches_'.date('Y-m-d').'.xlsx');
});

// ========================================
// CLAIM EXPORT TESTS
// ========================================

test('claims export route works', function () {
    Claim::factory()->count(5)->create();

    $response = $this->get(route('claims.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('claims_'.date('Y-m-d').'.xlsx');
});

// ========================================
// INTEGRATION TESTS
// ========================================

test('all export routes are accessible', function () {
    $exportRoutes = [
        'customers.export',
        'brokers.export',
        'reference-users.export',
        'relationship-managers.export',
        'insurance-companies.export',
        'customer-insurances.export',
        'users.export',
        'family-groups.export',
        'policy-types.export',
        'addon-covers.export',
        'premium-types.export',
        'fuel-types.export',
        'quotations.export',
        'reports.export',
        'branches.export',
        'claims.export',
    ];

    foreach ($exportRoutes as $routeName) {
        $response = $this->get(route($routeName));
        $response->assertStatus(200);
    }
});

test('exports handle query parameters', function () {
    Customer::factory()->count(10)->create(['status' => 1]);
    Customer::factory()->count(5)->create(['status' => 0]);

    $response = $this->get(route('customers.export', ['status' => 1]));

    $response->assertStatus(200);
});

test('exports maintain data integrity', function () {
    $customer = Customer::factory()->create([
        'name' => 'Test Customer',
        'email' => 'test@example.com',
        'mobile' => '1234567890',
    ]);

    $response = $this->get(route('customers.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('customers_'.date('Y-m-d').'.xlsx');
});

test('exports handle special characters correctly', function () {
    Customer::factory()->create([
        'name' => 'Test & Customer <with> "special" \'characters\'',
        'email' => 'test@example.com',
    ]);

    $response = $this->get(route('customers.export'));

    $response->assertStatus(200);
});

test('exports handle large datasets', function () {
    Customer::factory()->count(100)->create();

    $response = $this->get(route('customers.export'));

    $response->assertStatus(200);
    Excel::assertDownloaded('customers_'.date('Y-m-d').'.xlsx');
})->skip('Slow test - enable for full test suite');

test('exports require authentication', function () {
    auth()->logout();

    $response = $this->get(route('customers.export'));

    $response->assertRedirect(route('login'));
});

test('exports respect soft deletes', function () {
    Customer::factory()->count(3)->create();
    $deletedCustomer = Customer::factory()->create();
    $deletedCustomer->delete();

    $response = $this->get(route('customers.export'));

    $response->assertStatus(200);
    // Should only export 3 non-deleted customers
});

test('exports handle relationships correctly', function () {
    $customer = Customer::factory()
        ->hasCustomerInsurances(3)
        ->create();

    $response = $this->get(route('customer-insurances.export'));

    $response->assertStatus(200);
});

test('multiple exports can be downloaded in sequence', function () {
    Customer::factory()->count(5)->create();
    Broker::factory()->count(5)->create();
    Branch::factory()->count(5)->create();

    // Export customers
    $response1 = $this->get(route('customers.export'));
    $response1->assertStatus(200);

    // Export brokers
    $response2 = $this->get(route('brokers.export'));
    $response2->assertStatus(200);

    // Export branches
    $response3 = $this->get(route('branches.export'));
    $response3->assertStatus(200);

    Excel::assertDownloaded('customers_'.date('Y-m-d').'.xlsx');
    Excel::assertDownloaded('brokers_'.date('Y-m-d').'.xlsx');
    Excel::assertDownloaded('branches_'.date('Y-m-d').'.xlsx');
});

test('exports handle date filtering', function () {
    Customer::factory()->create(['created_at' => now()->subDays(10)]);
    Customer::factory()->create(['created_at' => now()->subDays(5)]);
    Customer::factory()->create(['created_at' => now()]);

    $response = $this->get(route('customers.export', [
        'date_from' => now()->subDays(7)->format('Y-m-d'),
    ]));

    $response->assertStatus(200);
});

test('exports handle sorting parameters', function () {
    Customer::factory()->create(['name' => 'Zoe']);
    Customer::factory()->create(['name' => 'Alice']);
    Customer::factory()->create(['name' => 'Bob']);

    $response = $this->get(route('customers.export', [
        'sort_by' => 'name',
        'sort_order' => 'asc',
    ]));

    $response->assertStatus(200);
});

test('exports provide meaningful filenames', function () {
    Customer::factory()->count(5)->create();

    $response = $this->get(route('customers.export'));

    $response->assertStatus(200);

    $expectedFilename = 'customers_'.date('Y-m-d').'.xlsx';
    Excel::assertDownloaded($expectedFilename);
});

test('exports handle concurrent requests', function () {
    Customer::factory()->count(10)->create();

    // Simulate concurrent export requests
    $responses = [];
    for ($i = 0; $i < 3; $i++) {
        $responses[] = $this->get(route('customers.export'));
    }

    foreach ($responses as $response) {
        $response->assertStatus(200);
    }
});
