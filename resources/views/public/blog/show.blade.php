@extends('public.layout')

@section('title', $post->meta_title ?? $post->title . ' | Midas Portal Blog')
@section('meta_description', $post->meta_description ?? $post->excerpt)
@section('meta_keywords', $post->meta_keywords ?? implode(', ', $post->tags ?? []))

@section('content')
<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="bg-light py-3 animate-fade-in">
    <div class="container">
        <ol class="breadcrumb mb-0">
            <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/blog') }}">Blog</a></li>
            <li class="breadcrumb-item"><a href="{{ url('/blog?category=' . $post->category) }}">{{ $post->category_name }}</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($post->title, 50) }}</li>
        </ol>
    </div>
</nav>

<!-- Article Content -->
<article class="py-5">
    <div class="container">
        <div class="row">
            <!-- Main Content -->
            <div class="col-lg-8">
                <!-- Article Header -->
                <header class="mb-4 scroll-reveal">
                    <span class="badge bg-{{ $post->category_badge }} mb-3">{{ $post->category_name }}</span>
                    <h1 class="display-5 fw-bold mb-3">{{ $post->title }}</h1>

                    <div class="d-flex flex-wrap gap-3 text-muted mb-4">
                        <span><i class="far fa-calendar"></i> {{ $post->published_date }}</span>
                        <span><i class="far fa-clock"></i> {{ $post->reading_time_minutes }} min read</span>
                        <span><i class="far fa-eye"></i> {{ number_format($post->views_count) }} views</span>
                    </div>

                    <p class="lead text-muted">{{ $post->excerpt }}</p>
                </header>

                <hr class="my-4">

                <!-- Article Body -->
                <div class="article-content scroll-reveal delay-200">
                    {!! $post->content !!}
                </div>

                <!-- Tags -->
                @if($post->tags && count($post->tags) > 0)
                <div class="mt-5 pt-4 border-top">
                    <h6 class="mb-3">Tags:</h6>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($post->tags as $tag)
                        <a href="{{ url('/blog?search=' . urlencode($tag)) }}" class="badge bg-secondary text-decoration-none">
                            {{ $tag }}
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Share Buttons -->
                <div class="mt-4 pt-4 border-top scroll-reveal">
                    <h6 class="mb-3">Share this article:</h6>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url('/blog/' . $post->slug)) }}" target="_blank" class="btn btn-outline-primary btn-sm hover-scale" data-cta="blog-share-facebook">
                            <i class="fab fa-facebook-f"></i> Facebook
                        </a>
                        <a href="https://twitter.com/intent/tweet?url={{ urlencode(url('/blog/' . $post->slug)) }}&text={{ urlencode($post->title) }}" target="_blank" class="btn btn-outline-info btn-sm hover-scale" data-cta="blog-share-twitter">
                            <i class="fab fa-twitter"></i> Twitter
                        </a>
                        <a href="https://www.linkedin.com/shareArticle?mini=true&url={{ urlencode(url('/blog/' . $post->slug)) }}&title={{ urlencode($post->title) }}" target="_blank" class="btn btn-outline-primary btn-sm hover-scale" data-cta="blog-share-linkedin">
                            <i class="fab fa-linkedin-in"></i> LinkedIn
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($post->title . ' - ' . url('/blog/' . $post->slug)) }}" target="_blank" class="btn btn-outline-success btn-sm hover-scale" data-cta="blog-share-whatsapp">
                            <i class="fab fa-whatsapp"></i> WhatsApp
                        </a>
                    </div>
                </div>

                <!-- Related Posts -->
                @if($relatedPosts->count() > 0)
                <div class="mt-5 pt-4 border-top">
                    <h4 class="mb-4 scroll-reveal">Related Articles</h4>
                    <div class="row g-3">
                        @foreach($relatedPosts as $related)
                        <div class="col-md-4">
                            <div class="modern-card modern-card-gradient h-100 scroll-reveal hover-lift" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                                <span class="badge bg-{{ $related->category_badge }} mb-2 small">{{ $related->category_name }}</span>
                                <h6 class="fw-bold">
                                    <a href="{{ url('/blog/' . $related->slug) }}" class="text-decoration-none text-dark">
                                        {{ Str::limit($related->title, 60) }}
                                    </a>
                                </h6>
                                <p class="small text-muted">{{ Str::limit($related->excerpt, 80) }}</p>
                                <a href="{{ url('/blog/' . $related->slug) }}" class="btn btn-sm btn-outline-primary mt-2 hover-scale" data-cta="blog-related-{{ $related->slug }}">
                                    Read More →
                                </a>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4 scroll-reveal delay-400">
                <div class="sticky-top" style="top: 20px;">
                    <!-- Categories Widget -->
                    <div class="modern-card modern-card-gradient mb-4 hover-lift">
                        <h5 class="fw-bold mb-3">Categories</h5>
                        <div class="d-grid gap-2">
                            <a href="{{ url('/blog?category=product-updates') }}" class="btn btn-outline-primary btn-sm text-start hover-scale">
                                Product Updates
                            </a>
                            <a href="{{ url('/blog?category=insurance-tips') }}" class="btn btn-outline-success btn-sm text-start hover-scale">
                                Insurance Tips
                            </a>
                            <a href="{{ url('/blog?category=claims') }}" class="btn btn-outline-warning btn-sm text-start hover-scale">
                                Claims Guide
                            </a>
                            <a href="{{ url('/blog?category=insurance-types') }}" class="btn btn-outline-info btn-sm text-start hover-scale">
                                Insurance Types
                            </a>
                            <a href="{{ url('/blog?category=addons') }}" class="btn btn-outline-danger btn-sm text-start hover-scale">
                                Add-ons & Riders
                            </a>
                        </div>
                    </div>

                    <!-- Popular Posts Widget -->
                    <div class="modern-card modern-card-gradient mb-4 hover-lift">
                        <h5 class="fw-bold mb-3">Popular Articles</h5>
                        @php
                            $popularPosts = \App\Models\Central\BlogPost::published()
                                ->orderBy('views_count', 'desc')
                                ->limit(5)
                                ->get();
                        @endphp
                        <div class="list-group list-group-flush">
                            @foreach($popularPosts as $popular)
                            <a href="{{ url('/blog/' . $popular->slug) }}" class="list-group-item list-group-item-action border-0 px-0 hover-scale">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div class="flex-grow-1">
                                        <h6 class="mb-1 small">{{ Str::limit($popular->title, 50) }}</h6>
                                        <small class="text-muted"><i class="far fa-eye"></i> {{ number_format($popular->views_count) }} views</small>
                                    </div>
                                </div>
                            </a>
                            @endforeach
                        </div>
                    </div>

                    <!-- Newsletter Widget -->
                    <div class="modern-card gradient-primary text-white hover-lift">
                        <h5 class="fw-bold">Stay Updated</h5>
                        <p class="small">Subscribe to get the latest insurance insights and tips</p>
                        <form action="{{ url('/newsletter/subscribe') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <input type="text" name="name" class="form-control" placeholder="Your Name (Optional)">
                            </div>
                            <div class="mb-2">
                                <input type="email" name="email" class="form-control" placeholder="Your email" required>
                            </div>
                            <div class="mb-2">
                                <div class="cf-turnstile" data-sitekey="{{ config('services.turnstile.key') }}" data-theme="light"></div>
                            </div>
                            <button type="submit" class="btn btn-light w-100 hover-glow" data-cta="blog-detail-newsletter">Subscribe</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>

<!-- Call to Action -->
<section class="gradient-primary position-relative overflow-hidden py-5">
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: -10%; right: 10%; width: 300px; height: 300px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="bottom: -10%; left: 5%; width: 250px; height: 250px; background: white; border-radius: 50%;"></div>
    </div>

    <div class="container text-center position-relative z-index-2">
        <h2 class="mb-3 text-white scroll-reveal">Need Help with Insurance?</h2>
        <p class="text-white opacity-75 mb-4 scroll-reveal delay-100">Our team is here to assist you with all your insurance needs</p>
        <div class="d-flex justify-content-center gap-3 scroll-reveal delay-200">
            <a href="{{ url('/contact') }}" class="btn btn-light btn-lg hover-lift" data-cta="blog-cta-contact">Contact Us</a>
            <a href="{{ url('/pricing') }}" class="btn btn-outline-light btn-lg hover-lift" data-cta="blog-cta-pricing">View Plans</a>
        </div>
    </div>
</section>

<style>
.article-content {
    font-size: 1.1rem;
    line-height: 1.8;
}

.article-content h2 {
    margin-top: 2rem;
    margin-bottom: 1rem;
    font-weight: 600;
}

.article-content h3 {
    margin-top: 1.5rem;
    margin-bottom: 0.75rem;
    font-weight: 600;
}

.article-content p {
    margin-bottom: 1.25rem;
}

.article-content ul, .article-content ol {
    margin-bottom: 1.25rem;
    padding-left: 2rem;
}

.article-content li {
    margin-bottom: 0.5rem;
}

.article-content strong {
    font-weight: 600;
    color: var(--primary-color);
}

.article-content a {
    color: var(--primary-color);
    text-decoration: underline;
}

.article-content a:hover {
    color: var(--primary-dark);
}

/* Z-index utilities */
.z-index-2 {
    z-index: 2;
}
</style>
@endsection
