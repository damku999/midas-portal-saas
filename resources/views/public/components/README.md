# Public Website Reusable Blade Components

This directory contains DRY (Don't Repeat Yourself) Blade components for the public website to eliminate code duplication and maintain consistency.

## Available Components

### 1. Hero Section (`hero.blade.php`)
Animated hero section with floating background elements, badge, title, description, and optional CTA buttons.

**Usage:**
```blade
@include('public.components.hero', [
    'badge' => 'About Midas Portal',
    'badgeIcon' => 'fas fa-building',
    'title' => 'Transforming Insurance Management',
    'description' => 'We\'re on a mission to empower insurance agencies across India',
    'showCta' => true,
    'ctaPrimary' => 'Get Started',
    'ctaPrimaryUrl' => url('/pricing'),
    'ctaSecondary' => 'Contact Us',
    'ctaSecondaryUrl' => url('/contact')
])
```

**Optional Props:**
- `containerClass` - Custom container class (default: 'py-5')
- `colClass` - Column class (default: 'col-lg-10')
- `alignClass` - Alignment class (default: 'text-center')
- `ctaPrimaryIcon`, `ctaSecondaryIcon` - Button icons
- `ctaPrimaryDataCta`, `ctaSecondaryDataCta` - Analytics tracking attributes

---

### 2. CTA Section (`cta-section.blade.php`)
Gradient call-to-action section with floating animations, perfect for conversion areas.

**Usage:**
```blade
@include('public.components.cta-section', [
    'title' => 'Ready to Transform Your Insurance Agency?',
    'description' => 'Join hundreds of successful agencies already using Midas Portal',
    'primaryText' => 'Start Free Trial',
    'primaryUrl' => url('/pricing'),
    'secondaryText' => 'Contact Sales',
    'secondaryUrl' => url('/contact'),
    'showNote' => true
])
```

**Optional Props:**
- `containerClass` - Custom container class
- `colClass` - Column class (default: 'col-lg-8')
- `primaryIcon`, `secondaryIcon` - Button icons
- `primaryDataCta`, `secondaryDataCta` - Analytics tracking
- `note` - Custom note text (default note available)

---

### 3. Feature Card (`feature-card.blade.php`)
Modern card with icon, title, description, and optional link.

**Usage:**
```blade
@include('public.components.feature-card', [
    'icon' => 'fas fa-shield-alt',
    'iconBg' => 'bg-primary',
    'title' => 'Industry Expertise',
    'description' => 'Built by insurance professionals who understand your challenges',
    'link' => url('/features'),
    'linkText' => 'Learn More',
    'delay' => 0.2
])
```

**Optional Props:**
- `cardClass` - Additional card classes
- `dataCta` - Analytics tracking attribute
- `delay` - Animation delay in seconds

---

### 4. Section Header (`section-header.blade.php`)
Consistent section header with optional badge, title, and description.

**Usage:**
```blade
@include('public.components.section-header', [
    'badge' => 'Our Features',
    'badgeIcon' => 'fas fa-star',
    'title' => 'Complete Insurance Management Solution',
    'description' => 'Everything you need to manage your insurance agency efficiently',
    'align' => 'center'
])
```

**Optional Props:**
- `headerClass` - Additional header classes
- `descClass` - Description classes

---

### 5. Stats Section (`stats-section.blade.php`)
Animated statistics section with counters.

**Usage:**
```blade
@include('public.components.stats-section', [
    'stats' => [
        ['count' => 500, 'label' => 'Active Agencies', 'icon' => 'fas fa-building'],
        ['count' => 50000, 'label' => 'Policies Managed', 'icon' => 'fas fa-file-alt'],
        ['count' => 99, 'label' => 'Customer Satisfaction', 'suffix' => '%']
    ],
    'bgClass' => 'gradient-primary',
    'textClass' => 'text-white'
])
```

**Optional Props:**
- `sectionClass` - Additional section classes
- `colSize` - Column size (auto-calculated by default)
- Individual stat props: `countClass`, `labelClass`, `suffix`

---

### 6. Icon Box (`icon-box.blade.php`)
Reusable icon container with customizable size and colors.

**Usage:**
```blade
@include('public.components.icon-box', [
    'icon' => 'fas fa-user',
    'bgClass' => 'bg-primary',
    'size' => '80px',
    'iconSize' => '2rem',
    'rounded' => '20px',
    'animate' => 'animate-pulse'
])
```

**Optional Props:**
- `boxClass` - Additional box classes
- All size/style props are optional with sensible defaults

---

### 7. Testimonial Card (`testimonial-card.blade.php`)
Card for displaying customer testimonials with rating and author info.

**Usage:**
```blade
@include('public.components.testimonial-card', [
    'quote' => 'Midas Portal has transformed how we manage our insurance agency',
    'author' => 'John Doe',
    'role' => 'CEO',
    'company' => 'ABC Insurance',
    'rating' => 5,
    'delay' => 0.2
])
```

**Optional Props:**
- `cardClass` - Additional card classes
- `rating` - Star rating (1-5)

---

### 8. FAQ Accordion (`faq-accordion.blade.php`)
Bootstrap accordion for FAQ sections with animations.

**Usage:**
```blade
@include('public.components.faq-accordion', [
    'accordionId' => 'pricingFAQ',
    'showFirst' => true,
    'faqs' => [
        [
            'question' => 'How quickly will I receive a response?',
            'answer' => 'We typically respond within 24 hours during business days.'
        ],
        [
            'question' => 'Can I schedule a demo?',
            'answer' => 'Yes! Contact us to schedule a personalized demo.'
        ]
    ]
])
```

**Optional Props:**
- `accordionId` - Unique ID (default: 'faqAccordion')
- `showFirst` - Show first item by default (default: true)

---

## Best Practices

1. **Always pass required props** - Components will break if required props are missing
2. **Use data-cta attributes** - For analytics tracking on all buttons/links
3. **Consistent delays** - Use multiples of 0.1s for staggered animations
4. **Icon classes** - Use Font Awesome 6.4.0 classes
5. **Color classes** - Use Bootstrap 5.3.0 or custom teal brand colors

## Benefits of Using Components

- **DRY Principle** - No repeated code across pages
- **Consistency** - Same look and feel everywhere
- **Maintainability** - Update once, applies everywhere
- **Rapid Development** - Build pages faster
- **Less Bugs** - Single source of truth for each pattern

## Migration Example

**Before (Repetitive):**
```blade
<section class="gradient-primary position-relative overflow-hidden py-5">
    <div class="position-absolute top-0 start-0 w-100 h-100 opacity-10">
        <!-- ... lots of repeated code ... -->
    </div>
    <div class="container py-5 position-relative z-index-2">
        <!-- ... more repeated code ... -->
    </div>
</section>
```

**After (Component):**
```blade
@include('public.components.cta-section', [
    'title' => 'Get Started',
    'description' => 'Join us today',
    'primaryText' => 'Sign Up',
    'primaryUrl' => url('/register')
])
```

## Next Steps

To fully implement DRY principles:
1. Refactor existing pages to use these components
2. Create additional components as patterns emerge
3. Document any new components added
4. Test all pages after refactoring
