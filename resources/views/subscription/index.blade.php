@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3"><i class="fas fa-credit-card me-2"></i>Subscription & Usage</h1>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Current Plan Card -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-primary shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h5 class="card-title mb-1">{{ $subscription->plan->name }} Plan</h5>
                            <p class="text-muted small mb-0">
                                @if($subscription->isOnTrial())
                                    <span class="badge bg-warning text-dark">Trial</span>
                                    <span class="ms-1">Ends {{ $subscription->trial_ends_at->diffForHumans() }}</span>
                                @else
                                    <span class="badge bg-success">Active</span>
                                @endif
                            </p>
                        </div>
                        <div class="text-end">
                            <h3 class="mb-0">₹{{ number_format($subscription->plan->price) }}</h3>
                            <small class="text-muted">/month</small>
                        </div>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <small class="text-muted">Next Billing Date</small>
                        <p class="mb-0 fw-bold">{{ $subscription->next_billing_date->format('M d, Y') }}</p>
                    </div>

                    <a href="{{ route('subscription.plans') }}" class="btn btn-primary btn-sm w-100">
                        <i class="fas fa-arrow-up me-1"></i>Upgrade Plan
                    </a>
                </div>
            </div>
        </div>

        <!-- Usage Warnings -->
        @if(count($usageSummary['warnings']) > 0)
        <div class="col-md-8">
            <div class="alert alert-warning">
                <h6 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Usage Warnings</h6>
                <ul class="mb-0">
                    @foreach($usageSummary['warnings'] as $warning)
                        <li>
                            <strong>{{ ucfirst($warning['type']) }}:</strong> {{ $warning['message'] }}
                            @if($warning['severity'] === 'critical')
                                <span class="badge bg-danger">Critical</span>
                            @endif
                        </li>
                    @endforeach
                </ul>
                <a href="{{ route('subscription.plans') }}" class="btn btn-warning btn-sm mt-2">
                    Upgrade Now
                </a>
            </div>
        </div>
        @endif
    </div>

    <!-- Usage Metrics -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-users fa-2x text-primary"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Users</h6>
                            <h4 class="mb-0">{{ $usageSummary['limits']['users']['current'] }} /
                                @if($usageSummary['limits']['users']['max'] == -1)
                                    Unlimited
                                @else
                                    {{ $usageSummary['limits']['users']['max'] }}
                                @endif
                            </h4>
                        </div>
                    </div>
                    @if($usageSummary['limits']['users']['max'] != -1)
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $usageSummary['limits']['users']['percentage'] >= 80 ? 'bg-warning' : 'bg-primary' }}"
                                 style="width: {{ min(100, $usageSummary['limits']['users']['percentage']) }}%"></div>
                        </div>
                        <small class="text-muted">{{ round($usageSummary['limits']['users']['percentage']) }}% used</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-user-tie fa-2x text-success"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Customers</h6>
                            <h4 class="mb-0">{{ $usageSummary['limits']['customers']['current'] }} /
                                @if($usageSummary['limits']['customers']['max'] == -1)
                                    Unlimited
                                @else
                                    {{ $usageSummary['limits']['customers']['max'] }}
                                @endif
                            </h4>
                        </div>
                    </div>
                    @if($usageSummary['limits']['customers']['max'] != -1)
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $usageSummary['limits']['customers']['percentage'] >= 80 ? 'bg-warning' : 'bg-success' }}"
                                 style="width: {{ min(100, $usageSummary['limits']['customers']['percentage']) }}%"></div>
                        </div>
                        <small class="text-muted">{{ round($usageSummary['limits']['customers']['percentage']) }}% used</small>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-database fa-2x text-info"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="mb-1">Storage</h6>
                            <h4 class="mb-0">{{ $usageSummary['limits']['storage']['current'] }} /
                                @if($usageSummary['limits']['storage']['max'] == -1)
                                    Unlimited
                                @else
                                    {{ $usageSummary['limits']['storage']['max'] }}
                                @endif
                                GB
                            </h4>
                        </div>
                    </div>
                    @if($usageSummary['limits']['storage']['max'] != -1)
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar {{ $usageSummary['limits']['storage']['percentage'] >= 80 ? 'bg-warning' : 'bg-info' }}"
                                 style="width: {{ min(100, $usageSummary['limits']['storage']['percentage']) }}%"></div>
                        </div>
                        <small class="text-muted">{{ round($usageSummary['limits']['storage']['percentage']) }}% used</small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Usage Details -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Detailed Usage</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <i class="fas fa-file-invoice fa-2x text-primary mb-2"></i>
                                <h4 class="mb-0">{{ $usageSummary['usage']['policies'] }}</h4>
                                <small class="text-muted">Total Policies</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                                <h4 class="mb-0">{{ $usageSummary['usage']['active_policies'] }}</h4>
                                <small class="text-muted">Active Policies</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <i class="fas fa-user-plus fa-2x text-info mb-2"></i>
                                <h4 class="mb-0">{{ $usageSummary['usage']['leads'] }}</h4>
                                <small class="text-muted">Total Leads</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center p-3">
                                <i class="fas fa-hdd fa-2x text-warning mb-2"></i>
                                <h4 class="mb-0">{{ round($usageSummary['usage']['storage_mb'], 2) }} MB</h4>
                                <small class="text-muted">Database Size</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
