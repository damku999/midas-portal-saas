{{--
    Reusable CTA (Call To Action) Section Component

    Props:
    - $title: CTA heading (required)
    - $description: CTA description (required)
    - $primaryText: Primary button text (required)
    - $primaryUrl: Primary button URL (required)
    - $secondaryText: Secondary button text (optional)
    - $secondaryUrl: Secondary button URL (optional)
    - $primaryIcon: Primary button icon (optional, default: 'fas fa-rocket')
    - $secondaryIcon: Secondary button icon (optional, default: 'fas fa-phone')
    - $showNote: Show note below buttons (optional, default: false)
    - $note: Note text (optional)
--}}

<section class="gradient-primary position-relative overflow-hidden py-5">
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: 10%; right: 10%; width: 80px; height: 80px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="bottom: 15%; left: 8%; width: 60px; height: 60px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-500" style="top: 60%; right: 15%; width: 50px; height: 50px; background: white; border-radius: 50%;"></div>
    </div>

    <div class="container {{ $containerClass ?? 'py-5' }} position-relative z-index-2">
        <div class="row justify-content-center">
            <div class="{{ $colClass ?? 'col-lg-8' }} text-center text-white">
                <h2 class="display-5 fw-bold mb-4 scroll-reveal">{{ $title }}</h2>
                <p class="lead mb-4 scroll-reveal delay-100">{{ $description }}</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center scroll-reveal delay-200">
                    <a href="{{ $primaryUrl }}" class="btn btn-light btn-lg px-5 hover-lift" data-cta="{{ $primaryDataCta ?? 'cta-primary' }}">
                        <i class="{{ $primaryIcon ?? 'fas fa-rocket' }} me-2"></i>{{ $primaryText }}
                    </a>

                    @if(isset($secondaryText) && isset($secondaryUrl))
                    <a href="{{ $secondaryUrl }}" class="btn btn-outline-light btn-lg px-5 hover-lift" data-cta="{{ $secondaryDataCta ?? 'cta-secondary' }}">
                        <i class="{{ $secondaryIcon ?? 'fas fa-phone' }} me-2"></i>{{ $secondaryText }}
                    </a>
                    @endif
                </div>

                @if($showNote ?? false)
                <p class="mt-3 small opacity-75 scroll-reveal delay-300">{!! $note ?? 'No credit card required • 14-day free trial • Cancel anytime' !!}</p>
                @endif

                {{ $slot ?? '' }}
            </div>
        </div>
    </div>
</section>
