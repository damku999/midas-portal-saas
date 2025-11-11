{{--
    Reusable Testimonial Card Component

    Props:
    - $quote: Testimonial quote text (required)
    - $author: Author name (required)
    - $role: Author role/title (optional)
    - $company: Company name (optional)
    - $rating: Star rating out of 5 (optional)
    - $delay: Animation delay (optional, default: '0')
--}}

<div class="modern-card modern-card-gradient scroll-reveal hover-lift {{ $cardClass ?? '' }}" style="animation-delay: {{ $delay ?? '0' }}s; position: relative;">
    @if(isset($rating))
    <div class="mb-3">
        @for($i = 1; $i <= 5; $i++)
            <i class="fas fa-star {{ $i <= $rating ? 'text-warning' : 'text-muted' }}"></i>
        @endfor
    </div>
    @endif

    <p class="text-muted mb-4">"{{ $quote }}"</p>

    <div class="d-flex align-items-center">
        <div class="icon-box bg-primary bg-opacity-10 me-3" style="width: 50px; height: 50px; font-size: 1.5rem;">
            <i class="fas fa-user text-dark"></i>
        </div>
        <div>
            <h6 class="fw-bold mb-0">{{ $author }}</h6>
            @if(isset($role))
            <small class="text-muted">
                {{ $role }}{{ isset($company) ? ', ' . $company : '' }}
            </small>
            @endif
        </div>
    </div>

    {{ $slot ?? '' }}
</div>
