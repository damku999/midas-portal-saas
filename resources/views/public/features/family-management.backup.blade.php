@extends('public.layout')

@section('title', 'Family Management - Midas Portal')
@section('meta_description', 'Group and manage family members with their insurance policies in one organized view. Manage dependents, track family policies, and share documents across family groups.')
@section('meta_keywords', 'family management, dependent insurance, family grouping, insurance family plans, shared policies, family insurance software')

@section('content')
<!-- Hero Section -->
<section class="py-5" style="background: var(--gradient-primary); color: white;">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-8">
                <h1 class="display-3 fw-bold mb-4">Family Management</h1>
                <p class="lead mb-4">Group and manage family members with their insurance policies in one organized view. Simplify family policy management, track dependents, and provide consolidated access to all family insurance needs.</p>
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
                <h2 class="display-5 fw-bold mb-4">What is Family Management?</h2>
                <p class="lead text-muted">Our Family Management module allows you to group family members together, manage their insurance policies collectively, and provide a unified view of all family insurance needs. Perfect for managing health insurance, life policies, and other family-oriented coverage plans.</p>
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
                            <i class="fas fa-user-friends fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Family Grouping</h5>
                        <p class="card-text text-muted">Create family groups with primary members and add dependents with defined relationships like spouse, children, and parents.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sitemap fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Relationship Mapping</h5>
                        <p class="card-text text-muted">Define relationships between family members including spouse, child, parent, sibling, and other dependents.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-shield-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Family Policy Tracking</h5>
                        <p class="card-text text-muted">Track all policies across family members in a consolidated view with renewal dates and coverage details.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-folder-open fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Shared Documents</h5>
                        <p class="card-text text-muted">Share documents across family members with secure access controls and centralized document management.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-pie fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Family Analytics</h5>
                        <p class="card-text text-muted">View consolidated family insurance coverage, total premiums, and identify coverage gaps across the family.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-bell fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Group Notifications</h5>
                        <p class="card-text text-muted">Send renewal reminders and important updates to entire family groups via WhatsApp, SMS, or email.</p>
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
                        <i class="fas fa-users fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Better Customer Experience</h5>
                        <p class="text-muted">Families can view all their policies in one place, making it easier to manage insurance needs and renewals.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-chart-line fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Increase Cross-Selling</h5>
                        <p class="text-muted">Identify coverage gaps across family members and recommend appropriate insurance products to fill those gaps.</p>
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
                        <p class="text-muted">Manage multiple family members efficiently with bulk operations and consolidated reporting capabilities.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Complete Coverage Visibility</h5>
                        <p class="text-muted">Get a holistic view of family insurance needs, ensuring no one falls through the cracks in coverage.</p>
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
                    <h5 class="fw-bold">Create Family Group</h5>
                    <p class="text-muted">Start by creating a family group and designating a primary member.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">2</span>
                    </div>
                    <h5 class="fw-bold">Add Family Members</h5>
                    <p class="text-muted">Add dependents and define their relationship to the primary member.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">3</span>
                    </div>
                    <h5 class="fw-bold">Link Policies</h5>
                    <p class="text-muted">Associate insurance policies with respective family members.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">4</span>
                    </div>
                    <h5 class="fw-bold">Manage Together</h5>
                    <p class="text-muted">View, track, and manage all family policies from a unified dashboard.</p>
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
                        <p class="card-text text-muted">Complete 360° CRM system for managing customer profiles and policy history.</p>
                        <a href="{{ url('/features/customer-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Policy Management</h5>
                        <p class="card-text text-muted">Manage all insurance types with renewal tracking and premium calculations.</p>
                        <a href="{{ url('/features/policy-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Customer Portal</h5>
                        <p class="card-text text-muted">Self-service portal for families to access policies and submit claims together.</p>
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
                <h2 class="display-4 fw-bold mb-4">Ready to Simplify Family Insurance Management?</h2>
                <p class="lead mb-4">Start your 14-day free trial today. No credit card required.</p>
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg shadow-sm">Start Free Trial</a>
                <a href="{{ url('/pricing') }}" class="btn btn-outline-light btn-lg ms-2">View Pricing</a>
            </div>
        </div>
    </div>
</section>
@endsection
