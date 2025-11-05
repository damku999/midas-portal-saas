@extends('central.layout')

@section('title', 'Plan Details')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h3 mb-0">Plan Details: {{ $plan->name }}</h1>
                <div class="btn-group">
                    <a href="{{ route('central.plans.edit', $plan) }}" class="btn btn-primary">
                        <i class="bi bi-pencil me-1"></i> Edit Plan
                    </a>
                    <a href="{{ route('central.plans.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left me-1"></i> Back to Plans
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Plan Overview -->
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Plan Information</h5>
                    <div>
                        @if($plan->is_active)
                            <span class="badge bg-success">Active</span>
                        @else
                            <span class="badge bg-danger">Inactive</span>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Plan Name</label>
                            <p class="mb-0"><strong>{{ $plan->name }}</strong></p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Slug</label>
                            <p class="mb-0"><code>{{ $plan->slug }}</code></p>
                        </div>
                    </div>

                    @if($plan->description)
                        <div class="row mb-3">
                            <div class="col-12">
                                <label class="text-muted small">Description</label>
                                <p class="mb-0">{{ $plan->description }}</p>
                            </div>
                        </div>
                    @endif

                    <hr>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Price</label>
                            <p class="mb-0">
                                <strong class="h4">${{ number_format($plan->price, 2) }}</strong>
                                <span class="text-muted">/ {{ $plan->billing_interval }}</span>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Billing Interval</label>
                            <p class="mb-0">
                                <span class="badge bg-secondary">{{ ucfirst($plan->billing_interval) }}</span>
                            </p>
                        </div>
                    </div>

                    <hr>

                    <h6 class="mb-3">Usage Limits</h6>
                    <div class="row">
                        <div class="col-md-3">
                            <label class="text-muted small">Max Users</label>
                            <p class="mb-0">
                                <strong>{{ $plan->max_users === -1 ? 'Unlimited' : number_format($plan->max_users) }}</strong>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Max Customers</label>
                            <p class="mb-0">
                                <strong>{{ $plan->max_customers === -1 ? 'Unlimited' : number_format($plan->max_customers) }}</strong>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Max Leads/Month</label>
                            <p class="mb-0">
                                <strong>{{ $plan->max_leads_per_month === -1 ? 'Unlimited' : number_format($plan->max_leads_per_month) }}</strong>
                            </p>
                        </div>
                        <div class="col-md-3">
                            <label class="text-muted small">Storage Limit</label>
                            <p class="mb-0">
                                <strong>{{ $plan->storage_limit_gb }}GB</strong>
                            </p>
                        </div>
                    </div>

                    @if($plan->features && count($plan->features) > 0)
                        <hr>
                        <h6 class="mb-3">Features</h6>
                        <ul class="list-unstyled mb-0">
                            @foreach($plan->features as $feature)
                                <li class="mb-2">
                                    <i class="bi bi-check-circle-fill text-success me-2"></i>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>
                    @endif

                    <hr>

                    <div class="row">
                        <div class="col-md-4">
                            <label class="text-muted small">Sort Order</label>
                            <p class="mb-0"><strong>{{ $plan->sort_order }}</strong></p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Created</label>
                            <p class="mb-0">{{ $plan->created_at->format('M d, Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small">Last Updated</label>
                            <p class="mb-0">{{ $plan->updated_at->format('M d, Y') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Subscriptions -->
            @if($plan->subscriptions->count() > 0)
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent Subscriptions ({{ $plan->subscriptions_count }} total)</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <th>Tenant</th>
                                        <th>Domain</th>
                                        <th>Status</th>
                                        <th>Started</th>
                                        <th>Next Billing</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($plan->subscriptions as $subscription)
                                        <tr>
                                            <td>
                                                <a href="{{ route('central.tenants.show', $subscription->tenant) }}">
                                                    {{ $subscription->tenant->data['company_name'] ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td>
                                                @if($subscription->tenant->domains->first())
                                                    <code>{{ $subscription->tenant->domains->first()->domain }}</code>
                                                @else
                                                    <span class="text-muted">No domain</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($subscription->status === 'active')
                                                    <span class="badge bg-success">Active</span>
                                                @elseif($subscription->status === 'trial')
                                                    <span class="badge bg-info">Trial</span>
                                                @elseif($subscription->status === 'cancelled')
                                                    <span class="badge bg-danger">Cancelled</span>
                                                @elseif($subscription->status === 'expired')
                                                    <span class="badge bg-secondary">Expired</span>
                                                @else
                                                    <span class="badge bg-warning">{{ ucfirst($subscription->status) }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ $subscription->starts_at ? $subscription->starts_at->format('M d, Y') : 'N/A' }}
                                            </td>
                                            <td>
                                                {{ $subscription->next_billing_date ? $subscription->next_billing_date->format('M d, Y') : 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Stats & Actions -->
        <div class="col-lg-4">
            <!-- Quick Stats -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">Quick Stats</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <small class="text-muted">Total Subscriptions</small>
                            <h3 class="mb-0">{{ $plan->subscriptions_count }}</h3>
                        </div>
                        <div class="text-end">
                            <i class="bi bi-people-fill text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <small class="text-muted">Monthly Revenue</small>
                            <h4 class="mb-0">${{ number_format($plan->subscriptions_count * $plan->price, 2) }}</h4>
                        </div>
                        <div class="text-end">
                            <i class="bi bi-cash-stack text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Actions</h5>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('central.plans.edit', $plan) }}" class="btn btn-primary">
                            <i class="bi bi-pencil me-1"></i> Edit Plan
                        </a>

                        <form action="{{ route('central.plans.toggle-status', $plan) }}" method="POST">
                            @csrf
                            @if($plan->is_active)
                                <button type="submit" class="btn btn-warning w-100">
                                    <i class="bi bi-toggle-off me-1"></i> Deactivate Plan
                                </button>
                            @else
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="bi bi-toggle-on me-1"></i> Activate Plan
                                </button>
                            @endif
                        </form>

                        @if($plan->subscriptions_count === 0)
                            <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deletePlanModal">
                                <i class="bi bi-trash me-1"></i> Delete Plan
                            </button>
                        @else
                            <button type="button" class="btn btn-danger" disabled title="Cannot delete - has subscriptions">
                                <i class="bi bi-trash me-1"></i> Delete Plan
                            </button>
                            <small class="text-muted">
                                <i class="bi bi-info-circle me-1"></i>
                                Cannot delete plan with active subscriptions
                            </small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div class="modal fade" id="deletePlanModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirm Deletion</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete the plan <strong>{{ $plan->name }}</strong>?</p>
                <p class="text-danger mb-0">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('central.plans.destroy', $plan) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">Delete Plan</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
