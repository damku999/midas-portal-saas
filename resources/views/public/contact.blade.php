@extends('public.layout')

@section('title', 'Contact Us - Midas Portal')
@section('meta_description', 'Contact Midas Portal for demo, support, or sales inquiries. Get in touch with our team for insurance management software solutions. Email: Info@midastech.in | Phone: +91 80000 71413')
@section('meta_keywords', 'contact midas portal, insurance software support, midas portal demo, insurance software inquiry, contact insurance crm, webmonks technologies contact, insurance software sales')

@section('content')

{{-- Hero Section using component --}}
@include('public.components.hero', [
    'badge' => 'Contact Us',
    'badgeIcon' => 'fas fa-comments',
    'title' => 'Get In Touch',
    'description' => 'Have questions about Midas Portal? Our team is here to help you find the perfect insurance management solution for your agency.',
    'containerClass' => 'py-5',
    'colClass' => 'col-lg-8'
])

{{-- Contact Info Cards Section --}}
<section class="py-5 bg-light">
    <div class="container">
        <div class="row g-4">
            <div class="col-md-3">
                @include('public.components.contact-info-card', [
                    'icon' => 'fas fa-envelope',
                    'title' => 'Email Us',
                    'subtitle' => 'For general inquiries',
                    'link' => 'Info@midastech.in',
                    'linkText' => 'Info@midastech.in',
                    'linkType' => 'email',
                    'delay' => 0
                ])
            </div>
            <div class="col-md-3">
                @include('public.components.contact-info-card', [
                    'icon' => 'fas fa-phone',
                    'title' => 'Call Us',
                    'subtitle' => 'Mon-Sat: 10 AM - 7 PM IST',
                    'link' => '+918000071413',
                    'linkText' => '+91 80000 71413',
                    'linkType' => 'phone',
                    'delay' => 0.2
                ])
            </div>
            <div class="col-md-3">
                @include('public.components.contact-info-card', [
                    'icon' => 'fab fa-whatsapp',
                    'title' => 'WhatsApp',
                    'subtitle' => 'Quick response',
                    'link' => 'https://wa.me/918000071413',
                    'linkText' => 'Chat with Us',
                    'linkType' => 'url',
                    'delay' => 0.4
                ])
            </div>
            <div class="col-md-3">
                @include('public.components.contact-info-card', [
                    'icon' => 'fas fa-map-marker-alt',
                    'title' => 'Visit Us',
                    'subtitle' => 'Ahmedabad, Gujarat',
                    'link' => '#office-location',
                    'linkText' => 'View on Map',
                    'linkType' => 'url',
                    'delay' => 0.6
                ])
            </div>
        </div>
    </div>
</section>

{{-- Contact Form & Office Info Section --}}
<section class="py-5">
    <div class="container py-4">
        <div class="row g-5">
            {{-- Contact Form --}}
            <div class="col-lg-7 scroll-reveal">
                <h2 class="fw-bold mb-4">Send Us a Message</h2>
                <p class="text-muted mb-4">Fill out the form below and our team will get back to you within 24 hours.</p>

                {{-- Alert Messages using component --}}
                @if(session('success'))
                    @include('public.components.alert-message', [
                        'type' => 'success',
                        'message' => session('success')
                    ])
                @endif

                @if(session('error'))
                    @include('public.components.alert-message', [
                        'type' => 'error',
                        'message' => session('error')
                    ])
                @endif

                @if($errors->any())
                    @include('public.components.alert-message', [
                        'type' => 'error',
                        'message' => 'Please correct the following errors:'
                    ])
                    <ul class="mb-3 text-danger">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                @endif

                {{-- Contact Form --}}
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

            {{-- Office Info Sidebar --}}
            <div class="col-lg-5 scroll-reveal delay-200">
                <div class="sticky-top" style="top: 100px;">
                    <h2 class="fw-bold mb-4">Office Information</h2>

                    {{-- Address Card --}}
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

                    {{-- Business Hours --}}
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

                    {{-- Quick Links --}}
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

{{-- Office Location Map Section --}}
<section class="py-5 bg-light" id="office-location">
    <div class="container">
        @include('public.components.section-header', [
            'title' => 'Find Us on Map',
            'description' => 'Visit our office in Ahmedabad, Gujarat'
        ])

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

{{-- FAQ Section using component --}}
<section class="py-5">
    <div class="container py-4">
        @include('public.components.section-header', [
            'title' => 'Frequently Asked Questions',
            'description' => 'Quick answers to common questions'
        ])

        <div class="row justify-content-center">
            <div class="col-lg-8">
                @include('public.components.faq-accordion', [
                    'accordionId' => 'contactFAQ',
                    'faqs' => [
                        [
                            'question' => 'How quickly will I receive a response?',
                            'answer' => 'We typically respond to all inquiries within 24 hours during business days. For urgent matters, please call us directly at +91 80000 71413.'
                        ],
                        [
                            'question' => 'Can I schedule a demo?',
                            'answer' => 'Yes! Contact us through the form above or call us to schedule a personalized demo of Midas Portal. We\'ll walk you through all features relevant to your insurance agency.'
                        ],
                        [
                            'question' => 'Do you offer technical support?',
                            'answer' => 'Yes, we provide comprehensive technical support to all our customers. Support options include email, phone, and WhatsApp. Enterprise customers get priority support with dedicated account managers.'
                        ],
                        [
                            'question' => 'What information should I include in my message?',
                            'answer' => 'Please include your agency name, number of users, current challenges, and specific requirements. This helps us provide you with the most accurate information and recommendations.'
                        ]
                    ]
                ])
            </div>
        </div>
    </div>
</section>

{{-- CTA Section using component --}}
@include('public.components.cta-section', [
    'title' => 'Ready to Get Started?',
    'description' => 'Start your 14-day free trial today. No credit card required.',
    'primaryText' => 'Start Free Trial',
    'primaryUrl' => url('/pricing'),
    'primaryDataCta' => 'contact-cta-trial',
    'containerClass' => 'py-4'
])

@endsection

@push('styles')
<style>
    .sticky-top {
        position: -webkit-sticky;
        position: sticky;
    }

    /* Z-index utilities */
    .z-index-2 {
        z-index: 2;
    }
</style>
@endpush
