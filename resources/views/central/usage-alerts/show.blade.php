@extends('central.layouts.app')

@section('title', 'Alert Details')

@section('content')
<div class="container-fluid">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('central.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('central.usage-alerts.index') }}">Usage Alerts</a></li>
            <li class="breadcrumb-item active">Alert #{{ $alert->id }}</li>
        </ol>
    </nav>

    <!-- Alert Header -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h3 class="mb-2">
                        @if($alert->threshold_level === 'warning')
                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                        @elseif($alert->threshold_level === 'critical')
                            <i class="fas fa-exclamation-circle text-danger me-2"></i>
                        @else
                            <i class="fas fa-ban text-dark me-2"></i>
                        @endif
                        {{ $alert->resource_type_display }} Alert
                    </h3>
                    <p class="text-muted mb-0">
                        <strong>{{ $alert->tenant->data['company_name'] ?? 'Unknown Company' }}</strong>
                        <span class="mx-2">•</span>
                        {{ $alert->tenant->domains->first()?->domain }}
                    </p>
                </div>
                <div class="col-md-4 text-md-end">
                    @if($alert->threshold_level === 'warning')
                        <span class="badge bg-warning fs-6 px-3 py-2">⚠️ Warning (80%)</span>
                    @elseif($alert->threshold_level === 'critical')
                        <span class="badge bg-danger fs-6 px-3 py-2">🚨 Critical (90%)</span>
                    @else
                        <span class="badge bg-dark fs-6 px-3 py-2">⛔ Exceeded (100%)</span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Alert Details -->
        <div class="col-md-8">
            <!-- Usage Information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i>Usage Details</h5>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Current Usage</h6>
                            <h2 class="mb-0">{{ $alert->usage_display }}</h2>
                            <div class="progress mt-3" style="height: 25px;">
                                <div class="progress-bar bg-{{ $alert->severity }}"
                                     style="width: {{ min($alert->usage_percentage, 100) }}%">
                                    <strong>{{ round($alert->usage_percentage, 1) }}%</strong>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="text-muted mb-3">Plan Information</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td><strong>Plan:</strong></td>
                                    <td>{{ $alert->tenant->subscription->plan->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Limit:</strong></td>
                                    <td>{{ $alert->limit_value }} {{ $alert->resource_type === 'storage' ? 'GB' : $alert->resource_type }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Available:</strong></td>
                                    <td>{{ max(0, $alert->limit_value - $alert->current_usage) }} remaining</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($alert->threshold_level === 'exceeded')
                        @php
                            $gracePeriodEnd = $alert->created_at->addDays(3);
                            $daysRemaining = max(0, now()->diffInDays($gracePeriodEnd, false));
                        @endphp
                        <div class="alert alert-danger">
                            <h6 class="alert-heading"><i class="fas fa-clock me-2"></i>Grace Period</h6>
                            <p class="mb-0">
                                @if($daysRemaining > 0)
                                    <strong>{{ $daysRemaining }} days remaining</strong> until resource creation is restricted.
                                @else
                                    Grace period has <strong>expired</strong>. New {{ strtolower($alert->resource_type_display) }} creation is now restricted.
                                @endif
                            </p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Current Tenant Usage (All Resources) -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-tachometer-alt me-2"></i>Current Tenant Usage</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        @foreach(['users', 'customers', 'storage'] as $resource)
                            @php
                                $limit = $currentUsage['limits'][$resource];
                                $current = $currentUsage['usage'][$resource];
                                $percentage = $currentUsage['limits'][$resource]['percentage'] ?? 0;
                            @endphp
                            <div class="col-md-4 mb-3">
                                <h6 class="text-muted">{{ ucfirst($resource) }}</h6>
                                <h4>{{ $limit['current'] }} / {{ $limit['max'] === -1 ? '∞' : $limit['max'] }}</h4>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar @if($percentage >= 90) bg-danger @elseif($percentage >= 80) bg-warning @else bg-success @endif"
                                         style="width: {{ min($percentage, 100) }}%">
                                    </div>
                                </div>
                                <small class="text-muted">{{ round($percentage, 1) }}%</small>
                            </div>
                        @endforeach
                    </div>

                    @if(!empty($currentUsage['warnings']))
                        <hr>
                        <div class="alert alert-warning mb-0">
                            <h6 class="alert-heading">Active Warnings</h6>
                            <ul class="mb-0">
                                @foreach($currentUsage['warnings'] as $warning)
                                    <li>{{ $warning['message'] }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Alert History -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-history me-2"></i>Alert History ({{ $alert->resource_type_display }})</h5>
                </div>
                <div class="card-body p-0">
                    @if($alertHistory->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Threshold</th>
                                        <th>Usage</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($alertHistory as $historyAlert)
                                        <tr class="{{ $historyAlert->id === $alert->id ? 'table-active' : '' }}">
                                            <td>
                                                {{ $historyAlert->created_at->format('M d, Y H:i') }}<br>
                                                <small class="text-muted">{{ $historyAlert->created_at->diffForHumans() }}</small>
                                            </td>
                                            <td>
                                                @if($historyAlert->threshold_level === 'warning')
                                                    <span class="badge bg-warning">Warning</span>
                                                @elseif($historyAlert->threshold_level === 'critical')
                                                    <span class="badge bg-danger">Critical</span>
                                                @else
                                                    <span class="badge bg-dark">Exceeded</span>
                                                @endif
                                            </td>
                                            <td>
                                                {{ round($historyAlert->usage_percentage, 1) }}%
                                                <small class="text-muted">({{ $historyAlert->usage_display }})</small>
                                            </td>
                                            <td>
                                                @if($historyAlert->alert_status === 'resolved')
                                                    <span class="badge bg-success">Resolved</span>
                                                @else
                                                    <span class="badge bg-info">{{ ucfirst($historyAlert->alert_status) }}</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4">
                            <p class="text-muted mb-0">No alert history available</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- Alert Status -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Alert Status</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-3">
                        <tr>
                            <td><strong>Status:</strong></td>
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
                        </tr>
                        <tr>
                            <td><strong>Created:</strong></td>
                            <td>{{ $alert->created_at->format('M d, Y H:i') }}</td>
                        </tr>
                        @if($alert->sent_at)
                            <tr>
                                <td><strong>Sent:</strong></td>
                                <td>{{ $alert->sent_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($alert->acknowledged_at)
                            <tr>
                                <td><strong>Acknowledged:</strong></td>
                                <td>{{ $alert->acknowledged_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @endif
                        @if($alert->resolved_at)
                            <tr>
                                <td><strong>Resolved:</strong></td>
                                <td>{{ $alert->resolved_at->format('M d, Y H:i') }}</td>
                            </tr>
                        @endif
                    </table>

                    @if($alert->notification_channels)
                        <div class="mb-3">
                            <strong>Notifications Sent:</strong><br>
                            @foreach($alert->notification_channels as $channel)
                                <span class="badge bg-secondary me-1">{{ ucfirst($channel) }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if($alert->notes)
                        <div class="alert alert-light">
                            <strong>Notes:</strong><br>
                            {{ $alert->notes }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- Actions -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Actions</h5>
                </div>
                <div class="card-body">
                    @if($alert->alert_status !== 'acknowledged')
                        <form action="{{ route('central.usage-alerts.acknowledge', $alert) }}" method="POST" class="mb-2">
                            @csrf
                            <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Add notes (optional)"></textarea>
                            <button type="submit" class="btn btn-primary btn-sm w-100">
                                <i class="fas fa-check me-1"></i> Acknowledge Alert
                            </button>
                        </form>
                    @endif

                    @if($alert->alert_status !== 'resolved')
                        <form action="{{ route('central.usage-alerts.resolve', $alert) }}" method="POST">
                            @csrf
                            <textarea name="notes" class="form-control form-control-sm mb-2" rows="2" placeholder="Resolution notes (optional)"></textarea>
                            <button type="submit" class="btn btn-success btn-sm w-100">
                                <i class="fas fa-check-circle me-1"></i> Resolve Alert
                            </button>
                        </form>
                    @endif

                    <hr>

                    <a href="{{ route('central.tenants.show', $alert->tenant) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                        <i class="fas fa-building me-1"></i> View Tenant
                    </a>

                    <a href="{{ route('central.usage-alerts.index') }}" class="btn btn-outline-secondary btn-sm w-100">
                        <i class="fas fa-arrow-left me-1"></i> Back to Alerts
                    </a>
                </div>
            </div>

            <!-- Tenant Information -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><i class="fas fa-building me-2"></i>Tenant Information</h5>
                </div>
                <div class="card-body">
                    <table class="table table-sm table-borderless">
                        <tr>
                            <td><strong>Company:</strong></td>
                            <td>{{ $alert->tenant->data['company_name'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Domain:</strong></td>
                            <td>{{ $alert->tenant->domains->first()?->domain }}</td>
                        </tr>
                        <tr>
                            <td><strong>Email:</strong></td>
                            <td>{{ $alert->tenant->data['email'] ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Plan:</strong></td>
                            <td>{{ $alert->tenant->subscription->plan->name ?? 'N/A' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Status:</strong></td>
                            <td>
                                <span class="badge bg-{{ $alert->tenant->subscription->status === 'active' ? 'success' : 'warning' }}">
                                    {{ ucfirst($alert->tenant->subscription->status ?? 'Unknown') }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
