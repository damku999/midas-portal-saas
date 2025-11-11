{{--
    Reusable Stats Section Component

    Props:
    - $stats: Array of stats with 'number', 'label', 'suffix', 'hasCounter', 'displayClass' keys (required)
    - $bgClass: Background class (optional, default: 'gradient-primary')
    - $textClass: Text color class (optional, default: 'text-white')
--}}

<section class="{{ $bgClass ?? 'section-modern gradient-primary' }} {{ $textClass ?? 'text-white' }} {{ $sectionClass ?? '' }}">
    <div class="container">
        <div class="row g-4 align-items-center text-center">
            @foreach($stats as $index => $stat)
            <div class="col-md-{{ isset($colSize) ? $colSize : (12 / count($stats)) }} scroll-reveal" style="animation-delay: {{ $index * 0.1 }}s;">
                @if($stat['hasCounter'] ?? true)
                    <div class="stat-number {{ $stat['displayClass'] ?? '' }}" data-count="{{ $stat['number'] }}">0</div>
                    <div class="h4 fw-bold mb-2">{{ $stat['suffix'] ?? '' }}</div>
                @else
                    <div class="h2 {{ $stat['displayClass'] ?? 'display-3' }} fw-bold mb-2">{{ $stat['number'] }}{{ $stat['suffix'] ?? '' }}</div>
                @endif
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
