@extends('central.layout')

@section('title', 'Edit Tenant')
@section('page-title', 'Edit Tenant: ' . ($tenant->data['company_name'] ?? 'N/A'))

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-edit me-2"></i>Edit Tenant Information
                </h6>
            </div>
            <div class="card-body">
                <form action="{{ route('central.tenants.update', $tenant) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Company Information -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom">Company Information</h6>

                    <div class="mb-3">
                        <label for="company_name" class="form-label">
                            Company Name <span class="text-danger">*</span>
                        </label>
                        <input type="text"
                               class="form-control @error('company_name') is-invalid @enderror"
                               id="company_name"
                               name="company_name"
                               value="{{ old('company_name', $tenant->data['company_name'] ?? '') }}"
                               required>
                        @error('company_name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Subscription Plan -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">Subscription Plan</h6>

                    @if($tenant->subscription)
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="plan_id" class="form-label">
                                    Plan <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('plan_id') is-invalid @enderror"
                                        id="plan_id"
                                        name="plan_id"
                                        required>
                                    @foreach($plans as $plan)
                                        <option value="{{ $plan->id }}"
                                                {{ old('plan_id', $tenant->subscription->plan_id) == $plan->id ? 'selected' : '' }}>
                                            {{ $plan->name }} - ₹{{ number_format($plan->price, 2) }}/{{ $plan->billing_interval }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('plan_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="col-md-6">
                                <label for="status" class="form-label">
                                    Status <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('status') is-invalid @enderror"
                                        id="status"
                                        name="status"
                                        required>
                                    <option value="trial" {{ old('status', $tenant->subscription->status) == 'trial' ? 'selected' : '' }}>Trial</option>
                                    <option value="active" {{ old('status', $tenant->subscription->status) == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="suspended" {{ old('status', $tenant->subscription->status) == 'suspended' ? 'selected' : '' }}>Suspended</option>
                                    <option value="cancelled" {{ old('status', $tenant->subscription->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                    <option value="expired" {{ old('status', $tenant->subscription->status) == 'expired' ? 'selected' : '' }}>Expired</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        @if($tenant->subscription->is_trial && $tenant->subscription->trial_ends_at)
                            <div class="mb-3">
                                <label for="trial_ends_at" class="form-label">
                                    Trial Ends At
                                </label>
                                <input type="date"
                                       class="form-control @error('trial_ends_at') is-invalid @enderror"
                                       id="trial_ends_at"
                                       name="trial_ends_at"
                                       value="{{ old('trial_ends_at', $tenant->subscription->trial_ends_at->format('Y-m-d')) }}">
                                @error('trial_ends_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        @endif
                    @else
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            This tenant has no active subscription.
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('central.tenants.show', $tenant) }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Tenant
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-info-circle me-2"></i>Tenant Information
                </h6>
            </div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-sm-4">Tenant ID:</dt>
                    <dd class="col-sm-8"><code>{{ $tenant->id }}</code></dd>

                    <dt class="col-sm-4">Domain:</dt>
                    <dd class="col-sm-8">{{ $tenant->domains->first()->domain ?? 'N/A' }}</dd>

                    <dt class="col-sm-4">Created:</dt>
                    <dd class="col-sm-8">{{ $tenant->created_at->format('M d, Y H:i') }}</dd>

                    <dt class="col-sm-4">Updated:</dt>
                    <dd class="col-sm-8">{{ $tenant->updated_at->format('M d, Y H:i') }}</dd>
                </dl>
            </div>
        </div>

        <div class="card mt-3">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-danger">
                    <i class="fas fa-exclamation-triangle me-2"></i>Danger Zone
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    Permanently delete this tenant and all associated data.
                </p>
                <form action="{{ route('central.tenants.destroy', $tenant) }}"
                      method="POST"
                      onsubmit="return confirm('Are you sure? This action cannot be undone!');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm w-100">
                        <i class="fas fa-trash-alt me-2"></i>Delete Tenant
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
