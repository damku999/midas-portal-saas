{{--
    Reusable Hero Section Component

    Props:
    - $badge: Badge text (required)
    - $badgeIcon: Font Awesome icon class (optional, default: 'fas fa-star')
    - $title: Main heading (required)
    - $description: Lead text (required)
    - $showCta: Show CTA buttons (optional, default: false)
    - $ctaPrimary: Primary CTA text (optional)
    - $ctaPrimaryUrl: Primary CTA URL (optional)
    - $ctaSecondary: Secondary CTA text (optional)
    - $ctaSecondaryUrl: Secondary CTA URL (optional)
    - $slot: Additional content (optional)
--}}

<section class="hero-section position-relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: 10%; left: 5%; width: 60px; height: 60px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="top: 70%; right: 10%; width: 50px; height: 50px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-500" style="bottom: 20%; left: 15%; width: 55px; height: 55px; background: white; border-radius: 50%;"></div>
    </div>

    <div class="container {{ $containerClass ?? 'py-5' }} position-relative z-index-2">
        <div class="row align-items-center justify-content-center {{ $alignClass ?? 'text-center' }}">
            <div class="{{ $colClass ?? 'col-lg-10' }} text-white">
                <span class="badge bg-white text-primary mb-3 px-4 py-2 animate-fade-in-down shadow-sm">
                    <i class="{{ $badgeIcon ?? 'fas fa-star' }} me-2"></i>{{ $badge }}
                </span>
                <h1 class="display-3 fw-bold mb-4 animate-fade-in-up delay-100">{{ $title }}</h1>
                <p class="lead mb-4 animate-fade-in-up delay-200">{{ $description }}</p>

                @if($showCta ?? false)
                <div class="d-flex {{ isset($alignClass) && str_contains($alignClass, 'text-center') ? 'justify-content-center' : '' }} gap-3 flex-wrap animate-fade-in-up delay-300">
                    @if(isset($ctaPrimary))
                    <a href="{{ $ctaPrimaryUrl }}" class="btn btn-light btn-lg px-5 hover-lift" data-cta="{{ $ctaPrimaryDataCta ?? 'hero-primary' }}">
                        <i class="{{ $ctaPrimaryIcon ?? 'fas fa-rocket' }} me-2"></i>{{ $ctaPrimary }}
                    </a>
                    @endif

                    @if(isset($ctaSecondary))
                    <a href="{{ $ctaSecondaryUrl }}" class="btn btn-outline-light btn-lg px-5 hover-lift" data-cta="{{ $ctaSecondaryDataCta ?? 'hero-secondary' }}">
                        <i class="{{ $ctaSecondaryIcon ?? 'fas fa-phone' }} me-2"></i>{{ $ctaSecondary }}
                    </a>
                    @endif
                </div>
                @endif

                {{ $slot ?? '' }}
            </div>
        </div>
    </div>
</section>
