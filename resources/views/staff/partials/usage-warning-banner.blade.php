@if(isset($usageAlertSummary) && $usageAlertSummary['total_active'] > 0)
    @php
        $summary = $usageAlertSummary;
        $hasCritical = $summary['has_critical'];
        $alerts = $summary['alerts'];

        // Determine highest severity alert
        $severity = 'warning';
        $icon = '⚠️';
        $bgClass = 'bg-warning';
        $textClass = 'text-dark';

        if ($summary['exceeded_count'] > 0) {
            $severity = 'danger';
            $icon = '⛔';
            $bgClass = 'bg-danger';
            $textClass = 'text-white';
        } elseif ($summary['critical_count'] > 0) {
            $severity = 'danger';
            $icon = '🚨';
            $bgClass = 'bg-danger';
            $textClass = 'text-white';
        }

        // Get the most severe alert for message
        $mainAlert = $alerts->first();

        // Check if banner was dismissed this session
        $bannerId = 'usage_alert_' . $mainAlert->id;
        $dismissed = session()->has('dismissed_banner_' . $bannerId);
    @endphp

    @if(!$dismissed)
        <div class="alert alert-{{ $severity }} alert-dismissible fade show mb-3 shadow-sm border-0"
             id="{{ $bannerId }}"
             style="border-left: 5px solid {{ $hasCritical ? '#dc2626' : '#f59e0b' }};">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-md-1 text-center">
                        <span style="font-size: 2.5rem;">{{ $icon }}</span>
                    </div>
                    <div class="col-md-9">
                        <h5 class="alert-heading mb-2 {{ $textClass }}">
                            @if($summary['exceeded_count'] > 0)
                                Usage Limit Exceeded
                            @elseif($summary['critical_count'] > 0)
                                Critical: Approaching Usage Limit
                            @else
                                Usage Warning
                            @endif
                        </h5>

                        @if($summary['exceeded_count'] > 0)
                            @php
                                $exceededAlert = $alerts->where('threshold_level', 'exceeded')->first();
                                $usageAlertService = app(\App\Services\UsageAlertService::class);
                                $graceDays = $usageAlertService->getGracePeriodRemaining(tenant(), $exceededAlert->resource_type);
                            @endphp
                            <p class="mb-2 {{ $textClass }}">
                                <strong>Your {{ $exceededAlert->resource_type_display }} usage has reached 100% of your plan limit.</strong>
                            </p>
                            @if($graceDays > 0)
                                <div class="alert alert-light mb-0" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-clock me-2"></i>
                                    <strong>Grace Period:</strong> {{ $graceDays }} day{{ $graceDays > 1 ? 's' : '' }} remaining.
                                    After this period, creating new {{ strtolower($exceededAlert->resource_type_display) }} will be restricted.
                                </div>
                            @else
                                <div class="alert alert-light mb-0" style="background: rgba(255,255,255,0.2);">
                                    <i class="fas fa-ban me-2"></i>
                                    <strong>Grace period expired.</strong> Creating new {{ strtolower($exceededAlert->resource_type_display) }} is now restricted.
                                    Please upgrade your plan to continue.
                                </div>
                            @endif
                        @else
                            <p class="mb-1 {{ $textClass }}">
                                @if($summary['total_active'] === 1)
                                    Your <strong>{{ $mainAlert->resource_type_display }}</strong> usage is at
                                    <strong>{{ round($mainAlert->usage_percentage, 1) }}%</strong>
                                    ({{ $mainAlert->usage_display }}).
                                @else
                                    You have <strong>{{ $summary['total_active'] }}</strong> active usage alerts:
                                    @if($summary['warning_count'] > 0)
                                        <span class="badge bg-light text-dark">{{ $summary['warning_count'] }} Warning</span>
                                    @endif
                                    @if($summary['critical_count'] > 0)
                                        <span class="badge bg-dark">{{ $summary['critical_count'] }} Critical</span>
                                    @endif
                                @endif
                            </p>
                            <small class="{{ $textClass }} opacity-75">
                                Monitor your usage closely or consider upgrading to avoid service interruptions.
                            </small>
                        @endif
                    </div>
                    <div class="col-md-2 text-end">
                        @if(Route::has('staff.settings.plans'))
                            <a href="{{ route('staff.settings.plans') }}"
                               class="btn {{ $hasCritical ? 'btn-light' : 'btn-primary' }} btn-sm mb-2 d-block">
                                <i class="fas fa-arrow-up me-1"></i> Upgrade Plan
                            </a>
                        @endif
                        <button type="button"
                                class="btn btn-sm {{ $hasCritical ? 'btn-outline-light' : 'btn-outline-secondary' }} d-block w-100"
                                onclick="viewUsageDetails()">
                            <i class="fas fa-chart-bar me-1"></i> View Details
                        </button>
                    </div>
                </div>
            </div>
            <button type="button"
                    class="btn-close {{ $hasCritical ? 'btn-close-white' : '' }}"
                    data-bs-dismiss="alert"
                    onclick="dismissBanner('{{ $bannerId }}')">
            </button>
        </div>
    @endif
@endif

@push('scripts')
<script>
function dismissBanner(bannerId) {
    // Store dismissal in session via AJAX
    fetch('/staff/dismiss-banner', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ banner_id: bannerId })
    });
}

function viewUsageDetails() {
    @if(Route::has('staff.settings.usage'))
        window.location.href = '{{ route("staff.settings.usage") }}';
    @else
        alert('Usage details page is not yet available. Please contact support for more information about your usage.');
    @endif
}
</script>
@endpush
