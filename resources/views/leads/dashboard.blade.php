@extends('layouts.app')

@section('title', 'Lead Dashboard')

@section('content')

    <div class="container-fluid">

        {{-- Alert Messages --}}
        @include('common.alert')

        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Lead Dashboard</h1>
            <a href="{{ route('leads.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                <i class="fas fa-plus fa-sm text-white-50"></i> Create New Lead
            </a>
        </div>

        <!-- Overview Statistics Cards -->
        <div class="row">
            <!-- Total Leads -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                    Total Leads
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $statistics['total_leads'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-users fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Active Leads -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                    Active Leads
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $statistics['active_leads'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Converted Leads -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Converted
                                </div>
                                <div class="row no-gutters align-items-center">
                                    <div class="col-auto">
                                        <div class="h5 mb-0 mr-3 font-weight-bold text-gray-800">
                                            {{ $statistics['converted_leads'] }}
                                        </div>
                                    </div>
                                    <div class="col">
                                        <div class="progress progress-sm mr-2">
                                            <div class="progress-bar bg-info" role="progressbar"
                                                 style="width: {{ $statistics['conversion_rate'] }}%"
                                                 aria-valuenow="{{ $statistics['conversion_rate'] }}" aria-valuemin="0" aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="text-xs text-muted mt-1">{{ $statistics['conversion_rate'] }}% conversion rate</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-trophy fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Lost Leads -->
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Lost Leads
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $statistics['lost_leads'] }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Personal Stats Row -->
        <div class="row">
            <!-- My Leads -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                    My Assigned Leads
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $myLeads }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-user-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Follow-ups Due -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                    Follow-ups Due
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $myFollowUps }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-calendar-check fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Overdue Follow-ups -->
            <div class="col-xl-4 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                    Overdue Follow-ups
                                </div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ $myOverdue }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent & Upcoming Activities -->
        <div class="row">
            <!-- Recent Activities -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Recent Activities</h6>
                    </div>
                    <div class="card-body">
                        @if($recentActivities && $recentActivities->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Lead</th>
                                            <th>Type</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($recentActivities as $activity)
                                        <tr>
                                            <td>{{ $activity->created_at->format('d/m H:i') }}</td>
                                            <td>
                                                <a href="{{ route('leads.show', $activity->lead_id) }}">
                                                    {{ $activity->lead->name ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-primary">{{ ucfirst($activity->activity_type) }}</span>
                                            </td>
                                            <td>{{ Str::limit($activity->notes ?? 'N/A', 30) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center mb-0">No recent activities</p>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Upcoming Activities -->
            <div class="col-xl-6 col-lg-6">
                <div class="card shadow mb-4">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">Upcoming Activities</h6>
                    </div>
                    <div class="card-body">
                        @if($upcomingActivities && $upcomingActivities->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Scheduled</th>
                                            <th>Lead</th>
                                            <th>Type</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($upcomingActivities as $activity)
                                        <tr>
                                            <td>{{ $activity->scheduled_at ? \Carbon\Carbon::parse($activity->scheduled_at)->format('d/m H:i') : 'N/A' }}</td>
                                            <td>
                                                <a href="{{ route('leads.show', $activity->lead_id) }}">
                                                    {{ $activity->lead->name ?? 'N/A' }}
                                                </a>
                                            </td>
                                            <td>
                                                <span class="badge badge-info">{{ ucfirst($activity->activity_type) }}</span>
                                            </td>
                                            <td>{{ Str::limit($activity->notes ?? 'N/A', 30) }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <p class="text-muted text-center mb-0">No upcoming activities</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@section('scripts')
@endsection
