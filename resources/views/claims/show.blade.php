@extends('layouts.app')

@section('title', 'Claim Details - ' . $claim->claim_number)

@section('content')
    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- Claim Header -->
        <div class="card shadow mb-3 mt-2">
            <div class="card-header py-2 d-flex justify-content-between align-items-center">
                <div>
                    <h6 class="mb-0 fw-bold text-primary">Claim Details</h6>
                    <small class="text-muted">{{ $claim->claim_number }}</small>
                </div>
                <div class="d-flex gap-2">
                    <!-- WhatsApp Actions Dropdown -->
                    @can('claim-create')
                        <div class="dropdown">
                            <button class="btn btn-success btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fab fa-whatsapp me-1"></i>WhatsApp
                            </button>
                            <ul class="dropdown-menu">
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
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="#" onclick="previewWhatsAppMessage('document_list')">
                                    <i class="fas fa-eye me-2"></i>Preview Document List
                                </a></li>
                                <li><a class="dropdown-item" href="#" onclick="previewWhatsAppMessage('pending_documents')">
                                    <i class="fas fa-eye me-2"></i>Preview Pending Documents
                                </a></li>
                            </ul>
                        </div>
                    @endcan

                    @can('claim-edit')
                        <a href="{{ route('claims.edit', $claim) }}"
                           class="btn btn-warning btn-sm">
                            <i class="fas fa-edit me-1"></i>Edit
                        </a>
                    @endcan
                    <a href="{{ route('claims.index') }}"
                       class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-chevron-left me-1"></i>Back
                    </a>
                </div>
            </div>
        </div>

        <!-- Overview Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Claim Number
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $claim->claim_number }}
                                    @can('claim-edit')
                                        <button class="btn btn-link btn-sm p-0 ms-2" onclick="editClaimNumber()" title="Edit Claim Number">
                                            <i class="fas fa-edit text-primary"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-file-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-{{ $claim->insurance_type == 'Health' ? 'success' : 'info' }} shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-{{ $claim->insurance_type == 'Health' ? 'success' : 'info' }} text-uppercase mb-1">
                                    Insurance Type
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $claim->insurance_type }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-{{ $claim->insurance_type == 'Health' ? 'heartbeat' : 'car' }} fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    Current Stage
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $claim->currentStage->stage_name ?? 'No Stage' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-tasks fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-{{ $claim->status ? 'success' : 'danger' }} shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-{{ $claim->status ? 'success' : 'danger' }} text-uppercase mb-1">
                                    Status
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $claim->status ? 'Active' : 'Inactive' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-{{ $claim->status ? 'check-circle' : 'times-circle' }} fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- Left Column -->
            <div class="col-lg-8">
                <!-- Edit Claim Form (Initially Hidden) -->
                <div class="card shadow mb-4" id="editClaimCard" style="display: none;">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-warning">
                            <i class="fas fa-edit me-2"></i>Edit Claim Details
                        </h6>
                        <button class="btn btn-secondary btn-sm" onclick="cancelEdit()">
                            <i class="fas fa-times me-1"></i>Cancel
                        </button>
                    </div>
                    <form method="POST" action="{{ route('claims.update', $claim) }}" id="claimEditForm">
                        @csrf
                        @method('PUT')
                        <div class="card-body py-3">
                            <!-- Policy Selection -->
                            <div class="mb-4">
                                <h6 class="text-muted fw-bold mb-3"><i class="fas fa-file-contract me-2"></i>Policy Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Select Policy/Insurance</label>
                                        <select class="form-control select2-policy-edit @error('customer_insurance_id') is-invalid @enderror"
                                                name="customer_insurance_id" id="customer_insurance_id_edit" required>
                                            @if($claim->customerInsurance)
                                                <option value="{{ $claim->customerInsurance->id }}" selected>
                                                    {{ $claim->customer->name }} - Policy: {{ $claim->customerInsurance->policy_no }}
                                                    @if($claim->customerInsurance->registration_no) - Reg: {{ $claim->customerInsurance->registration_no }} @endif
                                                    @if($claim->customerInsurance->insuranceCompany) - {{ $claim->customerInsurance->insuranceCompany->name }} @endif
                                                </option>
                                            @endif
                                        </select>
                                        <small class="text-muted">Start typing to search policies. You can search by customer name, policy number, registration number, email or mobile number.</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Insurance Type</label>
                                        <select class="form-control" name="insurance_type" id="insurance_type_edit" required>
                                            <option value="">Select Insurance Type</option>
                                            <option value="Health" {{ $claim->insurance_type == 'Health' ? 'selected' : '' }}>Health Insurance</option>
                                            <option value="Vehicle" {{ $claim->insurance_type == 'Vehicle' ? 'selected' : '' }}>Vehicle Insurance</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Claim Information -->
                            <div class="mb-4">
                                <h6 class="text-muted fw-bold mb-3"><i class="fas fa-clipboard-list me-2"></i>Claim Information</h6>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Incident Date</label>
                                        <input type="text" class="form-control date-picker-edit" name="incident_date" id="incident_date_edit"
                                               placeholder="DD/MM/YYYY" value="{{ $claim->incident_date ? $claim->incident_date->format('d/m/Y') : '' }}" required>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">WhatsApp Number</label>
                                        <input type="text" class="form-control" name="whatsapp_number" id="whatsapp_number_edit"
                                               placeholder="WhatsApp number for updates" value="{{ $claim->whatsapp_number }}">
                                        <small class="text-muted">Will be auto-filled from customer mobile if not provided</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold"><span class="text-danger">*</span> Status</label>
                                        <select class="form-control" name="status" required>
                                            <option value="">Select Status</option>
                                            <option value="1" {{ $claim->status ? 'selected' : '' }}>Active</option>
                                            <option value="0" {{ !$claim->status ? 'selected' : '' }}>Inactive</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="row g-3 mt-2">
                                    <div class="col-md-8">
                                        <label class="form-label fw-semibold">Description</label>
                                        <textarea class="form-control" name="description" rows="3"
                                                  placeholder="Describe the incident details..." maxlength="1000">{{ $claim->description }}</textarea>
                                        <small class="text-muted">Maximum 1000 characters</small>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold">Email Notifications</label>
                                        <div class="form-check mt-2">
                                            <input class="form-check-input" type="checkbox" name="send_email_notifications"
                                                   id="send_email_notifications_edit" value="1" {{ $claim->send_email_notifications ? 'checked' : '' }}>
                                            <label class="form-check-label" for="send_email_notifications_edit">
                                                Send email notifications to customer
                                            </label>
                                        </div>
                                        <small class="text-muted">Customer will receive claim updates via email</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Buttons -->
                            <div class="d-flex justify-content-end gap-2 pt-3 border-top">
                                <button type="button" class="btn btn-outline-secondary" onclick="cancelEdit()">
                                    Cancel
                                </button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>Update Claim
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Basic Information -->
                <div class="card shadow mb-4" id="basicInfoCard">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-info-circle me-2"></i>Basic Information
                        </h6>
                        @can('claim-edit')
                            <button class="btn btn-warning btn-sm" onclick="showEditForm()">
                                <i class="fas fa-edit me-1"></i>Edit Details
                            </button>
                        @endcan
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-semibold">Customer Name:</td>
                                        <td>{{ $claim->customer->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Email:</td>
                                        <td>{{ $claim->customer->email ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Mobile:</td>
                                        <td>{{ $claim->customer->mobile_number ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">WhatsApp:</td>
                                        <td>{{ $claim->whatsapp_number ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-semibold">Incident Date:</td>
                                        <td>{{ $claim->incident_date ? $claim->incident_date->format('d/m/Y') : 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Email Notifications:</td>
                                        <td>
                                            <span class="badge badge-{{ $claim->send_email_notifications ? 'success' : 'secondary' }}">
                                                {{ $claim->send_email_notifications ? 'Enabled' : 'Disabled' }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Created Date:</td>
                                        <td>{{ format_app_datetime($claim->created_at) }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Last Updated:</td>
                                        <td>{{ format_app_datetime($claim->updated_at) }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                        @if($claim->description)
                            <div class="mt-3">
                                <h6 class="fw-semibold">Description:</h6>
                                <div class="bg-light p-3 rounded">
                                    {{ $claim->description }}
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Policy Information -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-contract me-2"></i>Policy Information
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-semibold">Policy Number:</td>
                                        <td><span class="badge badge-info">{{ $claim->customerInsurance->policy_no ?? 'N/A' }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Registration:</td>
                                        <td>{{ $claim->customerInsurance->registration_no ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Insurance Company:</td>
                                        <td>{{ $claim->customerInsurance->insuranceCompany->name ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <table class="table table-borderless">
                                    <tr>
                                        <td class="fw-semibold">Policy Type:</td>
                                        <td>{{ $claim->customerInsurance->policyType->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Start Date:</td>
                                        <td>{{ $claim->customerInsurance->start_date ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="fw-semibold">Expiry Date:</td>
                                        <td>{{ $claim->customerInsurance->expired_date ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Claim Stages -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-list-ol me-2"></i>Claim Stages
                        </h6>
                        @can('claim-edit')
                            <button class="btn btn-primary btn-sm" onclick="addNewStage()">
                                <i class="fas fa-plus me-1"></i>Add Stage
                            </button>
                        @endcan
                    </div>
                    <div class="card-body">
                        @if($claim->stages->count() > 0)
                            <div class="timeline">
                                @foreach($claim->stages as $stage)
                                    <div class="timeline-item {{ $stage->is_current ? 'current' : '' }} {{ $stage->is_completed ? 'completed' : '' }}">
                                        <div class="timeline-marker">
                                            @if($stage->is_completed)
                                                <i class="fas fa-check text-success"></i>
                                            @elseif($stage->is_current)
                                                <i class="fas fa-clock text-warning"></i>
                                            @else
                                                <i class="fas fa-circle text-muted"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-content">
                                            <h6 class="mb-1">
                                                {{ $stage->stage_name }}
                                                @if($stage->is_current)
                                                    <span class="badge badge-warning badge-sm">Current</span>
                                                @endif
                                            </h6>
                                            @if($stage->description)
                                                <p class="text-muted mb-1">{{ $stage->description }}</p>
                                            @endif
                                            @if($stage->stage_date)
                                                <small class="text-muted">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    {{ format_app_datetime($stage->stage_date) }}
                                                </small>
                                            @endif
                                            @if($stage->notes)
                                                <div class="mt-2">
                                                    <small class="text-muted"><strong>Notes:</strong> {{ $stage->notes }}</small>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-list-ol fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">No Stages Found</h5>
                                <p class="text-muted">No stages have been created for this claim yet.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column -->
            <div class="col-lg-4">
                <!-- Document Checklist -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-file-upload me-2"></i>Document Checklist
                        </h6>
                        <div class="btn-group btn-group-sm">
                            @can('claim-edit')
                                <button class="btn btn-outline-primary btn-sm" onclick="selectAllDocuments()" title="Mark all documents as submitted">
                                    <i class="fas fa-check-double me-1"></i>Select All
                                </button>
                                <button class="btn btn-outline-secondary btn-sm" onclick="clearAllDocuments()" title="Clear all document selections">
                                    <i class="fas fa-times me-1"></i>Clear All
                                </button>
                            @endcan
                            @can('claim-create')
                                <button class="btn btn-success btn-sm" onclick="sendWhatsAppMessage('pending_documents')" title="Send Pending Documents">
                                    <i class="fab fa-whatsapp me-1"></i>Send Pending
                                </button>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        @if($claim->documents->count() > 0)
                            <!-- Progress Bar -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-sm">Document Progress</span>
                                    <span class="text-sm">{{ $claim->getDocumentCompletionPercentage() }}%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar"
                                         style="width: {{ $claim->getDocumentCompletionPercentage() }}%">
                                    </div>
                                </div>
                            </div>

                            <!-- Document List -->
                            <div class="document-checklist" id="documentChecklist">
                                @foreach($claim->documents as $document)
                                    <div class="form-check mb-2" data-document-id="{{ $document->id }}">
                                        @can('claim-edit')
                                            <input class="form-check-input document-checkbox" type="checkbox"
                                                   id="doc_{{ $document->id }}"
                                                   data-document-id="{{ $document->id }}"
                                                   {{ $document->is_submitted ? 'checked' : '' }}
                                                   onchange="updateDocumentStatus({{ $document->id }}, this.checked)">
                                        @else
                                            <input class="form-check-input" type="checkbox"
                                                   {{ $document->is_submitted ? 'checked' : '' }} disabled>
                                        @endcan
                                        <label class="form-check-label" for="doc_{{ $document->id }}">
                                            <strong>{{ $document->document_name }}</strong>
                                            @if($document->is_required)
                                                <span class="badge badge-danger badge-sm ms-2">Required</span>
                                            @else
                                                <span class="badge badge-warning badge-sm ms-2">Optional</span>
                                            @endif
                                            @if($document->is_submitted)
                                                <span class="badge badge-success badge-sm ms-1 submitted-badge">Submitted</span>
                                            @endif
                                        </label>
                                        @if($document->description)
                                            <small class="text-muted d-block">{{ $document->description }}</small>
                                        @endif
                                        @if($document->submitted_date)
                                            <small class="text-muted d-block submitted-date-info">
                                                <i class="fas fa-check me-1"></i>
                                                Submitted: {{ format_app_datetime($document->submitted_date) }}
                                            </small>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-file fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No Documents</h6>
                                <p class="text-muted">No documents have been created for this claim.</p>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- Liability Details -->
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-money-bill me-2"></i>Liability Details
                        </h6>
                        @can('claim-edit')
                            <button class="btn btn-primary btn-sm" onclick="editLiabilityDetails()">
                                <i class="fas fa-edit me-1"></i>Edit
                            </button>
                        @endcan
                    </div>
                    <div class="card-body" id="liabilityDetailsView">
                        @if($claim->liabilityDetail)
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold">Claim Type:</td>
                                    <td>
                                        <span class="badge badge-{{ $claim->liabilityDetail->claim_type == 'Cashless' ? 'success' : 'info' }}">
                                            {{ $claim->liabilityDetail->claim_type ?? 'Not Set' }}
                                        </span>
                                    </td>
                                </tr>
                                @if($claim->liabilityDetail->claim_type == 'Cashless')
                                    @if($claim->liabilityDetail->claim_amount)
                                        <tr>
                                            <td class="fw-semibold">Claim Amount:</td>
                                            <td>₹{{ number_format($claim->liabilityDetail->claim_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($claim->liabilityDetail->salvage_amount)
                                        <tr>
                                            <td class="fw-semibold">Salvage Amount:</td>
                                            <td>₹{{ number_format($claim->liabilityDetail->salvage_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($claim->liabilityDetail->less_claim_charge)
                                        <tr>
                                            <td class="fw-semibold">Less Claim Charge:</td>
                                            <td>₹{{ number_format($claim->liabilityDetail->less_claim_charge, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($claim->liabilityDetail->amount_to_be_paid)
                                        <tr>
                                            <td class="fw-semibold">Amount to be Paid by Customer:</td>
                                            <td>₹{{ number_format($claim->liabilityDetail->amount_to_be_paid, 2) }}</td>
                                        </tr>
                                    @endif
                                @elseif($claim->liabilityDetail->claim_type == 'Reimbursement')
                                    @if($claim->liabilityDetail->claim_amount)
                                        <tr>
                                            <td class="fw-semibold">Claim Amount:</td>
                                            <td>₹{{ number_format($claim->liabilityDetail->claim_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($claim->liabilityDetail->less_salvage_amount)
                                        <tr>
                                            <td class="fw-semibold">Less Salvage Amount:</td>
                                            <td>₹{{ number_format($claim->liabilityDetail->less_salvage_amount, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($claim->liabilityDetail->less_deductions)
                                        <tr>
                                            <td class="fw-semibold">Less Deductions:</td>
                                            <td>₹{{ number_format($claim->liabilityDetail->less_deductions, 2) }}</td>
                                        </tr>
                                    @endif
                                    @if($claim->liabilityDetail->claim_amount_received)
                                        <tr>
                                            <td class="fw-semibold">Claim Amount Received:</td>
                                            <td>₹{{ number_format($claim->liabilityDetail->claim_amount_received, 2) }}</td>
                                        </tr>
                                    @endif
                                @endif
                                @if($claim->liabilityDetail->notes)
                                    <tr>
                                        <td class="fw-semibold">Notes:</td>
                                        <td>{{ $claim->liabilityDetail->notes }}</td>
                                    </tr>
                                @endif
                            </table>
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-money-bill fa-3x text-muted mb-3"></i>
                                <h6 class="text-muted">No Liability Details</h6>
                                <p class="text-muted">Liability details have not been set for this claim.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Preview Modal -->
    <div class="modal fade" id="whatsappPreviewModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="fab fa-whatsapp me-2"></i>WhatsApp Message Preview
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Sending to:</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-phone"></i></span>
                            <input type="text" class="form-control" id="whatsappNumber" readonly>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Message:</label>
                        <div class="card">
                            <div class="card-body bg-light">
                                <pre id="messagePreview" class="mb-0" style="white-space: pre-wrap; font-family: inherit;"></pre>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="sendMessageBtn">
                        <i class="fab fa-whatsapp me-1"></i>Send Message
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Stage Modal -->
    <div class="modal fade" id="addStageModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="addStageForm">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-plus me-2"></i>Add New Stage
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="stageName" class="form-label">Stage Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="stageName" name="stage_name" required>
                        </div>
                        <div class="mb-3">
                            <label for="stageDescription" class="form-label">Description</label>
                            <textarea class="form-control" id="stageDescription" name="description" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="stageNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="stageNotes" name="notes" rows="3"></textarea>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendWhatsAppStage" name="send_whatsapp">
                            <label class="form-check-label" for="sendWhatsAppStage">
                                Send WhatsApp notification for this stage update
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Add Stage
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Claim Number Modal -->
    <div class="modal fade" id="editClaimNumberModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="editClaimNumberForm">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Claim Number
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="claimNumber" class="form-label">Claim Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="claimNumber" name="claim_number" value="{{ $claim->claim_number }}" required>
                            <small class="text-muted">Enter the claim number provided by the insurance company</small>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="sendWhatsAppClaimNumber" name="send_whatsapp">
                            <label class="form-check-label" for="sendWhatsAppClaimNumber">
                                Send WhatsApp notification with claim number
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Claim Number
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Liability Details Modal -->
    <div class="modal fade" id="editLiabilityModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="editLiabilityForm">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-edit me-2"></i>Edit Liability Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="claimType" class="form-label">Claim Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="claimType" name="claim_type" required onchange="toggleLiabilityFields()">
                                <option value="">Select Claim Type</option>
                                <option value="Cashless" {{ $claim->liabilityDetail && $claim->liabilityDetail->claim_type == 'Cashless' ? 'selected' : '' }}>Cashless</option>
                                <option value="Reimbursement" {{ $claim->liabilityDetail && $claim->liabilityDetail->claim_type == 'Reimbursement' ? 'selected' : '' }}>Reimbursement</option>
                            </select>
                        </div>

                        <!-- Cashless Fields -->
                        <div id="cashlessFields" style="display: none;">
                            <h6 class="text-primary mb-3">Cashless Type Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="claimAmount" class="form-label">Claim Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="claimAmount" name="claim_amount" step="0.01" min="0"
                                                   value="{{ $claim->liabilityDetail->claim_amount ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="salvageAmount" class="form-label">Salvage Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="salvageAmount" name="salvage_amount" step="0.01" min="0"
                                                   value="{{ $claim->liabilityDetail->salvage_amount ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lessClaimCharge" class="form-label">Less Claim Charge</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="lessClaimCharge" name="less_claim_charge" step="0.01" min="0"
                                                   value="{{ $claim->liabilityDetail->less_claim_charge ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="amountToBePaid" class="form-label">Amount to be Paid by Customer</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="amountToBePaid" name="amount_to_be_paid" step="0.01" min="0"
                                                   value="{{ $claim->liabilityDetail->amount_to_be_paid ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Reimbursement Fields -->
                        <div id="reimbursementFields" style="display: none;">
                            <h6 class="text-primary mb-3">Reimbursement Type Details</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="reimbClaimAmount" class="form-label">Claim Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="reimbClaimAmount" name="claim_amount" step="0.01" min="0"
                                                   value="{{ $claim->liabilityDetail->claim_amount ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lessSalvageAmount" class="form-label">Less Salvage Amount</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="lessSalvageAmount" name="less_salvage_amount" step="0.01" min="0"
                                                   value="{{ $claim->liabilityDetail->less_salvage_amount ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="lessDeductions" class="form-label">Less Deductions</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="lessDeductions" name="less_deductions" step="0.01" min="0"
                                                   value="{{ $claim->liabilityDetail->less_deductions ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="claimAmountReceived" class="form-label">Claim Amount Received</label>
                                        <div class="input-group">
                                            <span class="input-group-text">₹</span>
                                            <input type="number" class="form-control" id="claimAmountReceived" name="claim_amount_received" step="0.01" min="0"
                                                   value="{{ $claim->liabilityDetail->claim_amount_received ?? '' }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="liabilityNotes" class="form-label">Notes</label>
                            <textarea class="form-control" id="liabilityNotes" name="notes" rows="3">{{ $claim->liabilityDetail->notes ?? '' }}</textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>Update Liability Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link href="{{ cdn_url('cdn_select2_css') }}" rel="stylesheet" />
    <link href="{{ cdn_url('cdn_select2_bootstrap_theme_css') }}" rel="stylesheet" />
    <link href="{{ cdn_url('cdn_bootstrap_datepicker_css') }}" rel="stylesheet" />
    <style>
        /* Policy Search Dropdown Styling */
        .policy-option {
            padding: 8px 12px;
            border-bottom: 1px solid #eee;
        }

        .policy-option:last-child {
            border-bottom: none;
        }

        .policy-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
        }

        .policy-header strong {
            color: #2c3e50;
            font-size: 14px;
        }

        .policy-details small {
            color: #6c757d;
            font-size: 12px;
            line-height: 1.4;
        }

        .policy-details i {
            color: #007bff;
            margin-right: 4px;
        }

        .select2-container--bootstrap-5 .select2-dropdown {
            border: 1px solid #dee2e6;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        }

        .select2-container--bootstrap-5 .select2-results__option {
            padding: 0;
        }

        .select2-container--bootstrap-5 .select2-results__option--highlighted {
            background-color: #f8f9fa;
        }

        .select2-container--bootstrap-5 .select2-selection--single {
            height: calc(2.25rem + 2px);
            border: 1px solid #ced4da;
        }

        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 15px;
            top: 0;
            bottom: 0;
            width: 2px;
            background: #dee2e6;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -20px;
            top: 0;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            background: #fff;
            border: 2px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline-item.completed .timeline-marker {
            border-color: #28a745;
            background: #28a745;
        }

        .timeline-item.current .timeline-marker {
            border-color: #ffc107;
            background: #ffc107;
        }

        .timeline-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #dee2e6;
        }

        .timeline-item.completed .timeline-content {
            border-left-color: #28a745;
        }

        .timeline-item.current .timeline-content {
            border-left-color: #ffc107;
        }

        .document-checklist .form-check {
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }

        .document-checklist .form-check:last-child {
            border-bottom: none;
        }

        /* Force dropdown to show properly - but preserve existing styles */
        .dropdown-menu.show {
            display: block !important;
            position: absolute !important;
            z-index: 1000 !important;
            opacity: 1 !important;
            visibility: visible !important;
        }

        .dropdown-menu {
            display: none;
        }

        .dropdown {
            position: relative !important;
        }

        /* Fix for WhatsApp dropdown specifically */
        .btn-success + .dropdown-menu.show {
            transform: translate3d(0px, 32px, 0px) !important;
            top: 100% !important;
            left: 0 !important;
        }

        /* Preserve profile dropdown positioning */
        #userDropdown + .dropdown-menu.show {
            transform: none !important;
            top: auto !important;
            left: auto !important;
            right: 0 !important;
        }
    </style>
@endpush

@section('scripts')
    {{-- Include common claims functions --}}
    @include('claims.partials.common-functions')

    <script src="{{ cdn_url('cdn_select2_js') }}"></script>
    <script src="{{ cdn_url('cdn_bootstrap_datepicker_js') }}"></script>

<script>
    // Global variables
    const claimId = {{ $claim->id }};
    let currentMessageType = '';

    // WhatsApp functionality
    window.sendWhatsAppMessage = function(type) {
            currentMessageType = type;
            window.previewWhatsAppMessage(type, true);
        };

    window.previewWhatsAppMessage = function(type, autoSend = false) {
        currentMessageType = type;

        fetch(`{{ route('claims.whatsapp.preview', ['claim' => $claim->id, 'type' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', type))
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('messagePreview').textContent = data.preview;
                    document.getElementById('whatsappNumber').value = data.whatsapp_number;

                    const sendBtn = document.getElementById('sendMessageBtn');
                    sendBtn.style.display = autoSend ? 'block' : 'none';

                    window.showClaimModal('whatsappPreviewModal');
                } else {
                    showAlert('error', data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showAlert('error', 'Failed to load message preview');
            });
    }

    // Send WhatsApp message
    document.getElementById('sendMessageBtn').addEventListener('click', function() {
        const btn = this;
        const originalText = btn.innerHTML;

        // Prevent multiple clicks
        if (btn.disabled) return;

        // Set loading state
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';

        const url = getWhatsAppUrl(currentMessageType);

        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.showClaimAlert('success', data.message);
                window.hideClaimModal('whatsappPreviewModal');
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to send WhatsApp message');
        })
        .finally(() => {
            // Reset button state
            btn.disabled = false;
            btn.innerHTML = originalText;
        });
    });

    window.getWhatsAppUrl = function(type) {
        const routes = {
            'document_list': '{{ route('claims.whatsapp.documentList', $claim->id) }}',
            'pending_documents': '{{ route('claims.whatsapp.pendingDocuments', $claim->id) }}',
            'claim_number': '{{ route('claims.whatsapp.claimNumber', $claim->id) }}'
        };
        return routes[type];
    }

    // Document management
    window.updateDocumentStatus = function(documentId, isSubmitted) {
        fetch(`{{ route('claims.documents.updateStatus', ['claim' => $claim->id, 'document' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', documentId), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                is_submitted: isSubmitted
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateDocumentUI(documentId, isSubmitted);
                updateProgressBar(data.document_completion);
                showAlert('success', data.message);
            } else {
                // Revert checkbox if failed
                document.getElementById(`doc_${documentId}`).checked = !isSubmitted;
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            // Revert checkbox if failed
            document.getElementById(`doc_${documentId}`).checked = !isSubmitted;
            showAlert('error', 'Failed to update document status');
        });
    }

    window.updateDocumentUI = function(documentId, isSubmitted) {
        const documentDiv = document.querySelector(`[data-document-id="${documentId}"]`);
        const submittedBadge = documentDiv.querySelector('.submitted-badge');
        const submittedDateInfo = documentDiv.querySelector('.submitted-date-info');

        if (isSubmitted) {
            if (!submittedBadge) {
                const label = documentDiv.querySelector('.form-check-label');
                label.insertAdjacentHTML('beforeend', '<span class="badge badge-success badge-sm ms-1 submitted-badge">Submitted</span>');
            }

            if (!submittedDateInfo) {
                const now = new Date().toLocaleDateString('en-GB') + ' ' + new Date().toLocaleTimeString('en-GB', {hour12: false, hour: '2-digit', minute: '2-digit'});
                documentDiv.insertAdjacentHTML('beforeend',
                    `<small class="text-muted d-block submitted-date-info">
                        <i class="fas fa-check me-1"></i>
                        Submitted: ${now}
                    </small>`
                );
            }
        } else {
            if (submittedBadge) {
                submittedBadge.remove();
            }
            if (submittedDateInfo) {
                submittedDateInfo.remove();
            }
        }
    }

    window.updateProgressBar = function(percentage) {
        const progressBar = document.querySelector('.progress-bar');
        const progressText = progressBar.parentElement.previousElementSibling.querySelector('span:last-child');

        progressBar.style.width = percentage + '%';
        progressText.textContent = percentage + '%';
    }

    // Bulk document operations
    window.selectAllDocuments = function() {
        const checkboxes = document.querySelectorAll('.document-checkbox');
        const uncheckedBoxes = Array.from(checkboxes).filter(checkbox => !checkbox.checked);

        if (uncheckedBoxes.length === 0) {
            window.showClaimAlert('info', 'All documents are already selected');
            return;
        }

        // Show loading indicator
        const hideLoading = window.showClaimAlert('loading', `Selecting ${uncheckedBoxes.length} documents...`);

        let completedRequests = 0;
        let successCount = 0;
        let errorCount = 0;

        uncheckedBoxes.forEach(checkbox => {
            const documentId = checkbox.getAttribute('data-document-id');

            fetch(`{{ route('claims.documents.updateStatus', ['claim' => $claim->id, 'document' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', documentId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    is_submitted: true
                })
            })
            .then(response => response.json())
            .then(data => {
                completedRequests++;

                if (data.success) {
                    successCount++;
                    checkbox.checked = true;
                    updateDocumentUI(documentId, true);

                    // Update progress bar with the latest percentage from the last successful response
                    if (data.document_completion !== undefined) {
                        updateProgressBar(data.document_completion);
                    }
                } else {
                    errorCount++;
                }

                // Check if all requests are complete
                if (completedRequests === uncheckedBoxes.length) {
                    hideLoading();

                    if (successCount > 0 && errorCount === 0) {
                        window.showClaimAlert('success', `Successfully selected ${successCount} documents`);
                    } else if (successCount > 0 && errorCount > 0) {
                        window.showClaimAlert('warning', `Selected ${successCount} documents, failed ${errorCount} documents`);
                    } else {
                        window.showClaimAlert('error', 'Failed to select documents');
                    }
                }
            })
            .catch(error => {
                completedRequests++;
                errorCount++;
                console.error('Error updating document:', documentId, error);

                if (completedRequests === uncheckedBoxes.length) {
                    hideLoading();
                    window.showClaimAlert('error', `Selected ${successCount} documents, failed ${errorCount} documents`);
                }
            });
        });
    }

    window.clearAllDocuments = function() {
        const checkboxes = document.querySelectorAll('.document-checkbox');
        const checkedBoxes = Array.from(checkboxes).filter(checkbox => checkbox.checked);

        if (checkedBoxes.length === 0) {
            window.showClaimAlert('info', 'No documents are currently selected');
            return;
        }

        // Show loading indicator
        const hideLoading = window.showClaimAlert('loading', `Clearing ${checkedBoxes.length} documents...`);

        let completedRequests = 0;
        let successCount = 0;
        let errorCount = 0;

        checkedBoxes.forEach(checkbox => {
            const documentId = checkbox.getAttribute('data-document-id');

            fetch(`{{ route('claims.documents.updateStatus', ['claim' => $claim->id, 'document' => 'PLACEHOLDER']) }}`.replace('PLACEHOLDER', documentId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    is_submitted: false
                })
            })
            .then(response => response.json())
            .then(data => {
                completedRequests++;

                if (data.success) {
                    successCount++;
                    checkbox.checked = false;
                    updateDocumentUI(documentId, false);

                    // Update progress bar with the latest percentage from the last successful response
                    if (data.document_completion !== undefined) {
                        updateProgressBar(data.document_completion);
                    }
                } else {
                    errorCount++;
                }

                // Check if all requests are complete
                if (completedRequests === checkedBoxes.length) {
                    hideLoading();

                    if (successCount > 0 && errorCount === 0) {
                        window.showClaimAlert('success', `Successfully cleared ${successCount} documents`);
                    } else if (successCount > 0 && errorCount > 0) {
                        window.showClaimAlert('warning', `Cleared ${successCount} documents, failed ${errorCount} documents`);
                    } else {
                        window.showClaimAlert('error', 'Failed to clear documents');
                    }
                }
            })
            .catch(error => {
                completedRequests++;
                errorCount++;
                console.error('Error updating document:', documentId, error);

                if (completedRequests === checkedBoxes.length) {
                    hideLoading();
                    window.showClaimAlert('error', `Cleared ${successCount} documents, failed ${errorCount} documents`);
                }
            });
        });
    }

    // Stage management
    window.addNewStage = function() {
        document.getElementById('addStageForm').reset();
        const modal = new bootstrap.Modal(document.getElementById('addStageModal'));
        modal.show();
    }

    document.getElementById('addStageForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.send_whatsapp = document.getElementById('sendWhatsAppStage').checked;

        fetch('{{ route('claims.stages.add', $claim->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('addStageModal')).hide();
                location.reload(); // Reload to show new stage
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to add stage');
        });
    });

    // Claim number management
    window.editClaimNumber = function() {
        const modal = new bootstrap.Modal(document.getElementById('editClaimNumberModal'));
        modal.show();
    }

    document.getElementById('editClaimNumberForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());
        data.send_whatsapp = document.getElementById('sendWhatsAppClaimNumber').checked;

        fetch('{{ route('claims.claimNumber.update', $claim->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('success', data.message);
                bootstrap.Modal.getInstance(document.getElementById('editClaimNumberModal')).hide();

                if (data.whatsapp_result) {
                    if (data.whatsapp_result.success) {
                        showAlert('success', 'WhatsApp: ' + data.whatsapp_result.message);
                    } else {
                        showAlert('warning', 'Claim number updated, but WhatsApp failed: ' + data.whatsapp_result.message);
                    }
                }

                location.reload(); // Reload to show updated claim number
            } else {
                showAlert('error', data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('error', 'Failed to update claim number');
        });
    });

    // Liability details management
    window.editLiabilityDetails = function() {
        const modal = new bootstrap.Modal(document.getElementById('editLiabilityModal'));
        toggleLiabilityFields(); // Set initial field visibility
        modal.show();
    }

    window.toggleLiabilityFields = function() {
        const claimType = document.getElementById('claimType').value;
        const cashlessFields = document.getElementById('cashlessFields');
        const reimbursementFields = document.getElementById('reimbursementFields');

        if (claimType === 'Cashless') {
            cashlessFields.style.display = 'block';
            reimbursementFields.style.display = 'none';
        } else if (claimType === 'Reimbursement') {
            cashlessFields.style.display = 'none';
            reimbursementFields.style.display = 'block';
        } else {
            cashlessFields.style.display = 'none';
            reimbursementFields.style.display = 'none';
        }
    }

    document.getElementById('editLiabilityForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Show loading indicator
        const hideLoading = window.showClaimAlert('loading', 'Updating liability details...');

        const formData = new FormData(this);
        const data = Object.fromEntries(formData.entries());

        fetch('{{ route('claims.liability.update', $claim->id) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify(data)
        })
        .then(response => response.json())
        .then(data => {
            // Hide loading indicator
            hideLoading();

            if (data.success) {
                window.showClaimAlert('success', data.message);
                window.hideClaimModal('editLiabilityModal');
                // Reload the page to show updated liability details
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showClaimAlert('error', data.message || 'Failed to update liability details');
            }
        })
        .catch(error => {
            // Hide loading indicator
            hideLoading();
            console.error('Error:', error);
            window.showClaimAlert('error', 'Failed to update liability details');
        });
    });

    // Utility function to show alerts
    // Use the common alert function
    window.showAlert = window.showClaimAlert;

    // Edit form functionality
    window.showEditForm = function() {
        document.getElementById('basicInfoCard').style.display = 'none';
        document.getElementById('editClaimCard').style.display = 'block';

        // Initialize Select2 for edit form if not already initialized
        if (!$('.select2-policy-edit').hasClass('select2-hidden-accessible')) {
            initializeEditFormSelect2();
        }

        // Initialize date picker for edit form
        $('.date-picker-edit').datepicker({
            format: 'dd/mm/yyyy',
            autoclose: true,
            todayHighlight: true,
            endDate: new Date(),
            orientation: 'bottom'
        });
    }

    window.cancelEdit = function() {
        document.getElementById('editClaimCard').style.display = 'none';
        document.getElementById('basicInfoCard').style.display = 'block';
    }

    function initializeEditFormSelect2() {
        console.log('Show page initializing edit form Select2...');

        // Check if Select2 is loaded
        if (typeof $.fn.select2 === 'undefined') {
            console.error('Show page Select2 is not loaded!');
            return;
        }

        try {
            $('.select2-policy-edit').select2({
                theme: 'bootstrap-5',
                placeholder: 'Start typing to search policies...',
                allowClear: true,
                minimumInputLength: 2,
                width: '100%',
                dropdownAutoWidth: true,
                ajax: {
                    url: '{{ route("claims.searchPolicies") }}',
                    dataType: 'json',
                    delay: 300,
                    data: function (params) {
                        console.log('Show page searching for:', params.term);
                        return {
                            search: params.term
                        };
                    },
                    processResults: function (data) {
                        console.log('Show page search response:', data);

                        if (data.error) {
                            console.error('Show page search error:', data.error);
                            return { results: [] };
                        }

                        if (!data.results || !Array.isArray(data.results)) {
                            console.error('Show page invalid search response format:', data);
                            return { results: [] };
                        }

                        const mappedResults = data.results.map(function(item) {
                            return {
                                id: item.id,
                                text: item.text,
                                data: item
                            };
                        });

                        console.log('Show page mapped results:', mappedResults);
                        return { results: mappedResults };
                    },
                    cache: true,
                    error: function(xhr, status, error) {
                        console.error('Show page AJAX Error:', error);
                        console.error('Show page Status:', status);
                        console.error('Show page Response:', xhr.responseText);
                    }
                },
            templateResult: function(result) {
                if (!result.id) {
                    return result.text;
                }

                if (!result.data) {
                    return $('<div class="policy-option"><strong>' + result.text + '</strong></div>');
                }

                try {
                    var customerName = result.data.customer_name || 'Unknown Customer';
                    var policyNo = result.data.policy_no || 'No Policy';
                    var registrationNo = result.data.registration_no || '';
                    var insuranceCompany = result.data.insurance_company || 'No Company';
                    var policyType = result.data.policy_type || 'Policy';
                    var customerEmail = result.data.customer_email || '';
                    var customerMobile = result.data.customer_mobile || '';

                    var html = '<div class="policy-option">' +
                        '<div class="policy-header">' +
                            '<strong>' + customerName + '</strong>' +
                            '<span class="badge bg-primary" style="font-size: 10px;">' + policyType + '</span>' +
                        '</div>' +
                        '<div class="policy-details">' +
                            '<small class="d-block">' +
                                '<i class="fas fa-id-card"></i> Policy: ' + policyNo +
                                (registrationNo ? ' | <i class="fas fa-car"></i> Reg: ' + registrationNo : '') +
                                ' | <i class="fas fa-building"></i> ' + insuranceCompany +
                            '</small>';

                    if (customerEmail || customerMobile) {
                        html += '<small class="d-block mt-1">';
                        if (customerEmail) {
                            html += '<i class="fas fa-envelope"></i> ' + customerEmail;
                        }
                        if (customerEmail && customerMobile) {
                            html += ' | ';
                        }
                        if (customerMobile) {
                            html += '<i class="fas fa-phone"></i> ' + customerMobile;
                        }
                        html += '</small>';
                    }

                    html += '</div></div>';

                    return $(html);
                } catch (error) {
                    console.error('Show page error in templateResult:', error);
                    return $('<div class="policy-option"><strong>Error loading policy</strong></div>');
                }
            },
            templateSelection: function(result) {
                if (result.data) {
                    var customerName = result.data.customer_name || 'Unknown';
                    var policyNo = result.data.policy_no || 'No Policy';
                    var registrationNo = result.data.registration_no || '';

                    return customerName + ' - ' + policyNo +
                           (registrationNo ? ' (' + registrationNo + ')' : '');
                }
                return result.text || 'Select a policy...';
            }
            });

            console.log('Show page Select2 initialized successfully');
        } catch (error) {
            console.error('Show page failed to initialize Select2:', error);
        }

        // Handle policy selection
        $('.select2-policy-edit').on('select2:select', function (e) {
            var data = e.params.data.data;

            // Auto-set insurance type if suggested
            if (data.suggested_insurance_type && $('#insurance_type_edit').val() !== data.suggested_insurance_type) {
                $('#insurance_type_edit').val(data.suggested_insurance_type);
            }
        });
    }

    // Form submission
    document.getElementById('claimEditForm').addEventListener('submit', function(e) {
        e.preventDefault();

        // Show loading
        const hideLoading = window.showClaimAlert('loading', 'Updating claim details...');

        const formData = new FormData(this);

        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            hideLoading();

            if (data.success) {
                window.showClaimAlert('success', data.message || 'Claim updated successfully');
                setTimeout(() => location.reload(), 1000);
            } else {
                window.showClaimAlert('error', data.message || 'Failed to update claim');
            }
        })
        .catch(error => {
            hideLoading();
            console.error('Error:', error);
            window.showClaimAlert('error', 'Failed to update claim');
        });
    });

    // Initialize liability fields on page load
    document.addEventListener('DOMContentLoaded', function() {
        window.toggleLiabilityFields();

        // Bootstrap dropdowns work automatically - no custom code needed
    });
</script>
@endsection