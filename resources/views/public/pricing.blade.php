@extends('public.layout')

@section('title', 'Pricing - Midas Portal')

@section('content')
<section class="py-5">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold">Simple, Transparent Pricing</h1>
            <p class="lead text-muted">Choose the perfect plan for your insurance agency</p>
        </div>

        <div class="row g-4 justify-content-center">
            @forelse($plans as $plan)
            <div class="col-lg-4">
                <div class="pricing-card {{ $plan->slug === 'professional' ? 'featured' : '' }}">
                    @if($plan->slug === 'professional')
                    <div class="badge bg-primary mb-3">Most Popular</div>
                    @endif

                    <h3 class="fw-bold">{{ $plan->name }}</h3>
                    <p class="text-muted">{{ $plan->description }}</p>

                    <div class="my-4">
                        <h2 class="display-4 fw-bold">₹{{ number_format($plan->price, 0) }}</h2>
                        <p class="text-muted">/month</p>
                    </div>

                    @php
                        $features = json_decode($plan->features, true) ?? [];
                    @endphp

                    <ul class="list-unstyled mb-4">
                        @foreach($features as $feature)
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> {{ $feature }}</li>
                        @endforeach
                    </ul>

                    <a href="{{ url('/contact') }}" class="btn {{ $plan->slug === 'professional' ? 'btn-gradient' : 'btn-outline-primary' }} w-100">
                        Get Started
                    </a>
                </div>
            </div>
            @empty
            <!-- Fallback if plans not available -->
            <div class="col-lg-4">
                <div class="pricing-card">
                    <h3 class="fw-bold">Starter</h3>
                    <p class="text-muted">Perfect for small agencies</p>
                    <div class="my-4">
                        <h2 class="display-4 fw-bold">₹2,999</h2>
                        <p class="text-muted">/month</p>
                    </div>
                    <ul class="list-unstyled mb-4">
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Up to 3 users</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 100 customers</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> 5GB storage</li>
                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Basic support</li>
                    </ul>
                    <a href="{{ url('/contact') }}" class="btn btn-outline-primary w-100">Get Started</a>
                </div>
            </div>
            @endforelse
        </div>
    </div>
</section>
@endsection
