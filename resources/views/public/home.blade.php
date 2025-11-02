@extends('public.layout')

@section('title', 'Midas Portal - Modern Insurance Management SaaS')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">Manage Your Insurance Business with Confidence</h1>
                <p class="lead mb-4">Midas Portal is a modern, multi-tenant SaaS platform designed specifically for insurance agencies. Streamline operations, boost productivity, and grow your business.</p>
                <div class="d-flex gap-3">
                    <a href="{{ url('/pricing') }}" class="btn btn-light btn-lg px-4">Get Started Free</a>
                    <a href="{{ url('/features') }}" class="btn btn-outline-light btn-lg px-4">Learn More</a>
                </div>
                <p class="mt-4 small"><i class="fas fa-check-circle"></i> 14-day free trial &nbsp;&nbsp;<i class="fas fa-check-circle"></i> No credit card required</p>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-shield-alt" style="font-size: 300px; opacity: 0.2;"></i>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-5 fw-bold">Why Choose Midas Portal?</h2>
            <p class="lead text-muted">Everything you need to run a successful insurance agency</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4>Customer Management</h4>
                    <p class="text-muted">Complete customer relationship management with family groups, policies, and renewal tracking.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4>Lead Management</h4>
                    <p class="text-muted">Powerful lead tracking with automated workflows, WhatsApp campaigns, and conversion analytics.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4>Policy Management</h4>
                    <p class="text-muted">Manage all types of insurance policies with renewal reminders and document management.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h4>WhatsApp Integration</h4>
                    <p class="text-muted">Send automated WhatsApp messages, renewal reminders, and policy documents directly to customers.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4>Analytics & Reports</h4>
                    <p class="text-muted">Comprehensive dashboards and reports to track performance and make data-driven decisions.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-lock"></i>
                    </div>
                    <h4>Secure & Compliant</h4>
                    <p class="text-muted">Enterprise-grade security with complete data isolation and backup for each tenant.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="py-5">
    <div class="container py-5">
        <div class="row text-center">
            <div class="col-md-3">
                <h2 class="display-4 fw-bold text-primary">99.9%</h2>
                <p class="text-muted">Uptime SLA</p>
            </div>
            <div class="col-md-3">
                <h2 class="display-4 fw-bold text-primary">500+</h2>
                <p class="text-muted">Active Agencies</p>
            </div>
            <div class="col-md-3">
                <h2 class="display-4 fw-bold text-primary">50K+</h2>
                <p class="text-muted">Policies Managed</p>
            </div>
            <div class="col-md-3">
                <h2 class="display-4 fw-bold text-primary">24/7</h2>
                <p class="text-muted">Support</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Insurance Business?</h2>
                <p class="lead text-muted mb-4">Join hundreds of insurance agencies already using Midas Portal to grow their business.</p>
                <a href="{{ url('/pricing') }}" class="btn btn-gradient btn-lg px-5">Start Your Free Trial</a>
                <p class="mt-3 small text-muted">No credit card required • 14-day free trial • Cancel anytime</p>
            </div>
        </div>
    </div>
</section>
@endsection
