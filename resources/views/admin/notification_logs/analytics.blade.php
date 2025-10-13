@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-12">
            <h2 class="mb-0">
                <i class="fas fa-chart-bar mr-2"></i>Notification Analytics
            </h2>
            <p class="text-muted">
                Analysis from {{ $fromDate->format('d M Y') }} to {{ $toDate->format('d M Y') }}
            </p>
        </div>
    </div>

    <!-- Date Range Filter -->
    <div class="row mb-4">
        <div class="col-md-12">
            <form method="GET" action="{{ route('admin.notification-logs.analytics') }}" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2">From:</label>
                    <input type="date" name="from_date" class="form-control" value="{{ $fromDate->format('Y-m-d') }}">
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2">To:</label>
                    <input type="date" name="to_date" class="form-control" value="{{ $toDate->format('Y-m-d') }}">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Update
                </button>
                <a href="{{ route('admin.notification-logs.index') }}" class="btn btn-secondary ml-2">
                    <i class="fas fa-list"></i> View Logs
                </a>
            </form>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white">
                <div class="card-body">
                    <h5 class="card-title">Total Sent</h5>
                    <h2 class="mb-0">{{ number_format($statistics['total_sent']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white">
                <div class="card-body">
                    <h5 class="card-title">Successful</h5>
                    <h2 class="mb-0">{{ number_format($statistics['successful']) }}</h2>
                    <small>{{ $statistics['success_rate'] }}% success rate</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-danger text-white">
                <div class="card-body">
                    <h5 class="card-title">Failed</h5>
                    <h2 class="mb-0">{{ number_format($statistics['failed']) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white">
                <div class="card-body">
                    <h5 class="card-title">Success Rate</h5>
                    <h2 class="mb-0">{{ $statistics['success_rate'] }}%</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Channel Distribution -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notifications by Channel</h5>
                </div>
                <div class="card-body">
                    <canvas id="channelChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Status Distribution -->
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notifications by Status</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Volume Over Time -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Notification Volume Over Time</h5>
                </div>
                <div class="card-body">
                    <canvas id="volumeChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Channel Performance -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Channel Performance</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Channel</th>
                                    <th>Pending</th>
                                    <th>Sent</th>
                                    <th>Delivered</th>
                                    <th>Read</th>
                                    <th>Failed</th>
                                    <th>Total</th>
                                    <th>Success Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($channelPerformance as $channel => $statuses)
                                @php
                                    $pending = $statuses->where('status', 'pending')->sum('count');
                                    $sent = $statuses->where('status', 'sent')->sum('count');
                                    $delivered = $statuses->where('status', 'delivered')->sum('count');
                                    $read = $statuses->where('status', 'read')->sum('count');
                                    $failed = $statuses->where('status', 'failed')->sum('count');
                                    $total = $statuses->sum('count');
                                    $successful = $sent + $delivered + $read;
                                    $successRate = $total > 0 ? round(($successful / $total) * 100, 2) : 0;
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ ucfirst($channel) }}</strong>
                                    </td>
                                    <td>{{ number_format($pending) }}</td>
                                    <td>{{ number_format($sent) }}</td>
                                    <td>{{ number_format($delivered) }}</td>
                                    <td>{{ number_format($read) }}</td>
                                    <td>{{ number_format($failed) }}</td>
                                    <td><strong>{{ number_format($total) }}</strong></td>
                                    <td>
                                        <span class="badge badge-{{ $successRate >= 90 ? 'success' : ($successRate >= 70 ? 'warning' : 'danger') }}">
                                            {{ $successRate }}%
                                        </span>
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

    <!-- Top Templates -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Most Used Templates</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rank</th>
                                    <th>Template</th>
                                    <th>Type</th>
                                    <th>Usage Count</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($statistics['top_templates'] as $index => $templateData)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>
                                        @if($templateData->template)
                                            {{ $templateData->template->name }}
                                        @else
                                            <span class="text-muted">Unknown</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($templateData->template && $templateData->template->notificationType)
                                            <span class="badge badge-info">
                                                {{ $templateData->template->notificationType->name }}
                                            </span>
                                        @else
                                            <span class="text-muted">-</span>
                                        @endif
                                    </td>
                                    <td>{{ number_format($templateData->count) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Failed Notifications Requiring Attention -->
    @if($failedNotifications->isNotEmpty())
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card border-danger">
                <div class="card-header bg-danger text-white">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Failed Notifications Requiring Attention
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Channel</th>
                                    <th>Recipient</th>
                                    <th>Type</th>
                                    <th>Failed At</th>
                                    <th>Retries</th>
                                    <th>Error</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($failedNotifications as $log)
                                <tr>
                                    <td>{{ $log->id }}</td>
                                    <td>
                                        <i class="{{ $log->channel_icon }}"></i>
                                        {{ ucfirst($log->channel) }}
                                    </td>
                                    <td><small>{{ $log->recipient }}</small></td>
                                    <td>
                                        @if($log->notificationType)
                                            <span class="badge badge-secondary">
                                                {{ $log->notificationType->name }}
                                            </span>
                                        @endif
                                    </td>
                                    <td><small>{{ $log->created_at->format('Y-m-d H:i') }}</small></td>
                                    <td>
                                        <span class="badge badge-warning">{{ $log->retry_count }}/3</span>
                                    </td>
                                    <td>
                                        <small class="text-danger">{{ Str::limit($log->error_message, 50) }}</small>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.notification-logs.show', $log) }}" class="btn btn-sm btn-info">
                                            View
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
</div>

@push('scripts')
<script src="{{ cdn_url('cdn_chartjs_url') }}"></script>
<script>
// Channel Distribution Chart
const channelCtx = document.getElementById('channelChart').getContext('2d');
new Chart(channelCtx, {
    type: 'pie',
    data: {
        labels: {!! json_encode(array_keys($statistics['channel_counts'])) !!},
        datasets: [{
            data: {!! json_encode(array_values($statistics['channel_counts'])) !!},
            backgroundColor: [
                'rgba(54, 162, 235, 0.8)',
                'rgba(255, 99, 132, 0.8)',
                'rgba(255, 206, 86, 0.8)',
                'rgba(75, 192, 192, 0.8)',
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: {!! json_encode(array_keys($statistics['status_counts'])) !!},
        datasets: [{
            data: {!! json_encode(array_values($statistics['status_counts'])) !!},
            backgroundColor: [
                'rgba(255, 206, 86, 0.8)',
                'rgba(54, 162, 235, 0.8)',
                'rgba(75, 192, 192, 0.8)',
                'rgba(153, 102, 255, 0.8)',
                'rgba(255, 99, 132, 0.8)',
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true
    }
});

// Volume Over Time Chart
const volumeCtx = document.getElementById('volumeChart').getContext('2d');
new Chart(volumeCtx, {
    type: 'line',
    data: {
        labels: {!! json_encode($dailyVolume->pluck('date')->toArray()) !!},
        datasets: [{
            label: 'Notifications',
            data: {!! json_encode($dailyVolume->pluck('count')->toArray()) !!},
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            tension: 0.1,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>
@endpush
@endsection
