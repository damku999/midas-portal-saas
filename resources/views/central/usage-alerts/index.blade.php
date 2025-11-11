@extends('central.layouts.app')

@section('title', 'Usage Alerts Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Page Header -->
    <div class="page-header bg-gradient-primary text-white mb-4">
        <div class="container-fluid">
            <div class="row align-items-center py-4">
                <div class="col-md-8">
                    <h1 class="mb-0">
                        <i class="fas fa-chart-line me-2"></i>
                        Usage Alerts Dashboard
                    </h1>
                    <p class="mb-0 mt-2 opacity-75">Monitor tenant resource usage and alerts across the platform</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <button class="btn btn-light btn-sm" onclick="refreshDashboard()">
                        <i class="fas fa-sync-alt me-1"></i> Refresh
                    </button>
                    <a href="{{ route('central.tenants.index') }}" class="btn btn-outline-light btn-sm">
                        <i class="fas fa-building me-1"></i> View Tenants
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-warning bg-opacity-10 p-3 rounded">
                            <i class="fas fa-exclamation-triangle text-warning fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Warning Alerts</h6>
                            <h3 class="mb-0">{{ $stats['tenants_with_warnings'] }}</h3>
                            <small class="text-muted">Tenants at 80%+</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-danger bg-opacity-10 p-3 rounded">
                            <i class="fas fa-exclamation-circle text-danger fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Critical Alerts</h6>
                            <h3 class="mb-0">{{ $stats['tenants_with_critical'] }}</h3>
                            <small class="text-muted">Tenants at 90%+</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-dark bg-opacity-10 p-3 rounded">
                            <i class="fas fa-ban text-dark fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Exceeded Limits</h6>
                            <h3 class="mb-0 text-danger">{{ $stats['tenants_exceeded'] }}</h3>
                            <small class="text-muted">Tenants at 100%</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex align-items-center">
                        <div class="flex-shrink-0 bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-bell text-info fa-2x"></i>
                        </div>
                        <div class="flex-grow-1 ms-3">
                            <h6 class="text-muted mb-1">Total Active Alerts</h6>
                            <h3 class="mb-0">{{ $stats['total_active_alerts'] }}</h3>
                            <small class="text-muted">Across all resources</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Usage Analytics Chart -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <div class="d-flex align-items-center justify-content-between">
                        <h5 class="mb-0">
                            <i class="fas fa-chart-area me-2 text-primary"></i>
                            Usage Trends (Last 30 Days)
                        </h5>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-secondary" onclick="loadAnalytics(7)">7 Days</button>
                            <button class="btn btn-primary" onclick="loadAnalytics(30)">30 Days</button>
                            <button class="btn btn-outline-secondary" onclick="loadAnalytics(90)">90 Days</button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <canvas id="usageChart" height="80"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Resource Breakdown -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-users text-primary fa-3x mb-3"></i>
                    <h6 class="text-muted">User Alerts</h6>
                    <h3 class="mb-0">{{ $stats['alerts_by_resource']['users'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-user-tie text-success fa-3x mb-3"></i>
                    <h6 class="text-muted">Customer Alerts</h6>
                    <h3 class="mb-0">{{ $stats['alerts_by_resource']['customers'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <i class="fas fa-database text-warning fa-3x mb-3"></i>
                    <h6 class="text-muted">Storage Alerts</h6>
                    <h3 class="mb-0">{{ $stats['alerts_by_resource']['storage'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('central.usage-alerts.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="active" {{ request('status', 'active') === 'active' ? 'selected' : '' }}>Active Alerts</option>
                        <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>All Alerts</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Threshold Level</label>
                    <select name="threshold" class="form-select">
                        <option value="">All Levels</option>
                        <option value="warning" {{ request('threshold') === 'warning' ? 'selected' : '' }}>⚠️ Warning (80%)</option>
                        <option value="critical" {{ request('threshold') === 'critical' ? 'selected' : '' }}>🚨 Critical (90%)</option>
                        <option value="exceeded" {{ request('threshold') === 'exceeded' ? 'selected' : '' }}>⛔ Exceeded (100%)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Resource Type</label>
                    <select name="resource" class="form-select">
                        <option value="">All Resources</option>
                        <option value="users" {{ request('resource') === 'users' ? 'selected' : '' }}>Staff Users</option>
                        <option value="customers" {{ request('resource') === 'customers' ? 'selected' : '' }}>Customers</option>
                        <option value="storage" {{ request('resource') === 'storage' ? 'selected' : '' }}>Storage Space</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">&nbsp;</label>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary flex-grow-1">
                            <i class="fas fa-filter me-1"></i> Filter
                        </button>
                        <a href="{{ route('central.usage-alerts.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Alerts Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">
                <i class="fas fa-list me-2"></i>
                Active Alerts
                <span class="badge bg-secondary ms-2">{{ $alerts->total() }}</span>
            </h5>
        </div>
        <div class="card-body p-0">
            @if($alerts->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th>Tenant</th>
                                <th>Resource</th>
                                <th>Threshold</th>
                                <th>Usage</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($alerts as $alert)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div>
                                                <strong>{{ $alert->tenant->data['company_name'] ?? 'Unknown' }}</strong><br>
                                                <small class="text-muted">{{ $alert->tenant->domains->first()?->domain }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-{{ $alert->resource_type === 'users' ? 'users' : ($alert->resource_type === 'customers' ? 'user-tie' : 'database') }} me-1"></i>
                                        {{ $alert->resource_type_display }}
                                    </td>
                                    <td>
                                        @if($alert->threshold_level === 'warning')
                                            <span class="badge bg-warning">⚠️ Warning</span>
                                        @elseif($alert->threshold_level === 'critical')
                                            <span class="badge bg-danger">🚨 Critical</span>
                                        @else
                                            <span class="badge bg-dark">⛔ Exceeded</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>{{ $alert->usage_display }}</div>
                                        <div class="progress" style="height: 5px;">
                                            <div class="progress-bar bg-{{ $alert->severity }}"
                                                 style="width: {{ min($alert->usage_percentage, 100) }}%">
                                            </div>
                                        </div>
                                        <small class="text-muted">{{ round($alert->usage_percentage, 1) }}%</small>
                                    </td>
                                    <td>
                                        @if($alert->alert_status === 'pending')
                                            <span class="badge bg-secondary">Pending</span>
                                        @elseif($alert->alert_status === 'sent')
                                            <span class="badge bg-info">Sent</span>
                                        @elseif($alert->alert_status === 'acknowledged')
                                            <span class="badge bg-primary">Acknowledged</span>
                                        @else
                                            <span class="badge bg-success">Resolved</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ $alert->created_at->format('M d, Y') }}</small><br>
                                        <small class="text-muted">{{ $alert->created_at->diffForHumans() }}</small>
                                    </td>
                                    <td>
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('central.usage-alerts.show', $alert) }}"
                                               class="btn btn-outline-primary btn-sm"
                                               title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @if($alert->alert_status !== 'resolved')
                                                <button type="button"
                                                        class="btn btn-outline-success btn-sm"
                                                        onclick="resolveAlert({{ $alert->id }})"
                                                        title="Resolve">
                                                    <i class="fas fa-check"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer bg-white">
                    {{ $alerts->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-check-circle text-success fa-3x mb-3"></i>
                    <h5>All Clear!</h5>
                    <p class="text-muted">No active usage alerts at this time.</p>
                </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
let usageChart = null;

// Initialize chart on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAnalytics(30);
});

function loadAnalytics(days) {
    fetch(`/midas-admin/usage-alerts/analytics?days=${days}`)
        .then(response => response.json())
        .then(data => {
            renderChart(data);
        })
        .catch(error => {
            console.error('Error loading analytics:', error);
        });
}

function renderChart(data) {
    const ctx = document.getElementById('usageChart').getContext('2d');

    // Destroy existing chart if it exists
    if (usageChart) {
        usageChart.destroy();
    }

    usageChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Total Users',
                    data: data.datasets.users,
                    borderColor: '#3490dc',
                    backgroundColor: 'rgba(52, 144, 220, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Total Customers',
                    data: data.datasets.customers,
                    borderColor: '#38c172',
                    backgroundColor: 'rgba(56, 193, 114, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Storage (GB)',
                    data: data.datasets.storage,
                    borderColor: '#f6993f',
                    backgroundColor: 'rgba(246, 153, 63, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0
                    }
                }
            }
        }
    });
}

function resolveAlert(alertId) {
    if (!confirm('Are you sure you want to resolve this alert?')) {
        return;
    }

    fetch(`/midas-admin/usage-alerts/${alertId}/resolve`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ notes: 'Manually resolved by admin' })
    })
    .then(response => response.json())
    .then(data => {
        location.reload();
    })
    .catch(error => {
        alert('Error resolving alert');
        console.error(error);
    });
}

function refreshDashboard() {
    location.reload();
}
</script>
@endpush
@endsection
