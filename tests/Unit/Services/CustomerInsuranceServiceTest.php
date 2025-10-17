<?php

use App\Models\CustomerInsurance;
use App\Services\CustomerInsuranceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = app(CustomerInsuranceService::class);
});

it('calculates commission on net premium correctly', function () {
    $data = [
        'commission_on' => 'net_premium',
        'net_premium' => 10000,
        'my_commission_percentage' => 10,
        'transfer_commission_percentage' => 2,
        'reference_commission_percentage' => 1,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    expect($result['base_premium'])->toBe(10000);
    expect($result['my_commission'])->toBe(1000); // 10% of 10000
    expect($result['transfer_commission'])->toBe(200); // 2% of 10000
    expect($result['reference_commission'])->toBe(100); // 1% of 10000
    expect($result['actual_earnings'])->toBe(700); // 1000 - 200 - 100
});

it('calculates commission on od premium correctly', function () {
    $data = [
        'commission_on' => 'od_premium',
        'od_premium' => 5000,
        'net_premium' => 10000,
        'my_commission_percentage' => 15,
        'transfer_commission_percentage' => 3,
        'reference_commission_percentage' => 2,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    expect($result['base_premium'])->toBe(5000);
    expect($result['my_commission'])->toBe(750); // 15% of 5000
    expect($result['transfer_commission'])->toBe(150); // 3% of 5000
    expect($result['reference_commission'])->toBe(100); // 2% of 5000
    expect($result['actual_earnings'])->toBe(500); // 750 - 150 - 100
});

it('calculates commission on tp premium correctly', function () {
    $data = [
        'commission_on' => 'tp_premium',
        'tp_premium' => 3000,
        'net_premium' => 10000,
        'my_commission_percentage' => 8,
        'transfer_commission_percentage' => 1,
        'reference_commission_percentage' => 0.5,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    expect($result['base_premium'])->toBe(3000);
    expect($result['my_commission'])->toBe(240); // 8% of 3000
    expect($result['transfer_commission'])->toBe(30); // 1% of 3000
    expect($result['reference_commission'])->toBe(15); // 0.5% of 3000
    expect($result['actual_earnings'])->toBe(195); // 240 - 30 - 15
});

it('handles zero commission percentages', function () {
    $data = [
        'commission_on' => 'net_premium',
        'net_premium' => 10000,
        'my_commission_percentage' => 0,
        'transfer_commission_percentage' => 0,
        'reference_commission_percentage' => 0,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    expect($result['my_commission'])->toBe(0);
    expect($result['transfer_commission'])->toBe(0);
    expect($result['reference_commission'])->toBe(0);
    expect($result['actual_earnings'])->toBe(0);
});

it('handles null commission percentages', function () {
    $data = [
        'commission_on' => 'net_premium',
        'net_premium' => 10000,
        'my_commission_percentage' => null,
        'transfer_commission_percentage' => null,
        'reference_commission_percentage' => null,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    expect($result['my_commission'])->toBe(0);
    expect($result['transfer_commission'])->toBe(0);
    expect($result['reference_commission'])->toBe(0);
    expect($result['actual_earnings'])->toBe(0);
});

it('defaults to net premium when commission on is null', function () {
    $data = [
        'commission_on' => null,
        'net_premium' => 10000,
        'od_premium' => 5000,
        'my_commission_percentage' => 10,
        'transfer_commission_percentage' => 0,
        'reference_commission_percentage' => 0,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    // Should use net_premium as default
    expect($result['base_premium'])->toBe(10000);
    expect($result['my_commission'])->toBe(1000);
});

it('handles negative actual earnings', function () {
    $data = [
        'commission_on' => 'net_premium',
        'net_premium' => 10000,
        'my_commission_percentage' => 5,
        'transfer_commission_percentage' => 3,
        'reference_commission_percentage' => 3,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    // 5% - 3% - 3% = -1% (negative earnings)
    expect($result['my_commission'])->toBe(500); // 5% of 10000
    expect($result['transfer_commission'])->toBe(300); // 3% of 10000
    expect($result['reference_commission'])->toBe(300); // 3% of 10000
    expect($result['actual_earnings'])->toBe(-100); // 500 - 300 - 300 = -100
});

it('calculates with decimal percentages', function () {
    $data = [
        'commission_on' => 'net_premium',
        'net_premium' => 10000,
        'my_commission_percentage' => 12.5,
        'transfer_commission_percentage' => 2.5,
        'reference_commission_percentage' => 1.75,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    expect($result['my_commission'])->toBe(1250); // 12.5% of 10000
    expect($result['transfer_commission'])->toBe(250); // 2.5% of 10000
    expect($result['reference_commission'])->toBe(175); // 1.75% of 10000
    expect($result['actual_earnings'])->toBe(825); // 1250 - 250 - 175
});

it('handles large premium amounts', function () {
    $data = [
        'commission_on' => 'net_premium',
        'net_premium' => 1000000, // 10 lakh
        'my_commission_percentage' => 10,
        'transfer_commission_percentage' => 2,
        'reference_commission_percentage' => 1,
    ];

    $result = $this->service->calculateCommissionBreakdown(
        CustomerInsurance::factory()->make($data)
    );

    expect($result['base_premium'])->toBe(1000000);
    expect($result['my_commission'])->toBe(100000); // 10% of 10 lakh
    expect($result['transfer_commission'])->toBe(20000); // 2% of 10 lakh
    expect($result['reference_commission'])->toBe(10000); // 1% of 10 lakh
    expect($result['actual_earnings'])->toBe(70000); // 100000 - 20000 - 10000
});

it('validates store rules correctly', function () {
    $rules = $this->service->getStoreValidationRules();

    expect($rules)->toHaveKey('customer_id');
    expect($rules)->toHaveKey('branch_id');
    expect($rules)->toHaveKey('policy_no');
    expect($rules)->toHaveKey('final_premium_with_gst');

    expect($rules['customer_id'])->toContain('required');
    expect($rules['policy_no'])->toContain('required');
});
