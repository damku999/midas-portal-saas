@extends('public.layout')

@section('title', 'Insurance Blog - Tips, Guides & Industry Updates | Midas Portal')
@section('meta_description', 'Stay updated with insurance industry insights, tips, claim guides, and comprehensive information about different insurance types and add-ons.')
@section('meta_keywords', 'insurance blog, insurance tips, claims guide, insurance types, insurance addons, health insurance, motor insurance')

@section('content')
<!-- Page Header -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: 10%; left: 5%; width: 60px; height: 60px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="top: 70%; right: 10%; width: 50px; height: 50px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-500" style="bottom: 20%; left: 15%; width: 55px; height: 55px; background: white; border-radius: 50%;"></div>
    </div>

    <div class="container py-5 position-relative z-index-2">
        <div class="text-center text-white">
            <span class="badge bg-white text-primary mb-3 px-4 py-2 animate-fade-in-down shadow-sm">
                <i class="fas fa-newspaper me-2"></i>Blog & Insights
            </span>
            <h1 class="display-4 fw-bold mb-3 animate-fade-in-up delay-100">Insurance Blog & Insights</h1>
            <p class="lead animate-fade-in-up delay-200">Expert advice, industry updates, and comprehensive guides for all your insurance needs</p>

            <!-- Search Bar -->
            <div class="row justify-content-center mt-4 animate-fade-in-up delay-300">
                <div class="col-md-8">
                    <form action="{{ url('/blog') }}" method="GET" class="input-group input-group-lg">
                        <input type="text" name="search" class="form-control" placeholder="Search articles..." value="{{ request('search') }}">
                        <button class="btn btn-light hover-scale" type="submit">
                            <i class="fas fa-search"></i> Search
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Category Filter -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-center gap-2 scroll-reveal">
            <a href="{{ url('/blog') }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-primary' }} hover-scale">
                All Posts
            </a>
            <a href="{{ url('/blog?category=product-updates') }}" class="btn btn-sm {{ request('category') == 'product-updates' ? 'btn-primary' : 'btn-outline-primary' }} hover-scale">
                Product Updates
            </a>
            <a href="{{ url('/blog?category=insurance-tips') }}" class="btn btn-sm {{ request('category') == 'insurance-tips' ? 'btn-primary' : 'btn-outline-primary' }} hover-scale">
                Insurance Tips
            </a>
            <a href="{{ url('/blog?category=claims') }}" class="btn btn-sm {{ request('category') == 'claims' ? 'btn-warning text-dark' : 'btn-outline-warning' }} hover-scale">
                Claims Guide
            </a>
            <a href="{{ url('/blog?category=insurance-types') }}" class="btn btn-sm {{ request('category') == 'insurance-types' ? 'btn-info text-dark' : 'btn-outline-info' }} hover-scale">
                Insurance Types
            </a>
            <a href="{{ url('/blog?category=addons') }}" class="btn btn-sm {{ request('category') == 'addons' ? 'btn-danger' : 'btn-outline-danger' }} hover-scale">
                Add-ons & Riders
            </a>
        </div>
    </div>
</section>

<!-- Featured Post -->
@if($featuredPost && !request('search') && !request('category'))
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="mb-4 scroll-reveal">Featured Article</h2>
        <div class="modern-card modern-card-gradient shadow-lg scroll-reveal delay-200 hover-lift">
            <div class="row g-0">
                <div class="col-md-5">
                    <div class="gradient-primary" style="height: 100%; min-height: 300px; display: flex; align-items: center; justify-content: center; border-radius: 20px 0 0 20px;">
                        <i class="fas fa-newspaper text-white animate-pulse" style="font-size: 5rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="p-4 p-md-5">
                        <div class="mb-3">
                            <span class="badge bg-{{ $featuredPost->category_badge }}">{{ $featuredPost->category_name }}</span>
                            <span class="text-muted ms-2 small">
                                <i class="far fa-calendar"></i> {{ $featuredPost->published_date }}
                            </span>
                            <span class="text-muted ms-2 small">
                                <i class="far fa-clock"></i> {{ $featuredPost->reading_time_minutes }} min read
                            </span>
                            <span class="text-muted ms-2 small">
                                <i class="far fa-eye"></i> {{ number_format($featuredPost->views_count) }} views
                            </span>
                        </div>
                        <h3 class="mb-3">{{ $featuredPost->title }}</h3>
                        <p class="text-muted">{{ $featuredPost->excerpt }}</p>
                        <a href="{{ url('/blog/' . $featuredPost->slug) }}" class="btn btn-gradient hover-glow" data-cta="blog-featured-read">
                            Read Full Article <i class="fas fa-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endif

<!-- Blog Posts Grid -->
<section class="py-5">
    <div class="container">
        @if(request('search'))
        <div class="mb-4">
            <h4>Search Results for "{{ request('search') }}"</h4>
            <p class="text-muted">Found {{ $posts->total() }} {{ Str::plural('article', $posts->total()) }}</p>
        </div>
        @endif

        @if(request('category'))
        <div class="mb-4">
            <h4>{{ ucwords(str_replace('-', ' ', request('category'))) }}</h4>
            <p class="text-muted">{{ $posts->total() }} {{ Str::plural('article', $posts->total()) }}</p>
        </div>
        @endif

        @if($posts->count() > 0)
        <div class="row g-4">
            @foreach($posts as $post)
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient h-100 scroll-reveal hover-lift" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                    <div class="mb-3">
                        <span class="badge bg-{{ $post->category_badge }}">{{ $post->category_name }}</span>
                        <span class="text-muted ms-2 small">{{ $post->published_date }}</span>
                    </div>
                    <h5 class="fw-bold">
                        <a href="{{ url('/blog/' . $post->slug) }}" class="text-decoration-none text-dark">
                            {{ $post->title }}
                        </a>
                    </h5>
                    <p class="text-muted small">{{ Str::limit($post->excerpt, 120) }}</p>
                    <div class="d-flex justify-content-between align-items-center mt-3">
                        <small class="text-muted">
                            <i class="far fa-clock"></i> {{ $post->reading_time_minutes }} min
                        </small>
                        <small class="text-muted">
                            <i class="far fa-eye"></i> {{ number_format($post->views_count) }}
                        </small>
                        <a href="{{ url('/blog/' . $post->slug) }}" class="btn btn-sm btn-outline-primary hover-scale" data-cta="blog-read-{{ $post->slug }}">
                            Read More →
                        </a>
                    </div>
                    @if($post->tags && count($post->tags) > 0)
                    <div class="mt-3">
                        @foreach(array_slice($post->tags, 0, 3) as $tag)
                        <span class="badge bg-secondary me-1 small">{{ $tag }}</span>
                        @endforeach
                    </div>
                    @endif
                </div>
            </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-5">
            {{ $posts->links() }}
        </div>
        @else
        <div class="text-center py-5">
            <i class="fas fa-search text-muted" style="font-size: 4rem; opacity: 0.3;"></i>
            <h4 class="mt-3">No articles found</h4>
            <p class="text-muted">Try adjusting your search or filter criteria</p>
            <a href="{{ url('/blog') }}" class="btn btn-primary">View All Articles</a>
        </div>
        @endif
    </div>
</section>

<!-- Newsletter Signup -->
<section class="gradient-primary position-relative overflow-hidden py-5">
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: -10%; right: 10%; width: 300px; height: 300px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="bottom: -10%; left: 5%; width: 250px; height: 250px; background: white; border-radius: 50%;"></div>
    </div>

    <div class="container text-center position-relative z-index-2">
        <h2 class="mb-3 text-white scroll-reveal">Subscribe to Our Newsletter</h2>
        <p class="text-white opacity-75 mb-4 scroll-reveal delay-100">Get the latest insurance tips and industry insights delivered to your inbox</p>

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

        <form action="{{ url('/newsletter/subscribe') }}" method="POST" class="row g-3 justify-content-center scroll-reveal delay-200">
            @csrf
            <div class="col-auto">
                <input type="text" name="name" class="form-control" placeholder="Your Name (Optional)" style="min-width: 200px;">
            </div>
            <div class="col-auto">
                <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Enter your email" style="min-width: 300px;" required>
                @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            <div class="col-12 d-flex justify-content-center">
                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-theme="light"></div>
            </div>
            @error('cf-turnstile-response')
            <div class="col-12">
                <div class="text-danger small text-center bg-white rounded px-3 py-2 d-inline-block">{{ $message }}</div>
            </div>
            @enderror
            <div class="col-auto">
                <button type="submit" class="btn btn-light btn-lg hover-lift" data-cta="blog-newsletter-subscribe">
                    <i class="fas fa-envelope me-2"></i>Subscribe
                </button>
            </div>
        </form>
    </div>
</section>

@push('styles')
<style>
    /* Z-index utilities */
    .z-index-2 {
        z-index: 2;
    }
</style>
@endpush
@endsection
