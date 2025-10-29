@extends('layouts.customer')

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
                <a href="{{ route('customer.claims') }}"
                   class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-chevron-left me-1"></i>Back to Claims
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
                                Current Status
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                {{ $claim->currentStage->stage_name ?? 'Processing' }}
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
                                Claim Status
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
            <!-- Basic Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-info-circle me-2"></i>Claim Information
                    </h6>
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
                                    <td>{{ $claim->whatsapp_number ?? 'Same as mobile' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <td class="fw-semibold">Incident Date:</td>
                                    <td>{{ $claim->incident_date ? format_app_date($claim->incident_date) : 'N/A' }}</td>
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
                                    <td class="fw-semibold">Claim Created:</td>
                                    <td>{{ $claim->created_at ? format_app_datetime($claim->created_at) : 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="fw-semibold">Last Updated:</td>
                                    <td>{{ $claim->updated_at ? format_app_datetime($claim->updated_at) : 'N/A' }}</td>
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
                                    <td>{{ $claim->customerInsurance->policyType->policy_type ?? 'N/A' }}</td>
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

            <!-- Claim Status History -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-history me-2"></i>Status History
                    </h6>
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
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Status History</h5>
                            <p class="text-muted">No status updates have been recorded for this claim yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Document Checklist -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-clipboard-check me-2"></i>Required Documents
                    </h6>
                </div>
                <div class="card-body">
                    @if($claim->documents->count() > 0)
                        <!-- Progress Bar -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span class="text-sm">Submitted Progress</span>
                                <span class="text-sm">{{ $claim->getDocumentCompletionPercentage() }}%</span>
                            </div>
                            <div class="progress">
                                <div class="progress-bar" role="progressbar"
                                     style="width: {{ $claim->getDocumentCompletionPercentage() }}%">
                                </div>
                            </div>
                        </div>

                        <!-- Document List -->
                        <div class="document-checklist">
                            @foreach($claim->documents as $document)
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="checkbox"
                                           {{ $document->is_submitted ? 'checked' : '' }} disabled>
                                    <label class="form-check-label">
                                        <strong>{{ $document->document_name }}</strong>
                                        @if($document->is_required)
                                            <span class="badge badge-danger badge-sm ms-2">Required</span>
                                        @else
                                            <span class="badge badge-warning badge-sm ms-2">Optional</span>
                                        @endif
                                        @if($document->is_submitted)
                                            <span class="badge badge-success badge-sm ms-1">Submitted</span>
                                        @else
                                            <span class="badge badge-secondary badge-sm ms-1">Pending</span>
                                        @endif
                                    </label>
                                    @if($document->description)
                                        <small class="text-muted d-block">{{ $document->description }}</small>
                                    @endif
                                    @if($document->submitted_date)
                                        <small class="text-success d-block">
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
                            <p class="text-muted">No document requirements have been set for this claim.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Settlement Information -->
            @if($claim->liabilityDetail)
                <div class="card shadow mb-4">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-calculator me-2"></i>Settlement Details
                        </h6>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless">
                            <tr>
                                <td class="fw-semibold">Settlement Type:</td>
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
                                        <td class="fw-semibold">Amount to be Paid by You:</td>
                                        <td><strong>₹{{ number_format($claim->liabilityDetail->amount_to_be_paid, 2) }}</strong></td>
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
                                        <td class="fw-semibold">Amount You Will Receive:</td>
                                        <td><strong>₹{{ number_format($claim->liabilityDetail->claim_amount_received, 2) }}</strong></td>
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
                    </div>
                </div>
            @endif

            <!-- Contact Information -->
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">
                        <i class="fas fa-phone me-2"></i>Need Help?
                    </h6>
                </div>
                <div class="card-body text-center">
                    <p class="mb-3">For any questions about your claim, please contact us:</p>
                    <div class="mb-3">
                        <strong>{{ company_advisor_name() }}</strong><br>
                        <small class="text-muted">{{ company_title() }}</small>
                    </div>
                    <div class="mb-3">
                        <a href="tel:{{ str_replace(['+', ' '], '', company_phone()) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-phone me-1"></i>{{ company_phone() }}
                        </a>
                    </div>
                    <div class="mb-2">
                        <a href="{{ company_website() }}" target="_blank" class="text-muted text-decoration-none">
                            <i class="fas fa-globe me-1"></i>{{ str_replace(['https://', 'http://'], '', company_website()) }}
                        </a>
                    </div>
                    <small class="text-muted font-italic">"{{ company_tagline() }}"</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
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

    .table-borderless td {
        border: none;
        padding: 0.25rem 0.5rem;
    }
</style>
@endpush