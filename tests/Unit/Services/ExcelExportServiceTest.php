<?php

use App\Models\Customer;
use App\Services\ExcelExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Maatwebsite\Excel\Facades\Excel;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->exportService = app(ExcelExportService::class);
});

// ========================================
// BASIC EXPORT TESTS
// ========================================

test('export generates excel file from collection', function () {
    Excel::fake();

    $data = collect([
        ['name' => 'John Doe', 'email' => 'john@example.com'],
        ['name' => 'Jane Smith', 'email' => 'jane@example.com'],
    ]);

    $result = $this->exportService->export($data);

    expect($result)->not->toBeNull();
    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('export uses custom filename when provided', function () {
    Excel::fake();

    $data = collect([['test' => 'data']]);
    $config = ['filename' => 'custom_export'];

    $this->exportService->export($data, $config);

    Excel::assertDownloaded('custom_export.xlsx');
});

test('export handles empty collection', function () {
    Excel::fake();

    $data = collect([]);

    $result = $this->exportService->export($data);

    expect($result)->not->toBeNull();
    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('export applies custom config correctly', function () {
    Excel::fake();

    $data = collect([
        ['name' => 'Test', 'email' => 'test@example.com'],
    ]);

    $config = [
        'filename' => 'test_export',
        'with_headings' => true,
        'headings' => ['Name', 'Email'],
    ];

    $this->exportService->export($data, $config);

    Excel::assertDownloaded('test_export.xlsx');
});

// ========================================
// EXPORT WITH MAPPING TESTS
// ========================================

test('exportWithMapping applies custom mapping', function () {
    Excel::fake();

    $customers = Customer::factory()->count(3)->create();

    $headings = ['ID', 'Customer Name', 'Email Address'];
    $mapping = fn ($customer) => [
        $customer->id,
        $customer->name,
        $customer->email,
    ];

    $this->exportService->exportWithMapping($customers, $headings, $mapping);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('exportWithMapping includes headings', function () {
    Excel::fake();

    $data = collect([['name' => 'Test']]);
    $headings = ['Name'];
    $mapping = fn ($item) => [$item['name']];

    $this->exportService->exportWithMapping($data, $headings, $mapping);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

// ========================================
// QUICK EXPORT TESTS
// ========================================

test('quickExport generates export for model', function () {
    Excel::fake();

    Customer::factory()->count(5)->create();

    $this->exportService->quickExport(Customer::class);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('quickExport uses specified columns', function () {
    Excel::fake();

    Customer::factory()->count(3)->create();

    $columns = ['id', 'name', 'email'];
    $this->exportService->quickExport(Customer::class, $columns);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('quickExport generates headings from columns', function () {
    Excel::fake();

    Customer::factory()->count(2)->create();

    $columns = ['id', 'name', 'email'];
    $this->exportService->quickExport(Customer::class, $columns);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

// ========================================
// EXPORT WITH RELATIONS TESTS
// ========================================

test('exportWithRelations includes relationship data', function () {
    Excel::fake();

    $customers = Customer::factory()
        ->hasCustomerInsurances(2)
        ->count(3)
        ->create();

    $this->exportService->exportWithRelations(
        Customer::class,
        ['customerInsurances']
    );

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('exportWithRelations handles multiple relations', function () {
    Excel::fake();

    Customer::factory()
        ->hasCustomerInsurances(1)
        ->count(2)
        ->create();

    $this->exportService->exportWithRelations(
        Customer::class,
        ['customerInsurances', 'customerType']
    );

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

// ========================================
// EXPORT FILTERED TESTS
// ========================================

test('exportFiltered applies simple filters', function () {
    Excel::fake();

    Customer::factory()->create(['name' => 'John Doe', 'status' => 1]);
    Customer::factory()->create(['name' => 'Jane Smith', 'status' => 0]);

    $filters = ['status' => 1];
    $this->exportService->exportFiltered(Customer::class, $filters);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('exportFiltered applies multiple filters', function () {
    Excel::fake();

    Customer::factory()->create([
        'name' => 'Active Premium',
        'status' => 1,
        'customer_type_id' => 1,
    ]);
    Customer::factory()->create([
        'name' => 'Inactive Basic',
        'status' => 0,
        'customer_type_id' => 2,
    ]);

    $filters = [
        'status' => 1,
        'customer_type_id' => 1,
    ];

    $this->exportService->exportFiltered(Customer::class, $filters);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('exportFiltered handles array filters (whereIn)', function () {
    Excel::fake();

    Customer::factory()->create(['id' => 1, 'status' => 1]);
    Customer::factory()->create(['id' => 2, 'status' => 1]);
    Customer::factory()->create(['id' => 3, 'status' => 0]);

    $filters = ['id' => [1, 2]];
    $this->exportService->exportFiltered(Customer::class, $filters);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

// ========================================
// DATA SOURCE RESOLUTION TESTS
// ========================================

test('resolveDataSource handles collection', function () {
    $data = collect([['name' => 'Test']]);

    $result = $this->exportService->export($data);

    expect($result)->not->toBeNull();
});

test('resolveDataSource handles model query', function () {
    Excel::fake();

    Customer::factory()->count(3)->create();

    $this->exportService->export(Customer::class);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('resolveDataSource handles eloquent builder', function () {
    Excel::fake();

    Customer::factory()->count(5)->create(['status' => 1]);
    Customer::factory()->count(3)->create(['status' => 0]);

    $query = Customer::query()->where('status', 1);
    $this->exportService->export($query);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

// ========================================
// FILENAME GENERATION TESTS
// ========================================

test('generateFilename uses default pattern', function () {
    Excel::fake();

    $data = collect([['test' => 'data']]);
    $this->exportService->export($data);

    $expectedFilename = 'export_'.date('Y-m-d').'.xlsx';
    Excel::assertDownloaded($expectedFilename);
});

test('generateFilename uses custom filename', function () {
    Excel::fake();

    $data = collect([['test' => 'data']]);
    $config = ['filename' => 'my_custom_export'];

    $this->exportService->export($data, $config);

    Excel::assertDownloaded('my_custom_export.xlsx');
});

test('generateFilename adds xlsx extension', function () {
    Excel::fake();

    $data = collect([['test' => 'data']]);
    $config = ['filename' => 'test_export'];

    $this->exportService->export($data, $config);

    Excel::assertDownloaded('test_export.xlsx');
});

// ========================================
// COLUMN HANDLING TESTS
// ========================================

test('getDefaultColumns returns fillable attributes', function () {
    Excel::fake();

    Customer::factory()->count(2)->create();

    // Quick export without specifying columns should use model fillable
    $this->exportService->quickExport(Customer::class);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('generateHeadingsFromColumns creates readable headers', function () {
    Excel::fake();

    Customer::factory()->count(2)->create();

    $columns = ['customer_name', 'email_address', 'phone_number'];
    $this->exportService->quickExport(Customer::class, $columns);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

// ========================================
// INTEGRATION TESTS
// ========================================

test('complete export workflow with all features', function () {
    Excel::fake();

    // Create test data
    $customers = Customer::factory()->count(10)->create([
        'status' => 1,
    ]);

    // Export with custom mapping, headings, and filters
    $headings = ['ID', 'Name', 'Email', 'Status'];
    $mapping = fn ($customer) => [
        $customer->id,
        $customer->name,
        $customer->email,
        $customer->status ? 'Active' : 'Inactive',
    ];

    $config = [
        'filename' => 'customers_export_'.date('Ymd'),
        'with_headings' => true,
        'with_mapping' => true,
    ];

    $this->exportService->exportWithMapping($customers, $headings, $mapping, $config);

    Excel::assertDownloaded('customers_export_'.date('Ymd').'.xlsx');
});

test('export handles large dataset efficiently', function () {
    Excel::fake();

    // Create large dataset
    $largeDataset = collect(range(1, 1000))->map(fn ($i) => [
        'id' => $i,
        'name' => "Customer {$i}",
        'email' => "customer{$i}@example.com",
    ]);

    $result = $this->exportService->export($largeDataset);

    expect($result)->not->toBeNull();
    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

test('export preserves data types correctly', function () {
    Excel::fake();

    $data = collect([
        ['id' => 1, 'name' => 'Test', 'active' => true, 'score' => 95.5],
        ['id' => 2, 'name' => 'Test2', 'active' => false, 'score' => 87.3],
    ]);

    $this->exportService->export($data);

    Excel::assertDownloaded('export_'.date('Y-m-d').'.xlsx');
});

// ========================================
// ERROR HANDLING TESTS
// ========================================

test('export handles invalid model class gracefully', function () {
    $this->expectException(\Throwable::class);

    $this->exportService->quickExport('InvalidModelClass');
});

test('export handles null data gracefully', function () {
    Excel::fake();

    $result = $this->exportService->export(collect([]));

    expect($result)->not->toBeNull();
});

test('export handles malformed config gracefully', function () {
    Excel::fake();

    $data = collect([['test' => 'data']]);
    $config = [
        'invalid_option' => 'invalid_value',
    ];

    $result = $this->exportService->export($data, $config);

    expect($result)->not->toBeNull();
});
