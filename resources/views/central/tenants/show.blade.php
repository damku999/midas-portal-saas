@extends('central.layout')

@section('title', 'Tenant Details')
@section('page-title', 'Tenant: ' . ($tenant->data['company_name'] ?? 'N/A'))

@section('content')
<div class="row g-4">
    <!-- Main Information -->
    <div class="col-lg-8">
        <!-- Tenant Overview -->
        <div class="card mb-4">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-building me-2"></i>Tenant Overview
                </h6>
                <div>
                    <a href="{{ route('central.tenants.edit', $tenant) }}" class="btn btn-sm btn-primary">
                        <i class="fas fa-edit me-1"></i>Edit
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Company Name</label>
                        <p class="mb-0 fw-bold">{{ $tenant->data['company_name'] ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Tenant ID</label>
                        <p class="mb-0"><code>{{ $tenant->id }}</code></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Domain</label>
                        <p class="mb-0">
                            @if($tenant->domains->first())
                                <a href="http://{{ $tenant->domains->first()->domain }}" target="_blank">
                                    {{ $tenant->domains->first()->domain }}
                                    <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                </a>
                            @else
                                <span class="text-muted">No domain</span>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Admin User</label>
                        <p class="mb-0">
                            {{ $tenant->data['admin_name'] ?? 'N/A' }}
                            @if($tenant->data['admin_email'] ?? false)
                                <br><small class="text-muted">{{ $tenant->data['admin_email'] }}</small>
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Created At</label>
                        <p class="mb-0">{{ $tenant->created_at->format('F d, Y H:i:s') }}</p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="text-muted small">Last Updated</label>
                        <p class="mb-0">{{ $tenant->updated_at->format('F d, Y H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Subscription Information -->
        @if($tenant->subscription)
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-credit-card me-2"></i>Subscription Details
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Plan</label>
                            <p class="mb-0 fw-bold">
                                {{ $tenant->subscription->plan->name ?? 'N/A' }}
                                <span class="badge bg-info ms-2">
                                    ₹{{ number_format($tenant->subscription->plan->price ?? 0, 2) }}/{{ $tenant->subscription->plan->billing_interval ?? 'month' }}
                                </span>
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Status</label>
                            <p class="mb-0">
                                @php
                                    $statusClass = match($tenant->subscription->status) {
                                        'active' => 'badge-active',
                                        'trial' => 'badge-trial',
                                        'suspended' => 'badge-suspended',
                                        'cancelled' => 'badge-expired',
                                        'expired' => 'badge-expired',
                                        default => 'badge-expired'
                                    };
                                @endphp
                                <span class="badge-status {{ $statusClass }}">
                                    {{ ucfirst($tenant->subscription->status) }}
                                </span>
                            </p>
                        </div>
                        @if($tenant->subscription->is_trial && $tenant->subscription->trial_ends_at)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Trial Ends</label>
                                <p class="mb-0">
                                    {{ $tenant->subscription->trial_ends_at->format('F d, Y') }}
                                    <small class="text-muted">({{ $tenant->subscription->trial_ends_at->diffForHumans() }})</small>
                                </p>
                            </div>
                        @endif
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Monthly Revenue</label>
                            <p class="mb-0 fw-bold text-success">
                                ₹{{ number_format($tenant->subscription->mrr, 2) }}
                            </p>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="text-muted small">Started At</label>
                            <p class="mb-0">{{ $tenant->subscription->created_at->format('F d, Y') }}</p>
                        </div>
                        @if($tenant->subscription->cancelled_at)
                            <div class="col-md-6 mb-3">
                                <label class="text-muted small">Cancelled At</label>
                                <p class="mb-0">
                                    {{ $tenant->subscription->cancelled_at->format('F d, Y') }}
                                    @if($tenant->subscription->cancellation_reason)
                                        <br><small class="text-muted">{{ $tenant->subscription->cancellation_reason }}</small>
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>

                    <!-- Plan Limits -->
                    @if($tenant->subscription->plan)
                        <hr>
                        <h6 class="text-muted mb-3">Plan Limits</h6>
                        <div class="row">
                            <div class="col-md-3">
                                <small class="text-muted">Max Users</small>
                                <p class="mb-0 fw-bold">
                                    {{ $tenant->subscription->plan->max_users == -1 ? 'Unlimited' : $tenant->subscription->plan->max_users }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Max Customers</small>
                                <p class="mb-0 fw-bold">
                                    {{ $tenant->subscription->plan->max_customers == -1 ? 'Unlimited' : $tenant->subscription->plan->max_customers }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Leads/Month</small>
                                <p class="mb-0 fw-bold">
                                    {{ $tenant->subscription->plan->max_leads_per_month == -1 ? 'Unlimited' : $tenant->subscription->plan->max_leads_per_month }}
                                </p>
                            </div>
                            <div class="col-md-3">
                                <small class="text-muted">Storage</small>
                                <p class="mb-0 fw-bold">
                                    {{ $tenant->subscription->plan->storage_limit_gb }} GB
                                </p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        @else
            <div class="card mb-4">
                <div class="card-body">
                    <div class="alert alert-warning mb-0">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        This tenant has no active subscription.
                    </div>
                </div>
            </div>
        @endif

        <!-- Recent Activity -->
        <div class="card">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-history me-2"></i>Recent Activity
                </h6>
            </div>
            <div class="card-body">
                @if($recentActivity->count() > 0)
                    <div class="activity-feed">
                        @foreach($recentActivity as $activity)
                            <div class="activity-item mb-3 pb-3 border-bottom">
                                <div class="d-flex align-items-start">
                                    <div class="activity-icon me-3">
                                        @php
                                            $icon = match(true) {
                                                str_contains($activity->action, 'created') => 'fa-plus-circle text-success',
                                                str_contains($activity->action, 'updated') => 'fa-edit text-info',
                                                str_contains($activity->action, 'suspended') => 'fa-ban text-danger',
                                                str_contains($activity->action, 'activated') => 'fa-check-circle text-success',
                                                default => 'fa-info-circle text-muted'
                                            };
                                        @endphp
                                        <i class="fas {{ $icon }}"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <p class="mb-1">{{ $activity->description }}</p>
                                        <small class="text-muted">
                                            <i class="fas fa-user me-1"></i>
                                            {{ $activity->tenantUser->name ?? 'System' }}
                                            <span class="mx-1">•</span>
                                            {{ $activity->created_at->format('M d, Y H:i') }}
                                            <span class="mx-1">•</span>
                                            {{ $activity->created_at->diffForHumans() }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted text-center mb-0">No activity recorded yet</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Actions Sidebar -->
    <div class="col-lg-4">
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0 fw-bold text-primary">
                    <i class="fas fa-cogs me-2"></i>Actions
                </h6>
            </div>
            <div class="card-body">
                @if($tenant->subscription)
                    @if($tenant->subscription->status === 'active' || $tenant->subscription->status === 'trial')
                        <form action="{{ route('central.tenants.suspend', $tenant) }}" method="POST" class="mb-2" id="suspendTenantForm">
                            @csrf
                            <button type="button"
                                    class="btn btn-warning w-100 mb-2"
                                    onclick="confirmSuspendTenant()">
                                <i class="fas fa-ban me-2"></i>Suspend Tenant
                            </button>
                        </form>
                    @elseif($tenant->subscription->status === 'suspended')
                        <form action="{{ route('central.tenants.activate', $tenant) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 mb-2">
                                <i class="fas fa-check-circle me-2"></i>Activate Tenant
                            </button>
                        </form>
                    @endif
                @endif

                <a href="{{ route('central.tenants.edit', $tenant) }}" class="btn btn-primary w-100 mb-2">
                    <i class="fas fa-edit me-2"></i>Edit Details
                </a>

                <a href="{{ route('central.tenants.index') }}" class="btn btn-outline-secondary w-100">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </a>
            </div>
        </div>

        <div class="card">
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
                            class="btn btn-danger w-100"
                            data-confirm="Are you absolutely sure? This will permanently delete all tenant data including database, files, and configurations. This action cannot be undone!"
                            data-confirm-title="Delete Tenant"
                            data-confirm-button="Yes, Delete Permanently"
                            data-confirm-class="btn-danger"
                            onclick="confirmDeleteTenant()">
                        <i class="fas fa-trash-alt me-2"></i>Delete Tenant
                    </button>
                </form>
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

@php
    $companyName = $tenant->data['company_name'] ?? ($tenant->domains->first()->domain ?? $tenant->id);
@endphp

<x-central.delete-confirmation-modal
    :confirmation-text="$companyName"
    modal-id="deleteConfirmationModal"
    form-id="deleteTenantForm"
    item-type="Tenant"
/>

@push('scripts')
<script>
function confirmSuspendTenant() {
    showConfirmModal(
        'Suspend Tenant',
        'Are you sure you want to suspend this tenant? The tenant will not be able to access their account until reactivated.',
        function() {
            document.getElementById('suspendTenantForm').submit();
        },
        'Yes, Suspend',
        'btn-warning'
    );
}

function confirmDeleteTenant() {
    // Show custom modal
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    modal.show();
}
</script>
@endpush
