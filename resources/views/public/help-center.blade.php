@extends('public.layout')

@section('title', 'Help Center - Support & Resources | Midas Portal')
@section('meta_description', 'Find answers to common questions and get support for Midas Portal insurance management software.')
@section('meta_keywords', 'midas portal help, insurance software support, help center, faq')

@section('content')
<!-- Page Header -->
<div style="background: var(--gradient-primary); color: white; padding: 60px 0;">
    <div class="container text-center">
        <h1 class="display-4 fw-bold mb-3">Help Center</h1>
        <p class="lead mb-4">How can we help you today?</p>
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="input-group input-group-lg">
                    <input type="text" class="form-control" placeholder="Search for help articles...">
                    <button class="btn btn-light" type="button">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Help Categories -->
<section class="py-5">
    <div class="container">
        <div class="row g-4">
            <!-- Getting Started -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-rocket"></i>
                        </div>
                        <h5 class="card-title">Getting Started</h5>
                        <p class="card-text text-muted mb-3">Learn the basics of Midas Portal</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none">Quick Start Guide</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Initial Setup</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">User Management</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Dashboard Overview</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Customer Management -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <h5 class="card-title">Customer Management</h5>
                        <p class="card-text text-muted mb-3">Manage your customers effectively</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none">Adding Customers</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Family Management</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Customer Portal Access</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Import/Export Data</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Policy Management -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-file-contract"></i>
                        </div>
                        <h5 class="card-title">Policy Management</h5>
                        <p class="card-text text-muted mb-3">Handle policies and renewals</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none">Creating Policies</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Renewal Tracking</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Policy Documents</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Premium Calculations</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Claims Management -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-clipboard-check"></i>
                        </div>
                        <h5 class="card-title">Claims Management</h5>
                        <p class="card-text text-muted mb-3">Process and track claims</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none">Filing Claims</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Claim Status Tracking</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Document Requirements</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Settlement Process</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- WhatsApp Integration -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fab fa-whatsapp"></i>
                        </div>
                        <h5 class="card-title">WhatsApp Integration</h5>
                        <p class="card-text text-muted mb-3">Connect with customers on WhatsApp</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none">Setup WhatsApp</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Message Templates</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Automated Notifications</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Broadcast Messages</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Reports & Analytics -->
            <div class="col-md-6 col-lg-4">
                <div class="card h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="feature-icon mb-3" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5 class="card-title">Reports & Analytics</h5>
                        <p class="card-text text-muted mb-3">Generate insights and reports</p>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none">Dashboard Metrics</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Custom Reports</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Commission Tracking</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none">Export Options</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">Frequently Asked Questions</h2>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="faqAccordion">
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How do I get started with Midas Portal?
                            </button>
                        </h2>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                After signing up, you'll receive access credentials to your dedicated portal. Simply log in, complete the initial setup wizard, and you can start adding customers and managing policies right away.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Can I import my existing customer data?
                            </button>
                        </h2>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Yes! Midas Portal supports bulk data import through Excel/CSV files. We also provide migration assistance for customers on our Enterprise plan.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Is my data secure?
                            </button>
                        </h2>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                Absolutely. We use industry-standard encryption, multi-tenant data isolation, regular backups, and comply with data protection regulations. Your data is stored securely and is only accessible to authorized users.
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What support options are available?
                            </button>
                        </h2>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                            <div class="accordion-body">
                                We offer email support for all plans, with priority support and phone support available on Professional and Enterprise plans. Our support team is available Monday-Friday, 9 AM - 6 PM IST.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Support -->
<section class="py-5">
    <div class="container text-center">
        <h2 class="mb-3">Still Need Help?</h2>
        <p class="text-muted mb-4">Our support team is here to assist you</p>
        <div class="row justify-content-center">
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-envelope text-primary mb-3" style="font-size: 2rem;"></i>
                        <h5>Email Support</h5>
                        <p class="text-muted">Info@midastech.in</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card border-0 shadow-sm">
                    <div class="card-body">
                        <i class="fas fa-phone text-primary mb-3" style="font-size: 2rem;"></i>
                        <h5>Phone Support</h5>
                        <p class="text-muted">+91 80000 71413</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
