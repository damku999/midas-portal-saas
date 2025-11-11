@extends('public.layout')

@section('title', 'Complete Features & Modules - Midas Portal')

@section('meta_description', 'Explore 14 powerful modules: CRM, policy management, claims tracking, WhatsApp integration, analytics, and more. Complete insurance automation features for modern agencies.')

@section('meta_keywords', 'insurance management features, crm features insurance, policy management features, insurance automation tools, insurance crm modules, claims management system, insurance whatsapp integration, insurance analytics')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-8 text-white">
                <h1 class="display-3 fw-bold mb-4">Complete Insurance Management Solution</h1>
                <p class="lead mb-4">Explore all 14 powerful modules designed specifically for insurance agencies. From CRM to analytics, we've got everything you need to run your business efficiently.</p>
                <a href="#modules" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-arrow-down me-2"></i>Explore Modules
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Quick Navigation -->
<section class="py-3 bg-white border-bottom sticky-top" style="top: 76px; z-index: 1000;">
    <div class="container">
        <div class="d-flex flex-wrap gap-2 justify-content-center">
            <a href="#customer-management" class="btn btn-sm btn-outline-primary">Customer</a>
            <a href="#family-management" class="btn btn-sm btn-outline-primary">Family</a>
            <a href="#customer-portal" class="btn btn-sm btn-outline-primary">Portal</a>
            <a href="#lead-management" class="btn btn-sm btn-outline-primary">Leads</a>
            <a href="#policy-management" class="btn btn-sm btn-outline-primary">Policies</a>
            <a href="#claims-management" class="btn btn-sm btn-outline-primary">Claims</a>
            <a href="#whatsapp" class="btn btn-sm btn-outline-primary">WhatsApp</a>
            <a href="#quotation" class="btn btn-sm btn-outline-primary">Quotation</a>
            <a href="#analytics" class="btn btn-sm btn-outline-primary">Analytics</a>
            <a href="#commission" class="btn btn-sm btn-outline-primary">Commission</a>
            <a href="#documents" class="btn btn-sm btn-outline-primary">Documents</a>
            <a href="#staff" class="btn btn-sm btn-outline-primary">Staff</a>
            <a href="#master-data" class="btn btn-sm btn-outline-primary">Master Data</a>
            <a href="#notifications" class="btn btn-sm btn-outline-primary">Notifications</a>
        </div>
    </div>
</section>

<!-- All Modules Grid -->
<section class="py-5 bg-light" id="modules">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">All Modules</span>
            <h2 class="display-5 fw-bold mt-2 mb-3">14 Powerful Modules to Manage Everything</h2>
            <p class="lead text-muted">Click on any module to learn more about its features and benefits</p>
        </div>

        <div class="row g-4">
            <!-- Customer Management -->
            <div class="col-md-6 col-lg-4" id="customer-management">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Customer Management</h4>
                    <p class="text-muted mb-3">Complete 360° CRM with customer profiles, policy history, renewal tracking, and communication management.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Complete customer profiles</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Policy history tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Document storage</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Communication history</li>
                    </ul>
                    <a href="{{ url('/features/customer-management') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Family Management -->
            <div class="col-md-6 col-lg-4" id="family-management">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Family Management</h4>
                    <p class="text-muted mb-3">Group and manage family members with their insurance policies in one organized view.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Family grouping</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Dependent management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Family policy tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Shared documents</li>
                    </ul>
                    <a href="{{ url('/features/family-management') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Customer Portal -->
            <div class="col-md-6 col-lg-4" id="customer-portal">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Customer Portal</h4>
                    <p class="text-muted mb-3">Self-service portal for customers to access policies, submit claims, and manage their accounts 24/7.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Policy dashboard</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Document downloads</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Online claim submission</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Profile management</li>
                    </ul>
                    <a href="{{ url('/features/customer-portal') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Lead Management -->
            <div class="col-md-6 col-lg-4" id="lead-management">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Lead Management</h4>
                    <p class="text-muted mb-3">Powerful lead tracking system with automated workflows and conversion analytics.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Lead capture & scoring</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Lead assignment</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Follow-up automation</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Conversion tracking</li>
                    </ul>
                    <a href="{{ url('/features/lead-management') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Policy Management -->
            <div class="col-md-6 col-lg-4" id="policy-management">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Policy Management</h4>
                    <p class="text-muted mb-3">Manage all insurance types with renewal tracking, premium calculations, and document management.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>All insurance types</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Renewal tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Premium calculations</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>NCB tracking</li>
                    </ul>
                    <a href="{{ url('/features/policy-management') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Claims Management -->
            <div class="col-md-6 col-lg-4" id="claims-management">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-file-medical"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Claims Management</h4>
                    <p class="text-muted mb-3">Complete claims processing with status tracking and settlement management.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Claim registration</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Document management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Status tracking</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Settlement tracking</li>
                    </ul>
                    <a href="{{ url('/features/claims-management') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- WhatsApp Integration -->
            <div class="col-md-6 col-lg-4" id="whatsapp">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <h4 class="fw-bold mb-3">WhatsApp Integration</h4>
                    <p class="text-muted mb-3">Powerful WhatsApp Business API integration for automated messaging and document sharing.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Bulk messaging</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Template management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Automated reminders</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Document sharing</li>
                    </ul>
                    <a href="{{ url('/features/whatsapp-integration') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Quotation System -->
            <div class="col-md-6 col-lg-4" id="quotation">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Quotation System</h4>
                    <p class="text-muted mb-3">Generate professional quotations instantly with customizable templates and PDF export.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Quick quote generation</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Customizable templates</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Premium breakdown</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>PDF export</li>
                    </ul>
                    <a href="{{ url('/features/quotation-system') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Analytics & Reports -->
            <div class="col-md-6 col-lg-4" id="analytics">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Analytics & Reports</h4>
                    <p class="text-muted mb-3">Comprehensive dashboards and reports for data-driven business decisions.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Revenue analytics</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Agent performance</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Policy reports</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Custom report builder</li>
                    </ul>
                    <a href="{{ url('/features/analytics-reports') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Commission Tracking -->
            <div class="col-md-6 col-lg-4" id="commission">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-rupee-sign"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Commission Tracking</h4>
                    <p class="text-muted mb-3">Automated commission calculations and agent payout management system.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Auto calculations</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Multi-level commissions</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Payout management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>TDS calculation</li>
                    </ul>
                    <a href="{{ url('/features/commission-tracking') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Document Management -->
            <div class="col-md-6 col-lg-4" id="documents">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-folder-open"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Document Management</h4>
                    <p class="text-muted mb-3">Secure cloud storage for all policy documents and customer records.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Unlimited storage</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Category organization</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Version control</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Secure sharing</li>
                    </ul>
                    <a href="{{ url('/features/document-management') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Staff Management -->
            <div class="col-md-6 col-lg-4" id="staff">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Staff & Role Management</h4>
                    <p class="text-muted mb-3">Manage your team with role-based access control and performance tracking.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Role-based permissions</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>User management</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Activity logs</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Performance metrics</li>
                    </ul>
                    <a href="{{ url('/features/staff-management') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Master Data -->
            <div class="col-md-6 col-lg-4" id="master-data">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-database"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Master Data Management</h4>
                    <p class="text-muted mb-3">Centralized management of insurance companies, vehicles, RTOs, and all master data.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Insurance companies</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Vehicle database</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>RTO codes</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Bulk import/export</li>
                    </ul>
                    <a href="{{ url('/features/master-data-management') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>

            <!-- Notifications -->
            <div class="col-md-6 col-lg-4" id="notifications">
                <div class="module-card">
                    <div class="module-icon">
                        <i class="fas fa-bell"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Notifications & Alerts</h4>
                    <p class="text-muted mb-3">Multi-channel automated notifications via Email, SMS, and WhatsApp.</p>
                    <ul class="feature-list mb-4">
                        <li><i class="fas fa-check-circle text-success me-2"></i>Renewal reminders</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Multi-channel delivery</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Custom alert rules</li>
                        <li><i class="fas fa-check-circle text-success me-2"></i>Birthday wishes</li>
                    </ul>
                    <a href="{{ url('/features/notifications-alerts') }}" class="btn btn-outline-primary w-100">
                        Learn More <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-4">Ready to Experience All These Features?</h2>
                <p class="lead text-muted mb-4">Start your 14-day free trial and see how Midas Portal can transform your insurance business.</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ url('/pricing') }}" class="btn btn-gradient btn-lg px-5">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-primary btn-lg px-5">
                        <i class="fas fa-phone me-2"></i>Schedule a Demo
                    </a>
                </div>
                <p class="mt-3 small text-muted">No credit card required • 14-day free trial • Cancel anytime</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* Module Cards */
    .module-card {
        background: white;
        padding: 2rem;
        border-radius: 15px;
        border: 2px solid #e9ecef;
        height: 100%;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
    }

    .module-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        border-color: var(--primary-color);
    }

    .module-icon {
        width: 70px;
        height: 70px;
        background: var(--gradient-primary);
        border-radius: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2rem;
        color: white;
        margin-bottom: 1.5rem;
        transition: all 0.3s ease;
    }

    .module-card:hover .module-icon {
        transform: scale(1.1) rotate(5deg);
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

    /* Smooth Scroll */
    html {
        scroll-behavior: smooth;
    }

    /* Navigation buttons hover */
    .btn-outline-primary:hover {
        transform: translateY(-2px);
    }
</style>
@endpush

@push('scripts')
<script>
// Smooth scroll for navigation links
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                const offset = 150; // Account for sticky header
                const targetPosition = target.getBoundingClientRect().top + window.pageYOffset - offset;
                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            }
        });
    });

    // Highlight active navigation button on scroll
    const navButtons = document.querySelectorAll('.sticky-top a[href^="#"]');
    const sections = document.querySelectorAll('[id]');

    window.addEventListener('scroll', () => {
        let current = '';
        sections.forEach(section => {
            const sectionTop = section.offsetTop;
            if (pageYOffset >= (sectionTop - 200)) {
                current = section.getAttribute('id');
            }
        });

        navButtons.forEach(button => {
            button.classList.remove('btn-primary');
            button.classList.add('btn-outline-primary');
            if (button.getAttribute('href') === `#${current}`) {
                button.classList.remove('btn-outline-primary');
                button.classList.add('btn-primary');
            }
        });
    });
});
</script>
@endpush
