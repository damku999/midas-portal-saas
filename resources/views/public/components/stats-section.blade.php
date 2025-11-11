{{--
    Reusable Stats Section Component

    Props:
    - $stats: Array of stats with 'count', 'label', 'icon' keys (required)
    - $bgClass: Background class (optional, default: 'gradient-primary')
    - $textClass: Text color class (optional, default: 'text-white')
--}}

<section class="{{ $bgClass ?? 'gradient-primary' }} {{ $textClass ?? 'text-white' }} py-5 {{ $sectionClass ?? '' }}">
    <div class="container">
        <div class="row g-4 align-items-center">
            @foreach($stats as $index => $stat)
            <div class="col-md-{{ isset($colSize) ? $colSize : (12 / count($stats)) }} text-center scroll-reveal" style="animation-delay: {{ $index * 0.1 }}s;">
                <div class="stat-number {{ $stat['countClass'] ?? '' }}" data-count="{{ $stat['count'] }}">0</div>
                <div class="h4 fw-bold mb-2">{{ $stat['suffix'] ?? '+' }}</div>
                <p class="mb-0 {{ $stat['labelClass'] ?? 'opacity-75' }}">
                    @if(isset($stat['icon']))
                    <i class="{{ $stat['icon'] }} me-2"></i>
                    @endif
                    {{ $stat['label'] }}
                </p>
            </div>
            @endforeach
        </div>
    </div>
</section>
