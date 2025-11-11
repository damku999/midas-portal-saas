@extends('public.layout')

@section('title', 'Complete Features & Modules - Midas Portal')

@section('meta_description', 'Explore 14 powerful modules: CRM, policy management, claims tracking, WhatsApp integration, analytics, and more. Complete insurance automation features for modern agencies.')

@section('meta_keywords', 'insurance management features, crm features insurance, policy management features, insurance automation tools, insurance crm modules, claims management system, insurance whatsapp integration, insurance analytics')

@section('content')
<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <div class="container position-relative z-index-2">
        <div class="row align-items-center min-vh-50">
            <div class="col-lg-8 text-white">
                <span class="badge bg-white text-primary mb-3 px-4 py-2 animate-fade-in-down shadow-sm">
                    <i class="fas fa-th me-2"></i>14 Powerful Modules
                </span>
                <h1 class="display-3 fw-bold mb-4 animate-fade-in-up delay-100">Complete Insurance Management Solution</h1>
                <p class="lead mb-4 animate-fade-in-up delay-200">Explore all 14 powerful modules designed specifically for insurance agencies. From CRM to analytics, we've got everything you need to run your business efficiently.</p>
                <a href="#modules" class="btn btn-light btn-lg px-4 hover-lift animate-fade-in-up delay-300" data-cta="hero-explore">
                    <i class="fas fa-arrow-down me-2"></i>Explore Modules
                </a>
            </div>
        </div>
    </div>
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: 10%; left: 5%; width: 60px; height: 60px; background: rgba(255,255,255,0.2); border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="top: 50%; right: 10%; width: 40px; height: 40px; background: rgba(255,255,255,0.15); border-radius: 50%;"></div>
    </div>
</section>

<!-- Quick Navigation -->
<section class="py-3 bg-white shadow-sm sticky-top animate-fade-in-down" id="quick-nav" style="top: 70px; z-index: 999;">
    <div class="container">
        <div class="d-flex flex-wrap gap-2 justify-content-center small">
            <a href="#customer-management" class="btn btn-sm btn-outline-primary quick-nav-btn">Customer</a>
            <a href="#family-management" class="btn btn-sm btn-outline-primary quick-nav-btn">Family</a>
            <a href="#customer-portal" class="btn btn-sm btn-outline-primary quick-nav-btn">Portal</a>
            <a href="#lead-management" class="btn btn-sm btn-outline-primary quick-nav-btn">Leads</a>
            <a href="#policy-management" class="btn btn-sm btn-outline-primary quick-nav-btn">Policies</a>
            <a href="#claims-management" class="btn btn-sm btn-outline-primary quick-nav-btn">Claims</a>
            <a href="#whatsapp" class="btn btn-sm btn-outline-primary quick-nav-btn">WhatsApp</a>
            <a href="#quotation" class="btn btn-sm btn-outline-primary quick-nav-btn">Quotation</a>
            <a href="#analytics" class="btn btn-sm btn-outline-primary quick-nav-btn">Analytics</a>
            <a href="#commission" class="btn btn-sm btn-outline-primary quick-nav-btn">Commission</a>
            <a href="#documents" class="btn btn-sm btn-outline-primary quick-nav-btn">Documents</a>
            <a href="#staff" class="btn btn-sm btn-outline-primary quick-nav-btn">Staff</a>
            <a href="#master-data" class="btn btn-sm btn-outline-primary quick-nav-btn">Master Data</a>
            <a href="#notifications" class="btn btn-sm btn-outline-primary quick-nav-btn">Notifications</a>
        </div>
    </div>
</section>

<!-- All Modules Grid -->
<section class="section-modern bg-light bg-pattern-dots" id="modules">
    <div class="container">
        <div class="section-header scroll-reveal">
            <span class="badge badge-gradient mb-3 px-4 py-2">All Modules</span>
            <h2>14 Powerful Modules to Manage Everything</h2>
            <p>Click on any module to learn more about its features and benefits</p>
        </div>

        <div class="row g-4">
            <!-- Customer Management -->
            <div class="col-md-6 col-lg-4" id="customer-management">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift">
                    <div class="icon-box bg-primary bg-opacity-10">
                        <i class="fas fa-users text-dark"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Customer Management</h4>
                    <p class="text-muted mb-3">Complete 360° CRM with customer profiles, policy history, renewal tracking, and communication management.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Complete customer profiles</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Policy history tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Document storage</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Communication history</li>
                    </ul>
                    <a href="{{ url('/features/customer-management') }}" class="btn btn-outline-primary w-100" data-cta="feature-customer">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Family Management -->
            <div class="col-md-6 col-lg-4" id="family-management">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift">
                    <div class="icon-box bg-success bg-opacity-10">
                        <i class="fas fa-user-friends text-success"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Family Management</h4>
                    <p class="text-muted mb-3">Group and manage family members with their insurance policies in one organized view.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Family grouping</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Dependent management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Family policy tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Shared documents</li>
                    </ul>
                    <a href="{{ url('/features/family-management') }}" class="btn btn-outline-success w-100" data-cta="feature-family">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Customer Portal -->
            <div class="col-md-6 col-lg-4" id="customer-portal">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-200">
                    <div class="icon-box bg-info bg-opacity-10">
                        <i class="fas fa-user-circle text-info"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Customer Portal</h4>
                    <p class="text-muted mb-3">Self-service portal for customers to access policies, submit claims, and manage their accounts 24/7.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Policy dashboard</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Document downloads</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Online claim submission</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Profile management</li>
                    </ul>
                    <a href="{{ url('/features/customer-portal') }}" class="btn btn-outline-info w-100" data-cta="feature-portal">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Lead Management -->
            <div class="col-md-6 col-lg-4" id="lead-management">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-300">
                    <div class="icon-box bg-warning bg-opacity-10">
                        <i class="fas fa-user-plus text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Lead Management</h4>
                    <p class="text-muted mb-3">Powerful lead tracking system with automated workflows and conversion analytics.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Lead capture & scoring</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Lead assignment</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Follow-up automation</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Conversion tracking</li>
                    </ul>
                    <a href="{{ url('/features/lead-management') }}" class="btn btn-outline-warning w-100" data-cta="feature-leads">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Policy Management -->
            <div class="col-md-6 col-lg-4" id="policy-management">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-400">
                    <div class="icon-box bg-primary bg-opacity-10">
                        <i class="fas fa-shield-alt text-dark"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Policy Management</h4>
                    <p class="text-muted mb-3">Manage all insurance types with renewal tracking, premium calculations, and document management.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>All insurance types</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Renewal tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Premium calculations</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>NCB tracking</li>
                    </ul>
                    <a href="{{ url('/features/policy-management') }}" class="btn btn-outline-primary w-100" data-cta="feature-policy">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Claims Management -->
            <div class="col-md-6 col-lg-4" id="claims-management">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-500">
                    <div class="icon-box bg-danger bg-opacity-10">
                        <i class="fas fa-file-medical text-danger"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Claims Management</h4>
                    <p class="text-muted mb-3">Complete claims processing with status tracking and settlement management.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Claim registration</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Document management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Status tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Settlement tracking</li>
                    </ul>
                    <a href="{{ url('/features/claims-management') }}" class="btn btn-outline-danger w-100" data-cta="feature-claims">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- WhatsApp Integration -->
            <div class="col-md-6 col-lg-4" id="whatsapp">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-600">
                    <div class="icon-box bg-success bg-opacity-10">
                        <i class="fab fa-whatsapp text-success"></i>
                    </div>
                    <h4 class="fw-bold mb-3">WhatsApp Integration</h4>
                    <p class="text-muted mb-3">Powerful WhatsApp Business API integration for automated messaging and document sharing.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Bulk messaging</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Template management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Automated reminders</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Document sharing</li>
                    </ul>
                    <a href="{{ url('/features/whatsapp-integration') }}" class="btn btn-outline-success w-100" data-cta="feature-whatsapp">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Quotation System -->
            <div class="col-md-6 col-lg-4" id="quotation">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-700">
                    <div class="icon-box bg-info bg-opacity-10">
                        <i class="fas fa-file-invoice-dollar text-info"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Quotation System</h4>
                    <p class="text-muted mb-3">Generate professional quotations instantly with customizable templates and PDF export.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Quick quote generation</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Customizable templates</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Premium breakdown</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>PDF export</li>
                    </ul>
                    <a href="{{ url('/features/quotation-system') }}" class="btn btn-outline-info w-100" data-cta="feature-quotation">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Analytics & Reports -->
            <div class="col-md-6 col-lg-4" id="analytics">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-800">
                    <div class="icon-box bg-primary bg-opacity-10">
                        <i class="fas fa-chart-line text-dark"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Analytics & Reports</h4>
                    <p class="text-muted mb-3">Comprehensive dashboards and reports for data-driven business decisions.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Revenue analytics</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Agent performance</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Policy reports</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Custom report builder</li>
                    </ul>
                    <a href="{{ url('/features/analytics-reports') }}" class="btn btn-outline-primary w-100" data-cta="feature-analytics">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Commission Tracking -->
            <div class="col-md-6 col-lg-4" id="commission">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift">
                    <div class="icon-box bg-success bg-opacity-10">
                        <i class="fas fa-rupee-sign text-success"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Commission Tracking</h4>
                    <p class="text-muted mb-3">Automated commission calculations and agent payout management system.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Auto calculations</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Multi-level commissions</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Payout management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>TDS calculation</li>
                    </ul>
                    <a href="{{ url('/features/commission-tracking') }}" class="btn btn-outline-success w-100" data-cta="feature-commission">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Document Management -->
            <div class="col-md-6 col-lg-4" id="documents">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-100">
                    <div class="icon-box bg-warning bg-opacity-10">
                        <i class="fas fa-folder-open text-warning"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Document Management</h4>
                    <p class="text-muted mb-3">Secure cloud storage for all policy documents and customer records.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Unlimited storage</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Category organization</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Version control</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Secure sharing</li>
                    </ul>
                    <a href="{{ url('/features/document-management') }}" class="btn btn-outline-warning w-100" data-cta="feature-documents">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Staff Management -->
            <div class="col-md-6 col-lg-4" id="staff">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-200">
                    <div class="icon-box bg-info bg-opacity-10">
                        <i class="fas fa-user-tie text-info"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Staff & Role Management</h4>
                    <p class="text-muted mb-3">Manage your team with role-based access control and performance tracking.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Role-based permissions</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>User management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Activity logs</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Performance metrics</li>
                    </ul>
                    <a href="{{ url('/features/staff-management') }}" class="btn btn-outline-info w-100" data-cta="feature-staff">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Master Data -->
            <div class="col-md-6 col-lg-4" id="master-data">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-300">
                    <div class="icon-box bg-danger bg-opacity-10">
                        <i class="fas fa-database text-danger"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Master Data Management</h4>
                    <p class="text-muted mb-3">Centralized management of insurance companies, vehicles, RTOs, and all master data.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Insurance companies</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Vehicle database</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>RTO codes</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Bulk import/export</li>
                    </ul>
                    <a href="{{ url('/features/master-data-management') }}" class="btn btn-outline-danger w-100" data-cta="feature-master-data">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <div class="col-md-6 col-lg-4" id="notifications">
                <div class="modern-card modern-card-gradient scroll-reveal hover-lift delay-400">
                    <div class="icon-box bg-primary bg-opacity-10">
                        <i class="fas fa-bell text-dark"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Notifications & Alerts</h4>
                    <p class="text-muted mb-3">Multi-channel automated notifications via Email, SMS, and WhatsApp.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Renewal reminders</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Multi-channel delivery</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Custom alert rules</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Birthday wishes</li>
                    </ul>
                    <a href="{{ url('/features/notifications-alerts') }}" class="btn btn-outline-primary w-100" data-cta="feature-notifications">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta-modern text-white position-relative">
    <div class="container position-relative z-index-2">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center scroll-reveal">
                <span class="badge bg-white text-primary mb-4 px-4 py-2 shadow">
                    <i class="fas fa-rocket me-2"></i>Complete Solution
                </span>
                <h2 class="display-4 fw-bold mb-4">Ready to Experience All These Features?</h2>
                <p class="lead mb-5 fs-4">Start your <strong>14-day free trial</strong> and see how Midas Portal can transform your insurance business.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center mb-4">
                    <a href="{{ url('/pricing') }}" class="btn btn-light btn-lg px-5 py-3 shadow-lg hover-lift" data-cta="cta-start-trial">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-light btn-lg px-5 py-3 hover-lift" data-cta="cta-schedule-demo">
                        <i class="fas fa-phone me-2"></i>Schedule a Demo
                    </a>
                </div>
                <div class="d-flex flex-wrap gap-4 justify-content-center small">
                    <div><i class="fas fa-check-circle me-2"></i><strong>No credit card</strong> required</div>
                    <div><i class="fas fa-check-circle me-2"></i><strong>14-day</strong> free trial</div>
                    <div><i class="fas fa-check-circle me-2"></i><strong>Cancel</strong> anytime</div>
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

    /* Hero Min Height */
    .min-vh-50 {
        min-height: 50vh;
    }

    .z-index-2 {
        z-index: 2;
    }

    .feature-list {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
    }

    .feature-list li {
        padding: 0.5rem 0;
        font-size: 0.95rem;
    }

    /* Navigation buttons animations */
    .quick-nav-btn {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        font-size: 0.85rem;
        padding: 0.375rem 0.75rem;
    }

    .quick-nav-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .quick-nav-btn.active {
        background: var(--primary-color);
        color: white;
        border-color: var(--primary-color);
    }

    #quick-nav {
        backdrop-filter: blur(10px);
        background: rgba(255, 255, 255, 0.95) !important;
    }

    /* Scroll padding to account for sticky header */
    html {
        scroll-padding-top: 200px;
    }

    /* Responsive adjustments */
    @media (max-width: 767px) {
        .section-header h2 {
            font-size: 2rem !important;
        }

        html {
            scroll-padding-top: 180px;
        }

        .quick-nav-btn {
            font-size: 0.75rem;
            padding: 0.3rem 0.6rem;
        }
    }
</style>
@endpush

@push('scripts')
<script>
// Smooth scroll for navigation links with better offset handling
document.addEventListener('DOMContentLoaded', function() {
    const navHeight = 70; // Main navbar height
    const quickNavHeight = document.querySelector('#quick-nav') ? document.querySelector('#quick-nav').offsetHeight : 50;
    const totalOffset = navHeight + quickNavHeight + 20; // Added 20px padding

    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            const href = this.getAttribute('href');
            if (href !== '#' && href !== '#modules') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - totalOffset;
                    window.scrollTo({
                        top: targetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Highlight active navigation button on scroll
    const navButtons = document.querySelectorAll('.quick-nav-btn[href^="#"]');
    const sections = document.querySelectorAll('.modern-card').length > 0 ?
        Array.from(document.querySelectorAll('[id]')).filter(el => el.id && el.id !== 'quick-nav' && el.id !== 'modules') : [];

    if (sections.length > 0) {
        window.addEventListener('scroll', () => {
            let current = '';
            const scrollPos = window.pageYOffset + totalOffset + 50;

            sections.forEach(section => {
                const sectionTop = section.offsetTop;
                const sectionHeight = section.offsetHeight;
                if (scrollPos >= sectionTop && scrollPos < sectionTop + sectionHeight) {
                    current = section.getAttribute('id');
                }
            });

            navButtons.forEach(button => {
                button.classList.remove('active');
                if (button.getAttribute('href') === `#${current}`) {
                    button.classList.add('active');
                }
            });
        });
    }
});
</script>
@endpush
