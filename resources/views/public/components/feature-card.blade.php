{{--
    Reusable Feature Card Component

    Props:
    - $icon: Font Awesome icon class (required)
    - $iconBg: Icon background color class (optional, default: 'bg-primary')
    - $title: Card title (required)
    - $description: Card description (required)
    - $link: Optional link URL
    - $linkText: Link text (optional, default: 'Learn More')
    - $delay: Animation delay (optional, default: '0')
--}}

<div class="modern-card modern-card-gradient h-100 scroll-reveal hover-lift {{ $cardClass ?? '' }}" style="animation-delay: {{ $delay ?? '0' }}s;">
    <div class="icon-box {{ $iconBg ?? 'bg-primary' }} bg-opacity-10 mb-3">
        <i class="{{ $icon }} text-dark"></i>
    </div>
    <h5 class="fw-bold mb-3">{{ $title }}</h5>
    <p class="text-muted mb-3">{{ $description }}</p>

    @if(isset($link))
    <a href="{{ $link }}" class="btn btn-sm btn-outline-primary hover-scale" data-cta="{{ $dataCta ?? 'feature-card-link' }}">
        {{ $linkText ?? 'Learn More' }} <i class="fas fa-arrow-right ms-1"></i>
    </a>
    @endif

    {{ $slot ?? '' }}
</div>
