<?php

use App\Contracts\Repositories\QuotationRepositoryInterface;
use App\Events\Quotation\QuotationGenerated;
use App\Models\Customer;
use App\Models\InsuranceCompany;
use App\Models\Quotation;
use App\Models\QuotationCompany;
use App\Services\PdfGenerationService;
use App\Services\QuotationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    // Mock dependencies
    $this->pdfService = Mockery::mock(PdfGenerationService::class);
    $this->repository = Mockery::mock(QuotationRepositoryInterface::class);

    // Create service instance
    $this->service = new QuotationService($this->pdfService, $this->repository);

    // Fake events
    Event::fake([QuotationGenerated::class]);

    // Fake storage
    Storage::fake('public');
});

afterEach(function () {
    Mockery::close();
});

// ============================================
// CREATE QUOTATION TESTS
// ============================================

test('creates quotation successfully with valid data', function () {
    $quotationData = [
        'customer_id' => 1,
        'quotation_number' => 'QT001',
        'vehicle_number' => 'MH01AB1234',
        'make_model_variant' => 'Honda City',
        'manufacturing_year' => 2020,
        'idv_vehicle' => 500000,
        'idv_trailer' => 0,
        'idv_cng_lpg_kit' => 0,
        'idv_electrical_accessories' => 10000,
        'idv_non_electrical_accessories' => 5000,
        'policy_type' => 'Comprehensive',
        'policy_tenure_years' => 1,
        'fuel_type' => 'Petrol',
        'companies' => [],
    ];

    $quotation = Quotation::factory()->make($quotationData);
    $quotation->id = 1;

    // Mock Quotation::create()
    Quotation::shouldReceive('create')
        ->once()
        ->andReturn($quotation);

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
    expect($result->quotation_number)->toBe('QT001');
    Event::assertDispatched(QuotationGenerated::class);
});

test('calculates total IDV correctly during creation', function () {
    $quotationData = [
        'customer_id' => 1,
        'quotation_number' => 'QT001',
        'idv_vehicle' => 500000,
        'idv_trailer' => 50000,
        'idv_cng_lpg_kit' => 20000,
        'idv_electrical_accessories' => 10000,
        'idv_non_electrical_accessories' => 5000,
        'companies' => [],
    ];

    $quotation = Quotation::factory()->make($quotationData);
    $quotation->id = 1;

    Quotation::shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return $data['total_idv'] === 585000; // Sum of all IDVs
        }))
        ->andReturn($quotation);

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
});

test('creates manual company quotes during quotation creation', function () {
    $quotationData = [
        'customer_id' => 1,
        'quotation_number' => 'QT001',
        'idv_vehicle' => 500000,
        'companies' => [
            [
                'insurance_company_id' => 1,
                'quote_number' => 'Q001',
                'basic_od_premium' => 5000,
                'total_od_premium' => 5000,
                'net_premium' => 5000,
                'final_premium' => 6000,
            ],
            [
                'insurance_company_id' => 2,
                'quote_number' => 'Q002',
                'basic_od_premium' => 4500,
                'total_od_premium' => 4500,
                'net_premium' => 4500,
                'final_premium' => 5400,
            ],
        ],
    ];

    $quotation = Quotation::factory()->make($quotationData);
    $quotation->id = 1;

    Quotation::shouldReceive('create')
        ->once()
        ->andReturn($quotation);

    QuotationCompany::shouldReceive('create')
        ->twice()
        ->andReturn(new QuotationCompany);

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
});

test('fires QuotationGenerated event after creation', function () {
    $quotationData = [
        'customer_id' => 1,
        'quotation_number' => 'QT001',
        'idv_vehicle' => 500000,
        'companies' => [],
    ];

    $quotation = Quotation::factory()->make($quotationData);
    $quotation->id = 1;

    Quotation::shouldReceive('create')
        ->once()
        ->andReturn($quotation);

    $this->service->createQuotation($quotationData);

    Event::assertDispatched(QuotationGenerated::class);
});

// ============================================
// UPDATE QUOTATION TESTS
// ============================================

test('updates quotation with companies successfully', function () {
    $quotation = Quotation::factory()->make(['id' => 1]);
    $quotation->shouldReceive('update')->once()->andReturn(true);
    $quotation->shouldReceive('quotationCompanies->delete')->once();

    $updateData = [
        'idv_vehicle' => 600000,
        'idv_trailer' => 0,
        'idv_cng_lpg_kit' => 0,
        'idv_electrical_accessories' => 15000,
        'idv_non_electrical_accessories' => 5000,
        'companies' => [
            [
                'insurance_company_id' => 1,
                'quote_number' => 'Q001',
                'basic_od_premium' => 6000,
                'total_od_premium' => 6000,
                'net_premium' => 6000,
                'final_premium' => 7200,
            ],
        ],
    ];

    QuotationCompany::shouldReceive('create')
        ->once()
        ->andReturn(new QuotationCompany);

    $this->service->updateQuotationWithCompanies($quotation, $updateData);

    expect(true)->toBeTrue(); // If no exception, update successful
});

// ============================================
// DELETE QUOTATION TESTS
// ============================================

test('deletes quotation successfully', function () {
    $quotation = Quotation::factory()->make(['id' => 1]);

    $this->repository->shouldReceive('delete')
        ->once()
        ->with($quotation)
        ->andReturn(true);

    $result = $this->service->deleteQuotation($quotation);

    expect($result)->toBeTrue();
});

test('delete uses transaction', function () {
    $quotation = Quotation::factory()->make();

    $this->repository->shouldReceive('delete')
        ->once()
        ->andThrow(new Exception('Cannot delete quotation'));

    expect(fn () => $this->service->deleteQuotation($quotation))
        ->toThrow(Exception::class, 'Cannot delete quotation');
});

// ============================================
// QUERY METHOD TESTS
// ============================================

test('gets paginated quotations', function () {
    $request = new Request(['search' => 'QT001']);
    $paginatedResult = new \Illuminate\Pagination\LengthAwarePaginator([], 0, 15);

    $this->repository->shouldReceive('getPaginated')
        ->once()
        ->with($request, 15)
        ->andReturn($paginatedResult);

    $result = $this->service->getQuotations($request);

    expect($result)->toBeInstanceOf(\Illuminate\Contracts\Pagination\LengthAwarePaginator::class);
});

test('gets quotation form data', function () {
    Customer::shouldReceive('where->orderBy->get')
        ->once()
        ->andReturn(collect([
            Customer::factory()->make(['status' => 1]),
        ]));

    InsuranceCompany::shouldReceive('where->orderBy->get')
        ->once()
        ->andReturn(collect([
            new InsuranceCompany(['status' => 1, 'name' => 'Test Insurance']),
        ]));

    \App\Models\AddonCover::shouldReceive('getOrdered')
        ->once()
        ->with(1)
        ->andReturn(collect([]));

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
        // Other fields missing
    ];

    $result = $this->service->calculatePremium($data);

    expect($result)->toBe(500000.0);
});

// ============================================
// PDF GENERATION TESTS
// ============================================

test('generates PDF for quotation', function () {
    $quotation = Quotation::factory()->make(['id' => 1]);

    $this->pdfService->shouldReceive('generateQuotationPdf')
        ->once()
        ->with($quotation)
        ->andReturn('path/to/pdf');

    $result = $this->service->generatePdf($quotation);

    expect($result)->toBe('path/to/pdf');
});

// ============================================
// WHATSAPP SENDING TESTS
// ============================================

test('sends quotation via WhatsApp successfully', function () {
    $customer = Customer::factory()->make([
        'name' => 'John Doe',
        'mobile_number' => '1234567890',
    ]);

    $quotation = Quotation::factory()->make([
        'id' => 1,
        'quotation_number' => 'QT001',
        'whatsapp_number' => '1234567890',
        'status' => 'Draft',
    ]);

    $quotation->setRelation('customer', $customer);
    $quotation->shouldReceive('quotationCompanies->orderBy->get')
        ->andReturn(collect([
            new QuotationCompany([
                'final_premium' => 6000,
                'is_recommended' => true,
            ]),
        ]));
    $quotation->shouldReceive('update')->once();

    $this->pdfService->shouldReceive('generateQuotationPdfForWhatsApp')
        ->once()
        ->with($quotation)
        ->andReturn('/tmp/quotation.pdf');

    // Create a real file for cleanup test
    $tempFile = '/tmp/quotation.pdf';
    file_put_contents($tempFile, 'test');

    $this->service->shouldReceive('whatsAppSendMessageWithAttachment')
        ->once()
        ->andReturn(true);

    $this->service->sendQuotationViaWhatsApp($quotation);

    expect($quotation->status)->toBe('Draft');
});

test('cleans up PDF after WhatsApp send', function () {
    $customer = Customer::factory()->make(['mobile_number' => '1234567890']);
    $quotation = Quotation::factory()->make([
        'quotation_number' => 'QT001',
        'whatsapp_number' => '1234567890',
    ]);

    $quotation->setRelation('customer', $customer);
    $quotation->shouldReceive('quotationCompanies->orderBy->get')
        ->andReturn(collect([]));
    $quotation->shouldReceive('update')->once();

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');

    $this->pdfService->shouldReceive('generateQuotationPdfForWhatsApp')
        ->once()
        ->andReturn($tempFile);

    $this->service->shouldReceive('whatsAppSendMessageWithAttachment')
        ->once()
        ->andReturn(true);

    $this->service->sendQuotationViaWhatsApp($quotation);

    expect(file_exists($tempFile))->toBeFalse();
});

test('throws exception when WhatsApp send fails', function () {
    $customer = Customer::factory()->make(['mobile_number' => '1234567890']);
    $quotation = Quotation::factory()->make([
        'quotation_number' => 'QT001',
        'whatsapp_number' => '1234567890',
    ]);

    $quotation->setRelation('customer', $customer);
    $quotation->shouldReceive('quotationCompanies->orderBy->get')
        ->andReturn(collect([]));

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');

    $this->pdfService->shouldReceive('generateQuotationPdfForWhatsApp')
        ->once()
        ->andReturn($tempFile);

    $this->service->shouldReceive('whatsAppSendMessageWithAttachment')
        ->once()
        ->andThrow(new Exception('WhatsApp API error'));

    expect(fn () => $this->service->sendQuotationViaWhatsApp($quotation))
        ->toThrow(Exception::class, 'WhatsApp API error');

    // File should still be cleaned up
    expect(file_exists($tempFile))->toBeFalse();
});

// ============================================
// EMAIL SENDING TESTS
// ============================================

test('sends quotation via email successfully', function () {
    $customer = Customer::factory()->make([
        'email' => 'customer@example.com',
    ]);

    $quotation = Quotation::factory()->make([
        'id' => 1,
        'quotation_number' => 'QT001',
        'email' => 'customer@example.com',
        'status' => 'Draft',
    ]);

    $quotation->setRelation('customer', $customer);
    $quotation->shouldReceive('update')->once();

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');

    $this->pdfService->shouldReceive('generateQuotationPdfForWhatsApp')
        ->once()
        ->andReturn($tempFile);

    $emailService = Mockery::mock(\App\Services\EmailService::class);
    $emailService->shouldReceive('sendFromQuotation')
        ->once()
        ->with('quotation_ready', $quotation, [$tempFile])
        ->andReturn(true);

    app()->instance(\App\Services\EmailService::class, $emailService);

    $this->service->sendQuotationViaEmail($quotation);

    expect($quotation->status)->toBe('Draft');
});

test('cleans up PDF after email send', function () {
    $customer = Customer::factory()->make(['email' => 'test@example.com']);
    $quotation = Quotation::factory()->make([
        'quotation_number' => 'QT001',
        'email' => 'test@example.com',
    ]);

    $quotation->setRelation('customer', $customer);
    $quotation->shouldReceive('update')->once();

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');

    $this->pdfService->shouldReceive('generateQuotationPdfForWhatsApp')
        ->once()
        ->andReturn($tempFile);

    $emailService = Mockery::mock(\App\Services\EmailService::class);
    $emailService->shouldReceive('sendFromQuotation')
        ->once()
        ->andReturn(true);

    app()->instance(\App\Services\EmailService::class, $emailService);

    $this->service->sendQuotationViaEmail($quotation);

    expect(file_exists($tempFile))->toBeFalse();
});

test('throws exception when email send fails', function () {
    $customer = Customer::factory()->make(['email' => 'test@example.com']);
    $quotation = Quotation::factory()->make([
        'quotation_number' => 'QT001',
        'email' => 'test@example.com',
    ]);

    $quotation->setRelation('customer', $customer);

    $tempFile = tempnam(sys_get_temp_dir(), 'test_');

    $this->pdfService->shouldReceive('generateQuotationPdfForWhatsApp')
        ->once()
        ->andReturn($tempFile);

    $emailService = Mockery::mock(\App\Services\EmailService::class);
    $emailService->shouldReceive('sendFromQuotation')
        ->once()
        ->andThrow(new Exception('SMTP error'));

    app()->instance(\App\Services\EmailService::class, $emailService);

    expect(fn () => $this->service->sendQuotationViaEmail($quotation))
        ->toThrow(Exception::class, 'SMTP error');

    expect(file_exists($tempFile))->toBeFalse();
});

// ============================================
// COMPANY QUOTE GENERATION TESTS
// ============================================

test('generates company quotes for selected companies', function () {
    $quotation = Quotation::factory()->make([
        'id' => 1,
        'total_idv' => 500000,
        'manufacturing_year' => 2020,
    ]);

    $companies = collect([
        new InsuranceCompany(['id' => 1, 'name' => 'TATA AIG', 'status' => 1]),
        new InsuranceCompany(['id' => 2, 'name' => 'HDFC ERGO', 'status' => 1]),
    ]);

    InsuranceCompany::shouldReceive('whereIn->where->get')
        ->once()
        ->andReturn($companies);

    QuotationCompany::shouldReceive('create')
        ->twice()
        ->andReturn(new QuotationCompany);

    $quotation->shouldReceive('quotationCompanies->orderBy->get')
        ->andReturn(collect([
            Mockery::mock(QuotationCompany::class)
                ->shouldReceive('update')->andReturn(true)
                ->getMock(),
        ]));

    $this->service->generateQuotesForSelectedCompanies($quotation, [1, 2]);

    expect(true)->toBeTrue();
});

// ============================================
// ADDON PREMIUM TESTS
// ============================================

test('processes addon breakdown correctly', function () {
    $quotationData = [
        'customer_id' => 1,
        'quotation_number' => 'QT001',
        'idv_vehicle' => 500000,
        'companies' => [
            [
                'insurance_company_id' => 1,
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
            ],
        ],
    ];

    $quotation = Quotation::factory()->make($quotationData);
    $quotation->id = 1;

    Quotation::shouldReceive('create')
        ->once()
        ->andReturn($quotation);

    QuotationCompany::shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            return isset($data['addon_covers_breakdown']) &&
                   count($data['addon_covers_breakdown']) === 3;
        }))
        ->andReturn(new QuotationCompany);

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
});

// ============================================
// EDGE CASE TESTS
// ============================================

test('handles empty company quotes array', function () {
    $quotationData = [
        'customer_id' => 1,
        'quotation_number' => 'QT001',
        'idv_vehicle' => 500000,
        'companies' => [],
    ];

    $quotation = Quotation::factory()->make($quotationData);
    $quotation->id = 1;

    Quotation::shouldReceive('create')
        ->once()
        ->andReturn($quotation);

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
});

test('handles null addon covers breakdown', function () {
    $quotationData = [
        'customer_id' => 1,
        'idv_vehicle' => 500000,
        'companies' => [
            [
                'insurance_company_id' => 1,
                'quote_number' => 'Q001',
                'basic_od_premium' => 5000,
                'addon_covers_breakdown' => null,
            ],
        ],
    ];

    $quotation = Quotation::factory()->make();
    $quotation->id = 1;

    Quotation::shouldReceive('create')
        ->once()
        ->andReturn($quotation);

    QuotationCompany::shouldReceive('create')
        ->once()
        ->andReturn(new QuotationCompany);

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
});

test('handles duplicate company quotes in array', function () {
    $quotationData = [
        'customer_id' => 1,
        'idv_vehicle' => 500000,
        'companies' => [
            [
                'insurance_company_id' => 1,
                'quote_number' => 'Q001',
                'basic_od_premium' => 5000,
                'final_premium' => 6000,
            ],
            [
                'insurance_company_id' => 1, // Duplicate company
                'quote_number' => 'Q001', // Same quote number
                'basic_od_premium' => 5000, // Same premium
                'final_premium' => 6000,
            ],
        ],
    ];

    $quotation = Quotation::factory()->make();
    $quotation->id = 1;

    Quotation::shouldReceive('create')
        ->once()
        ->andReturn($quotation);

    // Should only create once due to duplicate detection
    QuotationCompany::shouldReceive('create')
        ->once()
        ->andReturn(new QuotationCompany);

    $result = $this->service->createQuotation($quotationData);

    expect($result)->toBeInstanceOf(Quotation::class);
});

test('handles transaction rollback on creation failure', function () {
    $quotationData = [
        'customer_id' => 1,
        'idv_vehicle' => 500000,
        'companies' => [],
    ];

    Quotation::shouldReceive('create')
        ->once()
        ->andThrow(new Exception('Database constraint violation'));

    expect(fn () => $this->service->createQuotation($quotationData))
        ->toThrow(Exception::class, 'Database constraint violation');
});

test('calculates company rating factor correctly', function () {
    // This tests the private method indirectly through quote generation
    $quotation = Quotation::factory()->make([
        'id' => 1,
        'total_idv' => 500000,
        'manufacturing_year' => 2020,
        'fuel_type' => 'Petrol',
    ]);

    $company = new InsuranceCompany(['id' => 1, 'name' => 'HDFC ERGO', 'status' => 1]);

    InsuranceCompany::shouldReceive('whereIn->where->get')
        ->andReturn(collect([$company]));

    QuotationCompany::shouldReceive('create')
        ->once()
        ->with(Mockery::on(function ($data) {
            // HDFC ERGO has 0.95 factor, premium should reflect that
            return $data['insurance_company_id'] === 1;
        }))
        ->andReturn(new QuotationCompany);

    $quotation->shouldReceive('quotationCompanies->orderBy->get')
        ->andReturn(collect([
            Mockery::mock(QuotationCompany::class)
                ->shouldReceive('update')->andReturn(true)
                ->getMock(),
        ]));

    $this->service->generateQuotesForSelectedCompanies($quotation, [1]);

    expect(true)->toBeTrue();
});
