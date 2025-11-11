{{--
    Reusable Newsletter Signup Component

    Props:
    - $title: Section title (optional, default: 'Subscribe to Our Newsletter')
    - $description: Section description (optional)
    - $actionUrl: Form action URL (required)
    - $showName: Show name field (optional, default: true)
    - $buttonText: Submit button text (optional, default: 'Subscribe')
    - $turnstileKey: Turnstile site key (optional, uses config by default)
    - $bgClass: Background class (optional, default: 'gradient-primary')
    - $textClass: Text color class (optional, default: 'text-white')
--}}

<section class="{{ $bgClass ?? 'gradient-primary' }} position-relative overflow-hidden py-5">
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: -10%; right: 10%; width: 300px; height: 300px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="bottom: -10%; left: 5%; width: 250px; height: 250px; background: white; border-radius: 50%;"></div>
    </div>

    <div class="container text-center position-relative z-index-2">
        <h2 class="mb-3 {{ $textClass ?? 'text-white' }} scroll-reveal">{{ $title ?? 'Subscribe to Our Newsletter' }}</h2>
        <p class="{{ $textClass ?? 'text-white' }} opacity-75 mb-4 scroll-reveal delay-100">
            {{ $description ?? 'Get the latest insurance tips and industry insights delivered to your inbox' }}
        </p>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mx-auto" style="max-width: 500px;">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show mx-auto" style="max-width: 500px;">
            {{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mx-auto" style="max-width: 500px;">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        @endif

        <form action="{{ $actionUrl }}" method="POST" class="row g-3 justify-content-center scroll-reveal delay-200">
            @csrf
            @if($showName ?? true)
            <div class="col-auto">
                <input type="text" name="name" class="form-control" placeholder="Your Name (Optional)" style="min-width: 200px;">
            </div>
            @endif
            <div class="col-auto">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email" style="min-width: 300px;" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 d-flex justify-content-center">
                <div class="cf-turnstile" data-sitekey="{{ $turnstileKey ?? config('services.turnstile.key') }}" data-theme="light"></div>
            </div>
            @error('cf-turnstile-response')
            <div class="col-12">
                <div class="text-danger small text-center bg-white rounded px-3 py-2 d-inline-block">{{ $message }}</div>
            </div>
            @enderror
            <div class="col-auto">
                <button type="submit" class="btn btn-light btn-lg hover-lift" data-cta="{{ $dataCta ?? 'newsletter-subscribe' }}">
                    <i class="fas fa-envelope me-2"></i>{{ $buttonText ?? 'Subscribe' }}
                </button>
            </div>
        </form>
    </div>
</section>
