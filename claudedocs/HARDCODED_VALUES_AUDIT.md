# Hardcoded Values Audit - Complete Report

## Found: 114 Locations Where App Settings Should Be Used

---

## Priority Breakdown

| Priority | Count | Impact | Effort |
|----------|-------|--------|--------|
| **MUST FIX** | **23** | Critical - Brand consistency | 2-3 hours |
| **SHOULD FIX** | **47** | High - Code quality | 3-4 hours |
| **OPTIONAL** | **44** | Low - Nice to have | 2-3 hours |

---

## MUST FIX (23 Issues) - Do These First! 🚨

### 1. Company Information (18 issues)

**Problem:** Hardcoded "Parth Rawal" everywhere

**Files Affected:**
1. `customer/claim-detail.blade.php` - 3 occurrences
2. `emails/customer/family-login-credentials.blade.php` - 2 occurrences
3. `emails/claim-notification.blade.php` - 4 occurrences
4. `emails/customer/password-reset.blade.php` - 1 occurrence
5. `emails/customer/email-verification.blade.php` - 1 occurrence
6. `customer/partials/footer.blade.php` - 1 occurrence

**Current:**
```php
<strong>Parth Rawal</strong>
Phone: +91 97277 93123
Website: https://parthrawal.in
```

**Fixed:**
```php
<strong>{{ company_advisor_name() }}</strong>
Phone: {{ company_phone() }}
Website: {{ company_website() }}
```

**Why Critical:** When you onboard a new client, all these will show wrong information!

---

### 2. Date Formats in Exports (5 issues)

**Files Affected:**
1. `app/Exports/ClaimsExport.php` - 3 occurrences
2. `app/Exports/CrossSellingExport.php` - 2 occurrences
3. `app/Exports/BranchesExport.php` - 1 occurrence

**Current:**
```php
$claim->incident_date->format('d/m/Y')
$claim->created_at->format('d/m/Y H:i')
```

**Fixed:**
```php
format_app_date($claim->incident_date)
format_app_datetime($claim->created_at)
```

**Why Critical:** Exports will always show d/m/Y even if user wants Y-m-d format!

---

## SHOULD FIX (47 Issues) - High Priority ⚠️

### 1. config('app.name') Usage (12 issues)

**Files Affected:**
- `app/Services/CustomerService.php`
- `app/Traits/Customer/HasCustomerTwoFactorAuth.php`
- Multiple email templates (10 files)

**Current:**
```php
config('app.name')
config('mail.from.address')
```

**Fixed:**
```php
company_name()
email_from_address()
```

**Why Important:** Centralized management, easier to change company name

---

### 2. Hardcoded Theme Colors (16 issues)

**Files Affected:**
- `app_settings/create.blade.php` - Default color picker: `#4e73df`
- `app_settings/edit.blade.php` - Default color picker: `#4e73df`
- `notification_templates/create.blade.php` - Bootstrap colors in JavaScript
- `notification_templates/edit.blade.php` - Bootstrap colors in JavaScript
- `auth/includes/head.blade.php` - Login page colors
- `emails/templated-notification.blade.php` - Email gradient
- `pdfs/quotation.blade.php` - PDF status colors

**Current:**
```javascript
'primary': { bg: '#0d6efd' }
border-top: 4px solid #2563eb;
```

**Fixed:**
```javascript
'primary': { bg: '{{ theme_color("primary") }}' }
border-top: 4px solid {{ theme_primary_color() }};
```

**Why Important:** Brand consistency across all pages, emails, PDFs

---

### 3. Email Configuration (3 issues)

**Files Affected:**
- `app/Services/CustomerService.php` - 2 occurrences
- `app/Mail/ClaimNotificationMail.php` - 1 occurrence

**Why Important:** Email settings should come from app settings, not config files

---

### 4. Pagination Values (2 issues)

**Files Affected:**
- `CustomerDeviceController.php` - Uses 50
- `NotificationLogController.php` - Uses 25

**Solution:** Create new app setting `pagination_per_page` or use existing `pagination_default`

---

### 5. Admin Email Restriction (1 issue)

**File:** `common/sidebar.blade.php`

**Current:**
```php
$userEmail === 'webmonks.in@gmail.com' || str_ends_with($userEmail, '@webmonks.in')
```

**Solution:** Create new app setting `system_admin_emails` (comma-separated)

---

## OPTIONAL (44 Issues) - Nice to Have ℹ️

### 1. Chart Colors (10+ issues)
- Dashboard charts
- Report analytics
- Notification analytics

**Note:** Charts often need specific color palettes for data visualization

---

### 2. Email Placeholders (5 issues)
- `guest@example.com`
- `email@example.com`

**Note:** These are instructional examples in UI, rarely seen

---

### 3. Input Placeholders (15+ issues)
- `placeholder="https://example.com"`
- `placeholder="email@example.com"`

**Note:** Acceptable as example text for users

---

### 4. External CDN URLs (10 issues)
- Google Fonts
- Bootstrap CDN
- Font Awesome

**Note:** Should remain as-is (external dependencies)

---

### 5. Laravel Welcome Page (1 file)
- `resources/views/welcome.blade.php`

**Note:** Default Laravel page, not used in production

---

## Quick Wins - Do These Now! 🎯

### Top 5 Quick Fixes (30 minutes):

1. **Replace "Parth Rawal" (5 files)** - 5 minutes
   ```php
   // Find: "Parth Rawal"
   // Replace with: {{ company_advisor_name() }}
   ```

2. **Replace phone number (4 files)** - 5 minutes
   ```php
   // Find: "+91 97277 93123" or "919727793123"
   // Replace with: {{ company_phone() }} or {{ company_phone_whatsapp() }}
   ```

3. **Replace website URL (2 files)** - 3 minutes
   ```php
   // Find: "https://parthrawal.in"
   // Replace with: {{ company_website() }}
   ```

4. **Fix CustomerService.php (1 file)** - 10 minutes
   - Replace config('app.name') with company_name()
   - Replace config('mail.from.address') with email_from_address()

5. **Fix date formats in exports (3 files)** - 7 minutes
   - Use format_app_date() and format_app_datetime()

---

## Available Helper Functions

### Company Info:
```php
company_name()              // "Parth Rawal Insurance Advisor"
company_advisor_name()      // "Parth Rawal"
company_website()           // "https://parthrawal.in"
company_phone()             // "+91 97277 93123"
company_phone_whatsapp()    // "919727793123"
company_title()             // "Your Trusted Insurance Advisor"
company_tagline()           // "Think of Insurance, Think of Us."
```

### Theme Colors:
```php
theme_color('primary')      // "#4e73df"
theme_color('success')      // "#1cc88a"
theme_color('danger')       // "#e74a3b"
theme_primary_color()       // Alias for theme_color('primary')
theme_link_color()          // Link color
theme_link_hover_color()    // Link hover color
```

### Email:
```php
email_from_address()        // "noreply@example.com"
email_from_name()           // Company name
```

### Date/Time:
```php
app_date_format()           // "d/m/Y"
format_app_date($date)      // Formats date using app setting
format_app_datetime($dt)    // Formats datetime using app setting
```

---

## Action Plan

### Phase 1: Critical Fixes (23 issues - 2-3 hours)
1. ✅ Replace hardcoded company info (18 issues)
2. ✅ Fix date formats in exports (5 issues)

### Phase 2: High Priority (47 issues - 3-4 hours)
1. Replace config() calls with helpers (15 issues)
2. Fix theme colors (16 issues)
3. Add pagination setting (2 issues)
4. Fix email configuration (3 issues)
5. Add admin emails setting (1 issue)

### Phase 3: Optional (44 issues - 2-3 hours)
1. Review chart colors (if needed)
2. Leave placeholders as-is
3. Delete welcome.blade.php

---

## Summary

**Total Found:** 114 locations
**Must Fix:** 23 (do immediately)
**Should Fix:** 47 (high priority)
**Optional:** 44 (nice to have)

**Estimated Time:**
- Phase 1: 2-3 hours
- Phase 2: 3-4 hours
- Phase 3: Optional

**Impact:**
- ✅ Brand consistency across all pages
- ✅ Easy to white-label for new clients
- ✅ Centralized configuration management
- ✅ Professional appearance

---

## Files Most Affected

### Top 10 Files Needing Fixes:

1. `emails/claim-notification.blade.php` - 4 fixes
2. `customer/claim-detail.blade.php` - 3 fixes
3. `app/Services/CustomerService.php` - 4 fixes
4. `app/Exports/ClaimsExport.php` - 3 fixes
5. `auth/includes/head.blade.php` - 16 color fixes
6. `notification_templates/create.blade.php` - 5 color fixes
7. `pdfs/quotation.blade.php` - 12 color fixes
8. `emails/customer/family-login-credentials.blade.php` - 2 fixes
9. `app/Exports/CrossSellingExport.php` - 2 fixes
10. `emails/templated-notification.blade.php` - 1 fix

---

**Audit Date:** {{ now()->format('Y-m-d H:i:s') }}
**Status:** Ready for implementation
**Next Step:** Phase 1 - Fix 23 critical issues
