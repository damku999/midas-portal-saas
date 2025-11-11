@extends('public.layout')

@section('title', 'Contact Us - Midas Portal')

@section('meta_description', 'Contact Midas Portal for demo, support, or sales inquiries. Get in touch with our team for insurance management software solutions. Email: Info@midastech.in | Phone: +91 80000 71413')

@section('meta_keywords', 'contact midas portal, insurance software support, midas portal demo, insurance software inquiry, contact insurance crm, webmonks technologies contact, insurance software sales')

@section('content')
<!-- Hero Section -->
<section class="hero-section position-relative overflow-hidden">
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: 10%; left: 5%; width: 60px; height: 60px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="top: 70%; right: 10%; width: 50px; height: 50px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-500" style="bottom: 20%; left: 15%; width: 55px; height: 55px; background: white; border-radius: 50%;"></div>
    </div>

    <div class="container py-5 position-relative z-index-2">
        <div class="row align-items-center justify-content-center text-center">
            <div class="col-lg-8 text-white">
                <span class="badge bg-white text-primary mb-3 px-4 py-2 animate-fade-in-down shadow-sm">
                    <i class="fas fa-comments me-2"></i>Contact Us
                </span>
                <h1 class="display-3 fw-bold mb-4 animate-fade-in-up delay-100">Get In Touch</h1>
                <p class="lead mb-0 animate-fade-in-up delay-200">Have questions about Midas Portal? Our team is here to help you find the perfect insurance management solution for your agency.</p>
            </div>
        </div>
    </div>
</section>

<!-- Contact Info Cards -->
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="modern-card modern-card-gradient h-100 text-center scroll-reveal hover-lift">
                    <div class="icon-box bg-primary bg-opacity-100 mx-auto" style="background: linear-gradient(135deg, #17b6b6 0%, #13918e 100%) !important;">
                        <i class="fas fa-envelope text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Email Us</h5>
                    <p class="text-muted small mb-2">For general inquiries</p>
                    <a href="mailto:Info@midastech.in" class="text-primary text-decoration-none fw-semibold hover-glow">Info@midastech.in</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="modern-card modern-card-gradient h-100 text-center scroll-reveal hover-lift delay-200">
                    <div class="icon-box bg-success bg-opacity-100 mx-auto" style="background: linear-gradient(135deg, #17b6b6 0%, #13918e 100%) !important;">
                        <i class="fas fa-phone text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Call Us</h5>
                    <p class="text-muted small mb-2">Mon-Sat: 10 AM - 7 PM IST</p>
                    <a href="tel:+918000071413" class="text-primary text-decoration-none fw-semibold hover-glow">+91 80000 71413</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="modern-card modern-card-gradient h-100 text-center scroll-reveal hover-lift delay-400">
                    <div class="icon-box bg-info bg-opacity-100 mx-auto" style="background: linear-gradient(135deg, #17b6b6 0%, #13918e 100%) !important;">
                        <i class="fab fa-whatsapp text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-2">WhatsApp</h5>
                    <p class="text-muted small mb-2">Quick response</p>
                    <a href="https://wa.me/918000071413" target="_blank" class="text-primary text-decoration-none fw-semibold hover-glow">Chat with Us</a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="modern-card modern-card-gradient h-100 text-center scroll-reveal hover-lift delay-600">
                    <div class="icon-box bg-warning bg-opacity-100 mx-auto" style="background: linear-gradient(135deg, #17b6b6 0%, #13918e 100%) !important;">
                        <i class="fas fa-map-marker-alt text-white"></i>
                    </div>
                    <h5 class="fw-bold mb-2">Visit Us</h5>
                    <p class="text-muted small mb-2">Ahmedabad, Gujarat</p>
                    <a href="#office-location" class="text-primary text-decoration-none fw-semibold hover-glow">View on Map</a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Contact Form & Office Info -->
<section class="py-5">
    <div class="container py-4">
        <div class="row g-5">
            <!-- Contact Form -->
            <div class="col-lg-7 scroll-reveal">
                <h2 class="fw-bold mb-4">Send Us a Message</h2>
                <p class="text-muted mb-4">Fill out the form below and our team will get back to you within 24 hours.</p>

                @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                @if($errors->any())
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <strong>Please correct the following errors:</strong>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                @endif

                <div class="modern-card modern-card-gradient hover-lift">
                    <div class="p-4">
                        <form action="{{ url('/contact') }}" method="POST">
                            @csrf
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Full Name *</label>
                                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="John Doe" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Email Address *</label>
                                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="john@example.com" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Phone Number</label>
                                    <input type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" placeholder="+91 98765 43210">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Company Name</label>
                                    <input type="text" name="company" class="form-control @error('company') is-invalid @enderror" value="{{ old('company') }}" placeholder="Your Insurance Agency">
                                    @error('company')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold">Message *</label>
                                    <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="5" placeholder="Tell us about your requirements..." required>{{ old('message') }}</textarea>
                                    @error('message')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <div class="cf-turnstile mb-3" data-sitekey="{{ config('services.turnstile.key') }}" data-theme="light"></div>
                                    @error('cf-turnstile-response')
                                        <div class="text-danger small mb-2">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-gradient btn-lg px-5 hover-glow" data-cta="contact-form-submit">
                                        <i class="fas fa-paper-plane me-2"></i>Send Message
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Office Info Sidebar -->
            <div class="col-lg-5 scroll-reveal delay-200">
                <div class="sticky-top" style="top: 100px;">
                    <h2 class="fw-bold mb-4">Office Information</h2>

                    <!-- Address Card -->
                    <div class="modern-card modern-card-gradient mb-4 hover-lift">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-building text-primary me-2"></i>Head Office
                        </h5>
                        <p class="text-muted mb-0">
                            <strong>WebMonks Technologies</strong><br>
                            C243, Second Floor, SoBo Center<br>
                            Gala Gymkhana Road, South Bopal<br>
                            Ahmedabad - 380058<br>
                            Gujarat, India
                        </p>
                    </div>

                    <!-- Business Hours -->
                    <div class="modern-card modern-card-gradient mb-4 hover-lift">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-clock text-primary me-2"></i>Business Hours
                        </h5>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Monday - Friday</span>
                            <span class="fw-semibold">10:00 AM - 7:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Saturday</span>
                            <span class="fw-semibold">10:00 AM - 5:00 PM</span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted">Sunday</span>
                            <span class="text-danger fw-semibold">Closed</span>
                        </div>
                        <hr class="my-3">
                        <p class="text-muted small mb-0">
                            <i class="fas fa-info-circle me-2"></i>All times are in Indian Standard Time (IST)
                        </p>
                    </div>

                    <!-- Quick Links -->
                    <div class="modern-card modern-card-gradient hover-lift">
                        <h5 class="fw-bold mb-3">
                            <i class="fas fa-link text-primary me-2"></i>Quick Actions
                        </h5>
                        <div class="d-grid gap-2">
                            <a href="{{ url('/pricing') }}" class="btn btn-outline-primary hover-scale" data-cta="contact-quick-pricing">
                                <i class="fas fa-tag me-2"></i>View Pricing Plans
                            </a>
                            <a href="{{ url('/features') }}" class="btn btn-outline-primary hover-scale" data-cta="contact-quick-features">
                                <i class="fas fa-list me-2"></i>Explore Features
                            </a>
                            <a href="{{ url('/about') }}" class="btn btn-outline-primary hover-scale" data-cta="contact-quick-about">
                                <i class="fas fa-info-circle me-2"></i>About Us
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Office Location Map -->
<section class="py-5 bg-light" id="office-location">
    <div class="container">
        <div class="text-center mb-5 scroll-reveal">
            <h2 class="fw-bold">Find Us on Map</h2>
            <p class="text-muted">Visit our office in Ahmedabad, Gujarat</p>
        </div>
        <div class="modern-card modern-card-gradient overflow-hidden scroll-reveal delay-200 hover-lift">
            <div class="ratio ratio-21x9">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3671.5986873434846!2d72.47057!3d23.022508!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zMjPCsDAxJzIxLjAiTiA3MsKwMjgnMTQuMSJF!5e0!3m2!1sen!2sin!4v1234567890"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade">
                </iframe>
            </div>
        </div>
    </div>
</section>

<!-- FAQ Section -->
<section class="py-5">
    <div class="container py-4">
        <div class="text-center mb-5 scroll-reveal">
            <h2 class="fw-bold">Frequently Asked Questions</h2>
            <p class="text-muted">Quick answers to common questions</p>
        </div>
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="accordion" id="contactFAQ">
                    <div class="accordion-item modern-card mb-3 scroll-reveal hover-lift">
                        <h3 class="accordion-header">
                            <button class="accordion-button fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                                How quickly will I receive a response?
                            </button>
                        </h3>
                        <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#contactFAQ">
                            <div class="accordion-body">
                                We typically respond to all inquiries within 24 hours during business days. For urgent matters, please call us directly at +91 80000 71413.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item modern-card mb-3 scroll-reveal hover-lift delay-100">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                                Can I schedule a demo?
                            </button>
                        </h3>
                        <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body">
                                Yes! Contact us through the form above or call us to schedule a personalized demo of Midas Portal. We'll walk you through all features relevant to your insurance agency.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item modern-card mb-3 scroll-reveal hover-lift delay-200">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                                Do you offer technical support?
                            </button>
                        </h3>
                        <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body">
                                Yes, we provide comprehensive technical support to all our customers. Support options include email, phone, and WhatsApp. Enterprise customers get priority support with dedicated account managers.
                            </div>
                        </div>
                    </div>

                    <div class="accordion-item modern-card scroll-reveal hover-lift delay-300">
                        <h3 class="accordion-header">
                            <button class="accordion-button collapsed fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                                What information should I include in my message?
                            </button>
                        </h3>
                        <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#contactFAQ">
                            <div class="accordion-body">
                                Please include your agency name, number of users, current challenges, and specific requirements. This helps us provide you with the most accurate information and recommendations.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="gradient-primary position-relative overflow-hidden py-5">
    <!-- Animated Background Elements -->
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <div class="position-absolute animate-float" style="top: -10%; right: 10%; width: 300px; height: 300px; background: white; border-radius: 50%;"></div>
        <div class="position-absolute animate-float delay-300" style="bottom: -10%; left: 5%; width: 250px; height: 250px; background: white; border-radius: 50%;"></div>
    </div>

    <div class="container py-4 position-relative z-index-2">
        <div class="row align-items-center">
            <div class="col-lg-8 mx-auto text-center text-white">
                <h2 class="fw-bold mb-3 scroll-reveal">Ready to Get Started?</h2>
                <p class="lead mb-4 scroll-reveal delay-100">Start your 14-day free trial today. No credit card required.</p>
                <a href="{{ url('/pricing') }}" class="btn btn-light btn-lg px-5 scroll-reveal delay-200 hover-lift" data-cta="contact-cta-trial">
                    <i class="fas fa-rocket me-2"></i>Start Free Trial
                </a>
            </div>
        </div>
    </div>
</section>
@endsection

@push('styles')
<style>
    .sticky-top {
        position: -webkit-sticky;
        position: sticky;
    }

    .accordion-button:not(.collapsed) {
        background-color: rgba(23, 182, 182, 0.1);
        color: #17b6b6;
    }

    .accordion-button:focus {
        box-shadow: none;
        border-color: #17b6b6;
    }

    .accordion-item {
        border: none !important;
    }

    /* Z-index utilities */
    .z-index-2 {
        z-index: 2;
    }
</style>
@endpush
