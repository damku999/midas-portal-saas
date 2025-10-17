<?php

use App\Models\CustomerInsurance;
use App\Models\ReferenceUser;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has correct fillable attributes', function () {
    $referenceUser = new ReferenceUser;
    $fillable = ['name', 'email', 'mobile_number', 'status'];

    expect($referenceUser->getFillable())->toBe($fillable);
});

it('can create reference user with all fields', function () {
    $referenceUser = ReferenceUser::create([
        'name' => 'Test Reference User',
        'email' => 'test@reference.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);

    $this->assertDatabaseHas('reference_users', [
        'name' => 'Test Reference User',
        'email' => 'test@reference.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);
});

it('has customer insurances relationship', function () {
    $referenceUser = ReferenceUser::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['reference_by' => $referenceUser->id]);

    expect($referenceUser->customerInsurances->contains($insurance))->toBeTrue();
});

it('uses soft deletes', function () {
    $referenceUser = ReferenceUser::factory()->create();
    $referenceUserId = $referenceUser->id;

    $referenceUser->delete();

    $this->assertSoftDeleted('reference_users', ['id' => $referenceUserId]);
});

it('can retrieve active reference users only', function () {
    ReferenceUser::factory()->count(3)->create(['status' => 1]);
    ReferenceUser::factory()->count(2)->create(['status' => 0]);

    $activeUsers = ReferenceUser::where('status', 1)->get();

    expect($activeUsers)->toHaveCount(3);
});
