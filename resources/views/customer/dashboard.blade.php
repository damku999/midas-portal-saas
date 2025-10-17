@extends('layouts.customer')

@section('title', 'Customer Dashboard')

@section('content')
<div class="dashboard-container">
    <div class="container-fluid">

        @if ($expiringPolicies->count() > 0)
        <!-- Critical Alerts Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card alert-card border-warning">
                    <div class="card-header bg-warning text-dark">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <h5 class="mb-0 fw-bold">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                Policies Expiring Soon
                                <span class="badge bg-danger ms-2">{{ $expiringPolicies->count() }}</span>
                            </h5>
                            <a href="{{ route('customer.policies') }}" class="btn btn-outline-dark btn-sm">
                                <i class="fas fa-arrow-right me-1"></i>View All Policies
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <p class="text-dark mb-3">
                            <i class="fas fa-clock me-1"></i>These policies will expire within the next 30 days. 
                            <strong>Take action now to avoid coverage gaps.</strong>
                        </p>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-file-alt me-1"></i>Policy Details</th>
                                        <th><i class="fas fa-user me-1"></i>Policy Holder</th>
                                        <th><i class="fas fa-building me-1"></i>Insurance Company</th>
                                        <th><i class="fas fa-calendar-times me-1"></i>Expires</th>
                                        <th><i class="fas fa-hourglass-half me-1"></i>Days Left</th>
                                        <th><i class="fas fa-cog me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($expiringPolicies as $policy)
                                    <tr>
                                        <td>
                                            <div>
                                                <strong class="text-dark">{{ $policy->policy_no ?? 'N/A' }}</strong>
                                                @if ($policy->registration_no)
                                                    <br><small class="text-muted">
                                                        <i class="fas fa-car me-1"></i>{{ $policy->registration_no }}
                                                    </small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle me-2 text-muted"></i>
                                                <div>
                                                    {{ $policy->customer->name }}
                                                    @if ($policy->customer_id === $customer->id)
                                                        <span class="badge bg-primary ms-1">You</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-dark">{{ $policy->insuranceCompany->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <span class="fw-medium">{{ formatDateForUi($policy->expired_date) }}</span>
                                        </td>
                                        <td>
                                            @php
                                                $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($policy->expired_date), false);
                                            @endphp
                                            <span class="badge bg-{{ $daysLeft <= 7 ? 'danger' : 'warning' }} fs-6">
                                                <i class="fas fa-clock me-1"></i>{{ $daysLeft }} days
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('customer.policies.detail', $policy->id) }}" 
                                               class="btn btn-info btn-sm">
                                                <i class="fas fa-eye me-1"></i>View Details
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if ($familyPolicies->count() > 0)
        <!-- Active Policies Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <h5 class="mb-0 fw-bold text-white">
                                <i class="fas fa-shield-alt me-2"></i>
                                @if ($isHead)
                                    Family Insurance Portfolio
                                @else
                                    Your Insurance Policies
                                @endif
                                <span class="badge bg-light text-primary ms-2">{{ $familyPolicies->count() }} Active</span>
                            </h5>
                            <a href="{{ route('customer.policies') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-external-link-alt me-1"></i>Manage All
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>Policy No</th>
                                        <th><i class="fas fa-user me-1"></i>Holder</th>
                                        <th><i class="fas fa-building me-1"></i>Company</th>
                                        <th><i class="fas fa-tag me-1"></i>Type</th>
                                        <th><i class="fas fa-credit-card me-1"></i>Premium</th>
                                        <th><i class="fas fa-rupee-sign me-1"></i>Amount</th>
                                        <th><i class="fas fa-calendar me-1"></i>Validity</th>
                                        <th><i class="fas fa-chart-line me-1"></i>Status</th>
                                        <th><i class="fas fa-tools me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($familyPolicies as $policy)
                                    <tr class="{{ $policy->customer_id === $customer->id ? 'table-light' : '' }}">
                                        <td>
                                            <div>
                                                <strong>{{ $policy->policy_no ?? 'N/A' }}</strong>
                                                @if ($policy->registration_no)
                                                    <br><small class="text-muted">{{ $policy->registration_no }}</small>
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="me-2">
                                                    @if ($policy->customer_id === $customer->id)
                                                        <i class="fas fa-user-check text-primary"></i>
                                                    @else
                                                        <i class="fas fa-user text-muted"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    {{ $policy->customer->name }}
                                                    @if ($policy->customer_id === $customer->id)
                                                        <span class="badge bg-primary ms-1">You</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $policy->insuranceCompany->name ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge bg-secondary">{{ $policy->policyType->name ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            @if($policy->premiumType)
                                                <span class="badge bg-info">{{ $policy->premiumType->name }}</span>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($policy->final_premium_with_gst)
                                                <strong class="text-success">{{ format_indian_currency($policy->final_premium_with_gst) }}</strong>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <small class="text-muted">From:</small> 
                                                <strong>{{ $policy->start_date ? formatDateForUi($policy->start_date) : 'N/A' }}</strong>
                                                @if ($policy->expired_date)
                                                    <br><small class="text-muted">To:</small> 
                                                    <strong>{{ formatDateForUi($policy->expired_date) }}</strong>
                                                    @php
                                                        $expiry = \Carbon\Carbon::parse($policy->expired_date);
                                                        $daysLeft = $expiry->diffInDays(now(), false);
                                                    @endphp
                                                    @if ($daysLeft > 0)
                                                        <br><small class="text-danger">
                                                            <i class="fas fa-exclamation-triangle me-1"></i>Expired {{ $daysLeft }} days ago
                                                        </small>
                                                    @elseif($daysLeft > -30)
                                                        <br><small class="text-warning">
                                                            <i class="fas fa-clock me-1"></i>{{ abs($daysLeft) }} days left
                                                        </small>
                                                    @endif
                                                @endif
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $isExpired = $policy->expired_date ? \Carbon\Carbon::parse($policy->expired_date)->isPast() : false;
                                                $isExpiringSoon = false;
                                                if ($policy->expired_date && !$isExpired) {
                                                    $daysLeft = now()->diffInDays(\Carbon\Carbon::parse($policy->expired_date), false);
                                                    $isExpiringSoon = $daysLeft <= 30;
                                                }
                                            @endphp
                                            @if ($isExpired)
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle me-1"></i>Expired
                                                </span>
                                            @elseif($isExpiringSoon)
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-exclamation-triangle me-1"></i>Expiring Soon
                                                </span>
                                            @else
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle me-1"></i>Active
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('customer.policies.detail', $policy->id) }}" 
                                               class="btn btn-info btn-sm" 
                                               data-bs-toggle="tooltip" 
                                               title="View policy details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <!-- No Policies State -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card text-center">
                    <div class="card-body py-5">
                        <div class="mb-4">
                            <i class="fas fa-shield-alt fa-4x text-muted opacity-50"></i>
                        </div>
                        <h5 class="text-muted mb-3">No Insurance Policies Found</h5>
                        <p class="text-muted mb-4">
                            @if ($customer->hasFamily())
                                No insurance policies found for your family group.
                            @else
                                You don't have any insurance policies or are not part of a family group.
                            @endif
                        </p>
                        <a href="{{ route('customer.quotations') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-2"></i>Get Your First Quote
                        </a>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if ($recentQuotations->count() > 0)
        <!-- Recent Quotations Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success">
                        <div class="d-flex align-items-center justify-content-between flex-wrap">
                            <h5 class="mb-0 fw-bold text-white">
                                <i class="fas fa-calculator me-2"></i>Recent Quotations
                                <span class="badge bg-light text-success ms-2">{{ $recentQuotations->count() }} Recent</span>
                            </h5>
                            <a href="{{ route('customer.quotations') }}" class="btn btn-light btn-sm">
                                <i class="fas fa-history me-1"></i>View All Quotes
                            </a>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th><i class="fas fa-hashtag me-1"></i>Quote Ref</th>
                                        <th><i class="fas fa-user me-1"></i>Requestor</th>
                                        <th><i class="fas fa-car me-1"></i>Vehicle Details</th>
                                        <th><i class="fas fa-chart-pie me-1"></i>Status</th>
                                        <th><i class="fas fa-calendar me-1"></i>Created</th>
                                        <th><i class="fas fa-download me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($recentQuotations as $quotation)
                                    <tr>
                                        <td>
                                            <strong class="text-primary">{{ $quotation->getQuoteReference() }}</strong>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-user-circle me-2 text-muted"></i>
                                                <div>
                                                    {{ $quotation->customer->name }}
                                                    @if ($quotation->customer_id === $customer->id)
                                                        <span class="badge bg-primary ms-1">You</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($quotation->vehicle_number)
                                                <div>
                                                    <strong class="text-dark">{{ $quotation->vehicle_number }}</strong>
                                                    <br><small class="text-muted">{{ $quotation->make_model_variant }}</small>
                                                </div>
                                            @else
                                                <span class="text-muted">
                                                    <i class="fas fa-clipboard-list me-1"></i>General Insurance
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($quotation->status == 'sent')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-paper-plane me-1"></i>Sent
                                                </span>
                                            @elseif($quotation->status == 'pending')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock me-1"></i>Pending
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <i class="fas fa-edit me-1"></i>{{ ucfirst($quotation->status ?? 'Draft') }}
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <span>{{ formatDateForUi($quotation->created_at) }}</span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('customer.quotations.detail', $quotation->id) }}" 
                                                   class="btn btn-success btn-sm"
                                                   data-bs-toggle="tooltip" 
                                                   title="View quote details">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                @if ($quotation->quotationCompanies->count() > 0)
                                                    <a href="{{ route('customer.quotations.download', $quotation->id) }}" 
                                                       class="btn btn-primary btn-sm"
                                                       data-bs-toggle="tooltip" 
                                                       title="Download quote">
                                                        <i class="fas fa-download"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Quick Actions moved to footer to avoid duplication -->
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Add fade-in animation to cards
    $(document).ready(function() {
        $('.card').each(function(index) {
            $(this).css('animation-delay', (index * 100) + 'ms').addClass('fade-in-scale');
        });
    });
</script>
@endpush