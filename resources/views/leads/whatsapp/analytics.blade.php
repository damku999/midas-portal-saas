@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1"><i class="fas fa-chart-line text-primary"></i> WhatsApp Analytics Dashboard</h4>
            <p class="text-muted mb-0">Performance metrics and insights for lead WhatsApp campaigns</p>
        </div>
        <div>
            <a href="{{ route('leads.whatsapp.campaigns.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to Campaigns
            </a>
        </div>
    </div>

    <!-- Date Filter -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('leads.whatsapp.analytics') }}" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small">From Date</label>
                    <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $filters['date_from'] }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small">To Date</label>
                    <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $filters['date_to'] }}">
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">
                        <i class="fas fa-filter"></i> Apply Filter
                    </button>
                    <a href="{{ route('leads.whatsapp.analytics') }}" class="btn btn-outline-secondary btn-sm">
                        <i class="fas fa-redo"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Overview Cards -->
    <div class="row mb-4">
        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Messages</p>
                            <h2 class="mb-0">{{ number_format($analytics['total_messages']) }}</h2>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="fas fa-envelope fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Sent Successfully</p>
                            <h2 class="mb-0 text-success">{{ number_format($analytics['sent_messages']) }}</h2>
                            <small class="text-muted">{{ $analytics['sent_messages'] > 0 ? round(($analytics['sent_messages'] / $analytics['total_messages']) * 100, 1) : 0 }}%</small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-circle fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Delivered</p>
                            <h2 class="mb-0 text-info">{{ number_format($analytics['delivered_messages']) }}</h2>
                            <small class="text-muted">Delivery Rate: {{ $analytics['delivery_rate'] }}%</small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="fas fa-check-double fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-3 col-md-6 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Failed</p>
                            <h2 class="mb-0 text-danger">{{ number_format($analytics['failed_messages']) }}</h2>
                            <small class="text-muted">Failure Rate: {{ $analytics['failure_rate'] }}%</small>
                        </div>
                        <div class="bg-danger bg-opacity-10 p-3 rounded">
                            <i class="fas fa-times-circle fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-lg-8 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-chart-line"></i> Message Delivery Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="deliveryTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-4 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-chart-pie"></i> Status Distribution</h6>
                </div>
                <div class="card-body">
                    <canvas id="statusPieChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Campaigns & Top Templates -->
    <div class="row">
        <div class="col-lg-7 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-bullhorn"></i> Recent Campaigns</h6>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Campaign</th>
                                    <th>Status</th>
                                    <th class="text-center">Sent</th>
                                    <th class="text-center">Success Rate</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentCampaigns as $campaign)
                                <tr>
                                    <td>
                                        <a href="{{ route('leads.whatsapp.campaigns.show', $campaign->id) }}" class="text-decoration-none">
                                            {{ Str::limit($campaign->name, 30) }}
                                        </a>
                                    </td>
                                    <td>
                                        @php
                                            $statusColors = ['draft' => 'secondary', 'scheduled' => 'info', 'active' => 'warning', 'completed' => 'success', 'paused' => 'dark'];
                                        @endphp
                                        <span class="badge bg-{{ $statusColors[$campaign->status] ?? 'secondary' }}">{{ ucfirst($campaign->status) }}</span>
                                    </td>
                                    <td class="text-center"><strong>{{ number_format($campaign->sent_count) }}</strong></td>
                                    <td class="text-center">
                                        @php
                                            $rate = $campaign->getSuccessRate();
                                            $rateColor = $rate >= 80 ? 'success' : ($rate >= 50 ? 'warning' : 'danger');
                                        @endphp
                                        <span class="badge bg-{{ $rateColor }}">{{ $rate }}%</span>
                                    </td>
                                    <td><small>{{ $campaign->created_at->format('d M Y') }}</small></td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted py-4">No campaigns yet</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-star"></i> Top Templates</h6>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @forelse($topTemplates as $template)
                        <div class="list-group-item d-flex justify-content-between align-items-center border-0">
                            <div>
                                <strong>{{ $template->name }}</strong>
                                <br><small class="text-muted">{{ ucfirst($template->category) }}</small>
                            </div>
                            <span class="badge bg-primary rounded-pill">{{ $template->usage_count }} uses</span>
                        </div>
                        @empty
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="mb-0">No templates created yet</p>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-info-circle"></i> Additional Metrics</h6>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h4 class="text-primary">{{ number_format($analytics['pending_messages']) }}</h4>
                            <p class="text-muted mb-0">Pending Messages</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-info">{{ number_format($analytics['messages_with_attachment']) }}</h4>
                            <p class="text-muted mb-0">With Attachments</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-success">{{ $analytics['delivery_rate'] }}%</h4>
                            <p class="text-muted mb-0">Avg Delivery Rate</p>
                        </div>
                        <div class="col-md-3">
                            <h4 class="text-danger">{{ $analytics['failure_rate'] }}%</h4>
                            <p class="text-muted mb-0">Avg Failure Rate</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Delivery Trend Chart (Line Chart)
const trendCtx = document.getElementById('deliveryTrendChart').getContext('2d');
const trendChart = new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
        datasets: [{
            label: 'Sent',
            data: [120, 150, 180, 140, 200, 160, 190],
            borderColor: 'rgb(75, 192, 192)',
            backgroundColor: 'rgba(75, 192, 192, 0.1)',
            tension: 0.4
        }, {
            label: 'Delivered',
            data: [110, 140, 170, 130, 190, 150, 180],
            borderColor: 'rgb(54, 162, 235)',
            backgroundColor: 'rgba(54, 162, 235, 0.1)',
            tension: 0.4
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'top',
            }
        }
    }
});

// Status Pie Chart
const pieCtx = document.getElementById('statusPieChart').getContext('2d');
const pieChart = new Chart(pieCtx, {
    type: 'doughnut',
    data: {
        labels: ['Sent', 'Delivered', 'Failed', 'Pending'],
        datasets: [{
            data: [
                {{ $analytics['sent_messages'] }},
                {{ $analytics['delivered_messages'] }},
                {{ $analytics['failed_messages'] }},
                {{ $analytics['pending_messages'] }}
            ],
            backgroundColor: [
                'rgb(75, 192, 192)',
                'rgb(54, 162, 235)',
                'rgb(255, 99, 132)',
                'rgb(255, 205, 86)'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom',
            }
        }
    }
});
</script>
@endpush
@endsection
