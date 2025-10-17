@extends('layouts.app')

@section('title', 'Security Dashboard')

@section('content')
<div class="container-fluid">
    <!-- Security Metrics Overview -->
    <div class="row mb-4">
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-shield-alt text-primary"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Total Events</p>
                                <h4 class="card-title">{{ number_format($metrics['total_events']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-exclamation-triangle text-warning"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Suspicious Events</p>
                                <h4 class="card-title">{{ number_format($metrics['suspicious_events']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-ban text-danger"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Failed Logins</p>
                                <h4 class="card-title">{{ number_format($metrics['failed_logins']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-lg-6 col-md-6 col-sm-6">
            <div class="card card-stats">
                <div class="card-body">
                    <div class="row">
                        <div class="col-5">
                            <div class="icon-big text-center">
                                <i class="fas fa-globe text-info"></i>
                            </div>
                        </div>
                        <div class="col-7">
                            <div class="numbers">
                                <p class="card-category">Unique IPs</p>
                                <h4 class="card-title">{{ number_format($metrics['unique_ips']) }}</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Security Analytics Charts -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title"><i class="fas fa-chart-line"></i> Security Analytics</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <canvas id="eventCategoryChart" height="200"></canvas>
                        </div>
                        <div class="col-md-6">
                            <canvas id="riskDistributionChart" height="200"></canvas>
                        </div>
                    </div>
                    <div class="mt-4">
                        <canvas id="hourlyActivityChart" height="100"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Risk Factors -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-exclamation-circle"></i> Top Risk Factors</h5>
                </div>
                <div class="card-body">
                    @if(!empty($metrics['top_risk_factors']))
                        @foreach($metrics['top_risk_factors'] as $factor => $count)
                            <div class="mb-3">
                                <div class="d-flex justify-content-between">
                                    <span class="text-capitalize">{{ str_replace('_', ' ', $factor) }}</span>
                                    <span class="badge badge-warning">{{ $count }}</span>
                                </div>
                                <div class="progress" style="height: 5px;">
                                    <div class="progress-bar bg-warning"
                                         style="width: {{ ($count / max($metrics['top_risk_factors'])) * 100 }}%"></div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted">No risk factors detected</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-4">
        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-clock"></i> Recent Activity (24h)</h5>
                    <a href="{{ route('security.audit-logs') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Event</th>
                                    <th>Actor</th>
                                    <th>Risk</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentActivity as $activity)
                                    <tr>
                                        <td>
                                            <small>{{ $activity->occurred_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-secondary">{{ $activity->event }}</span>
                                        </td>
                                        <td>
                                            @if($activity->actor)
                                                {{ class_basename($activity->actor_type) }}#{{ $activity->actor_id }}
                                            @else
                                                System
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $activity->risk_badge_class }}">
                                                {{ $activity->risk_level }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No recent activity</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Suspicious Activity -->
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title"><i class="fas fa-exclamation-triangle"></i> Suspicious Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Time</th>
                                    <th>Event</th>
                                    <th>IP Address</th>
                                    <th>Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($suspiciousActivity as $activity)
                                    <tr>
                                        <td>
                                            <small>{{ $activity->occurred_at->diffForHumans() }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">{{ $activity->event }}</span>
                                        </td>
                                        <td>
                                            <code>{{ $activity->ip_address }}</code>
                                        </td>
                                        <td>
                                            <span class="badge badge-danger">{{ $activity->risk_score }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No suspicious activity detected</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ cdn_url('cdn_chartjs_url') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Event Category Chart
    const eventCategoryCtx = document.getElementById('eventCategoryChart').getContext('2d');
    new Chart(eventCategoryCtx, {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($metrics['events_by_category'])),
            datasets: [{
                data: @json(array_values($metrics['events_by_category'])),
                backgroundColor: {!! json_encode(chart_colors_array()) !!}
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Events by Category'
                }
            }
        }
    });

    // Risk Distribution Chart
    const riskDistCtx = document.getElementById('riskDistributionChart').getContext('2d');
    new Chart(riskDistCtx, {
        type: 'doughnut',
        data: {
            labels: @json(array_keys($metrics['risk_distribution'])),
            datasets: [{
                data: @json(array_values($metrics['risk_distribution'])),
                backgroundColor: {!! json_encode(chart_colors_array()) !!}
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Risk Distribution'
                }
            }
        }
    });

    // Hourly Activity Chart
    const hourlyCtx = document.getElementById('hourlyActivityChart').getContext('2d');
    const hourlyData = @json($metrics['hourly_activity']);
    const hours = Array.from({length: 24}, (_, i) => i);
    const activities = hours.map(hour => hourlyData[hour] || 0);

    new Chart(hourlyCtx, {
        type: 'line',
        data: {
            labels: hours.map(h => h + ':00'),
            datasets: [{
                label: 'Activity Count',
                data: activities,
                borderColor: '{{ chart_color("primary") }}',
                backgroundColor: '{{ preg_replace("/0\.\d+\)/", "0.1)", chart_color("primary")) }}',
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                title: {
                    display: true,
                    text: 'Hourly Activity Pattern'
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });

    // Auto-refresh every 5 minutes
    setInterval(function() {
        location.reload();
    }, 300000);
});
</script>
@endsection