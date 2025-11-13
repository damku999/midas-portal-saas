@extends('central.layout')

@section('title', 'Tenant Details')
@section('page-title', 'Tenant: ' . ($tenant->company_name ?? 'N/A'))

@section('content')
<div class="row g-4">
    <!-- Main Content with Tabs -->
    <div class="col-lg-8">
        <!-- Tenant Overview Card -->
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
                        <p class="mb-0 fw-bold">{{ $tenant->company_name ?? 'N/A' }}</p>
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
                        <label class="text-muted small">Created At</label>
                        <p class="mb-0">{{ $tenant->created_at->format('F d, Y H:i:s') }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tabs for Subscription and Payments -->
        <ul class="nav nav-tabs mb-3" id="tenantTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="subscription-tab" data-bs-toggle="tab" data-bs-target="#subscription" type="button" role="tab">
                    <i class="fas fa-credit-card me-2"></i>Subscription
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="payments-tab" data-bs-toggle="tab" data-bs-target="#payments" type="button" role="tab">
                    <i class="fas fa-receipt me-2"></i>Payments ({{ $payments->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="invoices-tab" data-bs-toggle="tab" data-bs-target="#invoices" type="button" role="tab">
                    <i class="fas fa-file-invoice me-2"></i>Invoices ({{ $invoices->count() }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="activity-tab" data-bs-toggle="tab" data-bs-target="#activity" type="button" role="tab">
                    <i class="fas fa-history me-2"></i>Activity
                </button>
            </li>
        </ul>

        <div class="tab-content" id="tenantTabsContent">
            <!-- Subscription Tab -->
            <div class="tab-pane fade show active" id="subscription" role="tabpanel">
                @if($tenant->subscription)
                    <div class="card">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center">
                            <h6 class="mb-0 fw-bold">Subscription Details</h6>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#changePlanModal">
                                <i class="fas fa-exchange-alt me-1"></i>Change Plan
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Current Plan</label>
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
                                                'active' => 'bg-success',
                                                'trial' => 'bg-info',
                                                'suspended' => 'bg-warning',
                                                'cancelled' => 'bg-danger',
                                                'expired' => 'bg-secondary',
                                                default => 'bg-secondary'
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">
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
                                    <label class="text-muted small">Monthly Revenue (MRR)</label>
                                    <p class="mb-0 fw-bold text-success">
                                        ₹{{ number_format($tenant->subscription->mrr, 2) }}
                                    </p>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="text-muted small">Started At</label>
                                    <p class="mb-0">{{ $tenant->subscription->created_at->format('F d, Y') }}</p>
                                </div>
                                @if($tenant->subscription->next_billing_date)
                                    <div class="col-md-6 mb-3">
                                        <label class="text-muted small">Next Billing Date</label>
                                        <p class="mb-0">
                                            {{ $tenant->subscription->next_billing_date->format('F d, Y') }}
                                            <small class="text-muted">({{ $tenant->subscription->next_billing_date->diffForHumans() }})</small>
                                        </p>
                                    </div>
                                @endif
                                @if($tenant->subscription->cancelled_at)
                                    <div class="col-md-12 mb-3">
                                        <label class="text-muted small">Cancelled At</label>
                                        <p class="mb-0">
                                            {{ $tenant->subscription->cancelled_at->format('F d, Y') }}
                                            @if($tenant->subscription->cancellation_reason)
                                                <br><small class="text-muted">Reason: {{ $tenant->subscription->cancellation_reason }}</small>
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
                                    <div class="col-md-3 mb-2">
                                        <small class="text-muted d-block">Max Users</small>
                                        <strong>{{ $tenant->subscription->plan->max_users == -1 ? 'Unlimited' : number_format($tenant->subscription->plan->max_users) }}</strong>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <small class="text-muted d-block">Max Customers</small>
                                        <strong>{{ $tenant->subscription->plan->max_customers == -1 ? 'Unlimited' : number_format($tenant->subscription->plan->max_customers) }}</strong>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <small class="text-muted d-block">Leads/Month</small>
                                        <strong>{{ $tenant->subscription->plan->max_leads_per_month == -1 ? 'Unlimited' : number_format($tenant->subscription->plan->max_leads_per_month) }}</strong>
                                    </div>
                                    <div class="col-md-3 mb-2">
                                        <small class="text-muted d-block">Storage</small>
                                        <strong>{{ $tenant->subscription->plan->storage_limit_gb }} GB</strong>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-body">
                            <div class="alert alert-warning mb-0">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                This tenant has no active subscription.
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Payments Tab -->
            <div class="tab-pane fade" id="payments" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h6 class="mb-0 fw-bold">Payment History</h6>
                        <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                            <i class="fas fa-plus me-1"></i>Record Payment
                        </button>
                    </div>
                    <div class="card-body">
                        @if($payments->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Type</th>
                                            <th>Gateway</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($payments as $payment)
                                            <tr>
                                                <td>
                                                    <small>{{ $payment->created_at->format('M d, Y') }}</small>
                                                    <br>
                                                    <small class="text-muted">{{ $payment->created_at->format('h:i A') }}</small>
                                                </td>
                                                <td>
                                                    <strong>₹{{ number_format($payment->amount, 2) }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $payment->currency }}</small>
                                                </td>
                                                <td>
                                                    <span class="badge bg-secondary">{{ ucfirst($payment->type) }}</span>
                                                </td>
                                                <td>{{ ucfirst($payment->payment_gateway) }}</td>
                                                <td>
                                                    @php
                                                        $statusBadge = match($payment->status) {
                                                            'completed' => 'bg-success',
                                                            'pending' => 'bg-warning',
                                                            'failed' => 'bg-danger',
                                                            'refunded' => 'bg-info',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusBadge }}">{{ ucfirst($payment->status) }}</span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="viewPayment({{ $payment->id }})">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center mb-0">No payments recorded yet</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Invoices Tab -->
            <div class="tab-pane fade" id="invoices" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Invoice History</h6>
                    </div>
                    <div class="card-body">
                        @if($invoices->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Invoice #</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($invoices as $invoice)
                                            <tr>
                                                <td>
                                                    <strong>{{ $invoice->invoice_number }}</strong>
                                                    <br>
                                                    <small class="text-muted">{{ $invoice->customer_name }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $invoice->invoice_date->format('M d, Y') }}</small>
                                                    @if($invoice->due_date)
                                                        <br>
                                                        <small class="text-muted">Due: {{ $invoice->due_date->format('M d, Y') }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <strong>₹{{ number_format($invoice->total_amount, 2) }}</strong>
                                                    @if($invoice->gateway_charges > 0)
                                                        <br>
                                                        <small class="text-muted">With charges: ₹{{ number_format($invoice->total_with_gateway_charges, 2) }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $statusBadge = match($invoice->status) {
                                                            'paid' => 'bg-success',
                                                            'unpaid' => 'bg-warning',
                                                            'overdue' => 'bg-danger',
                                                            'cancelled' => 'bg-secondary',
                                                            default => 'bg-secondary'
                                                        };
                                                    @endphp
                                                    <span class="badge {{ $statusBadge }}">{{ ucfirst($invoice->status) }}</span>
                                                    @if($invoice->status === 'paid' && $invoice->paid_at)
                                                        <br>
                                                        <small class="text-muted">{{ $invoice->paid_at->format('M d') }}</small>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm" role="group">
                                                        <a href="{{ route('central.invoices.show', $invoice) }}"
                                                           class="btn btn-outline-primary"
                                                           target="_blank"
                                                           title="View Invoice">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <a href="{{ route('central.invoices.download', $invoice) }}"
                                                           class="btn btn-outline-success"
                                                           title="Download PDF">
                                                            <i class="fas fa-download"></i>
                                                        </a>
                                                        <form action="{{ route('central.invoices.send-email', $invoice) }}"
                                                              method="POST"
                                                              class="d-inline"
                                                              onsubmit="return confirm('Send invoice to {{ $invoice->customer_email ?? 'customer' }}?')">
                                                            @csrf
                                                            <button type="submit"
                                                                    class="btn btn-outline-info"
                                                                    title="Send Email"
                                                                    {{ empty($invoice->customer_email) ? 'disabled' : '' }}>
                                                                <i class="fas fa-envelope"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center mb-0">No invoices generated yet</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Activity Tab -->
            <div class="tab-pane fade" id="activity" role="tabpanel">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0 fw-bold">Recent Activity</h6>
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
                                                        str_contains($activity->action, 'payment') => 'fa-dollar-sign text-success',
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

                    @if($tenant->subscription->is_trial && $tenant->subscription->onTrial())
                        <form action="{{ route('central.tenants.end-trial', $tenant) }}" method="POST" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-info w-100 mb-2">
                                <i class="fas fa-forward me-2"></i>End Trial Now
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

        <!-- Quick Stats -->
        @if($tenant->subscription)
            <div class="card mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="fas fa-chart-line me-2"></i>Quick Stats
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Payments</small>
                        <h4 class="mb-0">{{ $payments->where('status', 'completed')->count() }}</h4>
                    </div>
                    <div class="mb-3">
                        <small class="text-muted d-block">Total Revenue</small>
                        <h4 class="mb-0 text-success">₹{{ number_format($payments->where('status', 'completed')->sum('amount'), 2) }}</h4>
                    </div>
                    <div class="mb-0">
                        <small class="text-muted d-block">Pending Payments</small>
                        <h4 class="mb-0 text-warning">{{ $payments->where('status', 'pending')->count() }}</h4>
                    </div>
                </div>
            </div>
        @endif

        <!-- Danger Zone -->
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
                            onclick="confirmDeleteTenant()">
                        <i class="fas fa-trash-alt me-2"></i>Delete Tenant
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Change Plan Modal -->
<div class="modal fade" id="changePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('central.tenants.change-plan', $tenant) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Change Subscription Plan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="new_plan_id" class="form-label">Select New Plan</label>
                        <select class="form-select" id="new_plan_id" name="new_plan_id" required>
                            <option value="">Choose a plan...</option>
                            @foreach($plans as $plan)
                                <option value="{{ $plan->id }}" {{ $tenant->subscription && $tenant->subscription->plan_id == $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} - ₹{{ number_format($plan->price, 2) }}/{{ $plan->billing_interval }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="change_reason" class="form-label">Reason for Change (Optional)</label>
                        <textarea class="form-control" id="change_reason" name="change_reason" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Change Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Record Payment Modal -->
<div class="modal fade" id="recordPaymentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('central.tenants.record-payment', $tenant) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Record Manual Payment</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" id="amount" name="amount" step="0.01" required>
                    </div>
                    <div class="mb-3">
                        <label for="payment_gateway" class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select class="form-select" id="payment_gateway" name="payment_gateway" required>
                            <option value="cash">Cash</option>
                            <option value="bank_transfer">Bank Transfer</option>
                            <option value="cheque">Cheque</option>
                            <option value="razorpay">Razorpay</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="gateway_payment_id" class="form-label">Transaction/Reference ID</label>
                        <input type="text" class="form-control" id="gateway_payment_id" name="gateway_payment_id">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="paid_at" class="form-label">Payment Date</label>
                        <input type="date" class="form-control" id="paid_at" name="paid_at" value="{{ now()->format('Y-m-d') }}">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Record Payment</button>
                </div>
            </form>
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
@endsection

@push('styles')
<style>
    .activity-feed .activity-item:last-child {
        border-bottom: none !important;
        padding-bottom: 0 !important;
        margin-bottom: 0 !important;
    }
    .nav-tabs .nav-link {
        color: #6c757d;
    }
    .nav-tabs .nav-link.active {
        color: #0d6efd;
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
function confirmSuspendTenant() {
    if (confirm('Are you sure you want to suspend this tenant? They will not be able to access their account until reactivated.')) {
        document.getElementById('suspendTenantForm').submit();
    }
}

function confirmDeleteTenant() {
    const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    modal.show();
}

function viewPayment(paymentId) {
    alert('Payment details view coming soon. Payment ID: ' + paymentId);
}
</script>
@endpush
