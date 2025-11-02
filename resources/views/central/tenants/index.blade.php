@extends('central.layout')

@section('title', 'Tenants')
@section('page-title', 'Tenant Management')

@section('content')
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0 fw-bold text-primary">
            <i class="fas fa-building me-2"></i>All Tenants
        </h6>
        <a href="{{ route('central.tenants.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Create New Tenant
        </a>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Company Name</th>
                        <th>Domain</th>
                        <th>Plan</th>
                        <th>Status</th>
                        <th>Trial Ends</th>
                        <th>MRR</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tenants as $tenant)
                        <tr>
                            <td><code>{{ substr($tenant->id, 0, 8) }}</code></td>
                            <td>
                                <strong>{{ $tenant->data['company_name'] ?? 'N/A' }}</strong>
                                @if($tenant->data['admin_name'] ?? false)
                                    <br><small class="text-muted">{{ $tenant->data['admin_name'] }}</small>
                                @endif
                            </td>
                            <td>
                                @if($tenant->domains->first())
                                    <a href="http://{{ $tenant->domains->first()->domain }}"
                                       target="_blank"
                                       class="text-decoration-none">
                                        {{ $tenant->domains->first()->domain }}
                                        <i class="fas fa-external-link-alt fa-xs ms-1"></i>
                                    </a>
                                @else
                                    <span class="text-muted">No domain</span>
                                @endif
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
                                            'cancelled' => 'badge-expired',
                                            'expired' => 'badge-expired',
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
                                @if($tenant->subscription && $tenant->subscription->trial_ends_at)
                                    <small>{{ $tenant->subscription->trial_ends_at->format('M d, Y') }}</small>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($tenant->subscription)
                                    <strong>₹{{ number_format($tenant->subscription->mrr, 2) }}</strong>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <small>{{ $tenant->created_at->format('M d, Y') }}</small>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('central.tenants.show', $tenant) }}"
                                       class="btn btn-outline-info"
                                       title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('central.tenants.edit', $tenant) }}"
                                       class="btn btn-outline-primary"
                                       title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>

                                    @if($tenant->subscription && $tenant->subscription->status === 'active')
                                        <form action="{{ route('central.tenants.suspend', $tenant) }}"
                                              method="POST"
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to suspend this tenant?');">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-warning"
                                                    title="Suspend">
                                                <i class="fas fa-ban"></i>
                                            </button>
                                        </form>
                                    @elseif($tenant->subscription && $tenant->subscription->status === 'suspended')
                                        <form action="{{ route('central.tenants.activate', $tenant) }}"
                                              method="POST"
                                              class="d-inline">
                                            @csrf
                                            <button type="submit"
                                                    class="btn btn-outline-success"
                                                    title="Activate">
                                                <i class="fas fa-check-circle"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center text-muted py-5">
                                <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                <h6>No tenants found</h6>
                                <p class="mb-0">Create your first tenant to get started.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($tenants->hasPages())
            <div class="mt-4">
                {{ $tenants->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
