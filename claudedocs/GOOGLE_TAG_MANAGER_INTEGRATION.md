# Google Tag Manager (GTM) Integration

**Implementation Date**: 2025-11-12
**GTM Container ID**: `GTM-MKQWQXQV`
**Status**: ✅ Fully Integrated Across All Portal Layouts

---

## Table of Contents

1. [Overview](#overview)
2. [What is Google Tag Manager?](#what-is-google-tag-manager)
3. [GTM vs Google Analytics 4](#gtm-vs-google-analytics-4)
4. [Implementation Details](#implementation-details)
5. [Configuration](#configuration)
6. [Code Integration](#code-integration)
7. [DataLayer Integration](#datalayer-integration)
8. [Testing & Verification](#testing--verification)
9. [Tag Management](#tag-management)
10. [Common Tags to Configure](#common-tags-to-configure)
11. [Best Practices](#best-practices)
12. [Troubleshooting](#troubleshooting)

---

## Overview

Google Tag Manager (GTM) is a tag management system that allows you to quickly deploy and manage marketing/analytics tags without modifying code. This implementation covers all 5 portal layouts in the Midas Portal multi-tenant insurance management system.

**Benefits**:
- 🚀 Deploy tracking tags without code changes
- 🎯 Centralized tag management in GTM dashboard
- 📊 Event tracking and custom triggers
- 🔄 Version control and rollback for tags
- 👥 Multi-user collaboration with permissions
- 🧪 Preview and debug mode before publishing

**Current Analytics Stack**:
- **Google Tag Manager** (GTM-MKQWQXQV) - Tag management container
- **Google Analytics 4** (G-21PCW1WJXT) - Integrated via direct code AND can be managed via GTM
- **Microsoft Clarity** (u4tcfro0dt) - Session recordings and heatmaps

---

## What is Google Tag Manager?

Google Tag Manager is a **container** that holds and manages all your tracking codes (called "tags"). Instead of adding multiple tracking scripts directly to your website, you add GTM once, then configure all other tags through the GTM interface.

**Key Components**:

1. **Tags**: Code snippets that send data (e.g., GA4 pageview, Facebook Pixel event)
2. **Triggers**: Conditions that determine when tags fire (e.g., page view, button click, form submit)
3. **Variables**: Dynamic values used in tags and triggers (e.g., page URL, user ID, click text)
4. **Data Layer**: JavaScript object that passes information from website to GTM

**Real-World Example**:
```
Without GTM: Add Facebook Pixel → edit 5 layout files → test → deploy
With GTM: Add Facebook Pixel tag in GTM dashboard → test in preview → publish (2 minutes)
```

---

## GTM vs Google Analytics 4

Both tools are from Google but serve different purposes:

| Feature | Google Tag Manager | Google Analytics 4 |
|---------|-------------------|-------------------|
| **Purpose** | Tag management system | Analytics tracking platform |
| **What it does** | Container for all tracking codes | Tracks user behavior and generates reports |
| **Installation** | One-time code installation | Direct code OR via GTM |
| **Updates** | Change tags via GTM dashboard | Change tracking via code updates |
| **Best for** | Managing multiple tracking tools | Detailed analytics and reporting |

**Current Setup**:
- ✅ GA4 installed directly via code (resources/views/common/head.blade.php)
- ✅ GTM installed as container (all 5 layouts)
- 💡 **Recommendation**: Keep both. GA4 direct code ensures baseline tracking even if GTM fails. Use GTM for additional tags (Facebook, LinkedIn, etc.)

**Why Both?**:
- **Redundancy**: If GTM has issues, GA4 still tracks via direct code
- **Speed**: Direct GA4 loads faster than GTM-managed GA4
- **Flexibility**: Use GTM for marketing tags, direct code for critical analytics

---

## Implementation Details

GTM is integrated across **all 5 portal layouts** with Google's official two-part code:

### 1. Public Website
**File**: `resources/views/public/layout.blade.php`
- **Head Script**: Lines 4-12 (as high in `<head>` as possible)
- **Body Noscript**: Lines 315-320 (immediately after `<body>` tag)
- **Covers**: Home, Features, Pricing, Blog, Contact, About, Privacy, Terms, Security pages

### 2. Central Admin Panel
**File**: `resources/views/central/layout.blade.php`
- **Head Script**: Lines 4-12
- **Body Noscript**: Lines 187-192
- **Covers**: Central admin dashboard, tenant management, plan management, blog post management, testimonials

### 3. Tenant Admin Portal
**Files**:
- `resources/views/common/head.blade.php` (head script, lines 2-10)
- `resources/views/layouts/app.blade.php` (body noscript, lines 8-13)
- **Covers**: 124+ tenant admin pages (dashboard, customers, policies, claims, quotations, leads, commissions, reports, staff, master data)

### 4. Customer Portal
**Files**:
- `resources/views/common/customer-head.blade.php` (head script, lines 2-10)
- `resources/views/layouts/customer.blade.php` (body noscript, lines 7-12)
- **Covers**: All customer portal pages (customer dashboard, policies, claims, quotations, profile, family members)

### 5. Coverage Summary

| Portal Section | Layout Files | GTM Coverage |
|---------------|--------------|--------------|
| Public Website | public/layout.blade.php | ✅ Complete |
| Central Admin | central/layout.blade.php | ✅ Complete |
| Tenant Admin | common/head.blade.php + layouts/app.blade.php | ✅ Complete (124+ pages) |
| Customer Portal | common/customer-head.blade.php + layouts/customer.blade.php | ✅ Complete |
| **Total Coverage** | **5 layouts** | **100% of portal** |

---

## Configuration

### Environment Variables

**File**: `.env.example` (lines 74-76)

```bash
# Google Tag Manager
GOOGLE_TAG_MANAGER_ID=GTM-MKQWQXQV
GOOGLE_TAG_MANAGER_ENABLED=true
```

**To Enable in Production**:

1. Copy variables to actual `.env` file:
```bash
GOOGLE_TAG_MANAGER_ID=GTM-MKQWQXQV
GOOGLE_TAG_MANAGER_ENABLED=true
```

2. Clear Laravel config cache:
```bash
php artisan config:clear
php artisan config:cache
```

3. Verify GTM loads on any page by viewing page source and searching for "GTM-MKQWQXQV"

### Laravel Service Configuration

**File**: `config/services.php` (lines 61-64)

```php
'google_tag_manager' => [
    'container_id' => env('GOOGLE_TAG_MANAGER_ID', 'GTM-MKQWQXQV'),
    'enabled' => env('GOOGLE_TAG_MANAGER_ENABLED', true),
],
```

**Fallback Strategy**: If environment variables are missing, defaults to:
- Container ID: `GTM-MKQWQXQV`
- Enabled: `true`

**To Disable GTM** (e.g., in development):
```bash
# In .env file
GOOGLE_TAG_MANAGER_ENABLED=false
```

---

## Code Integration

### Part 1: Head Script (JavaScript)

**Placement**: As high in the `<head>` element as possible (lines 2-10 in head files)

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

**What This Does**:
1. Checks if GTM is enabled via Laravel config
2. Creates `dataLayer` array for event tracking
3. Asynchronously loads GTM container script
4. Injects container ID from Laravel configuration

### Part 2: Body Noscript (Fallback)

**Placement**: Immediately after the opening `<body>` tag

```blade
@if(config('services.google_tag_manager.enabled'))
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={{ config('services.google_tag_manager.container_id') }}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
@endif
```

**What This Does**:
- Provides fallback tracking for browsers with JavaScript disabled
- Required by Google for complete GTM implementation
- Hidden iframe (0x0 pixels, display:none)

**Why Two Parts?**:
- **JavaScript version**: Full functionality for 99%+ of users
- **Noscript version**: Ensures tracking even when JS is disabled (rare but required for compliance)

---

## DataLayer Integration

The `dataLayer` is a JavaScript array that passes information from your website to GTM. It's automatically initialized by the GTM code.

### Current DataLayer Usage

**Automatic Variables Available**:
- Page URL, page path, page hostname
- Referrer URL
- Click element, click text, click URL
- Form element, form classes, form ID
- Scroll depth percentage
- Video title, duration, current time

### Adding Custom DataLayer Events

**Example 1: Track Policy Purchase**

```javascript
// In your Blade template or JavaScript file
dataLayer.push({
    'event': 'policy_purchased',
    'policy_type': 'Health Insurance',
    'policy_value': 50000,
    'customer_id': '{{ auth("customer")->id() }}',
    'tenant_id': '{{ tenant("id") }}'
});
```

**Example 2: Track Lead Conversion**

```javascript
dataLayer.push({
    'event': 'lead_converted',
    'lead_source': 'Website Contact Form',
    'policy_category': 'Motor Insurance',
    'estimated_value': 25000
});
```

**Example 3: Track User Login**

```javascript
// After successful login
dataLayer.push({
    'event': 'user_login',
    'user_type': 'customer', // or 'admin', 'staff'
    'tenant_id': '{{ tenant("id") }}',
    'login_method': 'email' // or 'social', '2fa'
});
```

### Accessing DataLayer in GTM

1. Go to GTM dashboard → Variables
2. Create new "Data Layer Variable"
3. Set Variable Name: e.g., `policy_type`
4. Use in triggers/tags to segment tracking

**Example Trigger**:
- Trigger Type: Custom Event
- Event Name: `policy_purchased`
- Condition: `policy_type` equals "Health Insurance"

---

## Testing & Verification

### 1. Check GTM Installation

**Method 1: View Page Source**
```bash
curl -s http://midastech.testing.in:8085/ | grep -i "googletagmanager"
```

Should return GTM script tags with container ID `GTM-MKQWQXQV`.

**Method 2: Browser DevTools**
1. Open any portal page
2. Right-click → Inspect → View Page Source
3. Search for "GTM-MKQWQXQV" (should appear twice: head script + noscript)

**Method 3: Network Tab**
1. Open DevTools → Network tab
2. Reload page
3. Filter by "gtm" - should see request to `googletagmanager.com/gtm.js?id=GTM-MKQWQXQV`

### 2. GTM Preview Mode

**Steps**:
1. Go to [Google Tag Manager](https://tagmanager.google.com/)
2. Select container `GTM-MKQWQXQV`
3. Click "Preview" button (top right)
4. Enter your website URL: `http://midastech.testing.in:8085/`
5. Click "Connect"

**What You'll See**:
- Tag Assistant window showing all fired tags
- Events timeline (Page View, Clicks, Form Submits)
- Variables and their values
- Tags that fired vs didn't fire

### 3. Verify DataLayer

**Browser Console Method**:
```javascript
// Open browser console on any page
console.log(dataLayer);
```

Should show array with GTM initialization event:
```javascript
[{
    "gtm.start": 1699876543210,
    "event": "gtm.js"
}]
```

### 4. Google Tag Assistant (Chrome Extension)

1. Install [Tag Assistant](https://chrome.google.com/webstore/detail/tag-assistant-legacy-by-g/kejbdjndbnbjgmefkgdddjlbokphdefk)
2. Visit any portal page
3. Click Tag Assistant icon
4. Should show "Google Tag Manager" with container ID `GTM-MKQWQXQV`

### 5. Test All Layouts

**Quick Test Script**:
```bash
# Test public website
curl -s http://midastech.testing.in:8085/ | grep -o "GTM-[A-Z0-9]*" | head -1

# Test central admin (requires auth)
# Login to central admin → check page source for GTM-MKQWQXQV

# Test tenant admin (requires tenant context)
# Login to tenant → check any admin page source

# Test customer portal (requires customer auth)
# Login as customer → check page source
```

---

## Tag Management

### Accessing GTM Dashboard

1. Go to [Google Tag Manager](https://tagmanager.google.com/)
2. Login with account that has access to container `GTM-MKQWQXQV`
3. Select container from list

### Creating a New Tag

**Example: Add Facebook Pixel**

1. **Create Tag**:
   - GTM Dashboard → Tags → New
   - Tag Name: "Facebook Pixel - All Pages"
   - Tag Type: Custom HTML
   - HTML: *[Paste Facebook Pixel code]*

2. **Set Trigger**:
   - Triggering: All Pages (Page View)
   - OR create custom trigger (e.g., "Product Pages Only")

3. **Test in Preview**:
   - Click "Preview" → Connect to website
   - Verify tag fires on correct pages
   - Check Tag Assistant for pixel events

4. **Publish**:
   - Click "Submit" → Add version name/description
   - Publish changes

### Built-in Tags Available

| Tag Type | Use Case | Example |
|----------|----------|---------|
| Google Analytics 4 | Pageviews, events | Track user behavior |
| Google Ads Conversion | Conversion tracking | Track policy purchases |
| Google Ads Remarketing | Retargeting | Build remarketing audiences |
| Floodlight Counter | Campaign tracking | Track ad campaign performance |
| Custom HTML | Any custom code | Facebook Pixel, LinkedIn Insight |
| Custom Image | Tracking pixels | Simple pixel-based tracking |

### Creating Triggers

**Common Trigger Types**:

1. **Page View Triggers**:
   - All Pages
   - Page URL contains "pricing"
   - Page Path matches "/features/*"

2. **Click Triggers**:
   - All Elements
   - Just Links
   - Click Text contains "Get Started"

3. **Form Submission**:
   - All Forms
   - Form ID equals "contact-form"

4. **Custom Events**:
   - Event Name: "policy_purchased"
   - DataLayer variable conditions

5. **Scroll Depth**:
   - Vertical Scroll Depth: 25%, 50%, 75%, 90%

6. **Video**:
   - YouTube video (start, progress, complete)

### Creating Variables

**Built-in Variables** (enable in Variables → Configure):
- Page URL, Path, Hostname
- Referrer
- Click Element, Text, Classes, ID
- Form Element, Classes, ID

**Custom Variables**:

1. **Data Layer Variable**:
   - Name: "Tenant ID"
   - Data Layer Variable Name: `tenant_id`

2. **JavaScript Variable**:
   - Name: "User Type"
   - Global Variable Name: `userType`

3. **URL Variable**:
   - Component Type: Query
   - Query Key: `utm_source`

---

## Common Tags to Configure

### 1. Facebook Pixel

**Setup**:
1. GTM → Tags → New → Custom HTML
2. Paste Facebook Pixel base code
3. Trigger: All Pages
4. Create additional tags for events (Purchase, Lead, ViewContent)

**Event Example**:
```html
<script>
fbq('track', 'Purchase', {
    value: {{policy_value}},
    currency: 'INR'
});
</script>
```

### 2. LinkedIn Insight Tag

**Setup**:
1. GTM → Tags → New → Custom HTML
2. Paste LinkedIn Insight Tag code
3. Trigger: All Pages
4. Create conversion tags for specific actions

### 3. Microsoft Ads UET Tag

**Setup**:
1. GTM → Tags → New → Custom HTML
2. Paste UET tracking code
3. Trigger: All Pages
4. Set up conversion goals in Microsoft Ads

### 4. Hotjar

**Setup**:
1. GTM → Tags → New → Custom HTML
2. Paste Hotjar tracking code
3. Trigger: All Pages (or specific sections)

**Note**: Midas Portal already has Microsoft Clarity (similar to Hotjar). Consider if both are needed.

### 5. Google Ads Conversion Tracking

**Setup**:
1. GTM → Tags → New → Google Ads Conversion Tracking
2. Enter Conversion ID and Label
3. Trigger: Custom event (e.g., "policy_purchased")
4. Set conversion value from dataLayer

---

## Best Practices

### 1. Tag Organization

✅ **DO**:
- Use descriptive tag names: "Facebook Pixel - Purchase Event" not "FB Tag 3"
- Group tags with naming convention: "GA4 - Event - Button Click"
- Use folders for organization (e.g., "Analytics", "Advertising", "Conversion Tracking")

❌ **DON'T**:
- Create duplicate tags for same functionality
- Use vague names like "Tag 1", "New Tag", "Test"

### 2. Trigger Strategy

✅ **DO**:
- Reuse triggers across similar tags
- Create specific triggers (e.g., "Homepage Only" vs firing all tags on all pages)
- Test triggers in Preview mode before publishing

❌ **DON'T**:
- Fire all tags on all pages (page load performance impact)
- Create separate triggers for same conditions

### 3. Version Control

✅ **DO**:
- Add descriptive version names when publishing
- Use workspace feature for team collaboration
- Test in Preview before publishing to production

❌ **DON'T**:
- Publish directly without testing
- Use generic version names like "Update 1", "Changes"

### 4. Performance Optimization

✅ **DO**:
- Use async/defer for custom HTML tags
- Limit number of tags firing on page load
- Use trigger exceptions to prevent unnecessary fires

❌ **DON'T**:
- Add synchronous heavy scripts in custom HTML
- Fire complex tags on every page view

### 5. Data Privacy

✅ **DO**:
- Respect DNT (Do Not Track) browser settings
- Use consent management for GDPR compliance
- Anonymize IP addresses where required

❌ **DON'T**:
- Send PII (personally identifiable information) via tags
- Track users without proper consent

### 6. Testing Workflow

**Recommended Process**:
1. Create/modify tag in workspace
2. Test in Preview mode on staging environment
3. Verify tag fires correctly in Tag Assistant
4. Submit for review (if team workflow)
5. Publish to production with descriptive version name
6. Monitor for 24-48 hours
7. Check analytics reports for data accuracy

---

## Troubleshooting

### Problem 1: GTM Not Loading

**Symptoms**: No GTM requests in Network tab, container not found in Tag Assistant

**Diagnosis**:
```bash
# Check if GTM is enabled
grep "GOOGLE_TAG_MANAGER_ENABLED" .env

# Check if container ID is correct
grep "GOOGLE_TAG_MANAGER_ID" .env

# View compiled config
php artisan config:show services.google_tag_manager
```

**Solutions**:
1. Verify `.env` file has correct values
2. Clear Laravel config cache: `php artisan config:clear`
3. Check if `config('services.google_tag_manager.enabled')` returns `true`
4. View page source - search for "GTM-MKQWQXQV" (should appear in head and body)

### Problem 2: Tags Not Firing

**Symptoms**: GTM loads but specific tags don't fire in Preview mode

**Diagnosis**:
1. Open GTM Preview mode
2. Navigate to page where tag should fire
3. Check "Tags Not Fired" section
4. Click tag to see why it didn't fire

**Common Causes**:
- Trigger conditions not met (e.g., URL doesn't match)
- Trigger set to fire on wrong event type
- Tag paused or has firing exceptions
- Variable value doesn't match trigger condition

**Solutions**:
1. Verify trigger conditions in GTM dashboard
2. Check variable values in Preview mode → Variables tab
3. Test trigger in Preview → Click "Debug" on trigger
4. Simplify trigger conditions for testing (e.g., "All Pages" trigger)

### Problem 3: DataLayer Not Updating

**Symptoms**: Custom dataLayer.push() not showing in Preview mode

**Diagnosis**:
```javascript
// Browser console on page with dataLayer push
console.log(dataLayer);
```

**Common Causes**:
- DataLayer push happens before GTM loads
- Syntax error in dataLayer.push() code
- Variable name mismatch between push and GTM variable

**Solutions**:
1. Ensure dataLayer push happens after GTM head script
2. Verify JavaScript syntax (no typos, proper JSON format)
3. Check browser console for JavaScript errors
4. Use GTM Preview → Variables → Data Layer to inspect values

### Problem 4: Duplicate Tracking

**Symptoms**: Events tracked twice (once by GTM, once by direct code)

**Example**: GA4 pageview fires from both:
- Direct GA4 code in `common/head.blade.php`
- GA4 tag configured in GTM

**Solutions**:
1. **Keep both** (recommended for redundancy): Different purposes
   - Direct code: Critical baseline tracking
   - GTM tag: Additional flexibility and tag management
2. **Choose one**:
   - Remove direct GA4 code, use only GTM tag OR
   - Disable GA4 tag in GTM, keep only direct code

**Current Setup**: Both are active (intentional for redundancy)

### Problem 5: Preview Mode Won't Connect

**Symptoms**: "Waiting for connection" message when trying to connect Preview mode

**Solutions**:
1. Disable browser extensions (especially ad blockers, privacy tools)
2. Allow third-party cookies for tagmanager.google.com
3. Try incognito/private browsing mode
4. Check if website is accessible from GTM servers (not localhost without tunnel)
5. For local development, use ngrok or similar tunneling tool

### Problem 6: Noscript Tag Not Working

**Symptoms**: Tracking fails for users with JavaScript disabled

**Diagnosis**:
1. Disable JavaScript in browser
2. Visit page
3. Check Network tab for request to `googletagmanager.com/ns.html`

**Solutions**:
1. Verify noscript tag is immediately after `<body>` tag
2. Check container ID in noscript matches head script
3. Ensure `@if(config('services.google_tag_manager.enabled'))` wraps both head and noscript

### Problem 7: Container ID Not Updating

**Symptoms**: Wrong container ID loading after updating .env

**Diagnosis**:
```bash
# Check cached config
php artisan config:show services.google_tag_manager

# Check actual .env value
grep "GOOGLE_TAG_MANAGER_ID" .env
```

**Solutions**:
```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Rebuild config cache
php artisan config:cache
```

---

## Additional Resources

### Official Documentation
- [Google Tag Manager Help Center](https://support.google.com/tagmanager)
- [GTM Developer Guide](https://developers.google.com/tag-platform/tag-manager)
- [DataLayer Documentation](https://developers.google.com/tag-platform/tag-manager/datalayer)

### GTM Dashboard
- [Access GTM Container](https://tagmanager.google.com/): Container ID `GTM-MKQWQXQV`

### Analytics Stack Documentation
- `claudedocs/GOOGLE_ANALYTICS_INTEGRATION.md` - GA4 implementation details
- `claudedocs/MICROSOFT_CLARITY_INTEGRATION.md` - Clarity session recordings
- `claudedocs/SEO_SITEMAP_IMPLEMENTATION.md` - SEO and sitemap optimization

### Video Tutorials
- [GTM Fundamentals Course (Google)](https://analytics.google.com/analytics/academy/course/5)
- [Measure School YouTube Channel](https://www.youtube.com/c/MeasureSchool) - GTM tutorials

---

## Implementation Checklist

Use this checklist to verify GTM integration is complete:

### Configuration
- ✅ GTM container ID added to `.env.example` (GTM-MKQWQXQV)
- ⏳ GTM container ID added to actual `.env` file (user responsibility)
- ✅ GTM config added to `config/services.php`
- ✅ Enable/disable toggle configured via environment variable

### Code Integration
- ✅ Head script added to `resources/views/public/layout.blade.php`
- ✅ Head script added to `resources/views/central/layout.blade.php`
- ✅ Head script added to `resources/views/common/head.blade.php` (tenant admin)
- ✅ Head script added to `resources/views/common/customer-head.blade.php` (customer portal)
- ✅ Noscript tag added to `resources/views/public/layout.blade.php`
- ✅ Noscript tag added to `resources/views/central/layout.blade.php`
- ✅ Noscript tag added to `resources/views/layouts/app.blade.php` (tenant admin)
- ✅ Noscript tag added to `resources/views/layouts/customer.blade.php` (customer portal)

### Testing
- ⏳ Verify GTM loads in browser DevTools Network tab
- ⏳ Test GTM Preview mode connection
- ⏳ Verify dataLayer initialization in browser console
- ⏳ Test across all 5 portal sections (public, central, tenant, customer)
- ⏳ Check Tag Assistant shows container GTM-MKQWQXQV

### GTM Dashboard Configuration
- ⏳ Create first tag (e.g., additional GA4 configuration)
- ⏳ Set up custom triggers for important events
- ⏳ Configure dataLayer variables
- ⏳ Test tag firing in Preview mode
- ⏳ Publish first GTM version

### Documentation
- ✅ Implementation documentation created (`claudedocs/GOOGLE_TAG_MANAGER_INTEGRATION.md`)
- ✅ Code examples for dataLayer usage provided
- ✅ Testing procedures documented
- ✅ Troubleshooting guide included

---

## Summary

Google Tag Manager (GTM-MKQWQXQV) is now fully integrated across all 5 layouts of the Midas Portal:

1. **Public Website**: Home, features, pricing, blog, contact pages
2. **Central Admin**: Tenant management, plan management, blog editor
3. **Tenant Admin**: Dashboard, customers, policies, claims, quotations (124+ pages)
4. **Customer Portal**: Customer dashboard, policies, claims, profile

**Next Steps**:

1. Add GTM credentials to actual `.env` file
2. Clear Laravel config cache
3. Test GTM loading on any portal page
4. Access GTM dashboard and configure your first tag
5. Use Preview mode to verify tags fire correctly
6. Publish your first GTM version with marketing/analytics tags

**Analytics Stack Complete**:
- ✅ Google Analytics 4 (G-21PCW1WJXT) - Quantitative metrics
- ✅ Microsoft Clarity (u4tcfro0dt) - Session recordings and heatmaps
- ✅ Google Tag Manager (GTM-MKQWQXQV) - Centralized tag management

For questions or issues, refer to the Troubleshooting section above or consult official [Google Tag Manager documentation](https://support.google.com/tagmanager).
