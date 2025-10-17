<?php

use App\Models\Customer;
use App\Models\CustomerInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('has correct fillable attributes', function () {
    $customer = new Customer;
    $expectedFillable = [
        'name',
        'email',
        'mobile_number',
        'status',
        'wedding_anniversary_date',
        'date_of_birth',
        'engagement_anniversary_date',
        'pan_card_number',
        'aadhar_card_number',
        'gst_number',
        'pan_card_path',
        'aadhar_card_path',
        'gst_path',
        'type',
        'family_group_id',
        'password',
        'email_verified_at',
        'password_changed_at',
        'must_change_password',
        'email_verification_token',
        'password_reset_sent_at',
        'password_reset_token',
        'password_reset_expires_at',
    ];

    expect($customer->getFillable())->toBe($expectedFillable);
});

it('can create customer with basic fields', function () {
    $customer = Customer::create([
        'name' => 'Test Customer',
        'email' => 'test@customer.com',
        'mobile_number' => '9876543210',
        'status' => true,
        'type' => 'Retail',
    ]);

    $this->assertDatabaseHas('customers', [
        'name' => 'Test Customer',
        'email' => 'test@customer.com',
        'mobile_number' => '9876543210',
    ]);
});

it('has insurance relationship', function () {
    $customer = Customer::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['customer_id' => $customer->id]);

    expect($customer->insurance->contains($insurance))->toBeTrue();
    expect($customer->insurance->first())->toBeInstanceOf(CustomerInsurance::class);
});

it('masks email correctly', function () {
    $customer = Customer::factory()->create(['email' => 'testuser@example.com']);

    $maskedEmail = $customer->getPrivacySafeData()['email'];

    expect($maskedEmail)->toContain('te******@example.com');
});

it('masks mobile number correctly', function () {
    $customer = Customer::factory()->create(['mobile_number' => '9876543210']);

    $maskedMobile = $customer->getPrivacySafeData()['mobile_number'];

    expect($maskedMobile)->toStartWith('98');
    expect($maskedMobile)->toEndWith('10');
    expect($maskedMobile)->toContain('*');
});

it('can check if customer is active', function () {
    $activeCustomer = Customer::factory()->create(['status' => true]);
    $inactiveCustomer = Customer::factory()->create(['status' => false]);

    expect($activeCustomer->isActive())->toBeTrue();
    expect($inactiveCustomer->isActive())->toBeFalse();
});

it('can check if customer is retail', function () {
    $retailCustomer = Customer::factory()->create(['type' => 'Retail']);

    expect($retailCustomer->isRetailCustomer())->toBeTrue();
    expect($retailCustomer->isCorporateCustomer())->toBeFalse();
});

it('can check if customer is corporate', function () {
    $corporateCustomer = Customer::factory()->create(['type' => 'Corporate']);

    expect($corporateCustomer->isCorporateCustomer())->toBeTrue();
    expect($corporateCustomer->isRetailCustomer())->toBeFalse();
});

it('generates default password correctly', function () {
    $password = Customer::generateDefaultPassword();

    expect(strlen($password))->toBe(8);
    expect($password)->toMatch('/^[A-Z0-9]+$/');
});

it('can set default password', function () {
    $customer = Customer::factory()->create();

    $plainPassword = $customer->setDefaultPassword();

    expect(strlen($plainPassword))->toBe(8);
    expect($customer->fresh()->must_change_password)->toBeTrue();
    expect(Hash::check($plainPassword, $customer->fresh()->password))->toBeTrue();
});

it('can change password', function () {
    $customer = Customer::factory()->create([
        'password' => Hash::make('oldpassword'),
        'must_change_password' => true,
    ]);

    $customer->changePassword('newpassword');

    expect(Hash::check('newpassword', $customer->fresh()->password))->toBeTrue();
    expect($customer->fresh()->must_change_password)->toBeFalse();
    expect($customer->fresh()->password_changed_at)->not->toBeNull();
});

it('can verify email with token', function () {
    $customer = Customer::factory()->create(['email_verified_at' => null]);
    $token = $customer->generateEmailVerificationToken();

    $result = $customer->verifyEmail($token);

    expect($result)->toBeTrue();
    expect($customer->fresh()->email_verified_at)->not->toBeNull();
    expect($customer->fresh()->email_verification_token)->toBeNull();
});

it('masks pan number correctly', function () {
    $customer = Customer::factory()->create(['pan_card_number' => 'CFDPB1228P']);

    $maskedPan = $customer->getMaskedPanNumber();

    expect($maskedPan)->toBe('CFD*****P');
});

it('formats date of birth for ui', function () {
    $customer = Customer::factory()->create([
        'date_of_birth' => '2000-01-15',
    ]);

    $formatted = $customer->date_of_birth_formatted;

    expect($formatted)->not->toBeNull();
});

it('uses soft deletes', function () {
    $customer = Customer::factory()->create();
    $customerId = $customer->id;

    $customer->delete();

    $this->assertSoftDeleted('customers', ['id' => $customerId]);
});

it('checks password correctly', function () {
    $customer = Customer::factory()->create([
        'password' => Hash::make('testpassword'),
    ]);

    expect($customer->checkPassword('testpassword'))->toBeTrue();
    expect($customer->checkPassword('wrongpassword'))->toBeFalse();
});
