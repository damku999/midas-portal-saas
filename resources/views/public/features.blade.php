@extends('public.layout')

@section('title', 'Features - Midas Portal')

@section('content')
<section class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold">Powerful Features for Modern Insurance Agencies</h1>
            <p class="lead text-muted">Everything you need to manage and grow your insurance business</p>
        </div>

        <div class="row g-5">
            <div class="col-md-6">
                <div class="d-flex">
                    <div class="feature-icon me-3">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <h4>Customer Management</h4>
                        <p class="text-muted">Complete CRM with customer profiles, family groups, policy history, and renewal tracking.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex">
                    <div class="feature-icon me-3">
                        <i class="fas fa-user-plus"></i>
                    </div>
                    <div>
                        <h4>Lead Management & Conversion</h4>
                        <p class="text-muted">Track leads from source to conversion with automated workflows and analytics.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex">
                    <div class="feature-icon me-3">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <div>
                        <h4>Policy Management</h4>
                        <p class="text-muted">Manage all insurance types - health, life, motor, home, and more.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex">
                    <div class="feature-icon me-3">
                        <i class="fab fa-whatsapp"></i>
                    </div>
                    <div>
                        <h4>WhatsApp Integration</h4>
                        <p class="text-muted">Send automated messages, renewal reminders, and policy documents via WhatsApp.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex">
                    <div class="feature-icon me-3">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <h4>Quotation Management</h4>
                        <p class="text-muted">Generate professional quotes instantly and share with customers.</p>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="d-flex">
                    <div class="feature-icon me-3">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <h4>Analytics & Reports</h4>
                        <p class="text-muted">Real-time dashboards and detailed reports for data-driven decisions.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
