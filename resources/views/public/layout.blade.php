<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Midas Portal - Modern Multi-Tenant Insurance Management SaaS Platform">
    <title>@yield('title', 'Midas Portal - Insurance Management SaaS')</title>

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/jpg" href="{{ asset('images/logo-icon@2000x.png') }}" />

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --primary-color: #17a2b8;
            --secondary-color: #424242;
            --success-color: #28a745;
            --webmonks-teal: #17a2b8;
            --webmonks-teal-light: #5fd0e3;
            --gradient-primary: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }

        .navbar-brand img {
            height: 45px;
            width: auto;
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

    @yield('styles')
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="{{ url('/') }}">
                <img src="{{ asset('images/logo.png') }}" alt="WebMonks Technologies" class="d-inline-block align-text-top">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/features') }}">Features</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/pricing') }}">Pricing</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/about') }}">About</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/contact') }}">Contact</a>
                    </li>
                    <li class="nav-item ms-3">
                        <a class="btn btn-outline-primary btn-sm" href="{{ route('central.login') }}">Admin Login</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    @yield('content')

    <!-- Footer -->
    <footer>
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5 class="text-white mb-3">
                        <img src="{{ asset('images/logo.png') }}" alt="WebMonks Technologies" style="height: 40px; filter: brightness(0) invert(1);">
                    </h5>
                    <p class="small">Transform your insurance business with cutting-edge technology. Built by WebMonks Technologies.</p>
                    <p class="small mt-3">
                        <i class="fas fa-map-marker-alt me-2"></i> C243, Second Floor, SoBo Center, Gala Gymkhana Road, South Bopal Ahmedabad - 380058<br>
                        <i class="fas fa-phone me-2"></i> +91 80000 71413<br>
                        <i class="fas fa-envelope me-2"></i> Info@midastech.in
                    </p>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="text-white mb-3">Product</h6>
                    <ul class="list-unstyled small">
                        <li><a href="{{ url('/features') }}" class="text-decoration-none text-light">Features</a></li>
                        <li><a href="{{ url('/pricing') }}" class="text-decoration-none text-light">Pricing</a></li>
                        <li><a href="#" class="text-decoration-none text-light">Demo</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="text-white mb-3">Company</h6>
                    <ul class="list-unstyled small">
                        <li><a href="{{ url('/about') }}" class="text-decoration-none text-light">About</a></li>
                        <li><a href="{{ url('/contact') }}" class="text-decoration-none text-light">Contact</a></li>
                        <li><a href="#" class="text-decoration-none text-light">Blog</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="text-white mb-3">Support</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="text-decoration-none text-light">Help Center</a></li>
                        <li><a href="#" class="text-decoration-none text-light">Documentation</a></li>
                        <li><a href="#" class="text-decoration-none text-light">API</a></li>
                    </ul>
                </div>
                <div class="col-md-2 mb-4">
                    <h6 class="text-white mb-3">Legal</h6>
                    <ul class="list-unstyled small">
                        <li><a href="#" class="text-decoration-none text-light">Privacy</a></li>
                        <li><a href="#" class="text-decoration-none text-light">Terms</a></li>
                        <li><a href="#" class="text-decoration-none text-light">Security</a></li>
                    </ul>
                </div>
            </div>
            <hr class="border-secondary">
            <div class="row">
                <div class="col-md-6 small">
                    &copy; {{ date('Y') }} Midas Portal. All rights reserved.
                </div>
                <div class="col-md-6 text-md-end small">
                    Built with <i class="fas fa-heart text-danger"></i> by WebMonks Technologies
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Cloudflare Turnstile - Temporarily disabled for testing -->
    {{-- <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script> --}}

    @yield('scripts')
</body>
</html>
