@extends('central.layout')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard Overview')

@section('content')
<div class="row g-4 mb-4">
    <!-- Total Tenants -->
    <div class="col-md-3">
        <div class="stat-card">
            <h6>Total Tenants</h6>
            <div class="d-flex align-items-center justify-content-between">
                <div class="h3 mb-0">{{ $totalTenants }}</div>
                <i class="fas fa-building fa-2x text-primary opacity-25"></i>
            </div>
        </div>
    </div>

    <!-- Active Tenants -->
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #1cc88a;">
            <h6>Active Tenants</h6>
            <div class="d-flex align-items-center justify-content-between">
                <div class="h3 mb-0">{{ $activeTenants }}</div>
                <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
            </div>
        </div>
    </div>

    <!-- Trial Tenants -->
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #f6c23e;">
            <h6>Trial Tenants</h6>
            <div class="d-flex align-items-center justify-content-between">
                <div class="h3 mb-0">{{ $trialTenants }}</div>
                <i class="fas fa-clock fa-2x text-warning opacity-25"></i>
            </div>
        </div>
    </div>

    <!-- Monthly Recurring Revenue -->
    <div class="col-md-3">
        <div class="stat-card" style="border-left-color: #36b9cc;">
            <h6>Monthly Revenue (MRR)</h6>
            <div class="d-flex align-items-center justify-content-between">
                <div class="h3 mb-0">₹{{ number_format($mrr, 2) }}</div>
                <i class="fas fa-rupee-sign fa-2x text-info opacity-25"></i>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Tenants -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-building me-2"></i>Recent Tenants
                </h6>
                <a href="{{ route('central.tenants.index') }}" class="btn btn-sm btn-outline-primary">
                    View All <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Company</th>
                                <th>Domain</th>
                                <th>Plan</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentTenants as $tenant)
                                <tr>
                                    <td>
                                        <strong>{{ $tenant->data['company_name'] ?? 'N/A' }}</strong>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $tenant->domains->first()->domain ?? 'N/A' }}
                                        </small>
                                    </td>
                                    <td>
                                        @if($tenant->subscription && $tenant->subscription->plan)
                                            <span class="badge bg-info">
                                                {{ $tenant->subscription->plan->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No Plan</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($tenant->subscription)
                                            @php
                                                $statusClass = match($tenant->subscription->status) {
                                                    'active' => 'badge-active',
                                                    'trial' => 'badge-trial',
                                                    'suspended' => 'badge-suspended',
                                                    default => 'badge-expired'
                                                };
                                            @endphp
                                            <span class="badge-status {{ $statusClass }}">
                                                {{ ucfirst($tenant->subscription->status) }}
                                            </span>
                                        @else
                                            <span class="badge-status badge-expired">No Subscription</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $tenant->created_at->format('M d, Y') }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('central.tenants.show', $tenant) }}"
                                           class="btn btn-sm btn-outline-primary"
                                           title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                        No tenants found. Create your first tenant to get started.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-history me-2"></i>Recent Activity
                </h6>
            </div>
            <div class="card-body">
                <div class="activity-feed">
                    @forelse($recentActivity as $activity)
                        <div class="activity-item mb-3 pb-3 border-bottom">
                            <div class="d-flex align-items-start">
                                <div class="activity-icon me-3">
                                    @php
                                        $icon = match(true) {
                                            str_contains($activity->action, 'created') => 'fa-plus-circle text-success',
                                            str_contains($activity->action, 'updated') => 'fa-edit text-info',
                                            str_contains($activity->action, 'suspended') => 'fa-ban text-danger',
                                            str_contains($activity->action, 'activated') => 'fa-check-circle text-success',
                                            str_contains($activity->action, 'login') => 'fa-sign-in-alt text-primary',
                                            default => 'fa-info-circle text-muted'
                                        };
                                    @endphp
                                    <i class="fas {{ $icon }}"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-1 small">{{ $activity->description }}</p>
                                    <small class="text-muted">
                                        <i class="fas fa-user me-1"></i>
                                        {{ $activity->tenantUser->name ?? 'System' }}
                                        <span class="mx-1">•</span>
                                        {{ $activity->created_at->diffForHumans() }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-history fa-2x mb-2 d-block"></i>
                            <small>No recent activity</small>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .activity-feed .activity-item:last-child {
        border-bottom: none !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
</style>
@endpush
