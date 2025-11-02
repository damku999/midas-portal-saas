@extends('public.layout')

@section('title', 'About Us - Midas Portal')

@section('content')
<section class="py-5">
    <div class="container py-5">
        <div class="row align-items-center mb-5">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">About Midas Portal</h1>
                <p class="lead">We're on a mission to help insurance agencies thrive in the digital age.</p>
                <p>Midas Portal was built by insurance professionals who understand the challenges of running an agency. We've combined industry expertise with modern technology to create a platform that actually works.</p>
            </div>
            <div class="col-lg-6">
                <div class="text-center">
                    <i class="fas fa-building" style="font-size: 200px; color: var(--primary-color); opacity: 0.1;"></i>
                </div>
            </div>
        </div>

        <div class="row g-4 mt-5">
            <div class="col-md-4 text-center">
                <div class="feature-icon mx-auto">
                    <i class="fas fa-rocket"></i>
                </div>
                <h4>Our Mission</h4>
                <p class="text-muted">Empower insurance agencies with technology that drives growth and efficiency.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-icon mx-auto">
                    <i class="fas fa-users"></i>
                </div>
                <h4>Our Team</h4>
                <p class="text-muted">Experienced developers and insurance professionals working together.</p>
            </div>
            <div class="col-md-4 text-center">
                <div class="feature-icon mx-auto">
                    <i class="fas fa-heart"></i>
                </div>
                <h4>Our Values</h4>
                <p class="text-muted">Customer success, innovation, and reliability are at our core.</p>
            </div>
        </div>
    </div>
</section>
@endsection
