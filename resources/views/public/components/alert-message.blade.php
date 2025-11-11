{{--
    Reusable Alert Message Component

    Props:
    - $type: Alert type - 'success', 'error', 'warning', 'info' (optional, default: 'info')
    - $message: Alert message text (required)
    - $dismissible: Show close button (optional, default: true)
    - $icon: Custom icon (optional, auto-selected based on type)
    - $containerClass: Additional container classes (optional)
--}}

@php
    $alertClass = match($type ?? 'info') {
        'success' => 'alert-success',
        'error' => 'alert-danger',
        'warning' => 'alert-warning',
        default => 'alert-info'
    };

    $defaultIcon = match($type ?? 'info') {
        'success' => 'fas fa-check-circle',
        'error' => 'fas fa-exclamation-circle',
        'warning' => 'fas fa-exclamation-triangle',
        default => 'fas fa-info-circle'
    };

    $iconClass = $icon ?? $defaultIcon;
@endphp

<div class="alert {{ $alertClass }} {{ ($dismissible ?? true) ? 'alert-dismissible' : '' }} fade show {{ $containerClass ?? '' }}">
    <i class="{{ $iconClass }} me-2"></i>{{ $message }}
    @if($dismissible ?? true)
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
