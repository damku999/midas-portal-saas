@extends('public.layout')

@section('title', 'Pricing - Midas Portal')

@section('meta_description', 'Transparent insurance software pricing plans starting at ₹2,999/month. No hidden fees, 14-day free trial. Choose the perfect plan for your insurance agency today.')

@section('meta_keywords', 'insurance software pricing, saas pricing plans, insurance management cost, affordable insurance software, insurance crm pricing, insurance software india pricing, insurance agency software cost')

@section('content')
<!-- Hero Section -->
@include('public.components.hero', [
    'badge' => 'Transparent Pricing',
    'badgeIcon' => 'fas fa-tag',
    'title' => 'Simple, Transparent Pricing',
    'description' => 'Choose the perfect plan for your insurance agency. No hidden fees, no surprises. All plans include a 14-day free trial.',
    'showCta' => false,
    'colClass' => 'col-lg-8',
    'containerClass' => 'py-5'
])

<!-- Pricing Features Banner -->
<section class="py-3 bg-light border-bottom">
    <div class="container">
        <div class="d-flex justify-content-center gap-4 flex-wrap text-center">
            <div class="hover-scale">
                <i class="fas fa-shield-alt fa-2x mb-2 text-primary"></i>
                <div class="small fw-semibold">14-Day Free Trial</div>
            </div>
            <div class="hover-scale">
                <i class="fas fa-credit-card fa-2x mb-2 text-primary"></i>
                <div class="small fw-semibold">No Credit Card Required</div>
            </div>
            <div class="hover-scale">
                <i class="fas fa-times-circle fa-2x mb-2 text-primary"></i>
                <div class="small fw-semibold">Cancel Anytime</div>
            </div>
            <div class="hover-scale">
                <i class="fas fa-sync-alt fa-2x mb-2 text-primary"></i>
                <div class="small fw-semibold">Switch Plans Anytime</div>
            </div>
        </div>
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
                        <h2 class="display-4 fw-bold">{{ $plan->formatted_price }}</h2>
                        <p class="text-muted">/{{ $plan->billing_interval_label }}</p>
                    </div>

                    <!-- Plan Limits -->
                    <div class="mb-3 p-3 bg-light rounded">
                        <div class="small text-muted mb-1">
                            <i class="fas fa-users me-1"></i> {{ $plan->max_users_label }}
                        </div>
                        <div class="small text-muted mb-1">
                            <i class="fas fa-user-friends me-1"></i> {{ $plan->max_customers_label }}
                        </div>
                        <div class="small text-muted mb-1">
                            <i class="fas fa-chart-line me-1"></i> {{ $plan->max_leads_label }}
                        </div>
                        <div class="small text-muted">
                            <i class="fas fa-hdd me-1"></i> {{ $plan->storage_limit_label }}
                        </div>
                    </div>

                    <!-- Features List -->
                    <ul class="list-unstyled mb-4">
                        @foreach($plan->features as $feature)
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
        @include('public.components.section-header', [
            'badge' => 'All Plans Include',
            'title' => 'Essential Features Across All Tiers',
            'description' => 'Every plan comes with these powerful features to transform your business'
        ])
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
        @include('public.components.section-header', [
            'badge' => 'FAQ',
            'title' => 'Frequently Asked Questions',
            'description' => 'Common questions about our pricing and plans'
        ])
        @include('public.components.faq-accordion', [
            'accordionId' => 'pricingFAQ',
            'showFirst' => true,
            'faqs' => [
                [
                    'question' => 'Can I change my plan later?',
                    'answer' => 'Yes! You can upgrade or downgrade your plan at any time. Changes take effect immediately, and we\'ll prorate the charges accordingly. There are no penalties for changing plans.'
                ],
                [
                    'question' => 'What happens after the free trial?',
                    'answer' => 'After your 14-day free trial, you can choose to continue with a paid plan or cancel at no cost. No credit card is required to start the trial, so there are no automatic charges. We\'ll remind you before the trial ends.'
                ],
                [
                    'question' => 'Are there any setup fees or hidden charges?',
                    'answer' => 'No setup fees, no hidden charges. The price you see is the price you pay. We believe in transparent pricing. All features listed in your plan are included in the monthly price.'
                ],
                [
                    'question' => 'What payment methods do you accept?',
                    'answer' => 'We accept all major credit cards (Visa, MasterCard, American Express), debit cards, UPI, net banking, and bank transfers. For Enterprise plans, we can also arrange invoicing with payment terms.'
                ],
                [
                    'question' => 'Do you offer discounts for annual billing?',
                    'answer' => 'Yes! Save up to 20% when you choose annual billing. Contact our sales team for special pricing on multi-year commitments and bulk licenses for large agencies.'
                ],
                [
                    'question' => 'What if I need more users or storage?',
                    'answer' => 'You can easily add more users or storage to your plan at any time. Additional users are charged at a prorated rate, and storage upgrades are available in 10GB increments. Contact us for custom requirements.'
                ]
            ]
        ])
    </div>
</section>

<!-- Trust Indicators -->
@include('public.components.stats-section', [
    'stats' => [
        [
            'number' => '500',
            'suffix' => '+',
            'label' => 'Active Agencies',
            'hasCounter' => true
        ],
        [
            'number' => '50',
            'suffix' => 'K+',
            'label' => 'Policies Managed',
            'hasCounter' => true
        ],
        [
            'number' => '99',
            'suffix' => '.9%',
            'label' => 'Uptime SLA',
            'hasCounter' => true
        ],
        [
            'number' => '4.8',
            'suffix' => '/5',
            'label' => 'Customer Rating',
            'hasCounter' => false,
            'displayClass' => 'display-3'
        ]
    ]
])

<!-- CTA Section -->
@include('public.components.cta-section', [
    'title' => 'Ready to Transform Your Insurance Business?',
    'description' => 'Start your 14-day free trial today. No credit card required. Cancel anytime.',
    'primaryText' => 'Start Free Trial',
    'primaryUrl' => url('/contact'),
    'primaryIcon' => 'fas fa-rocket',
    'primaryDataCta' => 'cta-start-trial',
    'secondaryText' => 'Talk to Sales',
    'secondaryUrl' => url('/contact'),
    'secondaryIcon' => 'fas fa-phone',
    'secondaryDataCta' => 'cta-talk-sales',
    'showNote' => true,
    'note' => '<strong>No credit card</strong> required • <strong>Full access</strong> to all features • <strong>Cancel anytime</strong> - no questions'
])
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
