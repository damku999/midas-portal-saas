<!DOCTYPE html>
<html lang="en">
<head>
    @if(config('services.google_tag_manager.enabled'))
    <!-- Google Tag Manager -->
    <script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
    new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
    j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
    'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','{{ config('services.google_tag_manager.container_id') }}');</script>
    <!-- End Google Tag Manager -->
    @endif

    @if(config('services.google_analytics.enabled'))
    <!-- Google tag (gtag.js) - Google Analytics 4 -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google_analytics.measurement_id') }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '{{ config('services.google_analytics.measurement_id') }}', {
            'send_page_view': true,
            'cookie_flags': 'SameSite=None;Secure'
        });
    </script>
    @endif

    @if(config('services.microsoft_clarity.enabled'))
    <!-- Microsoft Clarity -->
    <script type="text/javascript">
        (function(c,l,a,r,i,t,y){
            c[a]=c[a]||function(){(c[a].q=c[a].q||[]).push(arguments)};
            t=l.createElement(r);t.async=1;t.src="https://www.clarity.ms/tag/"+i;
            y=l.getElementsByTagName(r)[0];y.parentNode.insertBefore(t,y);
        })(window, document, "clarity", "script", "{{ config('services.microsoft_clarity.project_id') }}");
    </script>
    @endif

    <!-- 🔤 BASIC META TAGS -->
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="content-language" content="en">

    <!-- 📄 SEO META TAGS -->
    <title>@yield('title', 'Midas Portal - Insurance Management SaaS Platform')</title>
    <meta name="description" content="@yield('meta_description', 'Modern multi-tenant insurance management SaaS platform for agencies. Manage customers, policies, claims, quotations, and more with powerful automation.')">
    <meta name="keywords" content="@yield('meta_keywords', 'insurance management software, insurance CRM, policy management, claims management, insurance agency software, multi-tenant saas')">
    <meta name="author" content="WebMonks Technologies">

    <!-- 🧭 ROBOTS / INDEXING CONTROL -->
    <meta name="robots" content="index, follow, max-snippet:-1, max-image-preview:large, max-video-preview:-1">
    <meta name="googlebot" content="index, follow">
    <meta name="bingbot" content="index, follow">
    <meta name="referrer" content="no-referrer-when-downgrade">

    <!-- 🌐 CANONICAL & ALTERNATE LINKS -->
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="en-IN" href="{{ url()->current() }}">
    <link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">

    <!-- 🧩 ICONS & MANIFEST -->
    <link rel="icon" href="{{ asset('images/logo-icon@2000x.png') }}" sizes="any">
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('images/logo-icon@2000x.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/logo-icon@2000x.png') }}">
    <meta name="theme-color" content="#17b6b6">

    <!-- 📱 MOBILE & PWA -->
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="Midas Portal">
    <meta name="format-detection" content="telephone=no">
    <meta name="google" value="notranslate">

    <!-- 🎨 MICROSOFT / WINDOWS -->
    <meta name="msapplication-TileColor" content="#17b6b6">
    <meta name="msapplication-TileImage" content="{{ asset('images/logo-icon@2000x.png') }}">

    <!-- 💬 OPEN GRAPH (FACEBOOK, LINKEDIN, WHATSAPP) -->
    <meta property="og:title" content="@yield('title', 'Midas Portal - Insurance Management SaaS Platform')">
    <meta property="og:description" content="@yield('meta_description', 'Modern multi-tenant insurance management SaaS platform for agencies.')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ asset('images/logo.png') }}">
    <meta property="og:site_name" content="Midas Portal">
    <meta property="og:locale" content="en_US">
    <meta property="og:updated_time" content="{{ now()->toIso8601String() }}">

    <!-- 🐦 TWITTER CARDS -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('title', 'Midas Portal - Insurance Management SaaS Platform')">
    <meta name="twitter:description" content="@yield('meta_description', 'Modern multi-tenant insurance management SaaS platform for agencies.')">
    <meta name="twitter:image" content="{{ asset('images/logo.png') }}">
    <meta name="twitter:site" content="@MidasPortal">

    <!-- 🌍 GEO / LOCATION META (Ahmedabad, Gujarat, India) -->
    <meta name="geo.region" content="IN-GJ">
    <meta name="geo.placename" content="Ahmedabad">
    <meta name="geo.position" content="23.0225;72.5714">
    <meta name="ICBM" content="23.0225, 72.5714">

    <!-- ⚙️ PERFORMANCE HINTS -->
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">

    <!-- Bootstrap 5 CSS (Critical) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome (Non-blocking) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"></noscript>
    <!-- Modern Animations CSS (Non-blocking, Minified) -->
    <link rel="stylesheet" href="{{ asset('css/modern-animations.min.css') }}" media="print" onload="this.media='all'">
    <noscript><link rel="stylesheet" href="{{ asset('css/modern-animations.min.css') }}"></noscript>

    <style>
        :root {
            /* Brand Colors - Based on WebMonks Logo */
            --primary-color: #17b6b6;
            --primary-dark: #13918e;
            --primary-light: #4dd4d4;
            --secondary-color: #424242;
            --success-color: #28a745;
            --webmonks-teal: #17b6b6;
            --webmonks-gray: #4a4a4a;
            --gradient-primary: linear-gradient(135deg, #17b6b6 0%, #13918e 100%);
            --gradient-primary-hover: linear-gradient(135deg, #13918e 0%, #0f706e 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        /* Override Bootstrap primary color with brand color */
        .btn-primary {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-primary:hover,
        .btn-primary:focus {
            background-color: var(--primary-dark);
            border-color: var(--primary-dark);
        }

        .btn-outline-primary {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-primary:hover,
        .btn-outline-primary:focus {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: white;
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .bg-primary {
            background-color: var(--primary-color) !important;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .navbar-brand img {
            height: 45px;
            width: auto;
        }

        .navbar-nav .nav-link.active {
            color: var(--primary-color) !important;
            font-weight: 600;
        }

        .hero-section {
            background: var(--gradient-primary);
            color: white;
            padding: 100px 0;
            min-height: 600px;
            display: flex;
            align-items: center;
        }

        .feature-card {
            padding: 2rem;
            border-radius: 10px;
            border: 1px solid #e3e6f0;
            transition: all 0.3s;
            height: 100%;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: var(--gradient-primary);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .pricing-card {
            border: 2px solid #e3e6f0;
            border-radius: 15px;
            padding: 2.5rem;
            transition: all 0.3s;
            height: 100%;
        }

        .pricing-card:hover {
            border-color: var(--primary-color);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .pricing-card.featured {
            border-color: var(--primary-color);
            transform: scale(1.05);
            box-shadow: 0 15px 40px rgba(78, 115, 223, 0.3);
        }

        .btn-gradient {
            background: var(--gradient-primary);
            border: none;
            color: white;
            padding: 12px 30px;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s;
        }

        .btn-gradient:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            color: white;
        }

        footer {
            background: #2d3748;
            color: #cbd5e0;
            padding: 3rem 0 1rem;
        }
    </style>

    <!-- Schema.org Structured Data (JSON-LD) -->
    <script type="application/ld+json">
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Midas Portal",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web Browser",
        "offers": {
            "@type": "Offer",
            "price": "29.00",
            "priceCurrency": "USD",
            "priceValidUntil": "{{ now()->addYear()->format('Y-m-d') }}"
        },
        "aggregateRating": {
            "@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "150"
        },
        "provider": {
            "@type": "Organization",
            "name": "WebMonks Technologies",
            "url": "{{ url('/') }}",
            "logo": "{{ asset('images/logo.png') }}",
            "contactPoint": {
                "@type": "ContactPoint",
                "telephone": "+91-80000-71413",
                "contactType": "customer service",
                "email": "Info@midastech.in",
                "areaServed": "IN",
                "availableLanguage": ["English"]
            },
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "C243, Second Floor, SoBo Center, Gala Gymkhana Road, South Bopal",
                "addressLocality": "Ahmedabad",
                "addressRegion": "Gujarat",
                "postalCode": "380058",
                "addressCountry": "IN"
            }
        },
        "description": "Modern multi-tenant insurance management SaaS platform for agencies. Manage customers, policies, claims, quotations, and commissions with powerful automation.",
        "featureList": [
            "Customer Management",
            "Policy Management",
            "Claims Management",
            "Quotation System",
            "Lead Management",
            "WhatsApp Integration",
            "Analytics & Reports",
            "Commission Tracking"
        ]
    }
    </script>

    @yield('styles')
</head>
<body>
    @if(config('services.google_tag_manager.enabled'))
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google_tag_manager.container_id') }}"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    @endif

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo.png') }}" alt="WebMonks Technologies" class="d-inline-block align-text-top" width="180" height="45">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('features*') ? 'active' : '' }}" href="{{ url('/features') }}">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('pricing*') ? 'active' : '' }}" href="{{ url('/pricing') }}">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('about*') ? 'active' : '' }}" href="{{ url('/about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('blog*') ? 'active' : '' }}" href="{{ url('/blog') }}">Blog</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->is('contact*') ? 'active' : '' }}" href="{{ url('/contact') }}">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-outline-primary btn-sm" href="http://demo.midastech.in" target="_blank" rel="noopener noreferrer">Demo</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer class="position-relative overflow-hidden">
        <div class="container position-relative z-index-2">
            <div class="row g-4 scroll-reveal">
                <!-- Company Info -->
                <div class="col-lg-4 col-md-6 mb-4 animate-fade-in-up">
                    <h5 class="text-white mb-3">
                        <img src="{{ asset('images/logo.png') }}" alt="Midas Portal by WebMonks" style="height: 40px; filter: brightness(0) invert(1);" class="mb-3" width="160" height="40">
                    </h5>
                    <p class="small mb-3">Transform your insurance business with cutting-edge technology. Modern multi-tenant insurance management SaaS platform for agencies.</p>

                    <div class="small mb-3">
                        <div class="mb-2"><i class="fas fa-map-marker-alt me-2 text-primary"></i>C243, Second Floor, SoBo Center<br>
                        <span class="ms-4">Gala Gymkhana Road, South Bopal</span><br>
                        <span class="ms-4">Ahmedabad - 380058, Gujarat, India</span></div>
                        <div class="mb-2"><i class="fas fa-phone me-2 text-primary"></i><a href="tel:+918000071413" class="text-light text-decoration-none">+91 80000 71413</a></div>
                        <div class="mb-2"><i class="fas fa-envelope me-2 text-primary"></i><a href="mailto:Info@midastech.in" class="text-light text-decoration-none">Info@midastech.in</a></div>
                    </div>

                    <div class="mt-3">
                        <h6 class="text-white small mb-2 fw-bold">Follow Us</h6>
                        <a href="#" class="btn btn-sm btn-outline-light me-2 hover-lift" aria-label="Facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light me-2 hover-lift" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light me-2 hover-lift" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="btn btn-sm btn-outline-light hover-lift" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                    </div>
                </div>

                <!-- Product -->
                <div class="col-lg-2 col-md-6 col-6 mb-4 animate-fade-in-up delay-100">
                    <h6 class="text-white mb-3 fw-bold">Product</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ url('/features') }}" class="text-decoration-none text-light hover-primary">Features</a></li>
                        <li class="mb-2"><a href="{{ url('/pricing') }}" class="text-decoration-none text-light hover-primary">Pricing</a></li>
                        <li class="mb-2"><a href="http://demo.midastech.in" target="_blank" rel="noopener noreferrer" class="text-decoration-none text-light hover-primary">Live Demo</a></li>
                        <li class="mb-2"><a href="{{ url('/features/analytics-reports') }}" class="text-decoration-none text-light hover-primary">Analytics</a></li>
                        <li class="mb-2"><a href="{{ url('/features/whatsapp-integration') }}" class="text-decoration-none text-light hover-primary">Integrations</a></li>
                    </ul>
                </div>

                <!-- Features -->
                <div class="col-lg-2 col-md-6 col-6 mb-4 animate-fade-in-up delay-200">
                    <h6 class="text-white mb-3 fw-bold">Key Features</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ url('/features/customer-management') }}" class="text-decoration-none text-light hover-primary">Customers</a></li>
                        <li class="mb-2"><a href="{{ url('/features/policy-management') }}" class="text-decoration-none text-light hover-primary">Policies</a></li>
                        <li class="mb-2"><a href="{{ url('/features/claims-management') }}" class="text-decoration-none text-light hover-primary">Claims</a></li>
                        <li class="mb-2"><a href="{{ url('/features/quotation-system') }}" class="text-decoration-none text-light hover-primary">Quotations</a></li>
                        <li class="mb-2"><a href="{{ url('/features/whatsapp-integration') }}" class="text-decoration-none text-light hover-primary">WhatsApp</a></li>
                    </ul>
                </div>

                <!-- Company -->
                <div class="col-lg-2 col-md-6 col-6 mb-4 animate-fade-in-up delay-300">
                    <h6 class="text-white mb-3 fw-bold">Company</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ url('/about') }}" class="text-decoration-none text-light hover-primary">About Us</a></li>
                        <li class="mb-2"><a href="{{ url('/contact') }}" class="text-decoration-none text-light hover-primary">Contact Us</a></li>
                        <li class="mb-2"><a href="{{ url('/blog') }}" class="text-decoration-none text-light hover-primary">Blog & News</a></li>
                        <li class="mb-2"><a href="{{ url('/features/customer-portal') }}" class="text-decoration-none text-light hover-primary">Customer Portal</a></li>
                        <li class="mb-2"><a href="{{ url('/contact') }}#support" class="text-decoration-none text-light hover-primary">Support Center</a></li>
                    </ul>
                </div>

                <!-- Resources -->
                <div class="col-lg-2 col-md-6 col-6 mb-4 animate-fade-in-up delay-400">
                    <h6 class="text-white mb-3 fw-bold">Resources</h6>
                    <ul class="list-unstyled small">
                        <li class="mb-2"><a href="{{ url('/blog') }}" class="text-decoration-none text-light hover-primary">Knowledge Base</a></li>
                        <li class="mb-2"><a href="{{ url('/features') }}" class="text-decoration-none text-light hover-primary">User Guide</a></li>
                        <li class="mb-2"><a href="{{ url('/pricing') }}#faq" class="text-decoration-none text-light hover-primary">FAQ</a></li>
                        <li class="mb-2"><a href="{{ url('/privacy') }}" class="text-decoration-none text-light hover-primary">Privacy Policy</a></li>
                        <li class="mb-2"><a href="{{ url('/terms') }}" class="text-decoration-none text-light hover-primary">Terms of Service</a></li>
                    </ul>
                </div>
            </div>

            <!-- Trust Badges -->
            <div class="row mt-4 py-4 border-top border-secondary animate-fade-in-up delay-500">
                <div class="col-md-12 text-center">
                    <div class="d-flex flex-wrap justify-content-center align-items-center gap-4 small text-light">
                        <div class="hover-scale"><i class="fas fa-shield-alt text-success me-2"></i>SSL Secured</div>
                        <div class="hover-scale"><i class="fas fa-lock text-success me-2"></i>AES-256 Encrypted</div>
                        <div class="hover-scale"><i class="fas fa-check-circle text-success me-2"></i>GDPR Compliant</div>
                        <div class="hover-scale"><i class="fas fa-server text-success me-2"></i>99.9% Uptime</div>
                        <div class="hover-scale"><i class="fas fa-database text-success me-2"></i>Daily Backups</div>
                        <div class="hover-scale"><i class="fas fa-users-cog text-success me-2"></i>24/7 Support</div>
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <hr class="border-secondary mt-4">
            <div class="row py-3">
                <div class="col-md-6 small text-center text-md-start mb-2 mb-md-0">
                    &copy; {{ date('Y') }} Midas Portal by WebMonks Technologies. All rights reserved.
                </div>
                <div class="col-md-6 small text-center text-md-end">
                    Made in India <i class="fas fa-heart text-danger"></i> | Powered by WebMonks Technologies
                </div>
            </div>
        </div>
        <!-- Animated Background Elements for Footer -->
        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-5">
            <div class="position-absolute animate-float" style="top: 10%; left: 5%; width: 50px; height: 50px; background: rgba(255,255,255,0.1); border-radius: 50%;"></div>
            <div class="position-absolute animate-float delay-300" style="top: 60%; right: 10%; width: 40px; height: 40px; background: rgba(255,255,255,0.08); border-radius: 50%;"></div>
            <div class="position-absolute animate-float delay-500" style="bottom: 20%; left: 15%; width: 45px; height: 45px; background: rgba(255,255,255,0.06); border-radius: 50%;"></div>
        </div>
    </footer>

    <style>
        footer {
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
        }

        footer a.hover-primary:hover {
            color: var(--primary-color) !important;
            transition: color 0.3s ease;
            transform: translateX(3px);
        }

        footer .z-index-2 {
            z-index: 2;
        }

        footer .btn-outline-light {
            transition: all 0.3s ease;
        }

        footer .btn-outline-light:hover {
            background: var(--primary-color);
            border-color: var(--primary-color);
            transform: translateY(-2px);
        }
    </style>

    <!-- Bootstrap 5 JS (Deferred) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" defer></script>

    <!-- Cloudflare Turnstile (Async) -->
    <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

    <!-- Modern Animations JS (Deferred, Minified) -->
    <script src="{{ asset('js/modern-animations.min.js') }}" defer></script>

    @yield('scripts')
</body>
</html>
