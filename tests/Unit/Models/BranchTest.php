<?php

use App\Models\Branch;
use App\Models\CustomerInsurance;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('has correct fillable attributes', function () {
    $branch = new Branch;
    $fillable = ['name', 'email', 'mobile_number', 'status'];

    expect($branch->getFillable())->toBe($fillable);
});

it('can create branch with all fields', function () {
    $branch = Branch::create([
        'name' => 'Test Branch',
        'email' => 'test@branch.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);

    $this->assertDatabaseHas('branches', [
        'name' => 'Test Branch',
        'email' => 'test@branch.com',
        'mobile_number' => '9876543210',
        'status' => 1,
    ]);
});

it('has customer insurances relationship', function () {
    $branch = Branch::factory()->create();
    $insurance = CustomerInsurance::factory()->create(['branch_id' => $branch->id]);

    expect($branch->customerInsurances->contains($insurance))->toBeTrue();
    expect($branch->customerInsurances->first())->toBeInstanceOf(CustomerInsurance::class);
});

it('uses soft deletes', function () {
    $branch = Branch::factory()->create();
    $branchId = $branch->id;

    $branch->delete();

    $this->assertSoftDeleted('branches', ['id' => $branchId]);
    expect(Branch::withTrashed()->find($branchId)->deleted_at)->not->toBeNull();
});

it('can filter by status', function () {
    Branch::factory()->create(['status' => 1]);
    Branch::factory()->create(['status' => 0]);

    $activeBranches = Branch::where('status', 1)->get();
    $inactiveBranches = Branch::where('status', 0)->get();

    expect($activeBranches)->toHaveCount(1);
    expect($inactiveBranches)->toHaveCount(1);
});

it('can retrieve only active branches', function () {
    Branch::factory()->count(3)->create(['status' => 1]);
    Branch::factory()->count(2)->create(['status' => 0]);

    $activeBranches = Branch::where('status', 1)->get();

    expect($activeBranches)->toHaveCount(3);
    $activeBranches->each(function ($branch) {
        expect($branch->status)->toBe(1);
    });
});

it('stores audit trail fields', function () {
    $branch = Branch::factory()->create();

    expect($branch->created_at)->not->toBeNull();
    expect($branch->updated_at)->not->toBeNull();
    expect($branch->deleted_at)->toBeNull();
});
