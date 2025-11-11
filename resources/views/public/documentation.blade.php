@extends('public.layout')

@section('title', 'Documentation - User Guide & API Reference | Midas Portal')
@section('meta_description', 'Complete documentation for Midas Portal including user guides, API reference, and integration tutorials.')
@section('meta_keywords', 'midas portal documentation, api documentation, user guide, integration guide')

@section('content')
<!-- Page Header -->
<div style="background: var(--gradient-primary); color: white; padding: 60px 0;">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">Documentation</h1>
        <p class="lead">Complete guides to help you get the most out of Midas Portal</p>
    </div>
</div>

<!-- Documentation Navigation -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-lg-3 mb-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">DOCUMENTATION</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#overview" class="text-decoration-none">Overview</a></li>
                            <li class="mb-2"><a href="#user-guide" class="text-decoration-none">User Guide</a></li>
                            <li class="mb-2"><a href="#api-reference" class="text-decoration-none">API Reference</a></li>
                            <li class="mb-2"><a href="#integrations" class="text-decoration-none">Integrations</a></li>
                            <li class="mb-2"><a href="#webhooks" class="text-decoration-none">Webhooks</a></li>
                            <li class="mb-2"><a href="#security" class="text-decoration-none">Security</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-lg-9">
                <!-- Overview -->
                <div id="overview" class="mb-5">
                    <h2 class="mb-4">Overview</h2>
                    <p>Welcome to the Midas Portal documentation. This comprehensive guide will help you understand and utilize all features of our insurance management platform.</p>

                    <div class="row g-3 mt-4">
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-book text-primary me-2"></i>User Guide</h5>
                                    <p class="card-text">Step-by-step instructions for all features</p>
                                    <a href="#user-guide" class="btn btn-sm btn-outline-primary">Read Guide</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-primary h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-code text-primary me-2"></i>API Reference</h5>
                                    <p class="card-text">RESTful API documentation for developers</p>
                                    <a href="#api-reference" class="btn btn-sm btn-outline-primary">View API Docs</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- User Guide -->
                <div id="user-guide" class="mb-5">
                    <h2 class="mb-4">User Guide</h2>

                    <div class="accordion" id="userGuideAccordion">
                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#guide1">
                                    <i class="fas fa-rocket me-2"></i> Getting Started
                                </button>
                            </h2>
                            <div id="guide1" class="accordion-collapse collapse show">
                                <div class="accordion-body">
                                    <h6>Initial Setup</h6>
                                    <ol>
                                        <li>Log in to your Midas Portal account</li>
                                        <li>Complete your company profile</li>
                                        <li>Add staff members and assign roles</li>
                                        <li>Configure master data (insurance companies, products, etc.)</li>
                                        <li>Set up notification preferences</li>
                                    </ol>

                                    <h6 class="mt-3">Dashboard Overview</h6>
                                    <p>The dashboard provides a quick overview of your insurance business metrics including active policies, pending renewals, recent claims, and revenue analytics.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#guide2">
                                    <i class="fas fa-users me-2"></i> Customer Management
                                </button>
                            </h2>
                            <div id="guide2" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <h6>Adding Customers</h6>
                                    <p>Navigate to Customers → Add New and fill in the required information including personal details, contact information, and family members.</p>

                                    <h6 class="mt-3">Family Management</h6>
                                    <p>Link family members to create a complete household view. This helps in managing family floater policies and tracking all policies for related individuals.</p>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#guide3">
                                    <i class="fas fa-file-contract me-2"></i> Policy Management
                                </button>
                            </h2>
                            <div id="guide3" class="accordion-collapse collapse">
                                <div class="accordion-body">
                                    <h6>Creating Policies</h6>
                                    <p>Go to Policies → Create New. Select the customer, insurance company, product type, coverage details, and premium information.</p>

                                    <h6 class="mt-3">Renewal Tracking</h6>
                                    <p>The system automatically tracks policy expiry dates and sends renewal reminders via email and WhatsApp based on your configured timeline.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API Reference -->
                <div id="api-reference" class="mb-5">
                    <h2 class="mb-4">API Reference</h2>
                    <p class="mb-4">Our RESTful API allows you to integrate Midas Portal with your existing systems.</p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Base URL:</strong> <code>https://api.midastech.in/v1</code>
                    </div>

                    <h5 class="mt-4">Authentication</h5>
                    <p>All API requests require authentication using Bearer tokens:</p>
                    <pre class="bg-dark text-light p-3 rounded"><code>Authorization: Bearer YOUR_API_TOKEN</code></pre>

                    <h5 class="mt-4">Common Endpoints</h5>
                    <div class="table-responsive">
                        <table class="table table-bordered">
                            <thead class="table-light">
                                <tr>
                                    <th>Method</th>
                                    <th>Endpoint</th>
                                    <th>Description</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><span class="badge bg-success">GET</span></td>
                                    <td><code>/customers</code></td>
                                    <td>List all customers</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary">POST</span></td>
                                    <td><code>/customers</code></td>
                                    <td>Create a new customer</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">GET</span></td>
                                    <td><code>/policies</code></td>
                                    <td>List all policies</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-primary">POST</span></td>
                                    <td><code>/policies</code></td>
                                    <td>Create a new policy</td>
                                </tr>
                                <tr>
                                    <td><span class="badge bg-success">GET</span></td>
                                    <td><code>/claims</code></td>
                                    <td>List all claims</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Integrations -->
                <div id="integrations" class="mb-5">
                    <h2 class="mb-4">Integrations</h2>

                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fab fa-whatsapp text-success me-2"></i>WhatsApp Business API</h5>
                                    <p class="card-text">Connect your WhatsApp Business account to send automated notifications, renewal reminders, and customer communications.</p>
                                    <a href="#" class="btn btn-sm btn-outline-primary">Setup Guide</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <h5 class="card-title"><i class="fas fa-envelope text-primary me-2"></i>Email Service</h5>
                                    <p class="card-text">Configure SMTP settings to send automated emails for policy documents, reminders, and customer communications.</p>
                                    <a href="#" class="btn btn-sm btn-outline-primary">Setup Guide</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Webhooks -->
                <div id="webhooks" class="mb-5">
                    <h2 class="mb-4">Webhooks</h2>
                    <p>Set up webhooks to receive real-time notifications about events in your Midas Portal account.</p>

                    <h5 class="mt-4">Available Events</h5>
                    <ul>
                        <li><code>customer.created</code> - Triggered when a new customer is added</li>
                        <li><code>policy.created</code> - Triggered when a new policy is created</li>
                        <li><code>policy.expiring</code> - Triggered 30 days before policy expiry</li>
                        <li><code>claim.filed</code> - Triggered when a new claim is filed</li>
                        <li><code>claim.updated</code> - Triggered when claim status changes</li>
                    </ul>
                </div>

                <!-- Security -->
                <div id="security" class="mb-5">
                    <h2 class="mb-4">Security</h2>
                    <p>We take security seriously. Here's how we protect your data:</p>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <h6><i class="fas fa-lock text-success me-2"></i>Data Encryption</h6>
                                    <p class="small mb-0">All data is encrypted in transit (TLS 1.3) and at rest (AES-256)</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <h6><i class="fas fa-database text-success me-2"></i>Data Isolation</h6>
                                    <p class="small mb-0">Multi-tenant architecture ensures complete data separation</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <h6><i class="fas fa-shield-alt text-success me-2"></i>Regular Backups</h6>
                                    <p class="small mb-0">Automated daily backups with point-in-time recovery</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="card border-success h-100">
                                <div class="card-body">
                                    <h6><i class="fas fa-user-shield text-success me-2"></i>Access Control</h6>
                                    <p class="small mb-0">Role-based permissions and two-factor authentication</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-light">
    <div class="container text-center">
        <h2 class="mb-3">Need More Help?</h2>
        <p class="text-muted mb-4">Contact our support team for personalized assistance</p>
        <a href="{{ url('/contact') }}" class="btn btn-primary btn-lg">Contact Support</a>
    </div>
</section>
@endsection
