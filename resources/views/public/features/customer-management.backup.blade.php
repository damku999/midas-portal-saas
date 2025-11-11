@extends('public.layout')

@section('title', 'Customer Management - Midas Portal')
@section('meta_description', 'Complete 360° CRM system for insurance agencies. Manage customer data, documents, verification workflows, and family groups with our advanced customer management platform.')
@section('meta_keywords', 'insurance CRM, customer management, policy tracking, customer portal, family groups, KYC verification')

@section('content')
<!-- Hero Section -->
<section class="py-5" style="background: var(--gradient-primary); color: white;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4">Customer Management</h1>
                <p class="lead mb-4">Complete 360° CRM system designed specifically for insurance agencies. Manage your entire customer lifecycle from onboarding to policy renewals with powerful automation and insights.</p>
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg shadow-sm">Start Free Trial</a>
                <a href="{{ url('/pricing') }}" class="btn btn-outline-light btn-lg ms-2">View Pricing</a>
            </div>
        </div>
    </div>
</section>

<!-- Overview Section -->
<section class="py-5 bg-light">
    <div class="container py-4">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-5 fw-bold mb-4">What is Customer Management?</h2>
                <p class="lead text-muted">Our Customer Management module is a comprehensive CRM system built specifically for insurance businesses. It handles everything from customer onboarding and document verification to family group management and customer portal access.</p>
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
                            <i class="fas fa-user-plus fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Customer Onboarding</h5>
                        <p class="card-text text-muted">Streamlined onboarding process with automated verification workflows and WhatsApp welcome messages.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Document Management</h5>
                        <p class="card-text text-muted">Centralized document storage for KYC, identity proofs, address proofs with secure cloud storage.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-users fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Family Groups</h5>
                        <p class="card-text text-muted">Group family members together for shared policy access and consolidated family management.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-check-circle fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Verification Workflows</h5>
                        <p class="card-text text-muted">Multi-step verification process with status tracking and automated notifications.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-user-circle fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Customer Portal</h5>
                        <p class="card-text text-muted">Self-service portal access for customers to view policies, download documents, and track claims.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-search fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Advanced Search & Export</h5>
                        <p class="card-text text-muted">Powerful search filters and bulk export capabilities for reporting and analysis.</p>
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
                        <i class="fas fa-clock fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Save Time</h5>
                        <p class="text-muted">Reduce customer onboarding time by 60% with automated workflows and document verification.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Increase Retention</h5>
                        <p class="text-muted">Better customer experience leads to higher retention rates and more referrals.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Ensure Compliance</h5>
                        <p class="text-muted">Stay compliant with KYC regulations and maintain complete audit trails.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-database fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Centralize Data</h5>
                        <p class="text-muted">All customer information in one place, accessible from anywhere, anytime.</p>
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
                    <h5 class="fw-bold">Add Customer</h5>
                    <p class="text-muted">Create customer profile with basic details and contact information.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">2</span>
                    </div>
                    <h5 class="fw-bold">Upload Documents</h5>
                    <p class="text-muted">Collect and store KYC documents securely in the cloud.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">3</span>
                    </div>
                    <h5 class="fw-bold">Verify & Approve</h5>
                    <p class="text-muted">Complete verification workflow with automated notifications.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">4</span>
                    </div>
                    <h5 class="fw-bold">Grant Portal Access</h5>
                    <p class="text-muted">Provide customer portal credentials for self-service access.</p>
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
                        <h5 class="card-title fw-bold">Family Management</h5>
                        <p class="card-text text-muted">Group families and dependents for consolidated policy management.</p>
                        <a href="{{ url('/features/family-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Customer Portal</h5>
                        <p class="card-text text-muted">Self-service portal for policy viewing, document downloads, and claims tracking.</p>
                        <a href="{{ url('/features/customer-portal') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Policy Management</h5>
                        <p class="card-text text-muted">Complete insurance policy lifecycle management and renewal tracking.</p>
                        <a href="{{ url('/features/policy-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5" style="background: var(--gradient-primary); color: white;">
    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8 mx-auto text-center">
                <h2 class="display-4 fw-bold mb-4">Ready to Transform Your Customer Management?</h2>
                <p class="lead mb-4">Start your 14-day free trial today. No credit card required.</p>
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg shadow-sm">Start Free Trial</a>
                <a href="{{ url('/pricing') }}" class="btn btn-outline-light btn-lg ms-2">View Pricing</a>
            </div>
        </div>
    </div>
</section>
@endsection
