@extends('public.layout')

@section('title', 'About Us - Midas Portal')

@section('meta_description', 'Learn about Midas Portal by Webmonks Technologies - India\'s trusted insurance management platform serving 500+ agencies. Built by insurance professionals for insurance excellence.')

@section('meta_keywords', 'about midas portal, insurance software company, webmonks technologies, insurance saas platform india, insurance management company, insurance technology provider, indian insurance software')

@section('content')
<!-- Hero Section -->
<section class="hero-section">
    <div class="container">
        <div class="row align-items-center justify-content-center">
            <div class="col-lg-10 text-white text-center">
                <h1 class="display-3 fw-bold mb-4">Transforming Insurance Management</h1>
                <p class="lead mb-4">We're on a mission to empower insurance agencies across India with cutting-edge technology that drives growth, efficiency, and customer satisfaction.</p>
                <a href="{{ url('/contact') }}" class="btn btn-light btn-lg px-4">
                    <i class="fas fa-phone me-2"></i>Get in Touch
                </a>
            </div>
        </div>
    </div>
</section>

<!-- Our Story Section -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="row align-items-center">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <span class="text-primary fw-bold text-uppercase small">Our Story</span>
                <h2 class="display-5 fw-bold mt-2 mb-4">Built by Insurance Professionals, For Insurance Professionals</h2>
                <p class="text-muted mb-4">Midas Portal was born from real-world experience. Our founders spent years working in the insurance industry and witnessed firsthand the challenges that agencies face daily - managing customer data across spreadsheets, missing renewal opportunities, struggling with manual commission calculations, and lacking insights into business performance.</p>
                <p class="text-muted mb-4">We knew there had to be a better way. In 2020, we assembled a team of insurance experts and software engineers to build a solution that addresses these pain points. The result is Midas Portal - a comprehensive, cloud-based platform designed specifically for Indian insurance agencies.</p>
                <p class="text-muted">Today, we're proud to serve over 500+ insurance agencies across India, helping them manage 50,000+ policies and grow their businesses with confidence.</p>
            </div>
            <div class="col-lg-6">
                <div class="about-stats">
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold text-primary mb-0">2020</h2>
                        <p class="text-muted">Founded</p>
                    </div>
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold text-primary mb-0">500+</h2>
                        <p class="text-muted">Active Agencies</p>
                    </div>
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold text-primary mb-0">50K+</h2>
                        <p class="text-muted">Policies Managed</p>
                    </div>
                    <div class="stat-item">
                        <h2 class="display-4 fw-bold text-primary mb-0">99.9%</h2>
                        <p class="text-muted">Uptime SLA</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Mission, Vision, Values -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">What Drives Us</span>
            <h2 class="display-5 fw-bold mt-2 mb-3">Our Mission, Vision & Values</h2>
            <p class="lead text-muted">The principles that guide everything we do</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-rocket"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Our Mission</h4>
                    <p class="text-muted mb-0">To empower insurance agencies with modern technology that simplifies operations, enhances customer relationships, and drives sustainable growth. We believe every agency, regardless of size, deserves access to enterprise-grade tools.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Our Vision</h4>
                    <p class="text-muted mb-0">To become India's most trusted insurance management platform, setting the standard for innovation, reliability, and customer success. We envision a future where every insurance agency operates efficiently and profitably with the help of technology.</p>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="value-card">
                    <div class="value-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h4 class="fw-bold mb-3">Our Values</h4>
                    <p class="text-muted mb-0">Customer success is our success. We're committed to innovation, reliability, transparency, and continuous improvement. We build trust through consistent delivery and genuinely care about helping our customers achieve their goals.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- What Sets Us Apart -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Why Choose Us</span>
            <h2 class="display-5 fw-bold mt-2 mb-3">What Sets Us Apart</h2>
            <p class="lead text-muted">We're not just another software company</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Industry Expertise</h5>
                    <p class="text-muted mb-0">Built by insurance professionals who understand your challenges and workflows.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Modern Technology</h5>
                    <p class="text-muted mb-0">Cloud-based, mobile-responsive, and built with the latest web technologies.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5 class="fw-bold mb-3">24/7 Support</h5>
                    <p class="text-muted mb-0">Dedicated support team ready to help you whenever you need assistance.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3">
                <div class="feature-card text-center">
                    <div class="feature-icon mx-auto">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <h5 class="fw-bold mb-3">Regular Updates</h5>
                    <p class="text-muted mb-0">Continuous improvements and new features based on customer feedback.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Team Section -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Our Team</span>
            <h2 class="display-5 fw-bold mt-2 mb-3">Meet The People Behind Midas Portal</h2>
            <p class="lead text-muted">A diverse team of insurance experts, developers, and support professionals</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-3 text-center">
                <div class="team-member">
                    <div class="team-avatar mx-auto mb-3">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Insurance Experts</h5>
                    <p class="text-muted mb-0">15+ years combined experience</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="team-member">
                    <div class="team-avatar mx-auto mb-3">
                        <i class="fas fa-code"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Software Engineers</h5>
                    <p class="text-muted mb-0">Building robust solutions</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="team-member">
                    <div class="team-avatar mx-auto mb-3">
                        <i class="fas fa-paint-brush"></i>
                    </div>
                    <h5 class="fw-bold mb-1">UX/UI Designers</h5>
                    <p class="text-muted mb-0">Creating intuitive interfaces</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="team-member">
                    <div class="team-avatar mx-auto mb-3">
                        <i class="fas fa-headset"></i>
                    </div>
                    <h5 class="fw-bold mb-1">Support Team</h5>
                    <p class="text-muted mb-0">Always here to help</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Technology Stack -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Technology</span>
            <h2 class="display-5 fw-bold mt-2 mb-3">Built with Modern Technology</h2>
            <p class="lead text-muted">Enterprise-grade infrastructure for reliability and performance</p>
        </div>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="tech-card">
                    <i class="fas fa-cloud text-primary mb-3"></i>
                    <h5 class="fw-bold mb-2">Cloud Infrastructure</h5>
                    <p class="text-muted mb-0">Hosted on reliable cloud servers with automatic backups and 99.9% uptime guarantee.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="tech-card">
                    <i class="fas fa-lock text-primary mb-3"></i>
                    <h5 class="fw-bold mb-2">Bank-Grade Security</h5>
                    <p class="text-muted mb-0">SSL encryption, secure data centers, and regular security audits to protect your data.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="tech-card">
                    <i class="fas fa-mobile-alt text-primary mb-3"></i>
                    <h5 class="fw-bold mb-2">Mobile Responsive</h5>
                    <p class="text-muted mb-0">Access from any device - desktop, tablet, or mobile with seamless experience.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="tech-card">
                    <i class="fas fa-database text-primary mb-3"></i>
                    <h5 class="fw-bold mb-2">Scalable Database</h5>
                    <p class="text-muted mb-0">Handles millions of records with lightning-fast query performance.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="tech-card">
                    <i class="fas fa-sync-alt text-primary mb-3"></i>
                    <h5 class="fw-bold mb-2">Real-time Updates</h5>
                    <p class="text-muted mb-0">Instant synchronization across all devices and users in real-time.</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-4">
                <div class="tech-card">
                    <i class="fas fa-chart-line text-primary mb-3"></i>
                    <h5 class="fw-bold mb-2">Advanced Analytics</h5>
                    <p class="text-muted mb-0">Powerful reporting and analytics to drive data-driven decisions.</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Certifications & Compliance -->
<section class="py-5 bg-light">
    <div class="container py-5">
        <div class="text-center mb-5">
            <span class="text-primary fw-bold text-uppercase small">Trust & Compliance</span>
            <h2 class="display-5 fw-bold mt-2 mb-3">Certifications & Compliance</h2>
            <p class="lead text-muted">We take security and compliance seriously</p>
        </div>

        <div class="row g-4 justify-content-center">
            <div class="col-md-6 col-lg-3 text-center">
                <div class="compliance-card">
                    <i class="fas fa-shield-alt text-primary mb-3"></i>
                    <h5 class="fw-bold">SSL Certified</h5>
                    <p class="text-muted small mb-0">256-bit encryption</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="compliance-card">
                    <i class="fas fa-lock text-primary mb-3"></i>
                    <h5 class="fw-bold">ISO Compliant</h5>
                    <p class="text-muted small mb-0">Quality management</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="compliance-card">
                    <i class="fas fa-database text-primary mb-3"></i>
                    <h5 class="fw-bold">Data Protection</h5>
                    <p class="text-muted small mb-0">GDPR compliant</p>
                </div>
            </div>
            <div class="col-md-6 col-lg-3 text-center">
                <div class="compliance-card">
                    <i class="fas fa-server text-primary mb-3"></i>
                    <h5 class="fw-bold">Daily Backups</h5>
                    <p class="text-muted small mb-0">Automatic & secure</p>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="py-5 bg-white">
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h2 class="display-5 fw-bold mb-4">Ready to Transform Your Insurance Agency?</h2>
                <p class="lead text-muted mb-4">Join hundreds of successful agencies already using Midas Portal. Start your free 14-day trial today!</p>
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <a href="{{ url('/pricing') }}" class="btn btn-gradient btn-lg px-5">
                        <i class="fas fa-rocket me-2"></i>Start Free Trial
                    </a>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-primary btn-lg px-5">
                        <i class="fas fa-phone me-2"></i>Contact Sales
                    </a>
                </div>
                <p class="mt-3 small text-muted">No credit card required • 14-day free trial • Cancel anytime</p>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    /* About Stats Grid */
    .about-stats {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 2rem;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(23, 182, 182, 0.05) 0%, rgba(19, 145, 142, 0.05) 100%);
        border-radius: 15px;
    }

    .stat-item {
        text-align: center;
        padding: 1.5rem;
    }

    /* Value Cards */
    .value-card {
        background: white;
        padding: 2.5rem;
        border-radius: 15px;
        height: 100%;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        transition: all 0.3s ease;
    }

    .value-card:hover {
        transform: translateY(-10px);
        box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    }

    .value-icon {
        width: 80px;
        height: 80px;
        background: var(--gradient-primary);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1.5rem;
        font-size: 2rem;
        color: white;
    }

    /* Team Member */
    .team-member {
        padding: 2rem;
        background: white;
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .team-member:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    }

    .team-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(135deg, rgba(23, 182, 182, 0.1) 0%, rgba(19, 145, 142, 0.1) 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.5rem;
        color: var(--primary-color);
    }

    /* Tech Cards */
    .tech-card {
        padding: 2rem;
        background: #f8f9fa;
        border-radius: 10px;
        height: 100%;
        transition: all 0.3s ease;
    }

    .tech-card:hover {
        background: white;
        box-shadow: 0 10px 25px rgba(0,0,0,0.08);
        transform: translateY(-5px);
    }

    .tech-card i {
        font-size: 2.5rem;
        display: block;
    }

    /* Compliance Cards */
    .compliance-card {
        padding: 2rem;
        background: white;
        border-radius: 15px;
        transition: all 0.3s ease;
    }

    .compliance-card:hover {
        transform: scale(1.05);
        box-shadow: 0 10px 25px rgba(0,0,0,0.1);
    }

    .compliance-card i {
        font-size: 3rem;
        display: block;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .about-stats {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
    }
</style>
@endpush
