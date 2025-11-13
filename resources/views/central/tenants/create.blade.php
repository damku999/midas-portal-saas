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

                <form action="{{ route('central.tenants.store') }}" method="POST" enctype="multipart/form-data">
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
                                <span class="input-group-text">.</span>
                                <select class="form-select @error('domain') is-invalid @enderror"
                                        id="domain"
                                        name="domain"
                                        style="max-width: 250px;"
                                        required>
                                    @php
                                        $domains = config('tenancy-domains.domains', []);
                                        $currentEnv = app()->environment();
                                        $autoDetect = config('tenancy-domains.auto_detect_environment', true);
                                        $defaultDomain = config('tenancy-domains.default', config('app.domain', 'midastech.in'));
                                    @endphp
                                    @foreach($domains as $domain => $config)
                                        @if($config['enabled'] && (!$autoDetect || in_array($currentEnv, $config['environment'])))
                                            <option value="{{ $domain }}"
                                                    {{ old('domain', $defaultDomain) == $domain ? 'selected' : '' }}>
                                                {{ $config['label'] }}
                                            </option>
                                        @endif
                                    @endforeach
                                </select>
                                @error('subdomain')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                @error('domain')
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

                    <!-- Branding & Theme (Optional) -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">
                        Branding & Theme
                        <small class="text-muted fw-normal">(Optional - uses central defaults if not provided)</small>
                    </h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="company_tagline" class="form-label">
                                Company Tagline
                            </label>
                            <input type="text"
                                   class="form-control @error('company_tagline') is-invalid @enderror"
                                   id="company_tagline"
                                   name="company_tagline"
                                   value="{{ old('company_tagline') }}"
                                   placeholder="Your Trusted Insurance Partner">
                            @error('company_tagline')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Appears in emails and documents</small>
                        </div>

                        <div class="col-md-6">
                            <label for="theme_primary_color" class="form-label">
                                Primary Brand Color
                            </label>
                            <div class="input-group">
                                <input type="color"
                                       class="form-control form-control-color @error('theme_primary_color') is-invalid @enderror"
                                       id="theme_primary_color"
                                       name="theme_primary_color"
                                       value="{{ old('theme_primary_color', '#17a2b8') }}"
                                       style="width: 60px;">
                                <input type="text"
                                       class="form-control"
                                       id="theme_primary_color_hex"
                                       value="{{ old('theme_primary_color', '#17a2b8') }}"
                                       placeholder="#17a2b8"
                                       readonly>
                            </div>
                            @error('theme_primary_color')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Used for buttons, links, and branding</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label for="company_logo" class="form-label">
                                Company Logo
                            </label>
                            <input type="file"
                                   class="form-control @error('company_logo') is-invalid @enderror"
                                   id="company_logo"
                                   name="company_logo"
                                   accept="image/png,image/jpeg,image/jpg,image/svg+xml">
                            @error('company_logo')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">PNG, JPG, JPEG, or SVG (max 2MB). Leave empty to use central default logo.</small>
                        </div>
                    </div>

                    <!-- Communication Settings (Optional) -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">
                        Communication Settings
                        <small class="text-muted fw-normal">(Optional - uses central defaults if not provided)</small>
                    </h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="whatsapp_sender_id" class="form-label">
                                WhatsApp Sender ID
                            </label>
                            <input type="text"
                                   class="form-control @error('whatsapp_sender_id') is-invalid @enderror"
                                   id="whatsapp_sender_id"
                                   name="whatsapp_sender_id"
                                   value="{{ old('whatsapp_sender_id') }}"
                                   placeholder="919800071314">
                            @error('whatsapp_sender_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Phone number without + or spaces</small>
                        </div>

                        <div class="col-md-6">
                            <label for="whatsapp_auth_token" class="form-label">
                                WhatsApp Auth Token
                            </label>
                            <input type="text"
                                   class="form-control @error('whatsapp_auth_token') is-invalid @enderror"
                                   id="whatsapp_auth_token"
                                   name="whatsapp_auth_token"
                                   value="{{ old('whatsapp_auth_token') }}"
                                   placeholder="Enter API authentication token">
                            @error('whatsapp_auth_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Leave empty to use central WhatsApp config</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="email_from_address" class="form-label">
                                Email From Address
                            </label>
                            <input type="email"
                                   class="form-control @error('email_from_address') is-invalid @enderror"
                                   id="email_from_address"
                                   name="email_from_address"
                                   value="{{ old('email_from_address') }}"
                                   placeholder="noreply@company.com">
                            @error('email_from_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Email address for outgoing notifications</small>
                        </div>

                        <div class="col-md-6">
                            <label for="email_from_name" class="form-label">
                                Email From Name
                            </label>
                            <input type="text"
                                   class="form-control @error('email_from_name') is-invalid @enderror"
                                   id="email_from_name"
                                   name="email_from_name"
                                   value="{{ old('email_from_name') }}"
                                   placeholder="Company Name">
                            @error('email_from_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Display name for outgoing emails</small>
                        </div>
                    </div>

                    <!-- Advanced Settings (Collapsible) -->
                    <div class="accordion mt-4 mb-3" id="advancedSettings">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAdvanced">
                                    <i class="fas fa-cog me-2"></i>Advanced Settings (Optional)
                                </button>
                            </h2>
                            <div id="collapseAdvanced" class="accordion-collapse collapse" data-bs-parent="#advancedSettings">
                                <div class="accordion-body">
                                    <div class="row mb-3">
                                        <div class="col-md-4">
                                            <label for="timezone" class="form-label">
                                                Timezone
                                            </label>
                                            <select class="form-select @error('timezone') is-invalid @enderror"
                                                    id="timezone"
                                                    name="timezone">
                                                <option value="">Use Default (Asia/Kolkata)</option>
                                                <option value="Asia/Kolkata" {{ old('timezone') == 'Asia/Kolkata' ? 'selected' : '' }}>Asia/Kolkata (IST)</option>
                                                <option value="America/New_York" {{ old('timezone') == 'America/New_York' ? 'selected' : '' }}>America/New_York (EST)</option>
                                                <option value="Europe/London" {{ old('timezone') == 'Europe/London' ? 'selected' : '' }}>Europe/London (GMT)</option>
                                                <option value="Asia/Dubai" {{ old('timezone') == 'Asia/Dubai' ? 'selected' : '' }}>Asia/Dubai (GST)</option>
                                            </select>
                                            @error('timezone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="currency" class="form-label">
                                                Currency Code
                                            </label>
                                            <select class="form-select @error('currency') is-invalid @enderror"
                                                    id="currency"
                                                    name="currency">
                                                <option value="">Use Default (INR)</option>
                                                <option value="INR" {{ old('currency') == 'INR' ? 'selected' : '' }}>INR - Indian Rupee</option>
                                                <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD - US Dollar</option>
                                                <option value="EUR" {{ old('currency') == 'EUR' ? 'selected' : '' }}>EUR - Euro</option>
                                                <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP - British Pound</option>
                                                <option value="AED" {{ old('currency') == 'AED' ? 'selected' : '' }}>AED - UAE Dirham</option>
                                            </select>
                                            @error('currency')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>

                                        <div class="col-md-4">
                                            <label for="currency_symbol" class="form-label">
                                                Currency Symbol
                                            </label>
                                            <input type="text"
                                                   class="form-control @error('currency_symbol') is-invalid @enderror"
                                                   id="currency_symbol"
                                                   name="currency_symbol"
                                                   value="{{ old('currency_symbol') }}"
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
                            <label class="form-label">
                                Subscription Type <span class="text-danger">*</span>
                            </label>
                            <div class="mb-2">
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input @error('subscription_type') is-invalid @enderror"
                                           type="radio"
                                           name="subscription_type"
                                           id="subscription_type_trial"
                                           value="trial"
                                           {{ old('subscription_type', 'trial') == 'trial' ? 'checked' : '' }}
                                           required>
                                    <label class="form-check-label" for="subscription_type_trial">
                                        Trial Period
                                    </label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input @error('subscription_type') is-invalid @enderror"
                                           type="radio"
                                           name="subscription_type"
                                           id="subscription_type_paid"
                                           value="paid"
                                           {{ old('subscription_type') == 'paid' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="subscription_type_paid">
                                        Paid (Immediate)
                                    </label>
                                </div>
                            </div>
                            @error('subscription_type')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Choose trial or paid subscription</small>
                        </div>
                    </div>

                    <div class="row mb-3" id="trial_days_row">
                        <div class="col-md-12">
                            <label for="trial_days" class="form-label">
                                Trial Period (Days) <span class="text-danger">*</span>
                            </label>
                            <input type="number"
                                   class="form-control @error('trial_days') is-invalid @enderror"
                                   id="trial_days"
                                   name="trial_days"
                                   value="{{ old('trial_days', 14) }}"
                                   min="1"
                                   max="90">
                            @error('trial_days')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Number of days for the trial period (1-90 days)</small>
                        </div>
                    </div>

                    <!-- Database Configuration -->
                    <h6 class="text-muted mb-3 pb-2 border-bottom mt-4">Database Configuration</h6>

                    <div class="alert alert-info" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <small>Configure how the tenant database should be set up. Default values are prefilled.</small>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="db_name" class="form-label">
                                Database Name
                            </label>
                            <input type="text"
                                   class="form-control @error('db_name') is-invalid @enderror"
                                   id="db_name"
                                   name="db_name"
                                   value="{{ old('db_name') }}"
                                   placeholder="tenant_">
                            @error('db_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Auto-generated: tenant_{subdomain}. You can edit this.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="db_prefix" class="form-label">
                                Database Prefix
                            </label>
                            <input type="text"
                                   class="form-control @error('db_prefix') is-invalid @enderror"
                                   id="db_prefix"
                                   name="db_prefix"
                                   value="{{ old('db_prefix', 'tenant_') }}"
                                   placeholder="tenant_">
                            @error('db_prefix')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Prefix for database name (stored for future use)</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="db_username" class="form-label">
                                Database Username
                            </label>
                            <input type="text"
                                   class="form-control @error('db_username') is-invalid @enderror"
                                   id="db_username"
                                   name="db_username"
                                   value="{{ old('db_username', env('DB_USERNAME', 'root')) }}"
                                   placeholder="root">
                            @error('db_username')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">MySQL username for this tenant database</small>
                        </div>

                        <div class="col-md-6">
                            <label for="db_password" class="form-label">
                                Database Password
                            </label>
                            <input type="password"
                                   class="form-control @error('db_password') is-invalid @enderror"
                                   id="db_password"
                                   name="db_password"
                                   value="{{ old('db_password') }}"
                                   placeholder="••••••••">
                            @error('db_password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">MySQL password (leave blank to use default)</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="db_host" class="form-label">
                                Database Host
                            </label>
                            <input type="text"
                                   class="form-control @error('db_host') is-invalid @enderror"
                                   id="db_host"
                                   name="db_host"
                                   value="{{ old('db_host', env('DB_HOST', 'localhost')) }}"
                                   placeholder="localhost">
                            @error('db_host')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Database server hostname or IP</small>
                        </div>

                        <div class="col-md-6">
                            <label for="db_port" class="form-label">
                                Database Port
                            </label>
                            <input type="number"
                                   class="form-control @error('db_port') is-invalid @enderror"
                                   id="db_port"
                                   name="db_port"
                                   value="{{ old('db_port', env('DB_PORT', '3306')) }}"
                                   placeholder="3306"
                                   min="1"
                                   max="65535">
                            @error('db_port')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Database server port (default: 3306)</small>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-12">
                            <label class="form-label">Database Setup Options</label>

                            <div class="form-check mb-2">
                                <!-- Hidden field ensures false value is sent when checkbox is unchecked -->
                                <input type="hidden" name="db_create_database" value="0">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="db_create_database"
                                       name="db_create_database"
                                       value="1"
                                       {{ old('db_create_database', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="db_create_database">
                                    <strong>Create Database</strong>
                                    <br>
                                    <small class="text-muted">Create a new database for this tenant (recommended). Uncheck if using an existing database.</small>
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <!-- Hidden field ensures false value is sent when checkbox is unchecked -->
                                <input type="hidden" name="db_run_migrations" value="0">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="db_run_migrations"
                                       name="db_run_migrations"
                                       value="1"
                                       {{ old('db_run_migrations', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="db_run_migrations">
                                    <strong>Run Migrations</strong>
                                    <br>
                                    <small class="text-muted">Run database migrations to create all tables</small>
                                </label>
                            </div>

                            <div class="form-check mb-2">
                                <!-- Hidden field ensures false value is sent when checkbox is unchecked -->
                                <input type="hidden" name="db_run_seeders" value="0">
                                <input class="form-check-input"
                                       type="checkbox"
                                       id="db_run_seeders"
                                       name="db_run_seeders"
                                       value="1"
                                       {{ old('db_run_seeders', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="db_run_seeders">
                                    <strong>Run Seeders</strong>
                                    <br>
                                    <small class="text-muted">Seed database with master data and default records</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-warning" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <small>
                            <strong>Note:</strong> If you uncheck "Create Database", make sure the database already exists.
                            If you uncheck "Run Migrations" or "Run Seeders", you'll need to set up the database manually.
                        </small>
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
                        <button type="submit" class="btn btn-primary" id="createTenantBtn">
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

<!-- Progress Modal -->
<div class="modal fade" id="progressModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-cog fa-spin me-2"></i>Creating Tenant...
                </h5>
            </div>
            <div class="modal-body">
                <!-- Progress Bar -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="fw-bold">Overall Progress</span>
                        <span class="text-muted" id="progressPercentage">0%</span>
                    </div>
                    <div class="progress" style="height: 25px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar"
                             id="progressBar"
                             style="width: 0%">
                             <span id="progressText">Starting...</span>
                        </div>
                    </div>
                </div>

                <!-- Progress Steps -->
                <div id="progressSteps" class="border rounded p-3" style="max-height: 400px; overflow-y: auto; background-color: #f8f9fa;">
                    <p class="text-muted mb-2"><small><i class="fas fa-info-circle"></i> Detailed logs will appear here...</small></p>
                </div>

                <!-- Error Display -->
                <div id="errorDisplay" class="alert alert-danger mt-3 d-none">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> <span id="errorMessage"></span>
                </div>

                <!-- Success Display -->
                <div id="successDisplay" class="alert alert-success mt-3 d-none">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong>Success!</strong> Tenant created successfully.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger d-none" id="closeErrorBtn" onclick="closeModal()">
                    <i class="fas fa-times me-2"></i>Close
                </button>
                <button type="button" class="btn btn-success d-none" id="viewTenantBtn" onclick="redirectToTenant()">
                    <i class="fas fa-external-link-alt me-2"></i>View Tenant
                </button>
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
        const dbNameField = document.getElementById('db_name');

        // Only auto-fill if subdomain is empty
        if (!subdomainField.value) {
            const subdomain = e.target.value
                .toLowerCase()
                .replace(/[^a-z0-9]/g, '-')
                .replace(/-+/g, '-')
                .replace(/^-|-$/g, '');
            subdomainField.value = subdomain;
        }

        // Only auto-fill database name if it's empty
        if (!dbNameField.value || dbNameField.value === 'tenant_') {
            updateDatabaseName();
        }
    });

    // Auto-generate database name from subdomain and prefix
    function updateDatabaseName() {
        const subdomainField = document.getElementById('subdomain');
        const dbPrefixField = document.getElementById('db_prefix');
        const dbNameField = document.getElementById('db_name');

        if (subdomainField && dbPrefixField && dbNameField) {
            const prefix = dbPrefixField.value || 'tenant_';
            const subdomain = subdomainField.value || '';
            dbNameField.value = prefix + subdomain;
        }
    }

    // Update database name when subdomain changes
    document.getElementById('subdomain').addEventListener('input', updateDatabaseName);

    // Update database name when prefix changes
    document.getElementById('db_prefix').addEventListener('input', updateDatabaseName);

    // Initialize database name on page load
    updateDatabaseName();

    // Sync color picker with hex input
    const colorPicker = document.getElementById('theme_primary_color');
    const colorHex = document.getElementById('theme_primary_color_hex');

    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function(e) {
            colorHex.value = e.target.value.toUpperCase();
        });
    }

    // Toggle trial days field based on subscription type
    const trialRadio = document.getElementById('subscription_type_trial');
    const paidRadio = document.getElementById('subscription_type_paid');
    const trialDaysRow = document.getElementById('trial_days_row');
    const trialDaysInput = document.getElementById('trial_days');

    function toggleTrialDays() {
        if (paidRadio.checked) {
            trialDaysRow.style.display = 'none';
            trialDaysInput.removeAttribute('required');
            trialDaysInput.value = ''; // Clear value for paid subscriptions
        } else {
            trialDaysRow.style.display = '';
            trialDaysInput.setAttribute('required', 'required');
            if (!trialDaysInput.value) {
                trialDaysInput.value = '14'; // Default to 14 days
            }
        }
    }

    trialRadio.addEventListener('change', toggleTrialDays);
    paidRadio.addEventListener('change', toggleTrialDays);

    // Initialize on page load
    toggleTrialDays();

    // Tenant creation with progress tracking
    const form = document.querySelector('form');
    const progressModal = new bootstrap.Modal(document.getElementById('progressModal'));
    const progressBar = document.getElementById('progressBar');
    const progressText = document.getElementById('progressText');
    const progressPercentage = document.getElementById('progressPercentage');
    const progressSteps = document.getElementById('progressSteps');
    const errorDisplay = document.getElementById('errorDisplay');
    const errorMessage = document.getElementById('errorMessage');
    const successDisplay = document.getElementById('successDisplay');
    const closeModalBtn = document.getElementById('closeModalBtn');
    const createBtn = document.getElementById('createTenantBtn');

    let progressInterval = null;
    let progressKey = null;
    let redirectUrl = null;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        // Disable submit button
        createBtn.disabled = true;
        createBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Creating...';

        // Show progress modal
        progressModal.show();

        // Show initial loading state
        progressBar.style.width = '5%';
        progressPercentage.textContent = '5%';
        progressText.textContent = 'Initializing...';
        progressSteps.innerHTML = `
            <div class="mb-2 text-primary">
                <small>
                    <i class="fas fa-spinner fa-spin"></i> Preparing to create tenant...
                    <span class="text-muted" style="font-size: 0.8em;">(${new Date().toLocaleTimeString()})</span>
                </small>
            </div>
        `;

        // Prepare form data
        const formData = new FormData(form);
        const sessionId = Math.random().toString(36).substring(7);
        formData.append('session_id', sessionId);

        try {
            // Start tenant creation
            const response = await fetch('{{ route("central.tenants.store-with-progress") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                },
                body: formData
            });

            const result = await response.json();

            if (result.success) {
                progressKey = result.progress_key;
                redirectUrl = result.redirect_url;

                // Start polling for progress
                progressInterval = setInterval(updateProgress, 500);
            } else {
                showError(result.error || 'Failed to create tenant');
            }

        } catch (error) {
            showError('Network error: ' + error.message);
        }
    });

    async function updateProgress() {
        if (!progressKey) return;

        try {
            const response = await fetch('{{ route("central.tenants.progress") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ progress_key: progressKey })
            });

            const progress = await response.json();

            // Update progress bar
            progressBar.style.width = progress.percentage + '%';
            progressPercentage.textContent = progress.percentage + '%';
            progressText.textContent = progress.current_step + ' / ' + progress.total_steps;

            // Update steps display
            if (progress.steps && Object.keys(progress.steps).length > 0) {
                let stepsHTML = '';
                Object.values(progress.steps).forEach(step => {
                    const icon = step.status === 'completed' ? '✓' :
                                step.status === 'failed' ? '✗' :
                                step.status === 'running' ? '<i class="fas fa-spinner fa-spin"></i>' : '⋯';

                    const colorClass = step.status === 'completed' ? 'text-success' :
                                      step.status === 'failed' ? 'text-danger' :
                                      step.status === 'running' ? 'text-primary' : 'text-muted';

                    stepsHTML += `
                        <div class="mb-2 ${colorClass}">
                            <small>
                                <strong>[${step.number}/${progress.total_steps}]</strong>
                                ${icon} ${step.message}
                                <span class="text-muted" style="font-size: 0.8em;">(${new Date(step.timestamp).toLocaleTimeString()})</span>
                            </small>
                        </div>
                    `;
                });
                progressSteps.innerHTML = stepsHTML;

                // Auto-scroll to bottom
                progressSteps.scrollTop = progressSteps.scrollHeight;
            }

            // Check if completed
            if (progress.status === 'completed') {
                clearInterval(progressInterval);
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-success');
                progressText.textContent = 'Complete!';

                successDisplay.classList.remove('d-none');
                viewTenantBtn.classList.remove('d-none');

                // Store redirect URL if provided
                if (progress.redirect_url) {
                    redirectUrl = progress.redirect_url;
                }
            }

            // Check if failed
            if (progress.status === 'failed') {
                clearInterval(progressInterval);
                progressBar.classList.remove('progress-bar-animated');
                progressBar.classList.add('bg-danger');
                showError(progress.error || 'Tenant creation failed');
            }

        } catch (error) {
            clearInterval(progressInterval);
            showError('Failed to fetch progress: ' + error.message);
        }
    }

    function showError(message) {
        errorMessage.textContent = message;
        errorDisplay.classList.remove('d-none');
        closeErrorBtn.classList.remove('d-none');

        // Re-enable submit button so user can try again with same data
        createBtn.disabled = false;
        createBtn.innerHTML = '<i class="fas fa-save me-2"></i>Create Tenant';

        progressBar.classList.remove('progress-bar-animated');
        progressBar.classList.add('bg-danger');
    }

    function closeModal() {
        // Close modal WITHOUT reloading page - preserves form data
        progressModal.hide();

        // Reset modal state for next attempt
        errorDisplay.classList.add('d-none');
        successDisplay.classList.add('d-none');
        closeErrorBtn.classList.add('d-none');
        viewTenantBtn.classList.add('d-none');
        progressBar.classList.remove('bg-danger', 'bg-success');
        progressBar.classList.add('progress-bar-animated');
        progressBar.style.width = '0%';
        progressPercentage.textContent = '0%';
        progressText.textContent = 'Starting...';
        progressSteps.innerHTML = '<p class="text-muted mb-2"><small><i class="fas fa-info-circle"></i> Detailed logs will appear here...</small></p>';

        // Clear progress interval if still running
        if (progressInterval) {
            clearInterval(progressInterval);
            progressInterval = null;
        }

        // Re-enable modal closing
        const modalElement = document.getElementById('progressModal');
        modalElement.setAttribute('data-bs-backdrop', 'static');
        modalElement.setAttribute('data-bs-keyboard', 'false');
    }

    function redirectToTenant() {
        // Only redirect on success - clear form is not needed as user is leaving page
        if (redirectUrl) {
            window.location.href = redirectUrl;
        } else {
            window.location.href = '{{ route("central.tenants.index") }}';
        }
    }
</script>
@endpush
