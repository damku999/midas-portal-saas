@extends('central.layout')

@section('title', 'Edit Tenant')
@section('page-title', 'Edit Tenant: ' . ($tenant->company_name ?? 'N/A'))

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

                <form action="{{ route('central.tenants.update', $tenant) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

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
                                   value="{{ old('company_name', $tenant->company_name ?? '') }}"
                                   required>
                            @error('company_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="email" class="form-label">
                                Company Email <span class="text-danger">*</span>
                            </label>
                            <input type="email"
                                   class="form-control @error('email') is-invalid @enderror"
                                   id="email"
                                   name="email"
                                   value="{{ old('email', $tenant->email ?? '') }}"
                                   required>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Company Phone</label>
                        <input type="tel"
                               class="form-control @error('phone') is-invalid @enderror"
                               id="phone"
                               name="phone"
                               value="{{ old('phone', $tenant->phone ?? '') }}">
                        @error('phone')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Branding & Theme -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">Branding & Theme</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company_tagline" class="form-label">Company Tagline</label>
                            <input type="text"
                                   class="form-control @error('company_tagline') is-invalid @enderror"
                                   id="company_tagline"
                                   name="company_tagline"
                                   value="{{ old('company_tagline', $tenant->company_tagline ?? '') }}"
                                   placeholder="Your Trusted Insurance Partner">
                            @error('company_tagline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Appears in emails and documents</small>
                        </div>

                        <div class="col-md-6">
                            <label for="theme_primary_color" class="form-label">Primary Brand Color</label>
                            <div class="input-group">
                                <input type="color"
                                       class="form-control form-control-color @error('theme_primary_color') is-invalid @enderror"
                                       id="theme_primary_color"
                                       name="theme_primary_color"
                                       value="{{ old('theme_primary_color', $tenant->theme_primary_color ?? '#17a2b8') }}"
                                       style="width: 60px;">
                                <input type="text"
                                       class="form-control"
                                       id="theme_primary_color_hex"
                                       value="{{ old('theme_primary_color', $tenant->theme_primary_color ?? '#17a2b8') }}"
                                       placeholder="#17a2b8"
                                       readonly>
                            </div>
                            @error('theme_primary_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Used for buttons, links, and branding</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="company_logo" class="form-label">Company Logo</label>
                        @if(!empty($tenant->company_logo))
                            <div class="mb-2">
                                <small class="text-muted">Current logo: {{ basename($tenant->company_logo) }}</small>
                            </div>
                        @endif
                        <input type="file"
                               class="form-control @error('company_logo') is-invalid @enderror"
                               id="company_logo"
                               name="company_logo"
                               accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                        @error('company_logo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="text-muted">PNG, JPG, JPEG, or SVG (max 2MB). Leave empty to keep current logo.</small>
                    </div>

                    <!-- Communication Settings -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">Communication Settings</h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="whatsapp_sender_id" class="form-label">WhatsApp Sender ID</label>
                            <input type="text"
                                   class="form-control @error('whatsapp_sender_id') is-invalid @enderror"
                                   id="whatsapp_sender_id"
                                   name="whatsapp_sender_id"
                                   value="{{ old('whatsapp_sender_id', $tenant->whatsapp_sender_id ?? '') }}"
                                   placeholder="919800071314">
                            @error('whatsapp_sender_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Phone number without + or spaces</small>
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_auth_token" class="form-label">WhatsApp Auth Token</label>
                            <input type="text"
                                   class="form-control @error('whatsapp_auth_token') is-invalid @enderror"
                                   id="whatsapp_auth_token"
                                   name="whatsapp_auth_token"
                                   value="{{ old('whatsapp_auth_token', $tenant->whatsapp_auth_token ?? '') }}"
                                   placeholder="Enter API authentication token">
                            @error('whatsapp_auth_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave empty to use central WhatsApp config</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email_from_address" class="form-label">Email From Address</label>
                            <input type="email"
                                   class="form-control @error('email_from_address') is-invalid @enderror"
                                   id="email_from_address"
                                   name="email_from_address"
                                   value="{{ old('email_from_address', $tenant->email_from_address ?? '') }}"
                                   placeholder="noreply@company.com">
                            @error('email_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Email address for outgoing notifications</small>
                        </div>

                        <div class="col-md-6">
                            <label for="email_from_name" class="form-label">Email From Name</label>
                            <input type="text"
                                   class="form-control @error('email_from_name') is-invalid @enderror"
                                   id="email_from_name"
                                   name="email_from_name"
                                   value="{{ old('email_from_name', $tenant->email_from_name ?? '') }}"
                                   placeholder="Company Name">
                            @error('email_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Display name for outgoing emails</small>
                        </div>
                    </div>

                    <!-- Advanced Settings -->
                    <div class="accordion mt-4 mb-3" id="advancedSettings">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced">
                                    <i class="fas fa-cog me-2"></i>Advanced Settings
                                </button>
                            </h2>
                            <div id="collapseAdvanced" class="accordion-collapse collapse" data-bs-parent="#advancedSettings">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="timezone" class="form-label">Timezone</label>
                                            <select class="form-select @error('timezone') is-invalid @enderror"
                                                    id="timezone"
                                                    name="timezone">
                                                <option value="">Use Default (Asia/Kolkata)</option>
                                                <option value="Asia/Kolkata" {{ old('timezone', $tenant->timezone ?? '') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                                                <option value="America/New_York" {{ old('timezone', $tenant->timezone ?? '') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                                <option value="Europe/London" {{ old('timezone', $tenant->timezone ?? '') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                                                <option value="Asia/Dubai" {{ old('timezone', $tenant->timezone ?? '') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                                            </select>
                                            @error('timezone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="currency" class="form-label">Currency Code</label>
                                            <select class="form-select @error('currency') is-invalid @enderror"
                                                    id="currency"
                                                    name="currency">
                                                <option value="">Use Default (INR)</option>
                                                <option value="INR" {{ old('currency', $tenant->currency ?? '') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                                <option value="USD" {{ old('currency', $tenant->currency ?? '') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                                <option value="EUR" {{ old('currency', $tenant->currency ?? '') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                                <option value="GBP" {{ old('currency', $tenant->currency ?? '') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                                <option value="AED" {{ old('currency', $tenant->currency ?? '') == 'AED' ? 'selected' : '' }}>AED - UAE Dirham</option>
                                            </select>
                                            @error('currency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="currency_symbol" class="form-label">Currency Symbol</label>
                                            <input type="text"
                                                   class="form-control @error('currency_symbol') is-invalid @enderror"
                                                   id="currency_symbol"
                                                   name="currency_symbol"
                                                   value="{{ old('currency_symbol', $tenant->currency_symbol ?? '') }}"
                                                   placeholder="₹"
                                                   maxlength="5">
                                            @error('currency_symbol')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Plan -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">
                        Subscription Management
                        @if($tenant->subscription && $tenant->subscription->is_trial)
                            <span class="badge bg-warning text-dark ms-2">Trial</span>
                        @endif
                    </h6>

                    @if($tenant->subscription)
                        <!-- Trial End Action -->
                        @if($tenant->subscription->is_trial)
                            <div class="alert alert-info d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <i class="fas fa-info-circle me-2"></i>
                                    <strong>Trial Period Active</strong> - Ends on {{ $tenant->subscription->trial_ends_at->format('M d, Y') }}
                                </div>
                                <form action="{{ route('central.tenants.end-trial', $tenant) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-primary" onclick="return confirm('Are you sure you want to end the trial and convert to paid subscription?')">
                                        <i class="fas fa-check-circle me-1"></i>End Trial Now
                                    </button>
                                </form>
                            </div>
                        @endif

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

                        <!-- Subscription Dates -->
                        <h6 class="text-muted mb-3 mt-4">Subscription Dates</h6>

                        @if($tenant->subscription->is_trial && $tenant->subscription->trial_ends_at)
                            <div class="mb-3">
                                <label for="trial_ends_at" class="form-label">
                                    Trial Ends At
                                    <small class="text-muted">(Change to extend or shorten trial)</small>
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

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label for="starts_at" class="form-label">Subscription Start Date</label>
                                <input type="date"
                                       class="form-control @error('starts_at') is-invalid @enderror"
                                       id="starts_at"
                                       name="starts_at"
                                       value="{{ old('starts_at', $tenant->subscription->starts_at ? $tenant->subscription->starts_at->format('Y-m-d') : '') }}">
                                @error('starts_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">When the subscription started</small>
                            </div>

                            <div class="col-md-4">
                                <label for="ends_at" class="form-label">Subscription End Date</label>
                                <input type="date"
                                       class="form-control @error('ends_at') is-invalid @enderror"
                                       id="ends_at"
                                       name="ends_at"
                                       value="{{ old('ends_at', $tenant->subscription->ends_at ? $tenant->subscription->ends_at->format('Y-m-d') : '') }}">
                                @error('ends_at')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">When the current period ends</small>
                            </div>

                            <div class="col-md-4">
                                <label for="next_billing_date" class="form-label">Next Billing Date</label>
                                <input type="date"
                                       class="form-control @error('next_billing_date') is-invalid @enderror"
                                       id="next_billing_date"
                                       name="next_billing_date"
                                       value="{{ old('next_billing_date', $tenant->subscription->next_billing_date ? $tenant->subscription->next_billing_date->format('Y-m-d') : '') }}">
                                @error('next_billing_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">When to bill next</small>
                            </div>
                        </div>

                        <div class="alert alert-warning small">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Note:</strong> Changing subscription dates will affect billing cycles. Make sure dates are correct.
                        </div>
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
                    Permanently delete this tenant and all associated data. This action cannot be undone.
                </p>
                <form action="{{ route('central.tenants.destroy', $tenant) }}"
                      method="POST"
                      id="deleteTenantForm">
                    @csrf
                    @method('DELETE')
                    <button type="button"
                            class="btn btn-danger btn-sm w-100"
                            onclick="confirmDeleteTenant()">
                        <i class="fas fa-trash-alt me-2"></i>Delete Tenant
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@php
    $companyName = $tenant->company_name ?? ($tenant->domains->first()->domain ?? $tenant->id);
@endphp

<x-central.delete-confirmation-modal
    :confirmation-text="$companyName"
    modal-id="deleteConfirmationModal"
    form-id="deleteTenantForm"
    item-type="Tenant"
/>

@push('scripts')
<script>
    // Sync color picker with hex input
    const colorPicker = document.getElementById('theme_primary_color');
    const colorHex = document.getElementById('theme_primary_color_hex');

    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function(e) {
            colorHex.value = e.target.value.toUpperCase();
        });
    }

    function confirmDeleteTenant() {
        // Show custom modal
        const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
        modal.show();
    }
</script>
@endpush
@endsection
