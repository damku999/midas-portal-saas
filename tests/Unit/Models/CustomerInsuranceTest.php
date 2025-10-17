<?php

use App\Models\Branch;
use App\Models\Broker;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\InsuranceCompany;
use App\Models\PolicyType;
use App\Models\PremiumType;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has correct fillable attributes', function () {
    $insurance = new CustomerInsurance;
    $expectedFillable = [
        'issue_date',
        'branch_id',
        'broker_id',
        'relationship_manager_id',
        'customer_id',
        'insurance_company_id',
        'premium_type_id',
        'policy_type_id',
        'fuel_type_id',
        'policy_no',
        'registration_no',
        'rto',
        'make_model',
        'commission_on',
        'start_date',
        'expired_date',
        'tp_expiry_date',
        'maturity_date',
        'od_premium',
        'tp_premium',
        'net_premium',
        'premium_amount',
        'gst',
        'final_premium_with_gst',
        'sgst1',
        'cgst1',
        'cgst2',
        'sgst2',
        'my_commission_percentage',
        'my_commission_amount',
        'transfer_commission_percentage',
        'transfer_commission_amount',
        'reference_commission_percentage',
        'reference_commission_amount',
        'actual_earnings',
        'ncb_percentage',
        'mode_of_payment',
        'cheque_no',
        'insurance_status',
        'policy_document_path',
        'gross_vehicle_weight',
        'mfg_year',
        'reference_by',
        'plan_name',
        'premium_paying_term',
        'policy_term',
        'sum_insured',
        'pension_amount_yearly',
        'approx_maturity_amount',
        'life_insurance_payment_mode',
        'remarks',
        'status',
    ];

    expect($insurance->getFillable())->toBe($expectedFillable);
});

it('belongs to customer', function () {
    $customer = Customer::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['customer_id' => $customer->id]);

    expect($insurance->customer)->toBeInstanceOf(Customer::class);
    expect($insurance->customer->id)->toBe($customer->id);
});

it('belongs to branch', function () {
    $branch = Branch::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['branch_id' => $branch->id]);

    expect($insurance->branch)->toBeInstanceOf(Branch::class);
    expect($insurance->branch->id)->toBe($branch->id);
});

it('belongs to broker', function () {
    $broker = Broker::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['broker_id' => $broker->id]);

    expect($insurance->broker)->toBeInstanceOf(Broker::class);
    expect($insurance->broker->id)->toBe($broker->id);
});

it('belongs to insurance company', function () {
    $company = InsuranceCompany::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['insurance_company_id' => $company->id]);

    expect($insurance->insuranceCompany)->toBeInstanceOf(InsuranceCompany::class);
    expect($insurance->insuranceCompany->id)->toBe($company->id);
});

it('belongs to policy type', function () {
    $policyType = PolicyType::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['policy_type_id' => $policyType->id]);

    expect($insurance->policyType)->toBeInstanceOf(PolicyType::class);
    expect($insurance->policyType->id)->toBe($policyType->id);
});

it('belongs to premium type', function () {
    $premiumType = PremiumType::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['premium_type_id' => $premiumType->id]);

    expect($insurance->premiumType)->toBeInstanceOf(PremiumType::class);
    expect($insurance->premiumType->id)->toBe($premiumType->id);
});

it('uses soft deletes', function () {
    $insurance = CustomerInsurance::factory()->create();
    $insuranceId = $insurance->id;

    $insurance->delete();

    $this->assertSoftDeleted('customer_insurances', ['id' => $insuranceId]);
});

it('formats issue date for ui', function () {
    $insurance = CustomerInsurance::factory()->create([
        'issue_date' => '2024-01-15',
    ]);

    $formatted = $insurance->issue_date_formatted;

    expect($formatted)->not->toBeNull();
});

it('formats start date for ui', function () {
    $insurance = CustomerInsurance::factory()->create([
        'start_date' => '2024-01-15',
    ]);

    $formatted = $insurance->start_date_formatted;

    expect($formatted)->not->toBeNull();
});

it('formats expired date for ui', function () {
    $insurance = CustomerInsurance::factory()->create([
        'expired_date' => '2025-01-15',
    ]);

    $formatted = $insurance->expired_date_formatted;

    expect($formatted)->not->toBeNull();
});

it('can store commission fields', function () {
    $insurance = CustomerInsurance::factory()->create([
        'net_premium' => 10000,
        'my_commission_percentage' => 10,
        'my_commission_amount' => 1000,
        'transfer_commission_percentage' => 2,
        'transfer_commission_amount' => 200,
        'actual_earnings' => 800,
    ]);

    expect($insurance->net_premium)->toBe(10000);
    expect($insurance->my_commission_percentage)->toBe(10);
    expect($insurance->my_commission_amount)->toBe(1000);
    expect($insurance->actual_earnings)->toBe(800);
});

it('can store vehicle specific fields', function () {
    $insurance = CustomerInsurance::factory()->create([
        'registration_no' => 'GJ01AB1234',
        'rto' => 'GJ01',
        'make_model' => 'Maruti Swift',
        'mfg_year' => '2020',
        'ncb_percentage' => 20,
    ]);

    expect($insurance->registration_no)->toBe('GJ01AB1234');
    expect($insurance->rto)->toBe('GJ01');
    expect($insurance->make_model)->toBe('Maruti Swift');
    expect($insurance->mfg_year)->toBe('2020');
    expect($insurance->ncb_percentage)->toBe(20);
});

it('can store life insurance specific fields', function () {
    $insurance = CustomerInsurance::factory()->create([
        'plan_name' => 'Life Cover Plan',
        'sum_insured' => '500000',
        'premium_paying_term' => '10',
        'policy_term' => '20',
        'pension_amount_yearly' => '50000',
        'approx_maturity_amount' => '1000000',
    ]);

    expect($insurance->plan_name)->toBe('Life Cover Plan');
    expect($insurance->sum_insured)->toBe('500000');
    expect($insurance->premium_paying_term)->toBe('10');
    expect($insurance->policy_term)->toBe('20');
});
