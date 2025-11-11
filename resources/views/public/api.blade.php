@extends('public.layout')

@section('title', 'API - Developer Documentation | Midas Portal')
@section('meta_description', 'RESTful API documentation for Midas Portal. Integrate insurance management features into your applications.')
@section('meta_keywords', 'midas portal api, rest api, api documentation, developer api, insurance api')

@section('content')
<!-- Page Header -->
<div style="background: var(--gradient-primary); color: white; padding: 60px 0;">
    <div class="container">
        <h1 class="display-4 fw-bold mb-3">API Documentation</h1>
        <p class="lead">Build powerful insurance management integrations with our RESTful API</p>
        <div class="mt-4">
            <span class="badge bg-light text-dark me-2">Version 1.0</span>
            <span class="badge bg-light text-dark me-2">REST</span>
            <span class="badge bg-light text-dark">JSON</span>
        </div>
    </div>
</div>

<!-- Quick Start -->
<section class="py-5">
    <div class="container">
        <div class="row">
            <div class="col-lg-8">
                <h2 class="mb-4">Quick Start</h2>

                <div class="alert alert-info mb-4">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Base URL:</strong> <code>https://api.midastech.in/v1</code>
                </div>

                <h4 class="mt-4">Authentication</h4>
                <p>All API requests require authentication using Bearer tokens. Include your API key in the Authorization header:</p>

                <pre class="bg-dark text-light p-3 rounded"><code>curl -X GET "https://api.midastech.in/v1/customers" \
  -H "Authorization: Bearer YOUR_API_TOKEN" \
  -H "Content-Type: application/json"</code></pre>

                <div class="alert alert-warning mt-3">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    Keep your API keys secure and never share them publicly. You can generate API keys from your account settings.
                </div>

                <h4 class="mt-5">Response Format</h4>
                <p>All responses are returned in JSON format:</p>

                <pre class="bg-dark text-light p-3 rounded"><code>{
  "success": true,
  "data": {
    // Response data
  },
  "message": "Success message",
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}</code></pre>

                <h4 class="mt-5">Error Handling</h4>
                <p>Error responses include an error code and message:</p>

                <pre class="bg-dark text-light p-3 rounded"><code>{
  "success": false,
  "error": {
    "code": "VALIDATION_ERROR",
    "message": "The given data was invalid.",
    "errors": {
      "email": ["The email field is required."]
    }
  }
}</code></pre>

                <h4 class="mt-5">Rate Limiting</h4>
                <p>API requests are limited to:</p>
                <ul>
                    <li><strong>Basic Plan:</strong> 1,000 requests per hour</li>
                    <li><strong>Professional Plan:</strong> 5,000 requests per hour</li>
                    <li><strong>Enterprise Plan:</strong> 20,000 requests per hour</li>
                </ul>

                <div class="alert alert-secondary mt-3">
                    <strong>Rate limit headers:</strong>
                    <pre class="mb-0"><code>X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640000000</code></pre>
                </div>

                <!-- API Endpoints -->
                <h2 class="mt-5 mb-4">API Endpoints</h2>

                <!-- Customers API -->
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-users me-2"></i>Customers API</h5>
                    </div>
                    <div class="card-body">
                        <h6><span class="badge bg-success">GET</span> /customers</h6>
                        <p class="text-muted">Retrieve a list of all customers</p>
                        <pre class="bg-light p-3 rounded"><code>GET /v1/customers?page=1&per_page=15&search=john</code></pre>

                        <h6 class="mt-4"><span class="badge bg-success">GET</span> /customers/{id}</h6>
                        <p class="text-muted">Retrieve a specific customer</p>
                        <pre class="bg-light p-3 rounded"><code>GET /v1/customers/123</code></pre>

                        <h6 class="mt-4"><span class="badge bg-primary">POST</span> /customers</h6>
                        <p class="text-muted">Create a new customer</p>
                        <pre class="bg-light p-3 rounded"><code>POST /v1/customers
{
  "first_name": "John",
  "last_name": "Doe",
  "email": "john@example.com",
  "phone": "+919876543210",
  "date_of_birth": "1990-01-15"
}</code></pre>

                        <h6 class="mt-4"><span class="badge bg-warning text-dark">PUT</span> /customers/{id}</h6>
                        <p class="text-muted">Update an existing customer</p>

                        <h6 class="mt-4"><span class="badge bg-danger">DELETE</span> /customers/{id}</h6>
                        <p class="text-muted">Delete a customer</p>
                    </div>
                </div>

                <!-- Policies API -->
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h5 class="mb-0"><i class="fas fa-file-contract me-2"></i>Policies API</h5>
                    </div>
                    <div class="card-body">
                        <h6><span class="badge bg-success">GET</span> /policies</h6>
                        <p class="text-muted">Retrieve a list of all policies</p>
                        <pre class="bg-light p-3 rounded"><code>GET /v1/policies?customer_id=123&status=active</code></pre>

                        <h6 class="mt-4"><span class="badge bg-success">GET</span> /policies/{id}</h6>
                        <p class="text-muted">Retrieve a specific policy</p>

                        <h6 class="mt-4"><span class="badge bg-primary">POST</span> /policies</h6>
                        <p class="text-muted">Create a new policy</p>
                        <pre class="bg-light p-3 rounded"><code>POST /v1/policies
{
  "customer_id": 123,
  "product_id": 45,
  "policy_number": "POL-2024-001",
  "start_date": "2024-01-01",
  "end_date": "2025-01-01",
  "premium_amount": 15000,
  "sum_assured": 500000
}</code></pre>

                        <h6 class="mt-4"><span class="badge bg-warning text-dark">PUT</span> /policies/{id}</h6>
                        <p class="text-muted">Update an existing policy</p>

                        <h6 class="mt-4"><span class="badge bg-danger">DELETE</span> /policies/{id}</h6>
                        <p class="text-muted">Cancel a policy</p>
                    </div>
                </div>

                <!-- Claims API -->
                <div class="card mb-4">
                    <div class="card-header bg-warning text-dark">
                        <h5 class="mb-0"><i class="fas fa-clipboard-check me-2"></i>Claims API</h5>
                    </div>
                    <div class="card-body">
                        <h6><span class="badge bg-success">GET</span> /claims</h6>
                        <p class="text-muted">Retrieve a list of all claims</p>
                        <pre class="bg-light p-3 rounded"><code>GET /v1/claims?policy_id=123&status=pending</code></pre>

                        <h6 class="mt-4"><span class="badge bg-success">GET</span> /claims/{id}</h6>
                        <p class="text-muted">Retrieve a specific claim</p>

                        <h6 class="mt-4"><span class="badge bg-primary">POST</span> /claims</h6>
                        <p class="text-muted">File a new claim</p>
                        <pre class="bg-light p-3 rounded"><code>POST /v1/claims
{
  "policy_id": 123,
  "claim_type": "hospitalization",
  "claim_amount": 75000,
  "incident_date": "2024-12-01",
  "description": "Medical treatment for surgery"
}</code></pre>

                        <h6 class="mt-4"><span class="badge bg-warning text-dark">PUT</span> /claims/{id}</h6>
                        <p class="text-muted">Update claim status</p>
                    </div>
                </div>

                <!-- Webhooks -->
                <h2 class="mt-5 mb-4">Webhooks</h2>
                <p>Configure webhooks to receive real-time notifications about events in your account.</p>

                <div class="card">
                    <div class="card-body">
                        <h6>Available Events</h6>
                        <table class="table table-sm">
                            <tbody>
                                <tr>
                                    <td><code>customer.created</code></td>
                                    <td>New customer added</td>
                                </tr>
                                <tr>
                                    <td><code>customer.updated</code></td>
                                    <td>Customer information changed</td>
                                </tr>
                                <tr>
                                    <td><code>policy.created</code></td>
                                    <td>New policy created</td>
                                </tr>
                                <tr>
                                    <td><code>policy.expiring</code></td>
                                    <td>Policy expiring in 30 days</td>
                                </tr>
                                <tr>
                                    <td><code>claim.filed</code></td>
                                    <td>New claim filed</td>
                                </tr>
                                <tr>
                                    <td><code>claim.updated</code></td>
                                    <td>Claim status changed</td>
                                </tr>
                            </tbody>
                        </table>

                        <h6 class="mt-4">Webhook Payload Example</h6>
                        <pre class="bg-light p-3 rounded"><code>{
  "event": "policy.expiring",
  "data": {
    "id": 123,
    "policy_number": "POL-2024-001",
    "customer": {
      "id": 456,
      "name": "John Doe"
    },
    "expiry_date": "2025-01-01",
    "days_until_expiry": 30
  },
  "timestamp": "2024-12-01T10:30:00Z"
}</code></pre>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm sticky-top" style="top: 20px;">
                    <div class="card-body">
                        <h6 class="text-muted mb-3">API RESOURCES</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2"><a href="#" class="text-decoration-none"><i class="fas fa-users me-2"></i>Customers</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none"><i class="fas fa-file-contract me-2"></i>Policies</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none"><i class="fas fa-clipboard-check me-2"></i>Claims</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none"><i class="fas fa-file-invoice me-2"></i>Quotations</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none"><i class="fas fa-building me-2"></i>Companies</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none"><i class="fas fa-box me-2"></i>Products</a></li>
                            <li class="mb-2"><a href="#" class="text-decoration-none"><i class="fas fa-bell me-2"></i>Webhooks</a></li>
                        </ul>

                        <hr>

                        <h6 class="text-muted mb-3 mt-4">SDKs & LIBRARIES</h6>
                        <div class="d-grid gap-2">
                            <a href="#" class="btn btn-sm btn-outline-secondary text-start">
                                <i class="fab fa-js me-2"></i>JavaScript/Node.js
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-secondary text-start">
                                <i class="fab fa-python me-2"></i>Python
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-secondary text-start">
                                <i class="fab fa-php me-2"></i>PHP
                            </a>
                            <a href="#" class="btn btn-sm btn-outline-secondary text-start">
                                <i class="fas fa-gem me-2"></i>Ruby
                            </a>
                        </div>

                        <hr class="my-4">

                        <div class="alert alert-primary mb-0">
                            <h6><i class="fas fa-key me-2"></i>Need an API Key?</h6>
                            <p class="small mb-2">Log in to your account to generate API keys.</p>
                            <a href="{{ url('/contact') }}" class="btn btn-sm btn-primary w-100">Get Started</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
