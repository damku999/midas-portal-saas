@props(['subscription'])

@php
    $daysRemaining = $subscription->trialDaysRemaining();
    $isUrgent = $daysRemaining <= 3;
    $barColor = $daysRemaining <= 3 ? 'danger' : ($daysRemaining <= 7 ? 'warning' : 'info');
@endphp

@if($subscription->onTrial())
<div class="alert alert-{{ $barColor }} alert-dismissible fade show border-{{ $barColor }} mb-3" role="alert">
    <div class="d-flex align-items-center">
        <div class="flex-shrink-0 me-3">
            @if($isUrgent)
                <i class="bi bi-exclamation-triangle-fill" style="font-size: 2rem;"></i>
            @else
                <i class="bi bi-clock-history" style="font-size: 2rem;"></i>
            @endif
        </div>
        <div class="flex-grow-1">
            <h5 class="alert-heading mb-1">
                @if($isUrgent)
                    ⚠️ Trial Ending Soon!
                @else
                    🎉 You're on a Trial
                @endif
            </h5>
            <p class="mb-2">
                @if($daysRemaining == 1)
                    Your trial expires <strong>tomorrow</strong> ({{ $subscription->trial_ends_at->format('M d, Y \a\t h:i A') }}).
                @elseif($daysRemaining == 0)
                    Your trial expires <strong>today</strong>!
                @else
                    You have <strong>{{ $daysRemaining }} days</strong> remaining in your trial (expires {{ $subscription->trial_ends_at->format('M d, Y') }}).
                @endif
            </p>
            <p class="mb-0 small">
                Don't lose access to your data and features. Upgrade now to continue without interruption.
            </p>
        </div>
        <div class="flex-shrink-0 ms-3">
            <a href="{{ route('subscription.plans') }}" class="btn btn-{{ $barColor }} btn-sm">
                <i class="bi bi-arrow-up-circle me-1"></i> Upgrade Now
            </a>
        </div>
    </div>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
</div>

{{-- Progress bar showing trial time remaining --}}
@php
    $totalTrialDays = now()->diffInDays($subscription->starts_at);
    $daysUsed = $totalTrialDays - $daysRemaining;
    $percentage = $totalTrialDays > 0 ? ($daysUsed / $totalTrialDays) * 100 : 0;
@endphp

<div class="card mb-3 border-{{ $barColor }}">
    <div class="card-body py-2">
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted">Trial Progress</small>
            <small class="text-muted">{{ $daysRemaining }} of {{ $totalTrialDays }} days remaining</small>
        </div>
        <div class="progress" style="height: 8px;">
            <div class="progress-bar bg-{{ $barColor }}" role="progressbar"
                 style="width: {{ $percentage }}%;"
                 aria-valuenow="{{ $percentage }}"
                 aria-valuemin="0"
                 aria-valuemax="100">
            </div>
        </div>
    </div>
</div>
@endif
