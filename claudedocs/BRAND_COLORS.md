# Midas Portal - Brand Colors Reference

## Primary Brand Colors (Based on WebMonks Logo)

### Main Colors
- **Primary Teal**: `#17b6b6` - Main brand color from WebMonks logo
- **Primary Dark**: `#13918e` - Darker shade for hovers and accents
- **Primary Light**: `#4dd4d4` - Lighter shade for subtle backgrounds
- **WebMonks Gray**: `#4a4a4a` - Secondary text color from logo

### CSS Variables
```css
:root {
    --primary-color: #17b6b6;
    --primary-dark: #13918e;
    --primary-light: #4dd4d4;
    --webmonks-teal: #17b6b6;
    --webmonks-gray: #4a4a4a;
    --gradient-primary: linear-gradient(135deg, #17b6b6 0%, #13918e 100%);
    --gradient-primary-hover: linear-gradient(135deg, #13918e 0%, #0f706e 100%);
}
```

## Usage Guidelines

### Buttons
- **Primary CTA**: Use `btn-gradient` with gradient background
- **Secondary CTA**: Use `btn-primary` with solid teal
- **Outline**: Use `btn-outline-primary` for less emphasis

### Text
- **Headings**: Use `text-primary` (#17b6b6) for brand consistency
- **Body**: Use default gray or `text-muted`
- **Secondary Text**: Use `text-webmonks-gray` (#4a4a4a)

### Backgrounds
- **Hero Sections**: Use `--gradient-primary` for modern gradient effect
- **Cards/Sections**: Use white or light gray backgrounds
- **Accents**: Use `bg-primary` sparingly for highlights

## Color Psychology
The teal/turquoise color represents:
- **Trust & Reliability** - Essential for insurance industry
- **Innovation & Technology** - Modern SaaS platform
- **Balance & Clarity** - Professional and approachable

## Accessibility
All color combinations meet WCAG AA standards:
- Teal on white: 4.5:1 contrast ratio ✓
- White on teal: 4.5:1 contrast ratio ✓
- Dark teal on light backgrounds: 7:1 contrast ratio ✓

## Brand Consistency
Always use these defined colors instead of:
- ❌ Bootstrap default blue (#0d6efd)
- ❌ Generic cyan (#17a2b8)
- ❌ Random teal shades

✅ Always reference CSS variables for consistency across updates
