@extends('public.layout')

@section('title', 'Document Management - Midas Portal')
@section('meta_description', 'Secure cloud storage for all policy documents and customer records. Organize documents by category, track versions, share securely, and access from anywhere with unlimited storage.')
@section('meta_keywords', 'document management system, cloud document storage, policy documents, secure file sharing, version control, insurance documents')

@section('content')
<!-- Hero Section -->
<section class="py-5" style="background: var(--gradient-primary); color: white;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4">Document Management</h1>
                <p class="lead mb-4">Secure cloud storage for all your insurance documents with smart organization, version control, and instant sharing. Access policy documents, KYC files, and customer records from anywhere, anytime.</p>
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
                <h2 class="display-5 fw-bold mb-4">What is Document Management?</h2>
                <p class="lead text-muted">Our Document Management system provides secure cloud storage with intelligent organization by category, customer, policy, or claim. Track document versions, control access permissions, and share files securely with customers and team members.</p>
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
                            <i class="fas fa-cloud-upload-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Unlimited Storage</h5>
                        <p class="card-text text-muted">Store unlimited documents in secure cloud with automatic backups and 99.9% uptime guarantee.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-folder-tree fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Category Organization</h5>
                        <p class="card-text text-muted">Organize documents by policy type, customer, claim, or custom categories for easy retrieval.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-history fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Version Control</h5>
                        <p class="card-text text-muted">Track document versions with timestamps and maintain complete revision history.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-share-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Secure Sharing</h5>
                        <p class="card-text text-muted">Share documents via secure links, WhatsApp, or email with expiry dates and download limits.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-lock fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Access Control</h5>
                        <p class="card-text text-muted">Role-based permissions control who can view, edit, or delete documents.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-search fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Quick Search</h5>
                        <p class="card-text text-muted">Find documents instantly with powerful search by name, customer, policy, or tags.</p>
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
                        <i class="fas fa-shield-alt fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Keep Documents Safe</h5>
                        <p class="text-muted">Cloud storage protects against loss from fire, theft, or damage with automatic backups.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-search fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Find Files Instantly</h5>
                        <p class="text-muted">No more searching through filing cabinets - find any document in seconds with smart search.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-mobile-alt fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Access Anywhere</h5>
                        <p class="text-muted">Access documents from office, home, or on-the-go via mobile devices with internet connection.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-leaf fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Go Paperless</h5>
                        <p class="text-muted">Reduce paper usage, printing costs, and physical storage space with digital documents.</p>
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
                    <h5 class="fw-bold">Upload Documents</h5>
                    <p class="text-muted">Drag and drop files or upload from customer/policy forms.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">2</span>
                    </div>
                    <h5 class="fw-bold">Categorize & Tag</h5>
                    <p class="text-muted">Assign to categories and add tags for easy organization.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">3</span>
                    </div>
                    <h5 class="fw-bold">Set Permissions</h5>
                    <p class="text-muted">Control who can access, edit, or share each document.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">4</span>
                    </div>
                    <h5 class="fw-bold">Share & Track</h5>
                    <p class="text-muted">Share with customers and track who accessed or downloaded.</p>
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
                        <h5 class="card-title fw-bold">Customer Management</h5>
                        <p class="card-text text-muted">Attach documents directly to customer profiles for KYC and identity verification.</p>
                        <a href="{{ url('/features/customer-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Claims Management</h5>
                        <p class="card-text text-muted">Upload and track 30+ claim document types with completion monitoring.</p>
                        <a href="{{ url('/features/claims-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Customer Portal</h5>
                        <p class="card-text text-muted">Customers can download policy documents and upload claim files through portal.</p>
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
                <h2 class="display-4 fw-bold mb-4">Ready to Go Paperless?</h2>
                <p class="lead mb-4">Start your 14-day free trial today. No credit card required.</p>
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg shadow-sm">Start Free Trial</a>
                <a href="{{ url('/pricing') }}" class="btn btn-outline-light btn-lg ms-2">View Pricing</a>
            </div>
        </div>
    </div>
</section>
@endsection
