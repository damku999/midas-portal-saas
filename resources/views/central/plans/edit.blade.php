@extends('central.layout')

@section('title', 'Edit Plan')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Edit Plan: {{ $plan->name }}</h1>
                <a href="{{ route('central.plans.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Plans
                </a>
            </div>
        </div>
    </div>

    @php
        $hasSubscriptions = $plan->subscriptions_count > 0;
    @endphp

    @if($hasSubscriptions)
        <div class="alert alert-warning">
            <i class="bi bi-exclamation-triangle me-1"></i>
            <strong>Note:</strong> This plan has {{ $plan->subscriptions_count }} active subscription(s).
            Critical fields (price, limits, billing) are locked to protect existing subscriptions.
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8 mx-auto">
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('central.plans.update', $plan) }}" method="POST">
                        @csrf
                        @method('PUT')

                        <!-- Basic Information -->
                        <h5 class="mb-3">Basic Information</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">
                                    Plan Name <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $plan->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="slug" class="form-label">
                                    Slug <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control @error('slug') is-invalid @enderror"
                                       id="slug" name="slug" value="{{ old('slug', $plan->slug) }}"
                                       {{ $hasSubscriptions ? 'readonly' : 'required' }}>
                                @if($hasSubscriptions)
                                    <small class="text-muted">🔒 Locked (plan has subscriptions)</small>
                                @else
                                    <small class="text-muted">URL-friendly identifier (e.g., basic-plan)</small>
                                @endif
                                @error('slug')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $plan->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <hr class="my-4">

                        <!-- Pricing -->
                        <h5 class="mb-3">
                            Pricing
                            @if($hasSubscriptions)
                                <span class="badge bg-warning text-dark ms-2">Locked</span>
                            @endif
                        </h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label">
                                    Price <span class="text-danger">*</span>
                                </label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" class="form-control @error('price') is-invalid @enderror"
                                           id="price" name="price" value="{{ old('price', $plan->price) }}"
                                           step="0.01" min="0"
                                           {{ $hasSubscriptions ? 'readonly' : 'required' }}>
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                @if($hasSubscriptions)
                                    <small class="text-muted">🔒 Locked (plan has subscriptions)</small>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <label for="billing_interval" class="form-label">
                                    Billing Interval <span class="text-danger">*</span>
                                </label>
                                <select class="form-select @error('billing_interval') is-invalid @enderror"
                                        id="billing_interval" name="billing_interval"
                                        {{ $hasSubscriptions ? 'disabled' : 'required' }}>
                                    <option value="">Select Interval</option>
                                    <option value="week" {{ old('billing_interval', $plan->billing_interval) === 'week' ? 'selected' : '' }}>Weekly</option>
                                    <option value="month" {{ old('billing_interval', $plan->billing_interval) === 'month' ? 'selected' : '' }}>Monthly</option>
                                    <option value="two_month" {{ old('billing_interval', $plan->billing_interval) === 'two_month' ? 'selected' : '' }}>Every 2 Months</option>
                                    <option value="quarter" {{ old('billing_interval', $plan->billing_interval) === 'quarter' ? 'selected' : '' }}>Quarterly (3 Months)</option>
                                    <option value="six_month" {{ old('billing_interval', $plan->billing_interval) === 'six_month' ? 'selected' : '' }}>Half-Yearly (6 Months)</option>
                                    <option value="year" {{ old('billing_interval', $plan->billing_interval) === 'year' ? 'selected' : '' }}>Yearly</option>
                                </select>
                                @if($hasSubscriptions)
                                    <small class="text-muted">🔒 Locked (plan has subscriptions)</small>
                                @endif
                                @error('billing_interval')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Usage Limits -->
                        <h5 class="mb-3">
                            Usage Limits
                            @if($hasSubscriptions)
                                <span class="badge bg-warning text-dark ms-2">Locked</span>
                            @endif
                        </h5>
                        @if($hasSubscriptions)
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-1"></i>
                                Limits are locked to protect existing subscriptions
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="bi bi-info-circle me-1"></i>
                                Use -1 for unlimited limits
                            </div>
                        @endif

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="max_users" class="form-label">
                                    Max Users <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('max_users') is-invalid @enderror"
                                       id="max_users" name="max_users" value="{{ old('max_users', $plan->max_users) }}"
                                       min="-1"
                                       {{ $hasSubscriptions ? 'readonly' : 'required' }}>
                                @error('max_users')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="max_customers" class="form-label">
                                    Max Customers <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('max_customers') is-invalid @enderror"
                                       id="max_customers" name="max_customers" value="{{ old('max_customers', $plan->max_customers) }}"
                                       min="-1"
                                       {{ $hasSubscriptions ? 'readonly' : 'required' }}>
                                @error('max_customers')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label for="max_leads_per_month" class="form-label">
                                    Max Leads/Month <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('max_leads_per_month') is-invalid @enderror"
                                       id="max_leads_per_month" name="max_leads_per_month"
                                       value="{{ old('max_leads_per_month', $plan->max_leads_per_month) }}"
                                       min="-1"
                                       {{ $hasSubscriptions ? 'readonly' : 'required' }}>
                                @error('max_leads_per_month')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="storage_limit_gb" class="form-label">
                                    Storage Limit (GB) <span class="text-danger">*</span>
                                </label>
                                <input type="number" class="form-control @error('storage_limit_gb') is-invalid @enderror"
                                       id="storage_limit_gb" name="storage_limit_gb"
                                       value="{{ old('storage_limit_gb', $plan->storage_limit_gb) }}"
                                       min="1"
                                       {{ $hasSubscriptions ? 'readonly' : 'required' }}>
                                @error('storage_limit_gb')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <hr class="my-4">

                        <!-- Features -->
                        <h5 class="mb-3">
                            Features
                            @if($hasSubscriptions)
                                <span class="badge bg-warning text-dark ms-2">Locked</span>
                            @endif
                        </h5>
                        <div class="mb-3">
                            <label class="form-label">Plan Features</label>
                            <div id="features-container">
                                @php
                                    $features = old('features', $plan->features ?? []);
                                @endphp
                                @if(count($features) > 0)
                                    @foreach($features as $index => $feature)
                                        <div class="input-group mb-2 feature-row">
                                            <input type="text" class="form-control" name="features[]"
                                                   value="{{ $feature }}" placeholder="Enter feature..."
                                                   {{ $hasSubscriptions ? 'readonly' : '' }}>
                                            @if(!$hasSubscriptions)
                                                <button type="button" class="btn btn-outline-danger remove-feature">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            @endif
                                        </div>
                                    @endforeach
                                @else
                                    <div class="input-group mb-2 feature-row">
                                        <input type="text" class="form-control" name="features[]"
                                               placeholder="Enter feature..."
                                               {{ $hasSubscriptions ? 'readonly' : '' }}>
                                        @if(!$hasSubscriptions)
                                            <button type="button" class="btn btn-outline-danger remove-feature">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                @endif
                            </div>
                            @if(!$hasSubscriptions)
                                <button type="button" class="btn btn-sm btn-outline-primary" id="add-feature">
                                    <i class="bi bi-plus-circle me-1"></i> Add Feature
                                </button>
                            @endif
                        </div>

                        <hr class="my-4">

                        <!-- Status & Order -->
                        <h5 class="mb-3">Status & Display</h5>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="is_active"
                                           name="is_active" value="1" {{ old('is_active', $plan->is_active) ? 'checked' : '' }}>
                                    <label class="form-check-label" for="is_active">
                                        Active (available for new subscriptions)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="sort_order" class="form-label">Sort Order</label>
                                <input type="number" class="form-control @error('sort_order') is-invalid @enderror"
                                       id="sort_order" name="sort_order" value="{{ old('sort_order', $plan->sort_order) }}"
                                       min="0">
                                <small class="text-muted">Lower numbers appear first</small>
                                @error('sort_order')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('central.plans.index') }}" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Update Plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@if(!$hasSubscriptions)
@push('scripts')
<script>
    // Auto-generate slug from name (only if no subscriptions)
    document.getElementById('name').addEventListener('input', function(e) {
        const slug = e.target.value
            .toLowerCase()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
        document.getElementById('slug').value = slug;
    });

    // Add/Remove Features
    document.getElementById('add-feature').addEventListener('click', function() {
        const container = document.getElementById('features-container');
        const newFeature = document.createElement('div');
        newFeature.className = 'input-group mb-2 feature-row';
        newFeature.innerHTML = `
            <input type="text" class="form-control" name="features[]" placeholder="Enter feature...">
            <button type="button" class="btn btn-outline-danger remove-feature">
                <i class="bi bi-trash"></i>
            </button>
        `;
        container.appendChild(newFeature);
    });

    document.getElementById('features-container').addEventListener('click', function(e) {
        if (e.target.closest('.remove-feature')) {
            const featureRow = e.target.closest('.feature-row');
            if (document.querySelectorAll('.feature-row').length > 1) {
                featureRow.remove();
            } else {
                featureRow.querySelector('input').value = '';
            }
        }
    });
</script>
@endpush
@endif
@endsection
