@extends('public.layout')

@section('title', 'Quotation System - Midas Portal')
@section('meta_description', 'Generate professional insurance quotations instantly with multi-company comparison, IDV calculation, addon covers, and PDF export. Share quotations via WhatsApp and email with automated follow-ups.')
@section('meta_keywords', 'insurance quotation software, quote generation, premium calculator, insurance comparison, quotation PDF, IDV calculator')

@section('content')
<!-- Hero Section -->
@include('public.components.cta-section', [
    'title' => 'Quotation System',
    'description' => 'Generate professional quotations in seconds. Compare up to 10 insurance companies side-by-side, calculate IDV automatically, add addon covers, and share branded PDF quotations instantly via WhatsApp or email.',
    'primaryText' => 'Start Free Trial',
    'primaryUrl' => url('/contact'),
    'primaryIcon' => 'fas fa-rocket',
    'primaryDataCta' => 'hero-start-trial',
    'secondaryText' => 'View Pricing',
    'secondaryUrl' => url('/pricing'),
    'secondaryIcon' => 'fas fa-tag',
    'secondaryDataCta' => 'hero-view-pricing',
    'showNote' => false,
    'containerClass' => 'py-5',
    'colClass' => 'col-lg-8'
])

<!-- Overview Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-4">What is Quotation System?</h2>
                <p class="lead text-muted">Our Quotation System is a powerful multi-company comparison engine with automatic IDV calculation, 9 addon covers, and intelligent pricing recommendations. Generate professional PDF quotations with your branding and share them instantly with customers.</p>
            </div>
        </div>
    </div>
</section>

<!-- Key Features Section -->
<section class="py-5">
    <div class="container py-4">
        <h2 class="display-5 fw-bold text-center mb-5">Key Features</h2>
        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file-invoice-dollar fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Quick Quote Generation</h5>
                        <p class="card-text text-muted">Generate quotations in seconds with auto-numbering (QT/YY/00000001) and customer details pre-fill.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-balance-scale fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Multi-Company Comparison</h5>
                        <p class="card-text text-muted">Compare up to 10 insurance companies side-by-side with automatic ranking by best price.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-calculator fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">IDV Calculation</h5>
                        <p class="card-text text-muted">Automatic Insured Declared Value calculation with 5 components: Base, Age, Depreciation, Condition, Market.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-plus-circle fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">9 Addon Covers</h5>
                        <p class="card-text text-muted">Offer Zero Depreciation, Engine Protection, RSA, NCB Protection, and 5 more addon covers with smart pricing.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file-pdf fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Professional PDF Export</h5>
                        <p class="card-text text-muted">Generate branded PDF quotations with your logo, contact details, and professional layout.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fab fa-whatsapp fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Instant Sharing</h5>
                        <p class="card-text text-muted">Share quotations via WhatsApp, email, or download with automatic follow-up tracking.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Benefits Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <h2 class="display-5 fw-bold text-center mb-5">Why It Matters</h2>
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-stopwatch fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Save Time</h5>
                        <p class="text-muted">Generate quotations 10x faster than manual methods with automated calculations and templates.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Close More Sales</h5>
                        <p class="text-muted">Professional quotations with multiple options increase conversion rates by 40%.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-trophy fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Competitive Advantage</h5>
                        <p class="text-muted">Show customers you've compared all options, building trust and credibility.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-paint-brush fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Professional Branding</h5>
                        <p class="text-muted">Customized quotations with your logo and branding reinforce your agency's professionalism.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="py-5">
    <div class="container py-4">
        <h2 class="display-5 fw-bold text-center mb-5">How It Works</h2>
        <div class="row g-4">
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">1</span>
                    </div>
                    <h5 class="fw-bold">Enter Vehicle Details</h5>
                    <p class="text-muted">Input vehicle information and customer details or link to existing lead.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">2</span>
                    </div>
                    <h5 class="fw-bold">Select Companies</h5>
                    <p class="text-muted">Choose insurance companies to compare and select addon covers.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">3</span>
                    </div>
                    <h5 class="fw-bold">Generate & Review</h5>
                    <p class="text-muted">System calculates premiums and ranks companies by best value.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">4</span>
                    </div>
                    <h5 class="fw-bold">Share Quotation</h5>
                    <p class="text-muted">Send PDF via WhatsApp/Email and track customer engagement.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Related Features Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <h2 class="display-5 fw-bold text-center mb-5">Related Features</h2>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Lead Management</h5>
                        <p class="card-text text-muted">Generate quotations directly from leads and track conversion.</p>
                        <a href="{{ url('/features/lead-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">WhatsApp Integration</h5>
                        <p class="card-text text-muted">Share quotations instantly via WhatsApp with delivery tracking.</p>
                        <a href="{{ url('/features/whatsapp-integration') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Master Data Management</h5>
                        <p class="card-text text-muted">Manage insurance companies, addons, and pricing configurations.</p>
                        <a href="{{ url('/features/master-data-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
@include('public.components.cta-section', [
    'title' => 'Ready to Generate Professional Quotations?',
    'description' => 'Start your 14-day free trial today. No credit card required.',
    'primaryText' => 'Start Free Trial',
    'primaryUrl' => url('/contact'),
    'primaryIcon' => 'fas fa-rocket',
    'primaryDataCta' => 'cta-start-trial',
    'secondaryText' => 'View Pricing',
    'secondaryUrl' => url('/pricing'),
    'secondaryIcon' => 'fas fa-tag',
    'secondaryDataCta' => 'cta-view-pricing',
    'showNote' => false,
    'containerClass' => 'py-5',
    'colClass' => 'col-lg-8 mx-auto text-center'
])
@endsection
