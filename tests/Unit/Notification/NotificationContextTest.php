<?php

namespace Tests\Unit\Notification;

use App\Models\Claim;
use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\Quotation;
use App\Services\Notification\NotificationContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Test suite for NotificationContext
 *
 * Tests context building from different entities
 */
class NotificationContextTest extends TestCase
{
    use RefreshDatabase;

    // =======================================================
    // CONSTRUCTION TESTS
    // =======================================================

    /** @test */
    public function it_creates_empty_context()
    {
        $context = new NotificationContext;

        $this->assertNull($context->customer);
        $this->assertNull($context->insurance);
        $this->assertNull($context->quotation);
        $this->assertNull($context->claim);
        $this->assertEmpty($context->settings);
        $this->assertEmpty($context->customData);
    }

    /** @test */
    public function it_creates_context_with_customer()
    {
        $customer = Customer::factory()->create();

        $context = new NotificationContext(['customer' => $customer]);

        $this->assertNotNull($context->customer);
        $this->assertEquals($customer->id, $context->customer->id);
    }

    /** @test */
    public function it_creates_context_with_multiple_entities()
    {
        $customer = Customer::factory()->create();
        $insurance = CustomerInsurance::factory()->create();

        $context = new NotificationContext([
            'customer' => $customer,
            'insurance' => $insurance,
        ]);

        $this->assertNotNull($context->customer);
        $this->assertNotNull($context->insurance);
    }

    /** @test */
    public function it_creates_context_with_settings()
    {
        $settings = [
            'company' => ['name' => 'Midas Insurance'],
            'application' => ['portal_url' => 'https://portal.example.com'],
        ];

        $context = new NotificationContext(['settings' => $settings]);

        $this->assertNotEmpty($context->settings);
        $this->assertEquals('Midas Insurance', $context->getSetting('company.name'));
    }

    /** @test */
    public function it_creates_context_with_custom_data()
    {
        $customData = ['custom_field' => 'custom_value'];

        $context = new NotificationContext(['customData' => $customData]);

        $this->assertEquals('custom_value', $context->getCustomData('custom_field'));
    }

    // =======================================================
    // ENTITY PRESENCE CHECKS
    // =======================================================

    /** @test */
    public function it_checks_has_customer()
    {
        $context = new NotificationContext;
        $this->assertFalse($context->hasCustomer());

        $customer = Customer::factory()->create();
        $context->customer = $customer;
        $this->assertTrue($context->hasCustomer());
    }

    /** @test */
    public function it_checks_has_insurance()
    {
        $context = new NotificationContext;
        $this->assertFalse($context->hasInsurance());

        $insurance = CustomerInsurance::factory()->create();
        $context->insurance = $insurance;
        $this->assertTrue($context->hasInsurance());
    }

    /** @test */
    public function it_checks_has_quotation()
    {
        $context = new NotificationContext;
        $this->assertFalse($context->hasQuotation());

        $quotation = Quotation::factory()->create();
        $context->quotation = $quotation;
        $this->assertTrue($context->hasQuotation());
    }

    /** @test */
    public function it_checks_has_claim()
    {
        $context = new NotificationContext;
        $this->assertFalse($context->hasClaim());

        $claim = Claim::factory()->create();
        $context->claim = $claim;
        $this->assertTrue($context->hasClaim());
    }

    // =======================================================
    // REQUIRED CONTEXT VALIDATION
    // =======================================================

    /** @test */
    public function it_validates_required_context_all_present()
    {
        $customer = Customer::factory()->create();
        $insurance = CustomerInsurance::factory()->create();

        $context = new NotificationContext([
            'customer' => $customer,
            'insurance' => $insurance,
        ]);

        $this->assertTrue($context->hasRequiredContext(['customer', 'insurance']));
    }

    /** @test */
    public function it_validates_required_context_missing_entity()
    {
        $customer = Customer::factory()->create();

        $context = new NotificationContext(['customer' => $customer]);

        $this->assertFalse($context->hasRequiredContext(['customer', 'insurance']));
    }

    /** @test */
    public function it_validates_required_context_empty_requirements()
    {
        $context = new NotificationContext;

        $this->assertTrue($context->hasRequiredContext([]));
    }

    // =======================================================
    // SETTINGS ACCESS TESTS
    // =======================================================

    /** @test */
    public function it_gets_setting_with_dot_notation()
    {
        $context = new NotificationContext([
            'settings' => [
                'company' => ['name' => 'Midas Insurance', 'phone' => '+91 12345'],
            ],
        ]);

        $this->assertEquals('Midas Insurance', $context->getSetting('company.name'));
        $this->assertEquals('+91 12345', $context->getSetting('company.phone'));
    }

    /** @test */
    public function it_returns_null_for_missing_setting()
    {
        $context = new NotificationContext;

        $this->assertNull($context->getSetting('company.name'));
    }

    /** @test */
    public function it_sets_setting_value()
    {
        $context = new NotificationContext;

        $context->setSetting('custom_key', 'custom_value');

        $this->assertEquals('custom_value', $context->getSetting('custom_key'));
    }

    // =======================================================
    // CUSTOM DATA ACCESS TESTS
    // =======================================================

    /** @test */
    public function it_gets_custom_data()
    {
        $context = new NotificationContext([
            'customData' => ['field1' => 'value1'],
        ]);

        $this->assertEquals('value1', $context->getCustomData('field1'));
    }

    /** @test */
    public function it_returns_default_for_missing_custom_data()
    {
        $context = new NotificationContext;

        $this->assertNull($context->getCustomData('missing_field'));
        $this->assertEquals('default', $context->getCustomData('missing_field', 'default'));
    }

    /** @test */
    public function it_sets_custom_data()
    {
        $context = new NotificationContext;

        $context->setCustomData('new_field', 'new_value');

        $this->assertEquals('new_value', $context->getCustomData('new_field'));
    }

    // =======================================================
    // FACTORY METHODS TESTS
    // =======================================================

    /** @test */
    public function it_creates_context_from_customer_id()
    {
        $customer = Customer::factory()->create();

        $context = NotificationContext::fromCustomerId($customer->id);

        $this->assertNotNull($context->customer);
        $this->assertEquals($customer->id, $context->customer->id);
    }

    /** @test */
    public function it_creates_context_from_customer_id_with_insurance()
    {
        $customer = Customer::factory()->create();
        $insurance = CustomerInsurance::factory()->create([
            'customer_id' => $customer->id,
            'status' => true,
        ]);

        $context = NotificationContext::fromCustomerId($customer->id, $insurance->id);

        $this->assertNotNull($context->customer);
        $this->assertNotNull($context->insurance);
        $this->assertEquals($insurance->id, $context->insurance->id);
    }

    /** @test */
    public function it_creates_context_from_insurance_id()
    {
        $insurance = CustomerInsurance::factory()->create();

        $context = NotificationContext::fromInsuranceId($insurance->id);

        $this->assertNotNull($context->insurance);
        $this->assertNotNull($context->customer);
        $this->assertEquals($insurance->id, $context->insurance->id);
    }

    /** @test */
    public function it_creates_context_from_quotation_id()
    {
        $quotation = Quotation::factory()->create();

        $context = NotificationContext::fromQuotationId($quotation->id);

        $this->assertNotNull($context->quotation);
        $this->assertNotNull($context->customer);
        $this->assertEquals($quotation->id, $context->quotation->id);
    }

    /** @test */
    public function it_creates_context_from_claim_id()
    {
        $claim = Claim::factory()->create();

        $context = NotificationContext::fromClaimId($claim->id);

        $this->assertNotNull($context->claim);
        $this->assertNotNull($context->customer);
        $this->assertEquals($claim->id, $context->claim->id);
    }

    /** @test */
    public function it_creates_sample_context_with_real_data()
    {
        // Create some test data first
        Customer::factory()->create(['status' => true]);
        CustomerInsurance::factory()->create(['status' => true]);

        $context = NotificationContext::sample();

        // Sample context should have at least customer or insurance
        $this->assertTrue(
            $context->hasCustomer() || $context->hasInsurance(),
            'Sample context should contain at least one entity'
        );
    }

    // =======================================================
    // TO ARRAY CONVERSION TESTS
    // =======================================================

    /** @test */
    public function it_converts_context_to_array()
    {
        $customer = Customer::factory()->create();
        $insurance = CustomerInsurance::factory()->create();

        $context = new NotificationContext([
            'customer' => $customer,
            'insurance' => $insurance,
            'settings' => ['test' => 'value'],
        ]);

        $array = $context->toArray();

        $this->assertIsArray($array);
        $this->assertArrayHasKey('customer', $array);
        $this->assertArrayHasKey('insurance', $array);
        $this->assertArrayHasKey('quotation', $array);
        $this->assertArrayHasKey('claim', $array);
        $this->assertArrayHasKey('settings', $array);
        $this->assertArrayHasKey('customData', $array);
    }

    /** @test */
    public function it_converts_empty_context_to_array()
    {
        $context = new NotificationContext;

        $array = $context->toArray();

        $this->assertIsArray($array);
        $this->assertNull($array['customer']);
        $this->assertNull($array['insurance']);
        $this->assertEmpty($array['settings']);
    }

    // =======================================================
    // RELATIONSHIP LOADING TESTS
    // =======================================================

    /** @test */
    public function it_loads_customer_relationships_from_id()
    {
        $customer = Customer::factory()->create();

        $context = NotificationContext::fromCustomerId($customer->id);

        // Check that relationships are eager loaded
        $this->assertTrue($context->customer->relationLoaded('familyGroup'));
        $this->assertTrue($context->customer->relationLoaded('customerType'));
    }

    /** @test */
    public function it_loads_insurance_relationships_from_id()
    {
        $insurance = CustomerInsurance::factory()->create();

        $context = NotificationContext::fromInsuranceId($insurance->id);

        // Check that relationships are eager loaded
        $this->assertTrue($context->insurance->relationLoaded('customer'));
        $this->assertTrue($context->insurance->relationLoaded('insuranceCompany'));
        $this->assertTrue($context->insurance->relationLoaded('policyType'));
    }

    /** @test */
    public function it_loads_quotation_relationships_from_id()
    {
        $quotation = Quotation::factory()->create();

        $context = NotificationContext::fromQuotationId($quotation->id);

        // Check that relationships are eager loaded
        $this->assertTrue($context->quotation->relationLoaded('customer'));
        $this->assertTrue($context->quotation->relationLoaded('quotationCompanies'));
    }

    /** @test */
    public function it_handles_null_factory_results_gracefully()
    {
        // Non-existent ID
        $context = NotificationContext::fromCustomerId(99999);

        $this->assertNull($context->customer);
    }
}
