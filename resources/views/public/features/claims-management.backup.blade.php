@extends('public.layout')

@section('title', 'Claims Management - Midas Portal')
@section('meta_description', 'End-to-end insurance claims processing with document tracking, workflow automation, and settlement management. Handle Health and Vehicle claims efficiently with stage-based workflows.')
@section('meta_keywords', 'claims management software, insurance claims processing, claim tracking, settlement management, claims workflow automation')

@section('content')
<!-- Hero Section -->
<section class="py-5" style="background: var(--gradient-primary); color: white;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4">Claims Management</h1>
                <p class="lead mb-4">Complete claims processing workflow from registration to settlement. Track 30+ document types, manage approval stages, and keep customers informed with real-time notifications throughout the claims journey.</p>
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
                <h2 class="display-5 fw-bold mb-4">What is Claims Management?</h2>
                <p class="lead text-muted">Our Claims Management system provides end-to-end processing for Health and Vehicle insurance claims. From claim registration with auto-numbering (CLM{YYYY}{MM}{0001}) to settlement tracking, manage the entire claims lifecycle with complete document management and automated notifications.</p>
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
                            <i class="fas fa-file-medical fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Claim Registration</h5>
                        <p class="card-text text-muted">Register claims with auto-numbering, claim date, intimation tracking, and incident details.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-folder-open fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">30+ Document Types</h5>
                        <p class="card-text text-muted">Manage 10 Health and 20 Vehicle document types with completion tracking and reminders.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-tasks fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">4-Stage Workflow</h5>
                        <p class="card-text text-muted">Track claims through Registered, Under Review, Approved, and Settled/Rejected stages.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-calculator fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Liability Calculations</h5>
                        <p class="card-text text-muted">Track claim amount, deductibles, approved amount, and settlement calculations automatically.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-hospital fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Cashless vs Reimbursement</h5>
                        <p class="card-text text-muted">Separate workflows for cashless and reimbursement claims with appropriate document requirements.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-bell fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Real-time Notifications</h5>
                        <p class="card-text text-muted">Keep customers informed via WhatsApp, SMS, and email at every stage of the claims process.</p>
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
                        <h5 class="fw-bold">Faster Processing</h5>
                        <p class="text-muted">Reduce claim processing time by 60% with automated workflows and document tracking.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-smile fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Better Customer Experience</h5>
                        <p class="text-muted">Real-time updates and transparent tracking improve customer satisfaction and reduce support calls.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-double fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Complete Documentation</h5>
                        <p class="text-muted">Never miss required documents with automated checklists and completion tracking.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-bar fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Claims Analytics</h5>
                        <p class="text-muted">Track claim settlement ratios, average processing time, and identify bottlenecks for improvement.</p>
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
                    <h5 class="fw-bold">Register Claim</h5>
                    <p class="text-muted">Customer or staff creates claim with incident details and policy information.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">2</span>
                    </div>
                    <h5 class="fw-bold">Upload Documents</h5>
                    <p class="text-muted">Collect and upload required documents based on claim type.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">3</span>
                    </div>
                    <h5 class="fw-bold">Review & Approve</h5>
                    <p class="text-muted">Process through review stages with insurer coordination and approvals.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">4</span>
                    </div>
                    <h5 class="fw-bold">Settle Claim</h5>
                    <p class="text-muted">Record settlement details and notify customer of claim closure.</p>
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
                        <p class="card-text text-muted">Link claims to policies with automatic NCB impact tracking.</p>
                        <a href="{{ url('/features/policy-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Document Management</h5>
                        <p class="card-text text-muted">Secure storage for all claim documents with easy access and sharing.</p>
                        <a href="{{ url('/features/document-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Customer Portal</h5>
                        <p class="card-text text-muted">Customers can submit and track claims directly through the portal.</p>
                        <a href="{{ url('/features/customer-portal') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
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
                <h2 class="display-4 fw-bold mb-4">Ready to Streamline Claims Processing?</h2>
                <p class="lead mb-4">Start your 14-day free trial today. No credit card required.</p>
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg shadow-sm">Start Free Trial</a>
                <a href="{{ url('/pricing') }}" class="btn btn-outline-light btn-lg ms-2">View Pricing</a>
            </div>
        </div>
    </div>
</section>
@endsection
