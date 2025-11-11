{{--
    Reusable Contact Info Card Component

    Props:
    - $icon: Font Awesome icon class (required)
    - $title: Card title (required)
    - $subtitle: Card subtitle/description (optional)
    - $link: Contact link (email, phone, URL) (required)
    - $linkText: Link display text (required)
    - $linkType: Type of link - 'email', 'phone', 'url' (optional, default: 'url')
    - $delay: Animation delay (optional, default: '0')
--}}

<div class="modern-card modern-card-gradient h-100 text-center scroll-reveal hover-lift" style="animation-delay: {{ $delay ?? '0' }}s;">
    <div class="icon-box bg-primary bg-opacity-100 mx-auto" style="background: linear-gradient(135deg, #17b6b6 0%, #13918e 100%) !important;">
        <i class="{{ $icon }} text-white"></i>
    </div>
    <h5 class="fw-bold mb-2">{{ $title }}</h5>
    @if(isset($subtitle))
    <p class="text-muted small mb-2">{{ $subtitle }}</p>
    @endif

    @php
        $linkType = $linkType ?? 'url';
        $href = match($linkType) {
            'email' => 'mailto:' . $link,
            'phone' => 'tel:' . $link,
            default => $link
        };
        $target = ($linkType === 'url' && str_contains($link, 'http')) ? '_blank' : '';
    @endphp

    <a href="{{ $href }}" {{ $target ? 'target=' . $target : '' }} class="text-primary text-decoration-none fw-semibold hover-glow" data-cta="{{ $dataCta ?? 'contact-info-' . $linkType }}">
        {{ $linkText }}
    </a>
</div>
