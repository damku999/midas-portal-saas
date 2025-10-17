@extends('layouts.app')

@section('title', 'View App Setting')

@section('content')

    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- App Setting Details -->
        <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">App Setting Details</h6>
                <div class="d-flex gap-2">
                    @if (auth()->user()->hasPermissionTo('app-setting-edit'))
                        <a href="{{ route('app-settings.edit', $setting->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Edit
                        </a>
                    @endif
                    <a href="{{ route('app-settings.index') }}" onclick="window.history.go(-1); return false;"
                        class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                        <i class="fas fa-chevron-left me-2"></i>
                        <span>Back</span>
                    </a>
                </div>
            </div>
            <div class="card-body py-3">
                <!-- Section: Basic Information -->
                <div class="mb-4">
                    <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">
                        <i class="fas fa-cog me-2"></i>Basic Information
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Key</label>
                                <div class="p-2 bg-light border rounded">
                                    <span class="fw-bold text-primary">{{ $setting->key }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Category</label>
                                <div class="p-2 bg-light border rounded">
                                    <span class="badge bg-info text-white">
                                        {{ ucfirst($setting->category) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Value Configuration -->
                <div class="mb-4">
                    <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">
                        <i class="fas fa-database me-2"></i>Value Configuration
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Type</label>
                                <div class="p-2 bg-light border rounded">
                                    <span class="badge bg-secondary">{{ ucfirst($setting->type) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">
                                    Value
                                    @if($setting->is_encrypted)
                                        <span class="badge bg-warning text-dark ms-1">
                                            <i class="fas fa-lock"></i> Encrypted
                                        </span>
                                    @endif
                                </label>
                                <div class="p-2 bg-light border rounded">
                                    @if($setting->is_encrypted)
                                        <span id="encrypted-value-{{ $setting->id }}" class="text-muted font-monospace">******</span>
                                        <button type="button" class="btn btn-sm btn-outline-warning ms-2" id="decrypt-btn-{{ $setting->id }}" onclick="viewDecryptedValue({{ $setting->id }})">
                                            <i class="fas fa-eye"></i> View Decrypted
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-secondary ms-2 d-none" id="hide-btn-{{ $setting->id }}" onclick="hideDecryptedValue({{ $setting->id }})">
                                            <i class="fas fa-eye-slash"></i> Hide
                                        </button>
                                        <small class="d-block text-muted mt-1">Value is encrypted for security</small>
                                    @else
                                        @if($setting->type == 'json')
                                            <pre class="mb-0 small"><code>{{ json_encode($setting->value, JSON_PRETTY_PRINT) }}</code></pre>
                                        @elseif($setting->type == 'boolean')
                                            <span class="badge {{ $setting->value ? 'bg-success' : 'bg-secondary' }} fs-6">
                                                {{ $setting->value ? 'True' : 'False' }}
                                            </span>
                                        @else
                                            <span class="font-monospace">{{ $setting->value }}</span>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Description -->
                @if($setting->description)
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">
                            <i class="fas fa-info-circle me-2"></i>Description
                        </h6>
                        <div class="p-2 bg-light border rounded">
                            {{ $setting->description }}
                        </div>
                    </div>
                @endif

                <!-- Section: Security and Status -->
                <div class="mb-4">
                    <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">
                        <i class="fas fa-shield-alt me-2"></i>Security and Status
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Encryption Status</label>
                                <div class="p-2 bg-light border rounded">
                                    @if($setting->is_encrypted)
                                        <span class="badge bg-warning text-dark">
                                            <i class="fas fa-lock me-1"></i>Encrypted
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-unlock me-1"></i>Not Encrypted
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Status</label>
                                <div class="p-2 bg-light border rounded">
                                    @if($setting->is_active)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check-circle me-1"></i>Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="fas fa-times-circle me-1"></i>Inactive
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section: Timestamps -->
                <div class="mb-3">
                    <h6 class="text-muted fw-bold mb-3 border-bottom pb-2">
                        <i class="fas fa-clock me-2"></i>Timestamps
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Created At</label>
                                <div class="p-2 bg-light border rounded">
                                    <i class="far fa-calendar-plus me-1"></i>
                                    {{ $setting->created_at->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-bold text-muted small">Last Updated</label>
                                <div class="p-2 bg-light border rounded">
                                    <i class="far fa-calendar-check me-1"></i>
                                    {{ $setting->updated_at->format('d M Y, h:i A') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card-footer py-2 bg-light">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        @if (auth()->user()->hasPermissionTo('app-setting-delete'))
                            @if ($setting->is_active)
                                <a href="#"
                                    class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmationModal"
                                    data-title="Confirm Deactivation"
                                    data-message="Are you sure you want to deactivate <strong>{{ $setting->key }}</strong>?"
                                    data-confirm-text="Yes, Deactivate"
                                    data-confirm-class="btn-warning"
                                    data-action-url="{{ route('app-settings.status', ['id' => $setting->id, 'status' => 0]) }}"
                                    data-method="GET">
                                    <i class="fa fa-ban me-1"></i>Deactivate
                                </a>
                            @else
                                <a href="#"
                                    class="btn btn-success btn-sm"
                                    data-bs-toggle="modal"
                                    data-bs-target="#confirmationModal"
                                    data-title="Confirm Activation"
                                    data-message="Are you sure you want to activate <strong>{{ $setting->key }}</strong>?"
                                    data-confirm-text="Yes, Activate"
                                    data-confirm-class="btn-success"
                                    data-action-url="{{ route('app-settings.status', ['id' => $setting->id, 'status' => 1]) }}"
                                    data-method="GET">
                                    <i class="fa fa-check me-1"></i>Activate
                                </a>
                            @endif
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        <a class="btn btn-secondary btn-sm px-4" href="{{ route('app-settings.index') }}">
                            <i class="fas fa-list me-1"></i>Back to List
                        </a>
                        @if (auth()->user()->hasPermissionTo('app-setting-edit'))
                            <a class="btn btn-primary btn-sm px-4" href="{{ route('app-settings.edit', $setting->id) }}">
                                <i class="fas fa-edit me-1"></i>Edit Setting
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
<script>
    function viewDecryptedValue(settingId) {
        // Show loading state
        const valueEl = $('#encrypted-value-' + settingId);
        const decryptBtn = $('#decrypt-btn-' + settingId);
        const hideBtn = $('#hide-btn-' + settingId);

        decryptBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Loading...');

        // Make AJAX request to decrypt
        $.ajax({
            url: '{{ url("app-settings") }}/' + settingId + '/decrypt',
            method: 'GET',
            success: function(response) {
                if (response.success) {
                    // Show decrypted value
                    valueEl.text(response.value).removeClass('text-muted').addClass('text-success fw-bold');
                    decryptBtn.addClass('d-none');
                    hideBtn.removeClass('d-none');
                } else {
                    toastr.error(response.message || 'Failed to decrypt value');
                    decryptBtn.prop('disabled', false).html('<i class="fas fa-eye"></i> View Decrypted');
                }
            },
            error: function(xhr) {
                const message = xhr.responseJSON?.message || 'Error decrypting value';
                toastr.error(message);
                decryptBtn.prop('disabled', false).html('<i class="fas fa-eye"></i> View Decrypted');
            }
        });
    }

    function hideDecryptedValue(settingId) {
        const valueEl = $('#encrypted-value-' + settingId);
        const decryptBtn = $('#decrypt-btn-' + settingId);
        const hideBtn = $('#hide-btn-' + settingId);

        // Hide decrypted value
        valueEl.text('******').addClass('text-muted').removeClass('text-success fw-bold');
        hideBtn.addClass('d-none');
        decryptBtn.removeClass('d-none').prop('disabled', false);
    }
</script>
@endsection
