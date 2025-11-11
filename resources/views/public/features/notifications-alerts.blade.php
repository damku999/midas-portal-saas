@extends('public.layout')

@section('title', 'Notifications & Alerts - Midas Portal')
@section('meta_description', 'Multi-channel automated notifications via Email, SMS, and WhatsApp. Send renewal reminders, payment alerts, birthday wishes, and custom notifications with scheduling and tracking.')
@section('meta_keywords', 'insurance notifications, automated alerts, renewal reminders, multi-channel messaging, email SMS WhatsApp, notification system')

@section('content')
<!-- Hero Section -->
@include('public.components.cta-section', [
    'title' => 'Notifications & Alerts',
    'description' => 'Keep customers informed with automated multi-channel notifications. Send renewal reminders, payment alerts, birthday wishes, and custom messages via Email, SMS, and WhatsApp with delivery tracking and scheduling.',
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
                <h2 class="display-5 fw-bold mb-4">What is Notifications & Alerts?</h2>
                <p class="lead text-muted">Our Notifications & Alerts system provides unified multi-channel communication with template management, delivery tracking, and automated scheduling. Send renewal reminders at 30, 15, and 7 days before expiry, payment confirmations, birthday wishes, and custom alerts across Email, SMS, and WhatsApp.</p>
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
                            <i class="fas fa-sync-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Renewal Reminders</h5>
                        <p class="card-text text-muted">Automated reminders sent at 30, 15, and 7 days before policy expiry across all channels.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-share-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Multi-Channel Delivery</h5>
                        <p class="card-text text-muted">Send notifications via Email, SMS, and WhatsApp simultaneously or choose specific channels.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-sliders-h fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Custom Alert Rules</h5>
                        <p class="card-text text-muted">Create custom triggers based on events, dates, or actions with flexible scheduling options.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-birthday-cake fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Birthday Wishes</h5>
                        <p class="card-text text-muted">Automated birthday greetings sent to customers with personalized messages and special offers.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-file-alt fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Template Management</h5>
                        <p class="card-text text-muted">Create and manage message templates with dynamic variables and personalization.</p>
                    </div>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <div class="mb-3" style="width: 70px; height: 70px; background: var(--gradient-primary); border-radius: 12px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-chart-line fa-2x text-white"></i>
                        </div>
                        <h5 class="card-title fw-bold">Delivery Tracking</h5>
                        <p class="card-text text-muted">Track delivery status, open rates, and click-through rates with detailed analytics.</p>
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
                        <i class="fas fa-dollar-sign fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Increase Renewals</h5>
                        <p class="text-muted">Automated reminders increase renewal rates by 40% by catching customers before they forget.</p>
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
                        <p class="text-muted">Eliminate manual reminder calls and emails, freeing your team for high-value activities.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-smile fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Improve Engagement</h5>
                        <p class="text-muted">Regular touchpoints through birthday wishes and updates keep customers engaged with your brand.</p>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="d-flex mb-4">
                    <div class="flex-shrink-0">
                        <i class="fas fa-check-circle fa-2x" style="color: var(--primary-color);"></i>
                    </div>
                    <div class="ms-3">
                        <h5 class="fw-bold">Never Miss a Deadline</h5>
                        <p class="text-muted">Automated scheduling ensures no renewal or payment deadline slips through the cracks.</p>
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
                    <h5 class="fw-bold">Set Up Rules</h5>
                    <p class="text-muted">Configure notification rules and triggers for different events.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">2</span>
                    </div>
                    <h5 class="fw-bold">Create Templates</h5>
                    <p class="text-muted">Design message templates with personalization variables.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">3</span>
                    </div>
                    <h5 class="fw-bold">Auto-Send</h5>
                    <p class="text-muted">System automatically sends notifications based on triggers.</p>
                </div>
            </div>
            <div class="col-lg-3 col-md-6">
                <div class="text-center">
                    <div class="mx-auto mb-3" style="width: 80px; height: 80px; background: var(--gradient-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                        <span class="text-white fw-bold fs-3">4</span>
                    </div>
                    <h5 class="fw-bold">Track & Optimize</h5>
                    <p class="text-muted">Monitor delivery and engagement metrics to improve effectiveness.</p>
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
                        <h5 class="card-title fw-bold">WhatsApp Integration</h5>
                        <p class="card-text text-muted">Send notifications via WhatsApp Business API with high delivery rates.</p>
                        <a href="{{ url('/features/whatsapp-integration') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Policy Management</h5>
                        <p class="card-text text-muted">Automated renewal reminders linked to policy expiry dates.</p>
                        <a href="{{ url('/features/policy-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body p-4">
                        <h5 class="card-title fw-bold">Customer Management</h5>
                        <p class="card-text text-muted">Birthday alerts and personalized messages based on customer data.</p>
                        <a href="{{ url('/features/customer-management') }}" class="btn btn-sm btn-outline-primary">Learn More →</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
@include('public.components.cta-section', [
    'title' => 'Ready to Automate Customer Communications?',
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
