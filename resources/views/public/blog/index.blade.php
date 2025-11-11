@extends('public.layout')

@section('title', 'Insurance Blog - Tips, Guides & Industry Updates | Midas Portal')
@section('meta_description', 'Stay updated with insurance industry insights, tips, claim guides, and comprehensive information about different insurance types and add-ons.')
@section('meta_keywords', 'insurance blog, insurance tips, claims guide, insurance types, insurance addons, health insurance, motor insurance')

@section('content')
<!-- Page Header -->
<div style="background: var(--gradient-primary); color: white; padding: 60px 0;">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Insurance Blog & Insights</h1>
        <p class="lead">Expert advice, industry updates, and comprehensive guides for all your insurance needs</p>

        <!-- Search Bar -->
        <div class="row justify-content-center mt-4">
            <div class="col-md-8">
                <form action="{{ url('/blog') }}" method="GET" class="input-group input-group-lg">
                    <input type="text" name="search" class="form-control" placeholder="Search articles..." value="{{ request('search') }}">
                    <button class="btn btn-light" type="submit">
                        <i class="fas fa-search"></i> Search
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Category Filter -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="{{ url('/blog') }}" class="btn btn-sm {{ !request('category') ? 'btn-primary' : 'btn-outline-primary' }}">
                All Posts
            </a>
            <a href="{{ url('/blog?category=product-updates') }}" class="btn btn-sm {{ request('category') == 'product-updates' ? 'btn-primary' : 'btn-outline-primary' }}">
                Product Updates
            </a>
            <a href="{{ url('/blog?category=insurance-tips') }}" class="btn btn-sm {{ request('category') == 'insurance-tips' ? 'btn-primary' : 'btn-outline-primary' }}">
                Insurance Tips
            </a>
            <a href="{{ url('/blog?category=claims') }}" class="btn btn-sm {{ request('category') == 'claims' ? 'btn-warning text-dark' : 'btn-outline-warning' }}">
                Claims Guide
            </a>
            <a href="{{ url('/blog?category=insurance-types') }}" class="btn btn-sm {{ request('category') == 'insurance-types' ? 'btn-info text-dark' : 'btn-outline-info' }}">
                Insurance Types
            </a>
            <a href="{{ url('/blog?category=addons') }}" class="btn btn-sm {{ request('category') == 'addons' ? 'btn-danger' : 'btn-outline-danger' }}">
                Add-ons & Riders
            </a>
        </div>
    </div>
</section>

<!-- Featured Post -->
@if($featuredPost && !request('search') && !request('category'))
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="mb-4">Featured Article</h2>
        <div class="card border-0 shadow-lg">
            <div class="row g-0">
                <div class="col-md-5">
                    <div class="bg-primary" style="height: 100%; min-height: 300px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-newspaper text-white" style="font-size: 5rem; opacity: 0.3;"></i>
                    </div>
                </div>
                <div class="col-md-7">
                    <div class="card-body p-4 p-md-5">
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
                        <h3 class="card-title mb-3">{{ $featuredPost->title }}</h3>
                        <p class="card-text text-muted">{{ $featuredPost->excerpt }}</p>
                        <a href="{{ url('/blog/' . $featuredPost->slug) }}" class="btn btn-primary">
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
                <div class="card h-100 border-0 shadow-sm hover-lift">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-{{ $post->category_badge }}">{{ $post->category_name }}</span>
                            <span class="text-muted ms-2 small">{{ $post->published_date }}</span>
                        </div>
                        <h5 class="card-title">
                            <a href="{{ url('/blog/' . $post->slug) }}" class="text-decoration-none text-dark">
                                {{ $post->title }}
                            </a>
                        </h5>
                        <p class="card-text text-muted small">{{ Str::limit($post->excerpt, 120) }}</p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                <i class="far fa-clock"></i> {{ $post->reading_time_minutes }} min
                            </small>
                            <small class="text-muted">
                                <i class="far fa-eye"></i> {{ number_format($post->views_count) }}
                            </small>
                            <a href="{{ url('/blog/' . $post->slug) }}" class="btn btn-sm btn-outline-primary">
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
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-3">Subscribe to Our Newsletter</h2>
        <p class="text-muted mb-4">Get the latest insurance tips and industry insights delivered to your inbox</p>

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

        <form action="{{ url('/newsletter/subscribe') }}" method="POST" class="row g-3 justify-content-center">
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
                <div class="text-danger small text-center">{{ $message }}</div>
            </div>
            @enderror
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </div>
        </form>
    </div>
</section>

<style>
.hover-lift {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}
.hover-lift:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15) !important;
}
</style>
@endsection
