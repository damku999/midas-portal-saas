@extends('public.layout')

@section('title', 'Pricing - Midas Portal')

@section('meta_description', 'Transparent insurance software pricing plans starting at ₹2,999/month. No hidden fees, 14-day free trial. Choose the perfect plan for your insurance agency today.')

@section('meta_keywords', 'insurance software pricing, saas pricing plans, insurance management cost, affordable insurance software, insurance crm pricing, insurance software india pricing, insurance agency software cost')

@section('content')
<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <div class="container py-5 position-relative z-index-2">
        <div class="row align-items-center justify-content-center text-center">
            <div class="col-lg-8">
                <span class="badge bg-white text-primary mb-3 px-4 py-2 animate-fade-in-down shadow-sm">
                    <i class="fas fa-tag me-2"></i>Transparent Pricing
                </span>
                <h1 class="display-3 fw-bold mb-4 animate-fade-in-up delay-100">Simple, Transparent Pricing</h1>
                <p class="lead mb-4 animate-fade-in-up delay-200">Choose the perfect plan for your insurance agency. No hidden fees, no surprises. All plans include a 14-day free trial.</p>
                <div class="d-flex justify-content-center gap-4 flex-wrap animate-fade-in-up delay-300">
                    <div class="text-center hover-scale">
                        <i class="fas fa-shield-alt fa-2x mb-2"></i>
                        <div class="small">14-Day Free Trial</div>
                    </div>
                    <div class="text-center hover-scale">
                        <i class="fas fa-credit-card fa-2x mb-2"></i>
                        <div class="small">No Credit Card Required</div>
                    </div>
                    <div class="text-center hover-scale">
                        <i class="fas fa-times-circle fa-2x mb-2"></i>
                        <div class="small">Cancel Anytime</div>
                    </div>
                    <div class="text-center hover-scale">
                        <i class="fas fa-sync-alt fa-2x mb-2"></i>
                        <div class="small">Switch Plans Anytime</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: 10%; left: 5%; width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="top: 50%; right: 10%; width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 50%;"></div>
    </div>
</section>

<!-- Pricing Cards -->
<section class="section-modern bg-white">
    <div class="container">
        <div class="row g-4 justify-content-center">
            @forelse($plans as $plan)
            <div class="col-lg-4">
                <div class="modern-card hover-lift scroll-reveal {{ $plan->slug === 'professional' ? 'border-primary shadow-lg' : '' }}" style="animation-delay: {{ $loop->index * 0.1 }}s;">
                    @if($plan->slug === 'professional')
                    <div class="popular-badge animate-pulse">
                        <i class="fas fa-crown me-1"></i>Most Popular
                    </div>
                    @endif

                    <h3 class="fw-bold">{{ $plan->name }}</h3>
                    <p class="text-muted">{{ $plan->description }}</p>

                    <div class="my-4">
                        <h2 class="display-4 fw-bold">₹{{ number_format($plan->price, 0) }}</h2>
                        <p class="text-muted">/month</p>
                    </div>

                    @php
                        $features = json_decode($plan->features, true) ?? [];
                    @endphp

                    <ul class="list-unstyled mb-4">
                        @foreach($features as $feature)
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ $feature }}</li>
                        @endforeach
                    </ul>

                    <a href="{{ url('/contact') }}" class="btn {{ $plan->slug === 'professional' ? 'btn-gradient animate-glow' : 'btn-outline-primary' }} w-100 btn-lg" data-cta="pricing-{{ $plan->slug }}">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                </div>
            </div>
            @empty
            <!-- Fallback Static Pricing Plans -->
            <div class="col-lg-4">
                <div class="modern-card hover-lift scroll-reveal">
                    <h3 class="fw-bold">Starter</h3>
                    <p class="text-muted">Perfect for small agencies getting started</p>
                    <div class="my-4">
                        <h2 class="display-4 fw-bold">₹2,999</h2>
                        <p class="text-muted">/month</p>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 3 users</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 500 customers</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 1,000 policies</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 5GB storage</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Email support</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Basic reports</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Mobile app access</li>
                    </ul>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-primary w-100 btn-lg" data-cta="pricing-starter">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="modern-card hover-lift scroll-reveal delay-100 border-primary shadow-lg">
                    <div class="popular-badge animate-pulse">
                        <i class="fas fa-crown me-1"></i>Most Popular
                    </div>
                    <h3 class="fw-bold">Professional</h3>
                    <p class="text-muted">For growing agencies with advanced needs</p>
                    <div class="my-4">
                        <h2 class="display-4 fw-bold">₹5,999</h2>
                        <p class="text-muted">/month</p>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 10 users</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited customers</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited policies</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 50GB storage</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Priority support</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Advanced reports & analytics</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> WhatsApp integration</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Custom branding</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> API access</li>
                    </ul>
                    <a href="{{ url('/contact') }}" class="btn btn-gradient animate-glow w-100 btn-lg" data-cta="pricing-professional">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="modern-card hover-lift scroll-reveal delay-200">
                    <h3 class="fw-bold">Enterprise</h3>
                    <p class="text-muted">For large agencies with custom requirements</p>
                    <div class="my-4">
                        <h2 class="display-4 fw-bold">Custom</h2>
                        <p class="text-muted">Contact us</p>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited users</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited everything</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Unlimited storage</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 24/7 dedicated support</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Custom integrations</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Custom features</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Dedicated account manager</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> On-premise deployment option</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> SLA guarantee</li>
                    </ul>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-primary w-100 btn-lg" data-cta="pricing-enterprise">
                        <i class="fas fa-phone me-2"></i>Contact Sales
                    </a>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>

<!-- All Plans Include -->
<section class="section-modern bg-light bg-pattern-dots">
    <div class="container">
        <div class="section-header scroll-reveal">
            <span class="badge badge-gradient mb-3 px-4 py-2">All Plans Include</span>
            <h2>Essential Features Across All Tiers</h2>
            <p>Every plan comes with these powerful features to transform your business</p>
        </div>
        <div class="row g-4">
            <div class="col-md-3 col-sm-6">
                <div class="text-center scroll-reveal hover-scale">
                    <div class="icon-box bg-primary bg-opacity-10 mx-auto">
                        <i class="fas fa-users text-dark"></i>
                    </div>
                    <h6 class="fw-bold mt-3">Customer Management</h6>
                    <p class="text-muted small">Complete 360° CRM for your clients</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center scroll-reveal hover-scale delay-100">
                    <div class="icon-box bg-primary bg-opacity-10 mx-auto">
                        <i class="fas fa-file-contract text-dark"></i>
                    </div>
                    <h6 class="fw-bold mt-3">Policy Management</h6>
                    <p class="text-muted small">Track all insurance policies</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center scroll-reveal hover-scale delay-100">
                    <div class="icon-box bg-primary bg-opacity-10 mx-auto">
                        <i class="fas fa-clipboard-check text-dark"></i>
                    </div>
                    <h6 class="fw-bold mt-3">Claims Tracking</h6>
                    <p class="text-muted small">Manage claims efficiently</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center scroll-reveal hover-scale delay-100">
                    <div class="icon-box bg-primary bg-opacity-10 mx-auto">
                        <i class="fas fa-bell text-dark"></i>
                    </div>
                    <h6 class="fw-bold mt-3">Renewal Alerts</h6>
                    <p class="text-muted small">Never miss a renewal date</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center scroll-reveal hover-scale delay-100">
                    <div class="icon-box bg-primary bg-opacity-10 mx-auto">
                        <i class="fas fa-chart-line text-dark"></i>
                    </div>
                    <h6 class="fw-bold mt-3">Reports & Analytics</h6>
                    <p class="text-muted small">Business insights at your fingertips</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center scroll-reveal hover-scale delay-100">
                    <div class="icon-box bg-primary bg-opacity-10 mx-auto">
                        <i class="fas fa-mobile-alt text-dark"></i>
                    </div>
                    <h6 class="fw-bold mt-3">Mobile Access</h6>
                    <p class="text-muted small">Work from anywhere, anytime</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center scroll-reveal hover-scale delay-100">
                    <div class="icon-box bg-primary bg-opacity-10 mx-auto">
                        <i class="fas fa-lock text-dark"></i>
                    </div>
                    <h6 class="fw-bold mt-3">Data Security</h6>
                    <p class="text-muted small">Bank-grade encryption</p>
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <div class="text-center scroll-reveal hover-scale delay-100">
                    <div class="icon-box bg-primary bg-opacity-10 mx-auto">
                        <i class="fas fa-cloud text-dark"></i>
                    </div>
                    <h6 class="fw-bold mt-3">Cloud Backup</h6>
                    <p class="text-muted small">Automatic daily backups</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing FAQ -->
<section class="section-modern bg-white" id="faq">
    <div class="container">
        <div class="section-header scroll-reveal">
            <span class="badge badge-gradient mb-3 px-4 py-2">FAQ</span>
            <h2>Frequently Asked Questions</h2>
            <p>Common questions about our pricing and plans</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion scroll-reveal" id="pricingFAQ">
                    <div class="accordion-item border-0 shadow-sm mb-3 hover-lift">
                        <h3 class="accordion-header">
                            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                Can I change my plan later?
                            </button>
                        </h3>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately, and we'll prorate the charges accordingly. There are no penalties for changing plans.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3 hover-lift">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                What happens after the free trial?
                            </button>
                        </h3>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                After your 14-day free trial, you can choose to continue with a paid plan or cancel at no cost. No credit card is required to start the trial, so there are no automatic charges. We'll remind you before the trial ends.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3 hover-lift">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Are there any setup fees or hidden charges?
                            </button>
                        </h3>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                No setup fees, no hidden charges. The price you see is the price you pay. We believe in transparent pricing. All features listed in your plan are included in the monthly price.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3 hover-lift">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What payment methods do you accept?
                            </button>
                        </h3>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                We accept all major credit cards (Visa, MasterCard, American Express), debit cards, UPI, net banking, and bank transfers. For Enterprise plans, we can also arrange invoicing with payment terms.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm mb-3 hover-lift">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                                Do you offer discounts for annual billing?
                            </button>
                        </h3>
                        <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                Yes! Save up to 20% when you choose annual billing. Contact our sales team for special pricing on multi-year commitments and bulk licenses for large agencies.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item border-0 shadow-sm hover-lift">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                                What if I need more users or storage?
                            </button>
                        </h3>
                        <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#pricingFAQ">
                            <div class="accordion-body">
                                You can easily add more users or storage to your plan at any time. Additional users are charged at a prorated rate, and storage upgrades are available in 10GB increments. Contact us for custom requirements.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust Indicators -->
<section class="gradient-primary text-white py-5">
    <div class="container">
        <div class="row g-4 align-items-center">
            <div class="col-md-3 text-center scroll-reveal">
                <div class="stat-number" data-count="500">0</div>
                <div class="h4 fw-bold mb-2">+</div>
                <p class="mb-0 opacity-75">Active Agencies</p>
            </div>
            <div class="col-md-3 text-center scroll-reveal delay-100">
                <div class="stat-number" data-count="50">0</div>
                <div class="h4 fw-bold mb-2">K+</div>
                <p class="mb-0 opacity-75">Policies Managed</p>
            </div>
            <div class="col-md-3 text-center scroll-reveal delay-200">
                <div class="stat-number" data-count="99">0</div>
                <div class="h4 fw-bold mb-2">.9%</div>
                <p class="mb-0 opacity-75">Uptime SLA</p>
            </div>
            <div class="col-md-3 text-center scroll-reveal delay-300">
                <div class="h2 display-3 fw-bold mb-2">4.8/5</div>
                <p class="mb-0 opacity-75">Customer Rating</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-modern text-white position-relative">
    <div class="container position-relative z-index-2">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center scroll-reveal">
                <span class="badge bg-white text-primary mb-4 px-4 py-2 shadow">
                    <i class="fas fa-rocket me-2"></i>Start Today
                </span>
                <h2 class="display-4 fw-bold mb-3">Ready to Transform Your Insurance Business?</h2>
                <p class="lead mb-5 fs-4">Start your <strong>14-day free trial</strong> today. No credit card required. Cancel anytime.</p>
                <div class="d-flex justify-content-center gap-3 mb-4">
                    <a href="{{ url('/contact') }}" class="btn btn-light btn-lg px-5 py-3 shadow-lg hover-lift" data-cta="cta-start-trial">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg px-5 py-3 hover-lift" data-cta="cta-talk-sales">
                        <i class="fas fa-phone me-2"></i>Talk to Sales
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-4 justify-content-center small">
                    <div><i class="fas fa-check-circle me-2"></i><strong>No credit card</strong> required</div>
                    <div><i class="fas fa-check-circle me-2"></i><strong>Full access</strong> to all features</div>
                    <div><i class="fas fa-check-circle me-2"></i><strong>Cancel anytime</strong> - no questions</div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Readability Improvements */
    .text-muted {
        color: #4a5568 !important;
    }

    p {
        line-height: 1.7;
    }

    .lead {
        line-height: 1.8;
    }

    .z-index-2 {
        z-index: 2;
    }

    /* Popular Badge */
    .popular-badge {
        position: absolute;
        top: -18px;
        right: 25px;
        background: linear-gradient(135deg, #17b6b6 0%, #13918e 100%);
        color: white;
        padding: 10px 24px;
        border-radius: 50px;
        font-size: 0.9rem;
        font-weight: 700;
        box-shadow: 0 8px 20px rgba(23, 182, 182, 0.4);
        z-index: 10;
    }

    /* Stat Numbers */
    .stat-number {
        font-size: 3rem;
        font-weight: 800;
        color: white;
    }

    /* Accordion Styling */
    .accordion-button:not(.collapsed) {
        background-color: var(--primary-light);
        color: var(--primary-dark);
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: var(--primary-color);
    }

    .accordion-item {
        transition: all 0.3s ease;
    }

    /* Responsive */
    @media (max-width: 767px) {
        .section-header h2 {
            font-size: 2rem !important;
        }
    }
</style>
@endpush
