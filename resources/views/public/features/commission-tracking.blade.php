@extends('public.layout')

@section('title', 'Commission Tracking - Midas Portal')
@section('meta_description', 'Automated commission calculations and agent payout management system. Track own, transfer, and reference commissions with multi-level support, payment status, and TDS calculations.')
@section('meta_keywords', 'commission tracking software, insurance commission calculator, agent payout, commission management, multi-level commission, TDS calculation')

@section('content')
<!-- Hero Section -->
@include('public.components.cta-section', [
    'title' => 'Commission Tracking',
    'description' => 'Automate commission calculations with multi-level support for own, transfer, and reference commissions. Track payments, generate reports, and ensure accurate agent payouts with built-in TDS calculations.',
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
                <h2 class="display-5 fw-bold mb-4">What is Commission Tracking?</h2>
                <p class="lead text-muted">Our Commission Tracking system provides automatic calculation and management of three commission types: Own (0.5%-15%), Transfer (0.25%-7.5%), and Reference (0.1%-5%). Track agent earnings, payment status, and generate detailed commission reports with tax calculations.</p>
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
                            <i class="fas fa-calculator fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Auto Calculations</h5>
                        <p class="card-text text-muted">Automatic commission calculation on policy issuance with configurable rates and percentage slabs.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-layer-group fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Multi-Level Commissions</h5>
                        <p class="card-text text-muted">Support for own sales, transferred policies, and external reference commissions with different rates.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-money-check-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Payout Management</h5>
                        <p class="card-text text-muted">Track payment status (pending, paid, on-hold) with payment date, mode, and reference tracking.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-percentage fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">TDS Calculation</h5>
                        <p class="card-text text-muted">Automatic TDS calculation and deduction based on threshold amounts with tax reports.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-bar fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Commission Reports</h5>
                        <p class="card-text text-muted">Monthly, quarterly, and annual reports with agent-wise breakdowns and payment summaries.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-users fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Team Tracking</h5>
                        <p class="card-text text-muted">Track individual and team commission earnings with leaderboards and performance metrics.</p>
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
                        <h5 class="fw-bold">Eliminate Calculation Errors</h5>
                        <p class="text-muted">Automated calculations ensure 100% accuracy, eliminating disputes and manual errors.</p>
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
                        <p class="text-muted">Reduce commission processing time from hours to minutes with automated workflows.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-smile fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Improve Agent Satisfaction</h5>
                        <p class="text-muted">Transparent tracking and timely payments keep agents motivated and satisfied.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-shield-alt fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Compliance Ready</h5>
                        <p class="text-muted">Built-in TDS calculations and tax reports ensure compliance with financial regulations.</p>
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
                    <h5 class="fw-bold">Configure Rates</h5>
                    <p class="text-muted">Set commission rates for different policy types and agent levels.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">2</span>
                    </div>
                    <h5 class="fw-bold">Auto-Calculate</h5>
                    <p class="text-muted">System calculates commissions automatically on policy issuance.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">3</span>
                    </div>
                    <h5 class="fw-bold">Review & Approve</h5>
                    <p class="text-muted">Review commission reports and approve for payment processing.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">4</span>
                    </div>
                    <h5 class="fw-bold">Process Payouts</h5>
                    <p class="text-muted">Mark payments as paid and generate payment receipts.</p>
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
                        <p class="card-text text-muted">Automatic commission calculation on every policy issuance and renewal.</p>
                        <a href="{{ url('/features/policy-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Analytics & Reports</h5>
                        <p class="card-text text-muted">Detailed commission analytics and performance tracking dashboards.</p>
                        <a href="{{ url('/features/analytics-reports') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Staff Management</h5>
                        <p class="card-text text-muted">Link commissions to staff profiles for complete performance tracking.</p>
                        <a href="{{ url('/features/staff-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
@include('public.components.cta-section', [
    'title' => 'Ready to Automate Commission Tracking?',
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
