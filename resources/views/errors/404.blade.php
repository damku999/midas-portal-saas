@if(request()->is('midas-admin*') || auth('central')->check())
    {{-- Central Admin 404 Page --}}
    @extends('central.layout')

    @section('title', 'Page Not Found')

    @section('content')
        <div class="container-fluid">
            <div class="text-center mt-5">
                <div class="error mx-auto" data-text="404">404</div>
                <p class="lead text-gray-800 mb-5">Page Not Found!</p>
                <p class="text-gray-500 mb-0">The page you are looking for does not exist.</p>
                <a href="{{ route('central.dashboard') }}" class="btn btn-primary mt-3">← Back to Dashboard</a>
            </div>
        </div>
    @endsection

@elseif(auth()->check())
    {{-- Tenant Admin 404 Page --}}
    @extends('layouts.app')

    @section('title', 'Page Not Found')

    @section('content')
        <div class="container-fluid">
            <div class="text-center mt-5">
                <div class="error mx-auto" data-text="404">404</div>
                <p class="lead text-gray-800 mb-5">Page Not Found!</p>
                <p class="text-gray-500 mb-0">The page you are looking for does not exist.</p>
                <a href="{{ route('home') }}" class="btn btn-primary mt-3">← Back to Dashboard</a>
            </div>
        </div>
    @endsection

@elseif(auth('customer')->check())
    {{-- Customer Portal 404 Page --}}
    @extends('layouts.customer')

    @section('title', 'Page Not Found')

    @section('content')
        <div class="container-fluid">
            <div class="text-center mt-5">
                <div class="error mx-auto" data-text="404">404</div>
                <p class="lead text-gray-800 mb-5">Page Not Found!</p>
                <p class="text-gray-500 mb-0">The page you are looking for does not exist.</p>
                <a href="{{ route('customer.dashboard') }}" class="btn btn-primary mt-3">← Back to Dashboard</a>
            </div>
        </div>
    @endsection

@else
    {{-- Public Website 404 Page --}}
    @extends('public.layout')

    @section('title', 'Page Not Found - 404 Error')

    @section('meta_description', 'The page you are looking for could not be found. Return to Midas Portal homepage or explore our insurance management features.')

    @section('content')
        <!-- 404 Error Section -->
        <section class="py-5 bg-light">
            <div class="container py-5">
                <div class="row justify-content-center text-center">
                    <div class="col-lg-8">
                        <!-- 404 Illustration -->
                        <div class="mb-4">
                            <h1 class="display-1 fw-bold text-primary">404</h1>
                        </div>

                        <!-- Error Message -->
                        <h2 class="h3 mb-3">Page Not Found</h2>
                        <p class="lead text-muted mb-4">
                            Oops! The page you are looking for doesn't exist or has been moved.
                        </p>

                        <!-- Helpful Links -->
                        <div class="d-flex gap-3 justify-content-center flex-wrap mb-5">
                            <a href="{{ route('public.home') }}" class="btn btn-primary btn-lg">
                                <i class="fas fa-home me-2"></i> Go to Homepage
                            </a>
                            <a href="{{ route('public.features') }}" class="btn btn-outline-primary btn-lg">
                                <i class="fas fa-star me-2"></i> View Features
                            </a>
                            <a href="{{ route('public.contact') }}" class="btn btn-outline-secondary btn-lg">
                                <i class="fas fa-envelope me-2"></i> Contact Us
                            </a>
                        </div>

                        <!-- Search Suggestion -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-body p-4">
                                <h5 class="card-title mb-3">Looking for something specific?</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <a href="{{ route('public.features') }}" class="text-decoration-none">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <i class="fas fa-th-large fa-2x text-primary me-3"></i>
                                                <div class="text-start">
                                                    <h6 class="mb-0">Features</h6>
                                                    <small class="text-muted">Explore all features</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('public.pricing') }}" class="text-decoration-none">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <i class="fas fa-tags fa-2x text-primary me-3"></i>
                                                <div class="text-start">
                                                    <h6 class="mb-0">Pricing</h6>
                                                    <small class="text-muted">View pricing plans</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('public.blog') }}" class="text-decoration-none">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <i class="fas fa-blog fa-2x text-primary me-3"></i>
                                                <div class="text-start">
                                                    <h6 class="mb-0">Blog</h6>
                                                    <small class="text-muted">Read our latest posts</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <div class="col-md-6">
                                        <a href="{{ route('public.help-center') }}" class="text-decoration-none">
                                            <div class="d-flex align-items-center p-3 bg-light rounded">
                                                <i class="fas fa-question-circle fa-2x text-primary me-3"></i>
                                                <div class="text-start">
                                                    <h6 class="mb-0">Help Center</h6>
                                                    <small class="text-muted">Get help and support</small>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <style>
            .display-1 {
                font-size: 10rem;
                font-weight: 700;
                line-height: 1;
            }

            @media (max-width: 768px) {
                .display-1 {
                    font-size: 6rem;
                }
            }
        </style>
    @endsection
@endif