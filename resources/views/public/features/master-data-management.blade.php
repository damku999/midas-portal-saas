@extends('public.layout')

@section('title', 'Master Data Management - Midas Portal')
@section('meta_description', 'Centralized management of insurance companies, vehicles, RTOs, and all master data. Bulk import/export, smart ordering, and dynamic seeding for efficient data administration.')
@section('meta_keywords', 'master data management, insurance companies database, vehicle database, RTO codes, bulk import, data administration')

@section('content')
<!-- Hero Section -->
@include('public.components.cta-section', [
    'title' => 'Master Data Management',
    'description' => 'Centralized management of insurance companies, vehicle databases, RTO codes, and all master data. Streamline data administration with bulk import/export, smart ordering, and dynamic seeding capabilities.',
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
                <h2 class="display-5 fw-bold mb-4">What is Master Data Management?</h2>
                <p class="lead text-muted">Our Master Data Management system provides centralized control over all reference data including insurance companies (with logos and branding), policy types, addon covers, fuel types, document types, and more. Maintain consistency across your organization with smart data organization.</p>
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
                            <i class="fas fa-building fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Insurance Companies</h5>
                        <p class="card-text text-muted">Manage insurance companies with logo uploads, branding colors, contact details, and commission structures.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-car fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Vehicle Database</h5>
                        <p class="card-text text-muted">Comprehensive vehicle database with make, model, variant, and year for accurate policy issuance.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-road fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">RTO Codes</h5>
                        <p class="card-text text-muted">Complete RTO code database for all Indian states with validation and auto-suggestions.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file-upload fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Bulk Import/Export</h5>
                        <p class="card-text text-muted">Import data from Excel/CSV and export for backup or migration with validation and error checking.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sort fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Smart Ordering</h5>
                        <p class="card-text text-muted">Drag-and-drop ordering for display sequence with automatic numbering and position tracking.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-cogs fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Dynamic Seeding</h5>
                        <p class="card-text text-muted">Automated data seeding for new tenants with configurable default values and customization.</p>
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
                        <i class="fas fa-check-circle fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Data Consistency</h5>
                        <p class="text-muted">Centralized master data ensures consistency across all modules and prevents duplication.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-clock fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Save Time</h5>
                        <p class="text-muted">Pre-populated data and bulk import reduce manual data entry time by 80%.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Reduce Errors</h5>
                        <p class="text-muted">Validated master data prevents typos and inconsistencies in policy issuance.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-sync fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Easy Updates</h5>
                        <p class="text-muted">Update master data once and it reflects across all policies, quotations, and reports.</p>
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
                    <h5 class="fw-bold">Access Master Data</h5>
                    <p class="text-muted">Navigate to master data section from admin dashboard.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">2</span>
                    </div>
                    <h5 class="fw-bold">Add or Import</h5>
                    <p class="text-muted">Add items manually or bulk import from Excel/CSV files.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">3</span>
                    </div>
                    <h5 class="fw-bold">Configure Details</h5>
                    <p class="text-muted">Set properties, logos, commission rates, and ordering.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">4</span>
                    </div>
                    <h5 class="fw-bold">Use Everywhere</h5>
                    <p class="text-muted">Master data automatically available in all modules.</p>
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
                        <h5 class="card-title fw-bold">Policy Management</h5>
                        <p class="card-text text-muted">Use master data for policy types, insurance companies, and addons.</p>
                        <a href="{{ url('/features/policy-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Quotation System</h5>
                        <p class="card-text text-muted">Insurance company data and addon covers for quotation generation.</p>
                        <a href="{{ url('/features/quotation-system') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Claims Management</h5>
                        <p class="card-text text-muted">Document type master data for claim processing workflows.</p>
                        <a href="{{ url('/features/claims-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
@include('public.components.cta-section', [
    'title' => 'Ready to Centralize Your Data?',
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
