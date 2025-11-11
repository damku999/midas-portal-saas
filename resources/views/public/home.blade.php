@extends('public.layout')

@section('title', 'Transform Your Insurance Business Today - Midas Portal')

@section('meta_description', 'Leading insurance management SaaS platform in India. Comprehensive CRM, policy management, claims tracking, and automation tools for insurance agencies. Start free trial today.')

@section('meta_keywords', 'insurance management saas, insurance agency software, policy management system, crm for insurance, insurance automation, insurance software india, insurance agency crm, policy tracking software')

@section('content')
<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <div class="container position-relative z-index-2">
        <div class="row align-items-center min-vh-75">
            <div class="col-lg-6 mb-5 mb-lg-0 scroll-reveal">
                <span class="badge bg-white text-primary mb-3 px-4 py-2 animate-fade-in-down shadow-sm">
                    <i class="fas fa-star me-2 text-warning"></i>Trusted by 500+ Insurance Agencies Across India
                </span>
                <h1 class="display-2 fw-bolder mb-4 animate-fade-in-up delay-100">
                    Transform Your<br>
                    <span class="position-relative">
                        Insurance Business
                        <svg class="position-absolute bottom-0 start-0 w-100" height="15" viewBox="0 0 500 15">
                            <path d="M0,10 Q250,0 500,10" fill="none" stroke="rgba(255,255,255,0.6)" stroke-width="3"/>
                        </svg>
                    </span><br>
                    <span class="text-white">Today!</span>
                </h1>
                <p class="lead mb-4 text-white fs-4 animate-fade-in-up delay-200">
                    <strong>Simplicity meets innovation</strong> for your success. Streamline operations, automate workflows, and grow your insurance agency with India's most comprehensive SaaS platform.
                </p>
                <div class="d-flex flex-wrap gap-3 mb-4 animate-fade-in-up delay-300">
                    <a href="#pricing" class="btn btn-light btn-lg px-5 py-3 shadow-lg hover-lift" data-cta="hero-start-trial">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial Now
                    </a>
                    <a href="{{ url('/features') }}" class="btn btn-outline-light btn-lg px-5 py-3 hover-lift" data-cta="hero-explore-features">
                        <i class="fas fa-arrow-right me-2"></i>Explore Features
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-4 text-white-50 small animate-fade-in-up delay-400">
                    <div><i class="fas fa-check-circle text-success me-2"></i><strong>14-day</strong> free trial</div>
                    <div><i class="fas fa-check-circle text-success me-2"></i><strong>No</strong> credit card required</div>
                    <div><i class="fas fa-check-circle text-success me-2"></i><strong>Cancel</strong> anytime</div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-illustration position-relative animate-fade-in delay-300">
                    <div class="floating-card card-1 hover-glow tilt-card">
                        <i class="fas fa-users text-primary"></i>
                        <span class="small fw-bold">CRM</span>
                    </div>
                    <div class="floating-card card-2 hover-glow tilt-card">
                        <i class="fas fa-shield-alt text-success"></i>
                        <span class="small fw-bold">Policies</span>
                    </div>
                    <div class="floating-card card-3 hover-glow tilt-card">
                        <i class="fab fa-whatsapp text-success"></i>
                        <span class="small fw-bold">WhatsApp</span>
                    </div>
                    <div class="floating-card card-4 hover-glow tilt-card">
                        <i class="fas fa-chart-line text-danger"></i>
                        <span class="small fw-bold">Analytics</span>
                    </div>
                    <div class="floating-card card-5 hover-glow tilt-card">
                        <i class="fas fa-file-invoice-dollar text-warning"></i>
                        <span class="small fw-bold">Quotes</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: 10%; left: 5%; width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="top: 50%; right: 10%; width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-500" style="bottom: 20%; left: 15%; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
    </div>
</section>

<!-- Insurance Types Banner -->
<section class="py-4 bg-light border-bottom">
    <div class="container">
        <div class="row text-center g-3">
            <div class="col-6 col-md-3 scroll-reveal">
                <div class="insurance-type-badge">
                    <i class="fas fa-car text-primary mb-2" style="font-size: 2.5rem;"></i>
                    <div class="fw-semibold">Motor Insurance</div>
                    <small class="text-muted">Comprehensive Coverage</small>
                </div>
            </div>
            <div class="col-6 col-md-3 scroll-reveal delay-100">
                <div class="insurance-type-badge">
                    <i class="fas fa-heartbeat text-danger mb-2" style="font-size: 2.5rem;"></i>
                    <div class="fw-semibold">Health Insurance</div>
                    <small class="text-muted">Family Protection</small>
                </div>
            </div>
            <div class="col-6 col-md-3 scroll-reveal delay-200">
                <div class="insurance-type-badge">
                    <i class="fas fa-home text-success mb-2" style="font-size: 2.5rem;"></i>
                    <div class="fw-semibold">Home Insurance</div>
                    <small class="text-muted">Property Security</small>
                </div>
            </div>
            <div class="col-6 col-md-3 scroll-reveal delay-300">
                <div class="insurance-type-badge">
                    <i class="fas fa-umbrella text-info mb-2" style="font-size: 2.5rem;"></i>
                    <div class="fw-semibold">Life Insurance</div>
                    <small class="text-muted">Future Planning</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trust Indicators -->
<section class="py-5 bg-white border-bottom">
    <div class="container">
        <div class="row align-items-center g-4 text-center">
            <div class="col-md-3 col-6 scroll-reveal">
                <div class="trust-indicator">
                    <i class="fas fa-shield-check text-success mb-2" style="font-size: 2rem;"></i>
                    <div class="fw-bold">Bank-Grade Security</div>
                    <small class="text-muted">AES-256 Encryption</small>
                </div>
            </div>
            <div class="col-md-3 col-6 scroll-reveal delay-100">
                <div class="trust-indicator">
                    <i class="fas fa-cloud-upload-alt text-primary mb-2" style="font-size: 2rem;"></i>
                    <div class="fw-bold">Cloud-Based</div>
                    <small class="text-muted">Access Anywhere</small>
                </div>
            </div>
            <div class="col-md-3 col-6 scroll-reveal delay-200">
                <div class="trust-indicator">
                    <i class="fas fa-headset text-info mb-2" style="font-size: 2rem;"></i>
                    <div class="fw-bold">24/7 Support</div>
                    <small class="text-muted">Always Available</small>
                </div>
            </div>
            <div class="col-md-3 col-6 scroll-reveal delay-300">
                <div class="trust-indicator">
                    <i class="fas fa-sync-alt text-warning mb-2" style="font-size: 2rem;"></i>
                    <div class="fw-bold">Auto Backups</div>
                    <small class="text-muted">Never Lose Data</small>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Features Section -->
<section class="section-modern bg-white bg-pattern-dots">
    <div class="container">
        <div class="section-header scroll-reveal">
            <span class="badge badge-gradient mb-3 px-4 py-2">Complete Solution</span>
            <h2>All The Modules You Need</h2>
            <p>Comprehensive features built specifically for insurance agencies to boost productivity and revenue</p>
        </div>

        <div class="row g-4">
            <!-- Customer Management -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift">
                    <div class="icon-box bg-primary bg-opacity-10">
                        <i class="fas fa-users text-dark"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Customer Management</h5>
                    <p class="text-muted mb-4">Complete CRM with 360° customer view, policy history, automated renewal tracking, and smart segmentation.</p>
                    <a href="{{ url('/features/customer-management') }}" class="btn btn-sm btn-outline-primary" data-cta="feature-customer-mgmt">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Family Management -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-100">
                    <div class="icon-box bg-success bg-opacity-10">
                        <i class="fas fa-user-friends text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Family Management</h5>
                    <p class="text-muted mb-4">Group families together and manage dependent policies efficiently with unified billing and communications.</p>
                    <a href="{{ url('/features/family-management') }}" class="btn btn-sm btn-outline-success" data-cta="feature-family-mgmt">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Customer Portal -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-200">
                    <div class="icon-box bg-info bg-opacity-10">
                        <i class="fas fa-user-circle text-info"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Customer Self-Service Portal</h5>
                    <p class="text-muted mb-4">Branded portal for customers to view policies, submit claims, download documents, and track status 24/7.</p>
                    <a href="{{ url('/features/customer-portal') }}" class="btn btn-sm btn-outline-info" data-cta="feature-portal">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Lead Management -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-300">
                    <div class="icon-box bg-warning bg-opacity-10">
                        <i class="fas fa-user-plus text-warning"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Lead Management & Conversion</h5>
                    <p class="text-muted mb-4">Powerful lead tracking with automated workflows, follow-up reminders, conversion analytics, and pipeline management.</p>
                    <a href="{{ url('/features/lead-management') }}" class="btn btn-sm btn-outline-warning" data-cta="feature-lead-mgmt">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Policy Management -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-400">
                    <div class="icon-box bg-primary bg-opacity-10">
                        <i class="fas fa-shield-alt text-dark"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Policy Management</h5>
                    <p class="text-muted mb-4">Manage all insurance types with automated renewal reminders, expiry alerts, and comprehensive policy lifecycle tracking.</p>
                    <a href="{{ url('/features/policy-management') }}" class="btn btn-sm btn-outline-primary" data-cta="feature-policy-mgmt">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Claims Management -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-500">
                    <div class="icon-box bg-danger bg-opacity-10">
                        <i class="fas fa-file-medical text-danger"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Claims Management</h5>
                    <p class="text-muted mb-4">Complete claims processing with status tracking, document management, settlement monitoring, and customer notifications.</p>
                    <a href="{{ url('/features/claims-management') }}" class="btn btn-sm btn-outline-danger" data-cta="feature-claims-mgmt">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- WhatsApp Integration -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-600">
                    <div class="icon-box bg-success bg-opacity-10">
                        <i class="fab fa-whatsapp text-success"></i>
                    </div>
                    <h5 class="fw-bold mb-3">WhatsApp Integration</h5>
                    <p class="text-muted mb-4">Send automated messages, reminders, documents, and quotations directly to customers via WhatsApp Business API.</p>
                    <a href="{{ url('/features/whatsapp-integration') }}" class="btn btn-sm btn-outline-success" data-cta="feature-whatsapp">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Quotation System -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-700">
                    <div class="icon-box bg-info bg-opacity-10">
                        <i class="fas fa-file-invoice-dollar text-info"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Professional Quotation System</h5>
                    <p class="text-muted mb-4">Generate beautiful, branded quotations instantly with PDF export, email/WhatsApp delivery, and comparison tools.</p>
                    <a href="{{ url('/features/quotation-system') }}" class="btn btn-sm btn-outline-info" data-cta="feature-quotation">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>

            <!-- Analytics & Reports -->
            <div class="col-md-6 col-lg-4">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-800">
                    <div class="icon-box bg-primary bg-opacity-10">
                        <i class="fas fa-chart-line text-dark"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Analytics & Business Intelligence</h5>
                    <p class="text-muted mb-4">Comprehensive dashboards and reports with real-time insights for data-driven business decisions and growth strategies.</p>
                    <a href="{{ url('/features/analytics-reports') }}" class="btn btn-sm btn-outline-primary" data-cta="feature-analytics">
                        Learn More <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="text-center mt-5 scroll-reveal">
            <a href="{{ url('/features') }}" class="btn btn-modern btn-gradient btn-lg px-5 py-3" data-cta="view-all-features">
                <i class="fas fa-th me-2"></i>Explore All 14 Features in Detail
            </a>
            <p class="mt-3 text-muted small">See how each feature can transform your insurance business</p>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="section-modern gradient-primary text-white">
    <div class="container">
        <div class="row text-center g-4">
            <div class="col-6 col-md-3 scroll-reveal">
                <div class="stat-card">
                    <div class="stat-number" data-count="99">0</div>
                    <div class="h4 fw-bold mb-2">.9%</div>
                    <p class="mb-0 opacity-75">Uptime SLA Guaranteed</p>
                </div>
            </div>
            <div class="col-6 col-md-3 scroll-reveal delay-100">
                <div class="stat-card">
                    <div class="stat-number" data-count="500">0</div>
                    <div class="h4 fw-bold mb-2">+</div>
                    <p class="mb-0 opacity-75">Active Insurance Agencies</p>
                </div>
            </div>
            <div class="col-6 col-md-3 scroll-reveal delay-200">
                <div class="stat-card">
                    <div class="stat-number" data-count="50">0</div>
                    <div class="h4 fw-bold mb-2">K+</div>
                    <p class="mb-0 opacity-75">Policies Managed Daily</p>
                </div>
            </div>
            <div class="col-6 col-md-3 scroll-reveal delay-300">
                <div class="stat-card">
                    <div class="h2 display-3 fw-bold mb-2">24/7</div>
                    <p class="mb-0 opacity-75">Expert Support Available</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Pricing Section -->
@if($plans->count() > 0)
<section class="section-modern bg-light" id="pricing">
    <div class="container">
        <div class="section-header scroll-reveal">
            <span class="badge badge-gradient mb-3 px-4 py-2">Flexible Pricing</span>
            <h2>Choose Your Perfect Plan</h2>
            <p>Transparent pricing with no hidden fees. <strong>Start with a 14-day free trial!</strong> No credit card required.</p>
        </div>

        <div class="row g-4 justify-content-center">
            @foreach($plans as $index => $plan)
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card modern-card scroll-reveal hover-lift {{ $index === 1 ? 'featured border-primary shadow-lg' : '' }}" style="animation-delay: {{ $index * 0.1 }}s;">
                        @if($index === 1)
                            <div class="popular-badge animate-pulse">
                                <i class="fas fa-crown me-1"></i>Most Popular Choice
                            </div>
                        @endif

                        <h4 class="fw-bold mb-2">{{ $plan->name }}</h4>
                        <p class="text-muted small mb-4">{{ $plan->description }}</p>

                        <div class="mb-4">
                            <h2 class="display-3 fw-bolder mb-0">
                                ₹{{ number_format($plan->price, 0) }}
                                <small class="fs-5 text-muted fw-normal">/{{ $plan->billing_interval }}</small>
                            </h2>
                            <small class="text-success"><i class="fas fa-check-circle me-1"></i>Save with annual billing</small>
                        </div>

                        <ul class="list-unstyled mb-4">
                            <li class="mb-3 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span><strong>{{ $plan->max_users == -1 ? 'Unlimited' : $plan->max_users }}</strong> Team Members</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span><strong>{{ $plan->max_customers == -1 ? 'Unlimited' : number_format($plan->max_customers) }}</strong> Customer Records</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span><strong>{{ $plan->max_leads_per_month == -1 ? 'Unlimited' : number_format($plan->max_leads_per_month) }}</strong> Leads per Month</span>
                            </li>
                            <li class="mb-3 d-flex align-items-start">
                                <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                <span><strong>{{ $plan->storage_limit_gb }} GB</strong> Secure Cloud Storage</span>
                            </li>
                            @foreach(array_slice($plan->features ?? [], 0, 4) as $feature)
                                <li class="mb-3 d-flex align-items-start">
                                    <i class="fas fa-check-circle text-success me-3 mt-1"></i>
                                    <span>{{ $feature }}</span>
                                </li>
                            @endforeach
                        </ul>

                        <a href="{{ url('/pricing') }}" class="btn {{ $index === 1 ? 'btn-gradient animate-glow' : 'btn-outline-primary' }} w-100 btn-lg py-3 mb-3" data-cta="pricing-{{ strtolower($plan->name) }}">
                            <i class="fas fa-rocket me-2"></i>Start Free Trial
                        </a>
                        <small class="text-muted text-center d-block">No credit card required</small>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="text-center mt-5 scroll-reveal">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="alert alert-info border-0 shadow-sm d-inline-block">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>All plans include:</strong> 14-day free trial • No credit card required • Cancel anytime • Full feature access
                    </div>
                </div>
            </div>
            <a href="{{ url('/pricing') }}" class="btn btn-outline-primary btn-lg mt-3" data-cta="view-detailed-pricing">
                <i class="fas fa-table me-2"></i>View Detailed Feature Comparison
            </a>
        </div>
    </div>
</section>
@endif

<!-- Testimonials Section -->
<section class="section-modern bg-white">
    <div class="container">
        <div class="section-header scroll-reveal">
            <span class="badge badge-gradient mb-3 px-4 py-2">Success Stories</span>
            <h2>Loved by Insurance Professionals</h2>
            <p>See what insurance agency owners are saying about Midas Portal</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4 col-md-6 scroll-reveal">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-warning me-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <small class="text-muted">5.0</small>
                    </div>
                    <p class="mb-4">Midas Portal transformed our agency operations. The WhatsApp integration alone saves us 10+ hours weekly. ROI was immediate!</p>
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-primary bg-opacity-10 me-3" style="width: 50px; height: 50px; font-size: 1.25rem;">
                            <i class="fas fa-user text-primary"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Rajesh Kumar</div>
                            <small class="text-muted">Director, Shield Insurance</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 scroll-reveal delay-100">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-warning me-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <small class="text-muted">5.0</small>
                    </div>
                    <p class="mb-4">Best insurance software in India! The customer portal reduced support calls by 60%. Our clients love the self-service features.</p>
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-success bg-opacity-10 me-3" style="width: 50px; height: 50px; font-size: 1.25rem;">
                            <i class="fas fa-user text-success"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Priya Sharma</div>
                            <small class="text-muted">CEO, SecureLife Agency</small>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-6 scroll-reveal delay-200">
                <div class="testimonial-card">
                    <div class="d-flex align-items-center mb-3">
                        <div class="text-warning me-2">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <small class="text-muted">5.0</small>
                    </div>
                    <p class="mb-4">The automated reminders and analytics helped us increase renewal rates by 35%. Excellent platform with outstanding support!</p>
                    <div class="d-flex align-items-center">
                        <div class="icon-box bg-info bg-opacity-10 me-3" style="width: 50px; height: 50px; font-size: 1.25rem;">
                            <i class="fas fa-user text-info"></i>
                        </div>
                        <div>
                            <div class="fw-bold">Amit Patel</div>
                            <small class="text-muted">Owner, Prime Insurance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Final CTA Section -->
<section class="cta-modern text-white position-relative">
    <div class="container position-relative z-index-2">
        <div class="row justify-content-center">
            <div class="col-lg-10 text-center scroll-reveal">
                <span class="badge bg-white text-primary mb-4 px-4 py-2 shadow">
                    <i class="fas fa-rocket me-2"></i>Limited Time Offer
                </span>
                <h2 class="display-3 fw-bolder mb-4">Ready to Transform Your Insurance Business?</h2>
                <p class="lead mb-5 fs-3">
                    Join <strong>500+ insurance agencies</strong> already using Midas Portal to grow their business. Start your <strong>14-day free trial</strong> today!
                </p>
                <div class="d-flex flex-wrap gap-3 justify-content-center mb-4">
                    <a href="{{ url('/pricing') }}" class="btn btn-light btn-lg px-5 py-3 shadow-lg hover-lift" data-cta="final-cta-start-trial">
                        <i class="fas fa-rocket me-2"></i>Start Your Free Trial Now
                    </a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg px-5 py-3 hover-lift" data-cta="final-cta-schedule-demo">
                        <i class="fas fa-calendar-alt me-2"></i>Schedule a Live Demo
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-4 justify-content-center small">
                    <div><i class="fas fa-check-circle me-2"></i><strong>No credit card</strong> required</div>
                    <div><i class="fas fa-check-circle me-2"></i><strong>Full access</strong> to all features</div>
                    <div><i class="fas fa-check-circle me-2"></i><strong>Cancel anytime</strong> - no questions asked</div>
                    <div><i class="fas fa-check-circle me-2"></i><strong>Setup support</strong> included</div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('styles')
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

    /* Custom Hero Styles */
    .min-vh-75 {
        min-height: 75vh;
    }

    .z-index-2 {
        z-index: 2;
    }

    /* Insurance Type Badge */
    .insurance-type-badge {
        padding: 20px 15px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
        border-radius: 12px;
    }

    .insurance-type-badge:hover {
        transform: translateY(-8px);
        background: white;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    /* Hero Illustration */
    .hero-illustration {
        position: relative;
        height: 500px;
    }

    .floating-card {
        position: absolute;
        background: white;
        width: 110px;
        height: 110px;
        border-radius: 20px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        animation: float 4s ease-in-out infinite;
        transition: all 0.3s ease;
        border: 1px solid rgba(0,0,0,0.05);
    }

    .floating-card:hover {
        transform: scale(1.15) rotate(5deg);
        box-shadow: 0 25px 50px rgba(0,0,0,0.2);
        z-index: 10;
    }

    .floating-card i {
        font-size: 2.5rem;
        margin-bottom: 8px;
    }

    .card-1 {
        top: 5%;
        left: 5%;
        animation-delay: 0s;
    }

    .card-2 {
        top: 10%;
        right: 8%;
        animation-delay: 0.5s;
    }

    .card-3 {
        bottom: 40%;
        left: 12%;
        animation-delay: 1s;
    }

    .card-4 {
        bottom: 8%;
        right: 5%;
        animation-delay: 1.5s;
    }

    .card-5 {
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        animation-delay: 2s;
        width: 130px;
        height: 130px;
    }

    /* Trust Indicators */
    .trust-indicator {
        padding: 1.5rem;
        transition: all 0.3s ease;
    }

    .trust-indicator:hover {
        transform: scale(1.05);
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

    /* Responsive adjustments */
    @media (max-width: 991px) {
        .hero-illustration {
            height: 400px;
        }

        .floating-card {
            width: 90px;
            height: 90px;
        }

        .floating-card i {
            font-size: 2rem;
        }

        .card-5 {
            width: 110px;
            height: 110px;
        }
    }

    @media (max-width: 767px) {
        .hero-illustration {
            height: 350px;
        }

        .floating-card {
            width: 75px;
            height: 75px;
        }

        .floating-card i {
            font-size: 1.5rem;
        }

        .floating-card span {
            font-size: 0.7rem;
        }

        .card-5 {
            width: 95px;
            height: 95px;
        }

        .section-header h2 {
            font-size: 2rem !important;
        }
    }
</style>
@endsection
