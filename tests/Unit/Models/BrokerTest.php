<?php

use App\Models\Broker;
use App\Models\CustomerInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has correct fillable attributes', function () {
    $broker = new Broker;
    $fillable = ['name', 'email', 'mobile_number', 'status'];

    expect($broker->getFillable())->toBe($fillable);
});

it('can create broker with all fields', function () {
    $broker = Broker::create([
        'name' => 'Test Broker',
        'email' => 'test@broker.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);

    $this->assertDatabaseHas('brokers', [
        'name' => 'Test Broker',
        'email' => 'test@broker.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);
});

it('has customer insurances relationship', function () {
    $broker = Broker::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['broker_id' => $broker->id]);

    expect($broker->customerInsurances->contains($insurance))->toBeTrue();
    expect($broker->customerInsurances->first())->toBeInstanceOf(CustomerInsurance::class);
});

it('uses soft deletes', function () {
    $broker = Broker::factory()->create();
    $brokerId = $broker->id;

    $broker->delete();

    $this->assertSoftDeleted('brokers', ['id' => $brokerId]);
});

it('can toggle status', function () {
    $broker = Broker::factory()->create(['status' => 1]);

    $broker->update(['status' => 0]);

    expect($broker->fresh()->status)->toBe(0);
});

it('validates email uniqueness', function () {
    Broker::factory()->create(['email' => 'unique@broker.com']);

    expect(fn () => Broker::factory()->create(['email' => 'unique@broker.com']))
        ->toThrow(\Illuminate\Database\QueryException::class);
});
