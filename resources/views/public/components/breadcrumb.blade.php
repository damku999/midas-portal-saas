{{--
    Reusable Breadcrumb Component

    Props:
    - $items: Array of breadcrumb items with 'label' and optional 'url' keys (required)
    - $bgClass: Background class (optional, default: 'bg-light')
--}}

<nav aria-label="breadcrumb" class="{{ $bgClass ?? 'bg-light' }} py-3 animate-fade-in">
    <div class="container">
        <ol class="breadcrumb mb-0">
            @foreach($items as $index => $item)
                @if($loop->last)
                    <li class="breadcrumb-item active" aria-current="page">{{ $item['label'] }}</li>
                @else
                    <li class="breadcrumb-item">
                        <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    </li>
                @endif
            @endforeach
        </ol>
    </div>
</nav>
