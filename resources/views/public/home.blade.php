@extends('public.layout')

@section('title', 'Transform Your Insurance Business Today - Midas Portal')

@section('meta_description', 'Leading insurance management SaaS platform in India. Comprehensive CRM, policy management, claims tracking, and automation tools for insurance agencies. Start free trial today.')

@section('meta_keywords', 'insurance management saas, insurance agency software, policy management system, crm for insurance, insurance automation, insurance software india, insurance agency crm, policy tracking software')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-5 mb-lg-0">
                <span class="badge bg-white text-primary mb-3 px-3 py-2">
                    <i class="fas fa-star me-2"></i>Trusted by 500+ Insurance Agencies
                </span>
                <h1 class="display-3 fw-bold mb-4">
                    Transform Your<br>
                    Insurance Business<br>
                    <span class="text-white">Today!</span>
                </h1>
                <p class="lead mb-4 text-white-50">Where simplicity meets innovation for your success. Streamline operations, automate workflows, and grow your insurance agency with India's most comprehensive SaaS platform.</p>
                <div class="d-flex flex-wrap gap-3 mb-4">
                    <a href="#pricing" class="btn btn-light btn-lg px-4 shadow">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                    <a href="{{ url('/features') }}" class="btn btn-outline-light btn-lg px-4">
                        <i class="fas fa-arrow-right me-2"></i>Explore Features
                    </a>
                </div>
                <p class="small text-white-50">
                    <i class="fas fa-check-circle me-2"></i>14-day free trial
                    <i class="fas fa-check-circle ms-3 me-2"></i>No credit card required
                    <i class="fas fa-check-circle ms-3 me-2"></i>Cancel anytime
                </p>
            </div>
            <div class="col-lg-6">
                <div class="hero-illustration">
                    <div class="floating-card card-1">
                        <i class="fas fa-users text-primary"></i>
                        <span class="small">CRM</span>
                    </div>
                    <div class="floating-card card-2">
                        <i class="fas fa-shield-alt text-success"></i>
                        <span class="small">Policies</span>
                    </div>
                    <div class="floating-card card-3">
                        <i class="fab fa-whatsapp text-success"></i>
                        <span class="small">WhatsApp</span>
                    </div>
                    <div class="floating-card card-4">
                        <i class="fas fa-chart-line text-danger"></i>
                        <span class="small">Analytics</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Insurance Types Banner -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-6 col-md-3">
                <div class="insurance-type-badge">
                    <i class="fas fa-car text-primary mb-2"></i>
                    <div class="fw-semibold">Motor Insurance</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="insurance-type-badge">
                    <i class="fas fa-heartbeat text-danger mb-2"></i>
                    <div class="fw-semibold">Health Insurance</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="insurance-type-badge">
                    <i class="fas fa-home text-success mb-2"></i>
                    <div class="fw-semibold">Home Insurance</div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="insurance-type-badge">
                    <i class="fas fa-umbrella text-info mb-2"></i>
                    <div class="fw-semibold">Life Insurance</div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Complete Solution</span>
            <h2 class="display-5 fw-bold mt-2 mb-3">All The Modules You Need</h2>
            <p class="lead text-muted">Comprehensive features built specifically for insurance agencies</p>
        </div>

        <div class="row g-4">
            <!-- Customer Management -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Customer Management</h5>
                    <p class="text-muted mb-0">Complete CRM with 360° customer view, policy history, and renewal tracking.</p>
                </div>
            </div>

            <!-- Family Management -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Family Management</h5>
                    <p class="text-muted mb-0">Group families together and manage dependent policies efficiently.</p>
                </div>
            </div>

            <!-- Customer Portal -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Customer Portal</h5>
                    <p class="text-muted mb-0">Self-service portal for customers to view policies and submit claims 24/7.</p>
                </div>
            </div>

            <!-- Lead Management -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Lead Management</h5>
                    <p class="text-muted mb-0">Powerful lead tracking with automated workflows and conversion analytics.</p>
                </div>
            </div>

            <!-- Policy Management -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Policy Management</h5>
                    <p class="text-muted mb-0">Manage all types of insurance policies with renewal reminders.</p>
                </div>
            </div>

            <!-- Claims Management -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Claims Management</h5>
                    <p class="text-muted mb-0">Complete claims processing with status tracking and settlement monitoring.</p>
                </div>
            </div>

            <!-- WhatsApp Integration -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h5 class="fw-bold mb-3">WhatsApp Integration</h5>
                    <p class="text-muted mb-0">Send automated WhatsApp messages, reminders, and documents to customers.</p>
                </div>
            </div>

            <!-- Quotation System -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Quotation System</h5>
                    <p class="text-muted mb-0">Generate professional quotations instantly with PDF export.</p>
                </div>
            </div>

            <!-- Analytics & Reports -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Analytics & Reports</h5>
                    <p class="text-muted mb-0">Comprehensive dashboards and reports for data-driven decisions.</p>
                </div>
            </div>

            <!-- Commission Tracking -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Commission Tracking</h5>
                    <p class="text-muted mb-0">Automated commission calculations and agent payout management.</p>
                </div>
            </div>

            <!-- Document Management -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Document Management</h5>
                    <p class="text-muted mb-0">Secure cloud storage for all policy documents and customer records.</p>
                </div>
            </div>

            <!-- Staff Management -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Staff & Role Management</h5>
                    <p class="text-muted mb-0">Manage your team with role-based access control and performance tracking.</p>
                </div>
            </div>

            <!-- Master Data -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Master Data Management</h5>
                    <p class="text-muted mb-0">Centralized management of insurance companies, vehicles, and policy types.</p>
                </div>
            </div>

            <!-- Notifications -->
            <div class="col-md-6 col-lg-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Notifications & Alerts</h5>
                    <p class="text-muted mb-0">Multi-channel automated notifications via Email, SMS, and WhatsApp.</p>
                </div>
            </div>
        </div>

        <div class="text-center mt-5">
            <a href="{{ url('/features') }}" class="btn btn-primary btn-lg px-5">
                <i class="fas fa-arrow-right me-2"></i>Explore All Features in Detail
            </a>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h2 class="display-4 fw-bold text-primary mb-0">99.9%</h2>
                    <p class="text-muted mb-0">Uptime SLA</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h2 class="display-4 fw-bold text-primary mb-0">500+</h2>
                    <p class="text-muted mb-0">Active Agencies</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h2 class="display-4 fw-bold text-primary mb-0">50K+</h2>
                    <p class="text-muted mb-0">Policies Managed</p>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="stat-card">
                    <h2 class="display-4 fw-bold text-primary mb-0">24/7</h2>
                    <p class="text-muted mb-0">Support</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
@if($plans->count() > 0)
<section class="py-5 bg-white" id="pricing">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Flexible Plans</span>
            <h2 class="display-5 fw-bold mt-2 mb-3">Choose Your Perfect Plan</h2>
            <p class="lead text-muted">Transparent pricing with no hidden fees. Start with a 14-day free trial!</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach($plans as $index => $plan)
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card {{ $index === 1 ? 'featured' : '' }}">
                        @if($index === 1)
                            <div class="popular-badge">
                                <i class="fas fa-star me-1"></i>Most Popular
                            </div>
                        @endif

                        <h4 class="fw-bold mb-2">{{ $plan->name }}</h4>
                        <p class="text-muted small mb-4">{{ $plan->description }}</p>

                        <div class="mb-4">
                            <h2 class="display-4 fw-bold mb-0">
                                ₹{{ number_format($plan->price, 0) }}
                                <small class="fs-6 text-muted fw-normal">/{{ $plan->billing_interval }}</small>
                            </h2>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ $plan->max_users == -1 ? 'Unlimited' : $plan->max_users }} Users
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ $plan->max_customers == -1 ? 'Unlimited' : number_format($plan->max_customers) }} Customers
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ $plan->max_leads_per_month == -1 ? 'Unlimited' : number_format($plan->max_leads_per_month) }} Leads/Month
                            </li>
                            <li class="mb-2">
                                <i class="fas fa-check-circle text-success me-2"></i>
                                {{ $plan->storage_limit_gb }} GB Storage
                            </li>
                            @foreach(array_slice($plan->features ?? [], 0, 4) as $feature)
                                <li class="mb-2">
                                    <i class="fas fa-check-circle text-success me-2"></i>
                                    {{ $feature }}
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ url('/pricing') }}" class="btn {{ $index === 1 ? 'btn-gradient' : 'btn-outline-primary' }} w-100 btn-lg">
                            Get Started
                        </a>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5">
            <p class="text-muted mb-2">All plans include 14-day free trial • No credit card required</p>
            <a href="{{ url('/pricing') }}" class="btn btn-link text-primary">
                View detailed comparison <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</section>
@endif

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Insurance Business?</h2>
                <p class="lead text-muted mb-4">Join hundreds of insurance agencies already using Midas Portal to grow their business.</p>
                <a href="{{ url('/pricing') }}" class="btn btn-gradient btn-lg px-5 shadow">
                    <i class="fas fa-rocket me-2"></i>Start Your Free Trial
                </a>
                <p class="mt-3 small text-muted">No credit card required • 14-day free trial • Cancel anytime</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Smooth Scroll */
    html {
        scroll-behavior: smooth;
    }

    /* Insurance Type Badge */
    .insurance-type-badge {
        padding: 15px;
        transition: all 0.3s ease;
        cursor: pointer;
    }

    .insurance-type-badge:hover {
        transform: translateY(-5px);
    }

    .insurance-type-badge i {
        font-size: 2.5rem;
        display: block;
    }

    /* Hero Illustration */
    .hero-illustration {
        position: relative;
        height: 450px;
    }

    .floating-card {
        position: absolute;
        background: white;
        width: 100px;
        height: 100px;
        border-radius: 15px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 15px 35px rgba(0,0,0,0.15);
        animation: float 3s ease-in-out infinite;
        transition: all 0.3s ease;
    }

    .floating-card:hover {
        transform: scale(1.1);
        box-shadow: 0 20px 45px rgba(0,0,0,0.2);
    }

    .floating-card i {
        font-size: 2rem;
        margin-bottom: 5px;
    }

    .card-1 {
        top: 10%;
        left: 5%;
        animation-delay: 0s;
    }

    .card-2 {
        top: 15%;
        right: 10%;
        animation-delay: 0.5s;
    }

    .card-3 {
        bottom: 35%;
        left: 15%;
        animation-delay: 1s;
    }

    .card-4 {
        bottom: 10%;
        right: 5%;
        animation-delay: 1.5s;
    }

    @keyframes float {
        0%, 100% {
            transform: translateY(0px);
        }
        50% {
            transform: translateY(-20px);
        }
    }

    /* Stats Card */
    .stat-card {
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .stat-card:hover {
        transform: scale(1.05);
    }

    /* Popular Badge */
    .popular-badge {
        position: absolute;
        top: -15px;
        right: 20px;
        background: var(--gradient-primary);
        color: white;
        padding: 8px 20px;
        border-radius: 50px;
        font-size: 0.85rem;
        font-weight: 600;
        box-shadow: 0 5px 15px rgba(23, 162, 184, 0.3);
    }

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .hero-illustration {
            height: 350px;
        }

        .floating-card {
            width: 80px;
            height: 80px;
        }

        .floating-card i {
            font-size: 1.5rem;
        }

        .floating-card span {
            font-size: 0.75rem;
        }
    }

    @media (max-width: 767px) {
        .hero-illustration {
            height: 300px;
        }

        .floating-card {
            width: 70px;
            height: 70px;
        }

        .floating-card i {
            font-size: 1.25rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// Smooth scroll for anchor links
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
});
</script>
@endpush
