<?php

use App\Events\Quotation\QuotationGenerated;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Quotation;
use App\Models\QuotationCompany;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(QuotationService::class);

    // Fake events
    Event::fake([QuotationGenerated::class]);

    // Create required related models
    $this->customer = Customer::factory()->create();
    $this->insuranceCompany = InsuranceCompany::factory()->create();
});

// ============================================
// CREATE QUOTATION TESTS
// ============================================

test('creates quotation successfully with valid data', function () {
    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'make_model_variant' => 'Honda City VX',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'idv_trailer' => 0,
        'idv_cng_lpg_kit' => 0,
        'idv_electrical_accessories' => 10000,
        'idv_non_electrical_accessories' => 5000,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [],
    ];

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
    expect($result->quotation_number)->toBe($quotationData['quotation_number']);
    expect($result->customer_id)->toBe($this->customer->id);
    expect($result->total_idv)->toBe(515000); // Sum of all IDVs
});

test('calculates total IDV correctly during creation', function () {
    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'idv_vehicle' => 500000,
        'idv_trailer' => 50000,
        'idv_cng_lpg_kit' => 20000,
        'idv_electrical_accessories' => 10000,
        'idv_non_electrical_accessories' => 5000,
        'manufacturing_year' => 2020,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [],
    ];

    $result = $this->service->createQuotation($quotationData);

    expect($result->total_idv)->toBe(585000); // Sum of all IDVs
});

test('fires QuotationGenerated event after creation', function () {
    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [],
    ];

    $this->service->createQuotation($quotationData);

    Event::assertDispatched(QuotationGenerated::class);
});

test('creates manual company quotes during quotation creation', function () {
    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [
            [
                'insurance_company_id' => $this->insuranceCompany->id,
                'quote_number' => 'Q001',
                'basic_od_premium' => 5000,
                'total_od_premium' => 5000,
                'net_premium' => 5000,
                'final_premium' => 6000,
            ],
        ],
    ];

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
    expect($result->quotationCompanies()->count())->toBe(1);
});

// ============================================
// DELETE QUOTATION TESTS
// ============================================

test('deletes quotation successfully', function () {
    $quotation = Quotation::factory()->create([
        'customer_id' => $this->customer->id,
    ]);
    $quotationId = $quotation->id;

    $result = $this->service->deleteQuotation($quotation);

    expect($result)->toBeTrue();
    expect(Quotation::find($quotationId))->toBeNull();
});

// ============================================
// QUERY METHOD TESTS
// ============================================

test('gets paginated quotations', function () {
    Quotation::factory()->count(20)->create([
        'customer_id' => $this->customer->id,
    ]);

    $request = new Request;
    $result = $this->service->getQuotations($request);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
    expect($result->total())->toBe(20);
    expect($result->perPage())->toBe(15);
});

test('gets quotation form data', function () {
    $result = $this->service->getQuotationFormData();

    expect($result)->toHaveKey('customers');
    expect($result)->toHaveKey('insuranceCompanies');
    expect($result)->toHaveKey('addonCovers');
});

// ============================================
// PREMIUM CALCULATION TESTS
// ============================================

test('calculates premium correctly', function () {
    $data = [
        'idv_vehicle' => 500000,
        'idv_trailer' => 50000,
        'idv_cng_lpg_kit' => 20000,
        'idv_electrical_accessories' => 10000,
        'idv_non_electrical_accessories' => 5000,
    ];

    $result = $this->service->calculatePremium($data);

    expect($result)->toBe(585000.0);
});

test('calculates premium with zero values', function () {
    $data = [
        'idv_vehicle' => 500000,
        'idv_trailer' => 0,
        'idv_cng_lpg_kit' => 0,
        'idv_electrical_accessories' => 0,
        'idv_non_electrical_accessories' => 0,
    ];

    $result = $this->service->calculatePremium($data);

    expect($result)->toBe(500000.0);
});

test('handles missing IDV fields in calculation', function () {
    $data = [
        'idv_vehicle' => 500000,
    ];

    $result = $this->service->calculatePremium($data);

    expect($result)->toBe(500000.0);
});

// ============================================
// ADDON PREMIUM TESTS
// ============================================

test('processes addon breakdown correctly', function () {
    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [
            [
                'insurance_company_id' => $this->insuranceCompany->id,
                'quote_number' => 'Q001',
                'basic_od_premium' => 5000,
                'total_od_premium' => 5000,
                'net_premium' => 6000,
                'final_premium' => 7200,
                'addon_covers_breakdown' => [
                    'Zero Depreciation' => ['price' => 500],
                    'Engine Protection' => ['price' => 300],
                    'NCB Protection' => ['price' => 200],
                ],
                'total_addon_premium' => 1000,
            ],
        ],
    ];

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);

    $companyQuote = $result->quotationCompanies()->first();
    expect($companyQuote->addon_covers_breakdown)->toBeArray();
    expect(count($companyQuote->addon_covers_breakdown))->toBe(3);
});

// ============================================
// UPDATE QUOTATION TESTS
// ============================================

test('updates quotation with companies successfully', function () {
    $quotation = Quotation::factory()->create([
        'customer_id' => $this->customer->id,
        'idv_vehicle' => 500000,
    ]);

    // Add initial company quote
    QuotationCompany::factory()->create([
        'quotation_id' => $quotation->id,
        'insurance_company_id' => $this->insuranceCompany->id,
    ]);

    $updateData = [
        'idv_vehicle' => 600000,
        'idv_trailer' => 0,
        'idv_cng_lpg_kit' => 0,
        'idv_electrical_accessories' => 15000,
        'idv_non_electrical_accessories' => 5000,
        'companies' => [
            [
                'insurance_company_id' => $this->insuranceCompany->id,
                'quote_number' => 'Q002',
                'basic_od_premium' => 6000,
                'total_od_premium' => 6000,
                'net_premium' => 6000,
                'final_premium' => 7200,
            ],
        ],
    ];

    $this->service->updateQuotationWithCompanies($quotation, $updateData);

    $quotation->refresh();
    expect($quotation->idv_vehicle)->toBe(600000);
    expect($quotation->total_idv)->toBe(620000);
});

// ============================================
// EDGE CASE TESTS
// ============================================

test('handles empty company quotes array', function () {
    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [],
    ];

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
    expect($result->quotationCompanies()->count())->toBe(0);
});

test('handles null addon covers breakdown', function () {
    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [
            [
                'insurance_company_id' => $this->insuranceCompany->id,
                'quote_number' => 'Q001',
                'basic_od_premium' => 5000,
                'addon_covers_breakdown' => null,
            ],
        ],
    ];

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
});

test('handles transaction rollback on creation failure', function () {
    $quotationData = [
        'customer_id' => 999999, // Invalid customer
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'companies' => [],
    ];

    expect(fn () => $this->service->createQuotation($quotationData))
        ->toThrow(Exception::class);
});

test('handles multiple quotations for same customer', function () {
    Quotation::factory()->count(5)->create([
        'customer_id' => $this->customer->id,
    ]);

    $request = new Request(['customer_id' => $this->customer->id]);
    $result = $this->service->getQuotations($request);

    expect($result->total())->toBeGreaterThanOrEqual(5);
});

test('handles quotation with multiple company quotes', function () {
    $company2 = InsuranceCompany::factory()->create();
    $company3 = InsuranceCompany::factory()->create();

    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [
            [
                'insurance_company_id' => $this->insuranceCompany->id,
                'quote_number' => 'Q001',
                'basic_od_premium' => 5000,
                'final_premium' => 6000,
            ],
            [
                'insurance_company_id' => $company2->id,
                'quote_number' => 'Q002',
                'basic_od_premium' => 4500,
                'final_premium' => 5400,
            ],
            [
                'insurance_company_id' => $company3->id,
                'quote_number' => 'Q003',
                'basic_od_premium' => 5500,
                'final_premium' => 6600,
            ],
        ],
    ];

    $result = $this->service->createQuotation($quotationData);

    expect($result->quotationCompanies()->count())->toBe(3);
});

test('handles zero IDV values', function () {
    $quotationData = [
        'customer_id' => $this->customer->id,
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 0,
        'idv_trailer' => 0,
        'idv_cng_lpg_kit' => 0,
        'idv_electrical_accessories' => 0,
        'idv_non_electrical_accessories' => 0,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'whatsapp_number' => '1234567890',
        'companies' => [],
    ];

    $result = $this->service->createQuotation($quotationData);

    expect($result->total_idv)->toBe(0);
});

test('validates customer existence for quotation', function () {
    $quotationData = [
        'customer_id' => 999999, // Non-existent customer
        'quotation_number' => 'QT'.time(),
        'vehicle_number' => 'MH01AB1234',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'companies' => [],
    ];

    expect(fn () => $this->service->createQuotation($quotationData))
        ->toThrow(Exception::class);
});
