# Analytics Integration Guide

Complete guide for analytics integration on Midas Portal, including Google Analytics 4, Google Tag Manager, and Microsoft Clarity.

**Last Updated**: 2025-11-12

---

## Table of Contents
1. [Google Analytics 4](#google-analytics-4)
2. [Google Tag Manager](#google-tag-manager)
3. [Microsoft Clarity](#microsoft-clarity)
4. [Testing & Verification](#testing--verification)

---

## Google Analytics 4

### Configuration
**Measurement ID**: G-21PCW1WJXT
**Stream ID**: 9845847756
**Stream URL**: https://midastech.in

### Setup
**File**: `config/services.php`
```php
'google_analytics' => [
    'measurement_id' => env('GOOGLE_ANALYTICS_MEASUREMENT_ID', 'G-21PCW1WJXT'),
    'stream_id' => env('GOOGLE_ANALYTICS_STREAM_ID', '9845847756'),
    'enabled' => env('GOOGLE_ANALYTICS_ENABLED', true),
],
```

**Environment Variables** (`.env`):
```bash
GOOGLE_ANALYTICS_MEASUREMENT_ID=G-21PCW1WJXT
GOOGLE_ANALYTICS_STREAM_ID=9845847756
GOOGLE_ANALYTICS_ENABLED=true
```

### Implementation

**Public Website** (`resources/views/public/layout.blade.php`):
```blade
@if(config('services.google_analytics.enabled'))
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
```

**Admin Panel** (`resources/views/central/layout.blade.php`):
- Same as public, plus `user_id` tracking for individual admin users

### Tracked Data
- Page views across all layouts
- User sessions and engagement
- Traffic sources (organic, direct, referral, social)
- Device categories (desktop, mobile, tablet)
- User demographics and location
- Site speed and Core Web Vitals
- Admin user activity with unique IDs

---

## Google Tag Manager

### Configuration
**Container ID**: GTM-THNCHKWZ
**Environment**: Production

### Setup
**Environment Variables** (`.env`):
```bash
GOOGLE_TAG_MANAGER_ID=GTM-THNCHKWZ
GOOGLE_TAG_MANAGER_ENABLED=true
```

### Implementation

**Head Section**:
```blade
@if(config('services.google_tag_manager.enabled'))
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{{ config('services.google_tag_manager.container_id') }}');</script>
<!-- End Google Tag Manager -->
@endif
```

**Body Section**:
```blade
@if(config('services.google_tag_manager.enabled'))
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google_tag_manager.container_id') }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
@endif
```

### Benefits
- Single point of control for all marketing tags
- No code deployment needed for tag changes
- Built-in tag templates (GA4, Ads, Facebook Pixel)
- Version control and rollback capability
- Debug mode for testing

---

## Microsoft Clarity

### Configuration
**Project ID**: pf5h71s4of

### Setup
**Environment Variables** (`.env`):
```bash
MICROSOFT_CLARITY_ID=pf5h71s4of
MICROSOFT_CLARITY_ENABLED=true
```

### Implementation

**File**: `resources/views/public/layout.blade.php`
```blade
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
```

### Features
- **Session Recordings**: Watch real user sessions
- **Heatmaps**: Click, scroll, and attention heatmaps
- **Rage Clicks**: Identify frustration points
- **Dead Clicks**: Find non-functional elements
- **Quick Back**: Track rapid exits
- **JavaScript Errors**: Capture client-side errors
- **GDPR Compliant**: Automatic PII masking

### Dashboard Access
https://clarity.microsoft.com/projects/view/{{ project_id }}

---

## Testing & Verification

### 1. Real-Time Testing

**Google Analytics**:
1. Visit: https://analytics.google.com
2. Navigate to: Reports → Realtime → Overview
3. Browse your website
4. Verify page views appear in real-time

**Google Tag Manager**:
1. Visit site with GTM Preview mode enabled
2. Click "Preview" in GTM dashboard
3. Enter your website URL
4. Verify all tags fire correctly

**Microsoft Clarity**:
1. Visit: https://clarity.microsoft.com
2. Open your project
3. Check "Live" tab for active sessions

### 2. Browser DevTools Verification

```javascript
// Check GA4 data layer
console.log(window.dataLayer);

// Check GTM
console.log(window.google_tag_manager);

// Check Clarity
console.log(window.clarity);
```

### 3. Tag Assistant (Chrome Extension)

Install Google Tag Assistant:
1. Visit any page on your site
2. Click extension icon
3. Verify all tags fire correctly
4. Check for errors or warnings

### 4. Environment Control

**Development** (disable tracking):
```bash
GOOGLE_ANALYTICS_ENABLED=false
GOOGLE_TAG_MANAGER_ENABLED=false
MICROSOFT_CLARITY_ENABLED=false
```

**Production** (enable tracking):
```bash
GOOGLE_ANALYTICS_ENABLED=true
GOOGLE_TAG_MANAGER_ENABLED=true
MICROSOFT_CLARITY_ENABLED=true
```

---

## Privacy & Compliance

### GDPR Compliance
- ✅ GA4 automatically anonymizes IP addresses
- ✅ Clarity masks PII (emails, phone numbers) automatically
- ✅ Cookie flags: `SameSite=None;Secure`
- ⚠️ Recommended: Add cookie consent banner

### Data Retention
- **GA4**: 14 months (configurable)
- **Clarity**: 30 days for recordings, 1 year for aggregated data

### User Opt-Out
Implement cookie consent banner to allow users to opt-out of tracking.

---

## Key Reports & Dashboards

### Google Analytics
- **Realtime**: Active users and current pages
- **Acquisition**: Traffic sources and campaigns
- **Engagement**: Page views, events, conversions
- **Demographics**: Age, gender, interests, location
- **Technology**: Browsers, OS, screen resolutions

### Microsoft Clarity
- **Dashboard**: Overall session stats and recordings
- **Recordings**: Individual session playback
- **Heatmaps**: Aggregated user behavior patterns
- **Insights**: Automatic detection of issues

---

## Troubleshooting

| Issue | Solution |
|-------|----------|
| No data in GA4 | Verify `GOOGLE_ANALYTICS_ENABLED=true` in `.env` |
| GTM not loading | Check container ID is correct |
| Clarity not recording | Verify project ID, check browser console for errors |
| Ad blockers | Normal data loss of 10-30% expected |
| Duplicate tracking | Ensure scripts only in layout files |

---

## Summary

✅ **Analytics Stack Complete**
- **Google Analytics 4**: Comprehensive analytics and reporting
- **Google Tag Manager**: Centralized tag management
- **Microsoft Clarity**: Session recordings and heatmaps

✅ **Implemented Across**
- Public website
- Central admin panel (with user_id tracking)
- All layouts and pages

✅ **Privacy Compliant**
- IP anonymization enabled
- PII masking active
- Secure cookie flags
- GDPR-ready configuration

**Dashboard URLs**:
- GA4: https://analytics.google.com
- GTM: https://tagmanager.google.com
- Clarity: https://clarity.microsoft.com
