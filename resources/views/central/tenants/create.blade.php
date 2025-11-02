@extends('central.layout')

@section('title', 'Create Tenant')
@section('page-title', 'Create New Tenant')

@section('content')
<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-plus-circle me-2"></i>Tenant Information
                </h6>
            </div>
            <div class="card-body">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <strong>Validation Error:</strong> Please fix the following issues:
                        <ul class="mb-0 mt-2">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form action="{{ route('central.tenants.store') }}" method="POST">
                    @csrf

                    <!-- Company Information -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom">Company Information</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company_name" class="form-label">
                                Company Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('company_name') is-invalid @enderror"
                                   id="company_name"
                                   name="company_name"
                                   value="{{ old('company_name') }}"
                                   required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="subdomain" class="form-label">
                                Subdomain <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <input type="text"
                                       class="form-control @error('subdomain') is-invalid @enderror"
                                       id="subdomain"
                                       name="subdomain"
                                       value="{{ old('subdomain') }}"
                                       placeholder="company"
                                       required>
                                <span class="input-group-text">.{{ config('app.domain', 'midastech.in') }}</span>
                                @error('subdomain')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Only lowercase letters, numbers, and hyphens allowed</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                Company Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="phone" class="form-label">
                                Company Phone
                            </label>
                            <input type="tel"
                                   class="form-control @error('phone') is-invalid @enderror"
                                   id="phone"
                                   name="phone"
                                   value="{{ old('phone') }}">
                            @error('phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Admin User Information -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">Admin User Information</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="admin_first_name" class="form-label">
                                Admin First Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('admin_first_name') is-invalid @enderror"
                                   id="admin_first_name"
                                   name="admin_first_name"
                                   value="{{ old('admin_first_name') }}"
                                   required>
                            @error('admin_first_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="admin_last_name" class="form-label">
                                Admin Last Name <span class="text-danger">*</span>
                            </label>
                            <input type="text"
                                   class="form-control @error('admin_last_name') is-invalid @enderror"
                                   id="admin_last_name"
                                   name="admin_last_name"
                                   value="{{ old('admin_last_name') }}"
                                   required>
                            @error('admin_last_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="admin_email" class="form-label">
                                Admin Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('admin_email') is-invalid @enderror"
                                   id="admin_email"
                                   name="admin_email"
                                   value="{{ old('admin_email') }}"
                                   required>
                            @error('admin_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="admin_password" class="form-label">
                                Admin Password
                            </label>
                            <input type="password"
                                   class="form-control @error('admin_password') is-invalid @enderror"
                                   id="admin_password"
                                   name="admin_password"
                                   minlength="8">
                            @error('admin_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave blank to auto-generate (min 8 characters)</small>
                        </div>
                    </div>

                    <!-- Subscription Plan -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">Subscription Plan</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="plan_id" class="form-label">
                                Plan <span class="text-danger">*</span>
                            </label>
                            <select class="form-select @error('plan_id') is-invalid @enderror"
                                    id="plan_id"
                                    name="plan_id"
                                    required>
                                <option value="">Select a plan</option>
                                @foreach($plans as $plan)
                                    <option value="{{ $plan->id }}"
                                            {{ old('plan_id') == $plan->id ? 'selected' : '' }}>
                                        {{ $plan->name }} - ₹{{ number_format($plan->price, 2) }}/{{ $plan->billing_interval }}
                                    </option>
                                @endforeach
                            </select>
                            @error('plan_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="trial_days" class="form-label">
                                Trial Period (Days)
                            </label>
                            <input type="number"
                                   class="form-control @error('trial_days') is-invalid @enderror"
                                   id="trial_days"
                                   name="trial_days"
                                   value="{{ old('trial_days', 14) }}"
                                   min="0"
                                   max="90">
                            @error('trial_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Enter 0 for no trial period</small>
                        </div>
                    </div>

                    <!-- Additional Settings -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">Additional Settings</h6>

                    <div class="mb-3">
                        <div class="form-check">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="send_welcome_email"
                                   name="send_welcome_email"
                                   value="1"
                                   {{ old('send_welcome_email', true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="send_welcome_email">
                                Send welcome email to admin
                            </label>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-4">
                        <a href="{{ route('central.tenants.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Create Tenant
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
                    <i class="fas fa-info-circle me-2"></i>Information
                </h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">
                    <strong>What happens when you create a tenant?</strong>
                </p>
                <ul class="small text-muted">
                    <li>A new database will be created</li>
                    <li>Domain will be registered</li>
                    <li>Database tables will be migrated</li>
                    <li>Admin user will be created</li>
                    <li>Subscription will be activated</li>
                    <li>Welcome email will be sent (if enabled)</li>
                </ul>

                <div class="alert alert-warning mt-3" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <small>
                        <strong>Important:</strong> Make sure the subdomain is unique and follows naming conventions.
                    </small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Auto-generate subdomain from company name
    document.getElementById('company_name').addEventListener('input', function(e) {
        const subdomainField = document.getElementById('subdomain');
        if (!subdomainField.value) {
            const subdomain = e.target.value
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            subdomainField.value = subdomain;
        }
    });
</script>
@endpush
