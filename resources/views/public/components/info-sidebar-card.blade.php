{{--
    Reusable Info Sidebar Card Component

    Props:
    - $icon: Font Awesome icon class (required)
    - $title: Card title (required)
    - $slot: Card content (required)
--}}

<div class="modern-card modern-card-gradient mb-4 hover-lift">
    <h5 class="fw-bold mb-3">
        <i class="{{ $icon }} text-primary me-2"></i>{{ $title }}
    </h5>
    {{ $slot }}
</div>
