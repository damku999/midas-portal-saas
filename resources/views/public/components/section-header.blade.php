{{--
    Reusable Section Header Component

    Props:
    - $badge: Optional badge text
    - $badgeIcon: Optional badge icon (default: 'fas fa-star')
    - $title: Section title (required)
    - $description: Section description (optional)
    - $align: Text alignment (optional, default: 'center')
--}}

<div class="{{ $align ?? 'text-center' }} mb-5 scroll-reveal {{ $headerClass ?? '' }}">
    @if(isset($badge))
    <span class="text-primary fw-bold text-uppercase small">
        @if(isset($badgeIcon))
        <i class="{{ $badgeIcon }} me-2"></i>
        @endif
        {{ $badge }}
    </span>
    @endif

    <h2 class="display-5 fw-bold {{ isset($badge) ? 'mt-2' : '' }} mb-3">{{ $title }}</h2>

    @if(isset($description))
    <p class="lead text-muted {{ $descClass ?? '' }}">{!! $description !!}</p>
    @endif

    {{ $slot ?? '' }}
</div>
