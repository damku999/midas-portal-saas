@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1 class="h3 mb-3"><i class="fas fa-th me-2"></i>Choose Your Plan</h1>
            <p class="text-muted">Upgrade your plan to unlock more features and capacity</p>
        </div>
    </div>

    <!-- All Messages Display -->
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('success') || session('message'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') ?? session('message') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('warning'))
        <div class="alert alert-warning alert-dismissible fade show">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>{{ session('warning') }}</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('info'))
        <div class="alert alert-info alert-dismissible fade show">
            <i class="fas fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Current Plan Info -->
    <div class="alert alert-info mb-4">
        <i class="fas fa-info-circle me-2"></i>
        You are currently on the <strong>{{ $currentSubscription->plan->name }}</strong> plan.
        @if($currentSubscription->onTrial())
            Your trial ends {{ $currentSubscription->trial_ends_at->diffForHumans() }}.
        @endif
    </div>

    <!-- Pricing Plans -->
    <div class="row g-4 mb-4">
        @foreach($plans as $plan)
        <div class="col-lg-4">
            <div class="card shadow {{ $plan->id == $currentSubscription->plan_id ? 'border-primary' : '' }}" style="min-height: 550px;">
                @if($plan->id == $currentSubscription->plan_id)
                    <div class="card-header bg-primary text-white text-center">
                        <strong><i class="fas fa-check-circle me-1"></i>Current Plan</strong>
                    </div>
                @elseif($plan->is_featured)
                    <div class="card-header bg-success text-white text-center">
                        <strong><i class="fas fa-star me-1"></i>Most Popular</strong>
                    </div>
                @endif

                <div class="card-body d-flex flex-column">
                    <div class="text-center mb-4">
                        <h3 class="mb-2">{{ $plan->name }}</h3>
                        @if($plan->description)
                            <p class="text-muted small">{{ $plan->description }}</p>
                        @endif
                        <div class="display-4 fw-bold text-primary">₹{{ number_format($plan->price) }}</div>
                        <small class="text-muted">per month</small>
                    </div>

                    <hr>

                    <ul class="list-unstyled mb-4 flex-grow-1">
                        <li class="mb-2">
                            <i class="fas fa-users text-primary me-2"></i>
                            <strong>{{ $plan->max_users == -1 ? 'Unlimited' : $plan->max_users }}</strong> Users
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-user-tie text-success me-2"></i>
                            <strong>{{ $plan->max_customers == -1 ? 'Unlimited' : number_format($plan->max_customers) }}</strong> Customers
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-database text-info me-2"></i>
                            <strong>{{ $plan->max_storage_gb == -1 ? 'Unlimited' : $plan->max_storage_gb . ' GB' }}</strong> Storage
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-shield-alt text-warning me-2"></i>
                            <strong>Advanced Security</strong>
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-headset text-secondary me-2"></i>
                            <strong>{{ $plan->support_level }}</strong> Support
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-mobile-alt text-primary me-2"></i>
                            <strong>{{ $plan->features['mobile_app'] ?? true ? 'Yes' : 'No' }}</strong> Mobile App Access
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-chart-line text-success me-2"></i>
                            <strong>{{ $plan->features['advanced_reports'] ?? false ? 'Yes' : 'No' }}</strong> Advanced Reports
                        </li>
                        <li class="mb-2">
                            <i class="fas fa-cogs text-info me-2"></i>
                            <strong>{{ $plan->features['api_access'] ?? false ? 'Yes' : 'No' }}</strong> API Access
                        </li>
                    </ul>

                    @if($plan->id == $currentSubscription->plan_id)
                        <button class="btn btn-outline-primary" disabled>
                            <i class="fas fa-check me-1"></i>Current Plan
                        </button>
                    @elseif($plan->price <= $currentSubscription->plan->price)
                        <button class="btn btn-outline-secondary" disabled>
                            Lower Plan
                        </button>
                    @else
                        <a href="{{ route('subscription.upgrade', $plan) }}" class="btn btn-primary">
                            <i class="fas fa-arrow-up me-1"></i>Upgrade to {{ $plan->name }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Features Comparison Table -->
    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0"><i class="fas fa-table me-2"></i>Plan Comparison</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead class="table-light">
                        <tr>
                            <th>Feature</th>
                            @foreach($plans as $plan)
                                <th class="text-center">{{ $plan->name }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Price</strong></td>
                            @foreach($plans as $plan)
                                <td class="text-center">₹{{ number_format($plan->price) }}/month</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Users</strong></td>
                            @foreach($plans as $plan)
                                <td class="text-center">{{ $plan->max_users == -1 ? 'Unlimited' : $plan->max_users }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Customers</strong></td>
                            @foreach($plans as $plan)
                                <td class="text-center">{{ $plan->max_customers == -1 ? 'Unlimited' : number_format($plan->max_customers) }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Storage</strong></td>
                            @foreach($plans as $plan)
                                <td class="text-center">{{ $plan->max_storage_gb == -1 ? 'Unlimited' : $plan->max_storage_gb . ' GB' }}</td>
                            @endforeach
                        </tr>
                        <tr>
                            <td><strong>Support Level</strong></td>
                            @foreach($plans as $plan)
                                <td class="text-center">{{ $plan->support_level }}</td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
