{{--
    Reusable Icon Box Component

    Props:
    - $icon: Font Awesome icon class (required)
    - $bgClass: Background color class (optional, default: 'bg-primary')
    - $size: Icon box size (optional, default: '80px')
    - $iconSize: Icon font size (optional, default: '2rem')
    - $rounded: Border radius (optional, default: '20px')
    - $animate: Animation class (optional, default: '')
--}}

<div class="icon-box {{ $bgClass ?? 'bg-primary' }} bg-opacity-10 {{ $boxClass ?? '' }} {{ $animate ?? '' }}"
     style="width: {{ $size ?? '80px' }}; height: {{ $size ?? '80px' }}; border-radius: {{ $rounded ?? '20px' }}; font-size: {{ $iconSize ?? '2rem' }};">
    <i class="{{ $icon }} text-dark"></i>
    {{ $slot ?? '' }}
</div>
