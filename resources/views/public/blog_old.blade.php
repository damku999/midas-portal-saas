@extends('public.layout')

@section('title', 'Blog - Insurance Industry Insights & Updates | Midas Portal')
@section('meta_description', 'Stay updated with the latest insurance industry trends, best practices, and product updates from Midas Portal.')
@section('meta_keywords', 'insurance blog, insurance management tips, insurance technology, insurance CRM updates')

@section('content')
<!-- Page Header -->
<div style="background: var(--gradient-primary); color: white; padding: 60px 0;">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Blog & Insights</h1>
        <p class="lead">Stay updated with the latest insurance industry trends, best practices, and product updates</p>
    </div>
</div>

<!-- Blog Posts -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Blog Post 1 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-primary">Product Updates</span>
                            <span class="text-muted small ms-2">Dec 15, 2024</span>
                        </div>
                        <h5 class="card-title">Introducing Multi-Tenant Architecture</h5>
                        <p class="card-text text-muted">Learn how our new multi-tenant architecture helps insurance agencies scale efficiently while maintaining data security and isolation.</p>
                        <a href="#" class="btn btn-link text-decoration-none px-0">Read More →</a>
                    </div>
                </div>
            </div>

            <!-- Blog Post 2 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-success">Best Practices</span>
                            <span class="text-muted small ms-2">Dec 10, 2024</span>
                        </div>
                        <h5 class="card-title">10 Tips for Effective Insurance CRM</h5>
                        <p class="card-text text-muted">Discover essential strategies to maximize customer relationships and improve retention rates in the insurance industry.</p>
                        <a href="#" class="btn btn-link text-decoration-none px-0">Read More →</a>
                    </div>
                </div>
            </div>

            <!-- Blog Post 3 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-info text-dark">Industry Trends</span>
                            <span class="text-muted small ms-2">Dec 5, 2024</span>
                        </div>
                        <h5 class="card-title">The Future of Insurance Technology</h5>
                        <p class="card-text text-muted">Explore emerging technologies transforming the insurance landscape, from AI to blockchain and beyond.</p>
                        <a href="#" class="btn btn-link text-decoration-none px-0">Read More →</a>
                    </div>
                </div>
            </div>

            <!-- Blog Post 4 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-warning text-dark">WhatsApp</span>
                            <span class="text-muted small ms-2">Nov 28, 2024</span>
                        </div>
                        <h5 class="card-title">WhatsApp Integration Success Stories</h5>
                        <p class="card-text text-muted">See how insurance agencies are boosting customer engagement by 300% using WhatsApp Business API integration.</p>
                        <a href="#" class="btn btn-link text-decoration-none px-0">Read More →</a>
                    </div>
                </div>
            </div>

            <!-- Blog Post 5 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-danger">Security</span>
                            <span class="text-muted small ms-2">Nov 20, 2024</span>
                        </div>
                        <h5 class="card-title">Data Security in Insurance Management</h5>
                        <p class="card-text text-muted">Understanding compliance requirements and best practices for protecting sensitive customer information.</p>
                        <a href="#" class="btn btn-link text-decoration-none px-0">Read More →</a>
                    </div>
                </div>
            </div>

            <!-- Blog Post 6 -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="mb-3">
                            <span class="badge bg-primary">Product Updates</span>
                            <span class="text-muted small ms-2">Nov 15, 2024</span>
                        </div>
                        <h5 class="card-title">New Analytics Dashboard Released</h5>
                        <p class="card-text text-muted">Get powerful insights into your insurance business with our newly redesigned analytics and reporting dashboard.</p>
                        <a href="#" class="btn btn-link text-decoration-none px-0">Read More →</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-5">
            <nav>
                <ul class="pagination">
                    <li class="page-item active"><a class="page-link" href="#">1</a></li>
                    <li class="page-item"><a class="page-link" href="#">2</a></li>
                    <li class="page-item"><a class="page-link" href="#">3</a></li>
                    <li class="page-item"><a class="page-link" href="#">Next</a></li>
                </ul>
            </nav>
        </div>
    </div>
</section>

<!-- Newsletter Signup -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-3">Stay Updated</h2>
        <p class="text-muted mb-4">Subscribe to our newsletter for the latest insurance industry insights and product updates</p>
        <form class="row g-3 justify-content-center">
            <div class="col-auto">
                <input type="email" class="form-control" placeholder="Enter your email" style="min-width: 300px;">
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Subscribe</button>
            </div>
        </form>
    </div>
</section>
@endsection
