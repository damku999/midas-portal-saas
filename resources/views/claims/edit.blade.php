@extends('layouts.app')

@section('title', 'Edit Claim - ' . $claim->claim_number)

@section('content')

    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <div class="row">
            <!-- Left Column - Edit Form -->
            <div class="col-lg-8">
                <!-- Claim Form -->
                <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-primary">Edit Claim</h6>
                    <small class="text-muted">{{ $claim->claim_number }} | {{ $claim->customer->name ?? 'N/A' }} | {{ $claim->insurance_type }}</small>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('claims.show', $claim) }}"
                       class="btn btn-info btn-sm d-flex align-items-center">
                        <i class="fas fa-eye me-2"></i>
                        <span>View Details</span>
                    </a>
                    <a href="{{ route('claims.index') }}"
                        class="btn btn-outline-secondary btn-sm d-flex align-items-center">
                        <i class="fas fa-list me-2"></i>
                        <span>All Claims</span>
                    </a>
                </div>
            </div>
            <form method="POST" action="{{ route('claims.update', $claim) }}" id="claimForm">
                @csrf
                @method('PUT')
                <div class="card-body py-3">
                    <!-- Section 1: Policy Selection -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-file-contract me-2"></i>Policy Information</h6>
                        <x-claims.policy-selector mode="edit" :claim="$claim" />
                    </div>

                    <!-- Section 2: Claim Information -->
                    <div class="mb-4">
                        <h6 class="text-muted fw-bold mb-3"><i class="fas fa-clipboard-list me-2"></i>Claim Information</h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Incident Date</label>
                                <input type="text" class="form-control date-picker @error('incident_date') is-invalid @enderror"
                                       name="incident_date" id="incident_date" placeholder="DD/MM/YYYY"
                                       value="{{ old('incident_date', $claim->incident_date ? $claim->incident_date->format('d/m/Y') : '') }}" required>
                                @error('incident_date')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">WhatsApp Number</label>
                                <input type="text" class="form-control @error('whatsapp_number') is-invalid @enderror"
                                       name="whatsapp_number" id="whatsapp_number" placeholder="WhatsApp number for updates"
                                       value="{{ old('whatsapp_number', $claim->whatsapp_number) }}">
                                @error('whatsapp_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Will be auto-filled from customer mobile if not provided</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold"><span class="text-danger">*</span> Status</label>
                                <select class="form-control @error('status') is-invalid @enderror" name="status" required>
                                    <option value="">Select Status</option>
                                    <option value="1" {{ old('status', $claim->status ? '1' : '0') == '1' ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ old('status', $claim->status ? '1' : '0') == '0' ? 'selected' : '' }}>Inactive</option>
                                </select>
                                @error('status')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Description</label>
                                <textarea class="form-control @error('description') is-invalid @enderror"
                                          name="description" rows="3" placeholder="Describe the incident details..."
                                          maxlength="1000">{{ old('description', $claim->description) }}</textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="text-muted">Maximum 1000 characters</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Email Notifications</label>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="send_email_notifications"
                                           id="send_email_notifications" value="1"
                                           {{ old('send_email_notifications', $claim->send_email_notifications ? '1' : '0') == '1' ? 'checked' : '' }}>
                                    <label class="form-check-label" for="send_email_notifications">
                                        Send email notifications to customer
                                    </label>
                                </div>
                                <small class="text-muted">Customer will receive claim updates via email</small>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                        <a href="{{ route('claims.show', $claim) }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-2"></i>Update Claim
                        </button>
                    </div>
                </div>
            </form>
        </div>
            </div>

            <!-- Right Column - Claim Details -->
            <div class="col-lg-4">
                <!-- Current Claim Status -->
                <div class="card shadow mb-4 mt-2">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Current Status
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Status:</strong>
                            <span class="badge badge-{{ $claim->status ? 'success' : 'danger' }} ms-2">
                                {{ $claim->status ? 'Active' : 'Inactive' }}
                            </span>
                        </div>
                        <div class="mb-2">
                            <strong>Current Stage:</strong>
                            <span class="text-muted">{{ $claim->currentStage->stage_name ?? 'No Stage' }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Created:</strong>
                            <span class="text-muted">{{ $claim->created_at }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Last Updated:</strong>
                            <span class="text-muted">{{ $claim->updated_at }}</span>
                        </div>
                    </div>
                </div>

                <!-- Policy Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-contract me-2"></i>Policy Info
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-2">
                            <strong>Policy No:</strong>
                            <span class="badge badge-info">{{ $claim->customerInsurance->policy_no ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Registration:</strong>
                            <span class="text-muted">{{ $claim->customerInsurance->registration_no ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Insurance Company:</strong>
                            <span class="text-muted">{{ $claim->customerInsurance->insuranceCompany->name ?? 'N/A' }}</span>
                        </div>
                        <div class="mb-2">
                            <strong>Policy Type:</strong>
                            <span class="text-muted">{{ $claim->customerInsurance->policyType->name ?? 'N/A' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-bolt me-2"></i>Quick Actions
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <a href="{{ route('claims.show', $claim) }}" class="btn btn-info btn-sm">
                                <i class="fas fa-eye me-2"></i>View Full Details
                            </a>
                            @can('claim-create')
                                <div class="dropdown">
                                    <button class="btn btn-success btn-sm dropdown-toggle w-100" type="button" data-bs-toggle="dropdown">
                                        <i class="fab fa-whatsapp me-1"></i>WhatsApp
                                    </button>
                                    <ul class="dropdown-menu w-100">
                                        <li><a class="dropdown-item" href="#" onclick="sendWhatsAppMessage('document_list')">
                                            <i class="fas fa-list me-2"></i>Send Document List
                                        </a></li>
                                        <li><a class="dropdown-item" href="#" onclick="sendWhatsAppMessage('pending_documents')">
                                            <i class="fas fa-exclamation-triangle me-2"></i>Send Pending Documents
                                        </a></li>
                                        @if(!empty($claim->claim_number))
                                        <li><a class="dropdown-item" href="#" onclick="sendWhatsAppMessage('claim_number')">
                                            <i class="fas fa-hashtag me-2"></i>Send Claim Number
                                        </a></li>
                                        @endif
                                    </ul>
                                </div>
                            @endcan
                            <a href="{{ route('claims.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-list me-2"></i>Back to Claims
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Document Progress -->
                @if($claim->documents->count() > 0)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-upload me-2"></i>Document Progress
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-sm">Progress</span>
                                <span class="text-sm">{{ $claim->getDocumentCompletionPercentage() }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $claim->getDocumentCompletionPercentage() }}%">
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">
                            {{ $claim->documents->where('is_submitted', true)->count() }} of {{ $claim->documents->count() }} documents submitted
                        </small>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

@endsection

@push('styles')
    <link href="{{ cdn_url('cdn_bootstrap_datepicker_css') }}" rel="stylesheet" />
@endpush

@section('scripts')
    {{-- Include common claims functions --}}
    @include('claims.partials.common-functions')

    <script src="{{ cdn_url('cdn_bootstrap_datepicker_js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize date picker
            $('.date-picker').datepicker({
                format: 'dd/mm/yyyy',
                autoclose: true,
                todayHighlight: true,
                endDate: new Date(), // Cannot select future dates
                orientation: 'bottom'
            });

            // Form validation
            $('#claimForm').on('submit', function(e) {
                // Use policy selector validation
                if (typeof window.validatePolicySelection === 'function' && !window.validatePolicySelection()) {
                    e.preventDefault();
                    return false;
                }

                if (!$('#insurance_type').val()) {
                    e.preventDefault();
                    window.showClaimAlert('error', 'Please select insurance type.');
                    $('#insurance_type').focus();
                    return false;
                }

                if (!$('#incident_date').val()) {
                    e.preventDefault();
                    window.showClaimAlert('error', 'Please select incident date.');
                    $('#incident_date').focus();
                    return false;
                }
            });

            // Character counter for description
            $('textarea[name="description"]').on('input', function() {
                var maxLength = 1000;
                var currentLength = $(this).val().length;
                var remaining = maxLength - currentLength;

                var counterText = remaining + ' characters remaining';
                if (remaining < 0) {
                    counterText = Math.abs(remaining) + ' characters over limit';
                }

                $(this).siblings('.text-muted').text(counterText);
            });

            // WhatsApp functionality
            window.sendWhatsAppMessage = function(type) {
                const hideLoading = window.showClaimAlert('loading', 'Sending WhatsApp message...');

                const url = getWhatsAppUrl(type);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        window.showClaimAlert('success', data.message);
                    } else {
                        window.showClaimAlert('error', data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    window.showClaimAlert('error', 'Failed to send WhatsApp message');
                });
            }

            function getWhatsAppUrl(type) {
                const routes = {
                    'document_list': '{{ route('claims.whatsapp.documentList', $claim->id) }}',
                    'pending_documents': '{{ route('claims.whatsapp.pendingDocuments', $claim->id) }}',
                    'claim_number': '{{ route('claims.whatsapp.claimNumber', $claim->id) }}'
                };
                return routes[type];
            }
        });
    </script>
@endsection
