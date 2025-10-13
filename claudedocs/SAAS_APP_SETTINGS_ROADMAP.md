# SaaS Transformation: App Settings Roadmap

**Date**: 2025-10-11
**Purpose**: Comprehensive app settings needed to convert single-tenant insurance portal into multi-tenant SaaS platform
**Current State**: Single-tenant with 31 app settings
**Target State**: Multi-tenant SaaS with white-labeling, subscriptions, and tenant isolation

---

## 🎯 Executive Summary

To transform this insurance management system into a **SaaS platform**, we need **150+ additional app settings** across 15 categories. This document outlines every setting needed for multi-tenancy, white-labeling, billing, and enterprise features.

### SaaS Transformation Phases

```
Phase 1: Tenant Foundation (8-10 weeks)
├─ Tenant Model & Isolation
├─ Basic White-labeling
└─ Subscription Management

Phase 2: Advanced Features (6-8 weeks)
├─ Usage Quotas & Limits
├─ Custom Domains
└─ API Access Control

Phase 3: Enterprise Features (8-10 weeks)
├─ SSO & Advanced Auth
├─ Compliance & Audit
└─ Advanced Integrations
```

---

## 📊 Current vs Future State

| Aspect | Current (Single-Tenant) | Future (SaaS) |
|--------|------------------------|---------------|
| **Tenants** | 1 (Parth Rawal Insurance) | Unlimited insurance advisors |
| **Branding** | Fixed company info | Per-tenant white-labeling |
| **Billing** | None | Subscription-based (monthly/annual) |
| **Domains** | Single domain | Custom domains per tenant |
| **Limits** | Unlimited | Per-plan quotas (users, policies, etc.) |
| **Settings** | 31 global settings | 31 global + 150+ tenant settings |
| **Data Isolation** | Single database | Tenant-scoped queries |

---

## 1. 🏢 Tenant/Organization Management (20 settings)

### 1.1 Core Tenant Settings

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `tenant_id` | string | Unique tenant identifier (UUID) | - | "550e8400-e29b-41d4-a716-446655440000" |
| `tenant_name` | string | Organization name | - | "Sharma Insurance Brokers" |
| `tenant_subdomain` | string | Subdomain slug | - | "sharma-insurance" |
| `tenant_status` | enum | active, suspended, trial, cancelled | active | "active" |
| `tenant_tier` | enum | free, basic, professional, enterprise | basic | "professional" |
| `tenant_created_at` | datetime | Tenant creation date | now() | "2025-01-15 10:30:00" |
| `tenant_trial_ends_at` | datetime | Trial expiry date | +14 days | "2025-01-29 23:59:59" |
| `tenant_subscription_ends_at` | datetime | Subscription expiry | +30 days | "2025-02-15 23:59:59" |
| `tenant_owner_email` | string | Primary admin email | - | "admin@sharmainsurance.com" |
| `tenant_owner_name` | string | Primary admin name | - | "Rajesh Sharma" |
| `tenant_phone` | string | Organization phone | - | "+91 98765 43210" |
| `tenant_address` | text | Organization address | - | "123 MG Road, Mumbai" |
| `tenant_city` | string | City | - | "Mumbai" |
| `tenant_state` | string | State/Province | - | "Maharashtra" |
| `tenant_country` | string | Country | - | "India" |
| `tenant_pincode` | string | Postal code | - | "400001" |
| `tenant_gst_number` | string | GST/Tax ID (India-specific) | null | "27AABCU9603R1ZM" |
| `tenant_pan_number` | string | PAN number (India-specific) | null | "AABCU9603R" |
| `tenant_license_number` | string | Insurance license number | null | "INS/LIC/2024/12345" |
| `tenant_timezone` | string | Tenant timezone | Asia/Kolkata | "Asia/Kolkata" |

**Helper Functions**:
```php
function tenant_id(): string
function tenant_name(): string
function tenant_subdomain(): string
function is_tenant_active(): bool
function is_tenant_trial(): bool
function tenant_tier(): string
function days_until_trial_ends(): int
```

---

## 2. 🎨 White-labeling & Branding (25 settings)

### 2.1 Visual Branding

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `brand_primary_color` | color | Primary brand color (hex) | #007bff | "#FF6B35" |
| `brand_secondary_color` | color | Secondary brand color | #6c757d | "#004E89" |
| `brand_accent_color` | color | Accent/highlight color | #28a745 | "#1A936F" |
| `brand_text_color` | color | Main text color | #333333 | "#2C2C2C" |
| `brand_background_color` | color | Background color | #ffffff | "#F7F9FB" |
| `brand_logo_url` | url | Company logo (URL or path) | /default-logo.png | "https://cdn.tenant.com/logo.png" |
| `brand_logo_small_url` | url | Small/icon logo | /default-icon.png | "https://cdn.tenant.com/icon.png" |
| `brand_favicon_url` | url | Favicon URL | /favicon.ico | "https://cdn.tenant.com/favicon.ico" |
| `brand_email_logo_url` | url | Logo for emails | null | "https://cdn.tenant.com/email-logo.png" |
| `brand_watermark_url` | url | Document watermark | null | "https://cdn.tenant.com/watermark.png" |
| `brand_login_background` | url | Custom login page background | null | "https://cdn.tenant.com/login-bg.jpg" |
| `brand_font_family` | string | Custom font | "Arial, sans-serif" | "Poppins, sans-serif" |
| `brand_font_url` | url | Google Fonts or custom font URL | null | "https://fonts.googleapis.com/css2?family=Poppins" |

### 2.2 Content Customization

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `brand_company_tagline` | string | Company tagline | null | "Protecting Your Future, Today" |
| `brand_company_description` | text | About company | null | "We are India's leading insurance brokers..." |
| `brand_footer_text` | text | Custom footer text | null | "Licensed by IRDAI. Reg No: 12345" |
| `brand_terms_url` | url | Terms of service URL | null | "https://tenant.com/terms" |
| `brand_privacy_url` | url | Privacy policy URL | null | "https://tenant.com/privacy" |
| `brand_support_url` | url | Support page URL | null | "https://tenant.com/support" |
| `brand_social_facebook` | url | Facebook page URL | null | "https://facebook.com/sharmainsurance" |
| `brand_social_twitter` | url | Twitter URL | null | "https://twitter.com/sharmainsurance" |
| `brand_social_linkedin` | url | LinkedIn URL | null | "https://linkedin.com/company/sharma" |
| `brand_social_instagram` | url | Instagram URL | null | "https://instagram.com/sharmainsurance" |
| `brand_google_analytics_id` | string | GA tracking ID | null | "G-XXXXXXXXXX" |
| `brand_custom_css` | text | Custom CSS for advanced branding | null | ".header { background: #FF6B35; }" |

**Helper Functions**:
```php
function brand_primary_color(): string
function brand_logo_url(): string
function brand_company_tagline(): string
function has_custom_branding(): bool
```

---

## 3. 💳 Subscription & Billing (30 settings)

### 3.1 Subscription Plans

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `subscription_plan_id` | string | Current plan ID | free | "professional_annual" |
| `subscription_plan_name` | string | Plan display name | Free | "Professional Plan" |
| `subscription_status` | enum | active, past_due, cancelled, trial | trial | "active" |
| `subscription_billing_cycle` | enum | monthly, quarterly, annual | monthly | "annual" |
| `subscription_price` | decimal | Plan price | 0.00 | "29999.00" |
| `subscription_currency` | string | Billing currency | INR | "INR" |
| `subscription_start_date` | date | Subscription start | null | "2025-01-01" |
| `subscription_end_date` | date | Subscription end | null | "2026-01-01" |
| `subscription_renewal_date` | date | Next renewal date | null | "2026-01-01" |
| `subscription_auto_renew` | boolean | Auto-renewal enabled | true | true |
| `subscription_payment_method` | enum | card, upi, netbanking, invoice | card | "upi" |
| `subscription_grace_period_days` | integer | Days after expiry | 7 | 7 |
| `subscription_cancel_at_period_end` | boolean | Cancel when expires | false | false |
| `subscription_cancelled_at` | datetime | Cancellation date | null | null |
| `subscription_cancellation_reason` | text | Why cancelled | null | "Too expensive" |

### 3.2 Plan Limits & Quotas

| Setting Key | Type | Description | Default | Per Plan Values |
|-------------|------|-------------|---------|----------------|
| `plan_max_users` | integer | Max admin users | 1 | Free:1, Basic:5, Pro:25, Ent:unlimited |
| `plan_max_customers` | integer | Max customers | 50 | Free:50, Basic:500, Pro:5000, Ent:unlimited |
| `plan_max_policies` | integer | Max active policies | 100 | Free:100, Basic:1000, Pro:10000, Ent:unlimited |
| `plan_max_storage_mb` | integer | Storage limit (MB) | 100 | Free:100, Basic:1000, Pro:10000, Ent:unlimited |
| `plan_max_file_size_mb` | integer | Max file upload size | 2 | Free:2, Basic:10, Pro:50, Ent:100 |
| `plan_max_api_calls_per_hour` | integer | API rate limit | 100 | Free:100, Basic:1000, Pro:10000, Ent:100000 |
| `plan_max_email_sends_per_month` | integer | Email quota | 100 | Free:100, Basic:1000, Pro:10000, Ent:unlimited |
| `plan_max_whatsapp_sends_per_month` | integer | WhatsApp quota | 50 | Free:50, Basic:500, Pro:5000, Ent:unlimited |
| `plan_max_sms_sends_per_month` | integer | SMS quota | 0 | Free:0, Basic:100, Pro:1000, Ent:unlimited |

### 3.3 Payment Gateway Settings

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `billing_gateway` | enum | razorpay, stripe, payu, cashfree | razorpay | "razorpay" |
| `billing_razorpay_key_id` | string (encrypted) | Razorpay Key ID | null | "rzp_test_xxxxx" |
| `billing_razorpay_key_secret` | string (encrypted) | Razorpay Secret | null | "xxxxxxxxxxxxx" |
| `billing_stripe_publishable_key` | string (encrypted) | Stripe Publishable | null | "pk_test_xxxxx" |
| `billing_stripe_secret_key` | string (encrypted) | Stripe Secret | null | "sk_test_xxxxx" |
| `billing_webhook_secret` | string (encrypted) | Webhook secret | null | "whsec_xxxxx" |

**Helper Functions**:
```php
function subscription_plan_name(): string
function is_subscription_active(): bool
function is_subscription_trial(): bool
function days_until_renewal(): int
function get_plan_limit(string $feature): int
function has_reached_limit(string $feature): bool
function get_usage_percentage(string $feature): float
```

---

## 4. 📊 Usage Tracking & Analytics (15 settings)

### 4.1 Current Usage Counters

| Setting Key | Type | Description | Reset | Example Value |
|-------------|------|-------------|-------|---------------|
| `usage_current_users` | integer | Current active users | never | 12 |
| `usage_current_customers` | integer | Current customers | never | 1250 |
| `usage_current_policies` | integer | Current active policies | never | 3450 |
| `usage_storage_used_mb` | integer | Storage used (MB) | never | 4500 |
| `usage_api_calls_today` | integer | API calls today | daily | 2500 |
| `usage_api_calls_this_month` | integer | API calls this month | monthly | 45000 |
| `usage_emails_this_month` | integer | Emails sent this month | monthly | 850 |
| `usage_whatsapp_this_month` | integer | WhatsApp sent this month | monthly | 1200 |
| `usage_sms_this_month` | integer | SMS sent this month | monthly | 300 |
| `usage_reports_generated_this_month` | integer | Reports generated | monthly | 45 |
| `usage_exports_this_month` | integer | Data exports | monthly | 120 |
| `usage_last_login_at` | datetime | Last tenant activity | never | "2025-10-11 14:30:00" |
| `usage_last_api_call_at` | datetime | Last API usage | never | "2025-10-11 15:45:00" |
| `usage_total_logins` | integer | Total logins (lifetime) | never | 5420 |
| `usage_total_revenue` | decimal | Total revenue (lifetime) | never | "599999.00" |

**Helper Functions**:
```php
function get_current_usage(string $feature): int
function get_usage_limit(string $feature): int
function get_remaining_quota(string $feature): int
function is_quota_exceeded(string $feature): bool
function get_usage_stats(): array
```

---

## 5. 🔐 Feature Toggles (Per-Tenant) (20 settings)

| Setting Key | Type | Description | Free | Basic | Pro | Enterprise |
|-------------|------|-------------|------|-------|-----|------------|
| `feature_customer_portal` | boolean | Enable customer portal | ✅ | ✅ | ✅ | ✅ |
| `feature_two_factor_auth` | boolean | 2FA for users | ❌ | ✅ | ✅ | ✅ |
| `feature_device_tracking` | boolean | Device security | ❌ | ❌ | ✅ | ✅ |
| `feature_api_access` | boolean | REST API | ❌ | ❌ | ✅ | ✅ |
| `feature_custom_domain` | boolean | Custom domain | ❌ | ❌ | ❌ | ✅ |
| `feature_white_label` | boolean | Full white-labeling | ❌ | ❌ | ✅ | ✅ |
| `feature_advanced_reports` | boolean | Advanced reporting | ❌ | ❌ | ✅ | ✅ |
| `feature_bulk_operations` | boolean | Bulk import/export | ❌ | ✅ | ✅ | ✅ |
| `feature_email_notifications` | boolean | Email notifications | ✅ | ✅ | ✅ | ✅ |
| `feature_whatsapp_notifications` | boolean | WhatsApp | ❌ | ✅ | ✅ | ✅ |
| `feature_sms_notifications` | boolean | SMS notifications | ❌ | ❌ | ✅ | ✅ |
| `feature_document_templates` | boolean | Custom templates | ❌ | ✅ | ✅ | ✅ |
| `feature_multi_currency` | boolean | Multiple currencies | ❌ | ❌ | ✅ | ✅ |
| `feature_multi_language` | boolean | Multiple languages | ❌ | ❌ | ✅ | ✅ |
| `feature_audit_logs` | boolean | Audit trail | ❌ | ❌ | ✅ | ✅ |
| `feature_role_permissions` | boolean | Advanced permissions | ❌ | ✅ | ✅ | ✅ |
| `feature_data_export` | boolean | Data export | ✅ | ✅ | ✅ | ✅ |
| `feature_automated_backups` | boolean | Automated backups | ❌ | ❌ | ✅ | ✅ |
| `feature_priority_support` | boolean | Priority support | ❌ | ❌ | ❌ | ✅ |
| `feature_sso` | boolean | Single Sign-On | ❌ | ❌ | ❌ | ✅ |

**Helper Functions**:
```php
function is_feature_enabled(string $feature): bool
function get_enabled_features(): array
function can_access_feature(string $feature): bool
```

---

## 6. 🌐 Custom Domain & SSL (8 settings)

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `domain_custom_domain` | string | Custom domain | null | "insurance.sharmafinance.com" |
| `domain_custom_domain_verified` | boolean | Domain verified | false | true |
| `domain_custom_domain_verified_at` | datetime | Verification date | null | "2025-10-01 10:00:00" |
| `domain_ssl_enabled` | boolean | SSL/HTTPS enabled | false | true |
| `domain_ssl_certificate_issuer` | string | Certificate authority | null | "Let's Encrypt" |
| `domain_ssl_expires_at` | datetime | SSL expiry | null | "2026-01-01 00:00:00" |
| `domain_force_https` | boolean | Force HTTPS redirect | false | true |
| `domain_dns_records` | json | Required DNS records | null | {"type":"CNAME","name":"insurance","value":"app.saas.com"} |

**Helper Functions**:
```php
function has_custom_domain(): bool
function get_tenant_url(): string
function is_ssl_enabled(): bool
```

---

## 7. 🔗 API Access Control (12 settings)

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `api_enabled` | boolean | API access enabled | false | true |
| `api_key` | string (encrypted) | Primary API key | null | "sk_live_xxxxxxxxxxxxxxxx" |
| `api_key_secondary` | string (encrypted) | Secondary/backup key | null | "sk_live_yyyyyyyyyyyyyyyy" |
| `api_rate_limit_per_hour` | integer | Requests per hour | 100 | 10000 |
| `api_rate_limit_per_day` | integer | Requests per day | 1000 | 100000 |
| `api_allowed_ips` | json | Whitelist IPs | null | ["203.0.113.0/24","198.51.100.42"] |
| `api_webhook_url` | url | Webhook endpoint | null | "https://tenant.com/api/webhook" |
| `api_webhook_secret` | string (encrypted) | Webhook secret | null | "whsec_xxxxxxxxxx" |
| `api_webhook_events` | json | Subscribed events | null | ["policy.created","customer.updated"] |
| `api_cors_allowed_origins` | json | CORS origins | ["*"] | ["https://tenant.com","https://app.tenant.com"] |
| `api_version` | string | API version | "v1" | "v2" |
| `api_documentation_url` | url | API docs link | null | "https://docs.saas.com/api" |

**Helper Functions**:
```php
function is_api_enabled(): bool
function get_api_key(): string
function is_ip_whitelisted(string $ip): bool
function get_api_rate_limit(): int
```

---

## 8. 🔒 Security & Compliance (18 settings)

### 8.1 Authentication & Access

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `security_enforce_2fa` | boolean | Require 2FA for all users | false | true |
| `security_2fa_methods` | json | Allowed 2FA methods | ["totp"] | ["totp","sms","email"] |
| `security_password_min_length` | integer | Minimum password length | 8 | 12 |
| `security_password_require_uppercase` | boolean | Require uppercase | true | true |
| `security_password_require_lowercase` | boolean | Require lowercase | true | true |
| `security_password_require_numbers` | boolean | Require numbers | true | true |
| `security_password_require_symbols` | boolean | Require symbols | false | true |
| `security_password_expiry_days` | integer | Password expiry (0=never) | 0 | 90 |
| `security_session_timeout_minutes` | integer | Idle session timeout | 120 | 30 |
| `security_max_login_attempts` | integer | Failed login attempts | 5 | 3 |
| `security_lockout_duration_minutes` | integer | Account lockout time | 15 | 30 |
| `security_ip_whitelist` | json | Allowed IPs (admin) | null | ["203.0.113.0/24"] |
| `security_allowed_countries` | json | Geo-restriction | null | ["IN","US","GB"] |

### 8.2 Data Protection & Compliance

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `compliance_gdpr_enabled` | boolean | GDPR compliance | false | true |
| `compliance_data_retention_days` | integer | Keep data for (days) | 365 | 2555 (7 years) |
| `compliance_auto_delete_inactive` | boolean | Auto-delete inactive data | false | true |
| `compliance_pii_encryption` | boolean | Encrypt PII data | true | true |
| `compliance_audit_trail_enabled` | boolean | Enable audit logs | false | true |

**Helper Functions**:
```php
function is_2fa_enforced(): bool
function get_password_requirements(): array
function is_gdpr_enabled(): bool
function get_data_retention_days(): int
```

---

## 9. 📧 Communication Preferences (12 settings)

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `comm_default_channel` | enum | email, whatsapp, sms | email | "email" |
| `comm_fallback_channel` | enum | Fallback if primary fails | null | "sms" |
| `comm_email_from_domain` | string | Custom email domain | null | "notifications@tenant.com" |
| `comm_email_reply_to` | string | Reply-to address | null | "support@tenant.com" |
| `comm_email_signature` | text | Email signature | null | "Best regards,\nTeam Sharma" |
| `comm_whatsapp_business_number` | string | WhatsApp Business | null | "919876543210" |
| `comm_sms_sender_id` | string | SMS sender name | null | "SHARMA" |
| `comm_notification_hours_start` | time | Send notifications from | "09:00" | "08:00" |
| `comm_notification_hours_end` | time | Send notifications until | "21:00" | "20:00" |
| `comm_timezone_aware` | boolean | Send per recipient timezone | false | true |
| `comm_unsubscribe_url` | url | Unsubscribe page | null | "https://tenant.com/unsubscribe" |
| `comm_marketing_consent_required` | boolean | Require opt-in | true | true |

**Helper Functions**:
```php
function get_default_communication_channel(): string
function get_email_from_address(): string
function get_notification_hours(): array
```

---

## 10. 🌍 Internationalization (i18n) (8 settings)

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `i18n_default_language` | string | Default language | "en" | "hi" |
| `i18n_available_languages` | json | Enabled languages | ["en"] | ["en","hi","gu","mr"] |
| `i18n_auto_detect_language` | boolean | Auto-detect from browser | false | true |
| `i18n_default_currency` | string | Default currency | "INR" | "USD" |
| `i18n_available_currencies` | json | Enabled currencies | ["INR"] | ["INR","USD","EUR"] |
| `i18n_date_format` | string | Date format | "d/m/Y" | "Y-m-d" |
| `i18n_time_format` | string | Time format | "12h" | "24h" |
| `i18n_number_format` | enum | indian, western | "indian" | "indian" |

**Helper Functions**:
```php
function get_tenant_language(): string
function get_available_languages(): array
function get_tenant_currency(): string
function format_date_localized(\Carbon\Carbon $date): string
```

---

## 11. 🗄️ Backup & Data Export (10 settings)

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `backup_enabled` | boolean | Automated backups | false | true |
| `backup_frequency` | enum | hourly, daily, weekly | daily | "daily" |
| `backup_retention_days` | integer | Keep backups for | 7 | 30 |
| `backup_storage_provider` | enum | local, s3, gcs, azure | local | "s3" |
| `backup_s3_bucket` | string | S3 bucket name | null | "tenant-backups" |
| `backup_s3_region` | string | S3 region | null | "ap-south-1" |
| `backup_include_files` | boolean | Include uploaded files | true | true |
| `backup_encryption_enabled` | boolean | Encrypt backups | false | true |
| `export_allowed_formats` | json | Export formats | ["excel"] | ["excel","pdf","csv","json"] |
| `export_max_records_per_file` | integer | Max records per export | 10000 | 50000 |

**Helper Functions**:
```php
function is_backup_enabled(): bool
function get_backup_frequency(): string
function can_export_format(string $format): bool
```

---

## 12. 🔔 Notification Templates (Per-Tenant) (8 settings)

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `template_email_header_color` | color | Email header color | #007bff | "#FF6B35" |
| `template_email_footer_text` | text | Email footer | null | "This email was sent by Sharma Insurance" |
| `template_whatsapp_greeting` | text | WhatsApp greeting | "Hello" | "नमस्ते" (Hindi) |
| `template_whatsapp_signature` | text | WhatsApp signature | null | "Team Sharma Insurance" |
| `template_sms_signature` | string | SMS signature | null | "-Sharma Ins" |
| `template_renewal_reminder_days_before` | json | Days before renewal | [30,15,7,1] | [45,30,15,7,3,1] |
| `template_birthday_wishes_enabled` | boolean | Send birthday wishes | true | true |
| `template_policy_expiry_alert_days` | integer | Days before expiry alert | 30 | 60 |

---

## 13. 📊 Integrations (Per-Tenant) (15 settings)

### 13.1 Third-Party Integrations

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `integration_google_workspace` | boolean | Google Workspace SSO | false | true |
| `integration_microsoft_365` | boolean | Microsoft 365 SSO | false | true |
| `integration_slack_webhook` | url | Slack notifications | null | "https://hooks.slack.com/..." |
| `integration_zapier_enabled` | boolean | Zapier integration | false | true |
| `integration_quickbooks_enabled` | boolean | QuickBooks sync | false | true |
| `integration_tally_enabled` | boolean | Tally ERP sync (India) | false | true |
| `integration_payment_gateway` | enum | razorpay, stripe, payu | null | "razorpay" |
| `integration_sms_provider` | enum | twilio, msg91, kaleyra | null | "msg91" |
| `integration_email_provider` | enum | smtp, sendgrid, ses, mailgun | smtp | "sendgrid" |
| `integration_storage_provider` | enum | local, s3, gcs, azure | local | "s3" |
| `integration_analytics_provider` | enum | ga4, mixpanel, amplitude | null | "ga4" |
| `integration_support_desk` | enum | zendesk, freshdesk, intercom | null | "freshdesk" |

### 13.2 Insurance-Specific Integrations

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `insurance_irdai_integration` | boolean | IRDAI reporting | false | true |
| `insurance_api_aggregator` | enum | policybazaar, coverfox, custom | null | "custom" |
| `insurance_kyc_provider` | enum | digilocker, aadhaar, manual | manual | "digilocker" |

**Helper Functions**:
```php
function is_integration_enabled(string $integration): bool
function get_integration_credentials(string $integration): array
```

---

## 14. 🎛️ Admin Controls (Super Admin Only) (10 settings)

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `admin_can_login_as_tenant` | boolean | Super admin impersonation | true | true |
| `admin_require_approval_for_signup` | boolean | Manual tenant approval | false | true |
| `admin_trial_period_days` | integer | Default trial period | 14 | 30 |
| `admin_default_plan` | string | Default plan for new tenants | "free" | "basic" |
| `admin_max_tenants` | integer | Platform-wide tenant limit | 1000 | 10000 |
| `admin_maintenance_mode` | boolean | Platform maintenance | false | false |
| `admin_new_signups_enabled` | boolean | Allow new signups | true | false |
| `admin_featured_tenants` | json | Featured/showcase tenants | [] | ["tenant-uuid-1","tenant-uuid-2"] |
| `admin_default_timezone` | string | Platform timezone | "UTC" | "Asia/Kolkata" |
| `admin_support_email` | string | Platform support email | support@saas.com | "help@insuranceplatform.com" |

---

## 15. 📱 Mobile App Settings (8 settings)

| Setting Key | Type | Description | Default | Example Value |
|-------------|------|-------------|---------|---------------|
| `mobile_app_enabled` | boolean | Mobile app access | false | true |
| `mobile_ios_app_url` | url | iOS App Store link | null | "https://apps.apple.com/..." |
| `mobile_android_app_url` | url | Android Play Store link | null | "https://play.google.com/..." |
| `mobile_deep_linking_enabled` | boolean | Deep links | false | true |
| `mobile_push_notifications_enabled` | boolean | Push notifications | false | true |
| `mobile_fcm_server_key` | string (encrypted) | Firebase Cloud Messaging | null | "AAAAxxxxxxx:APA91..." |
| `mobile_apns_certificate` | text (encrypted) | Apple Push Certificate | null | "-----BEGIN CERTIFICATE-----..." |
| `mobile_app_version_required` | string | Minimum app version | null | "2.1.0" |

---

## 🗄️ Database Schema Changes

### New Tables Needed

```sql
-- Tenants table
CREATE TABLE tenants (
    id CHAR(36) PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    subdomain VARCHAR(100) UNIQUE NOT NULL,
    status ENUM('trial','active','suspended','cancelled') DEFAULT 'trial',
    tier ENUM('free','basic','professional','enterprise') DEFAULT 'free',
    trial_ends_at TIMESTAMP NULL,
    subscription_ends_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_subdomain (subdomain),
    INDEX idx_status (status)
);

-- Tenant settings (key-value store per tenant)
CREATE TABLE tenant_settings (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id CHAR(36) NOT NULL,
    `key` VARCHAR(100) NOT NULL,
    value TEXT,
    type ENUM('string','integer','boolean','json','text','encrypted') DEFAULT 'string',
    is_encrypted BOOLEAN DEFAULT FALSE,
    category VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_setting (tenant_id, `key`),
    INDEX idx_tenant_key (tenant_id, `key`),
    INDEX idx_category (category)
);

-- Subscriptions table
CREATE TABLE subscriptions (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id CHAR(36) NOT NULL,
    plan_id VARCHAR(50) NOT NULL,
    status ENUM('active','past_due','cancelled','expired') DEFAULT 'active',
    price DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'INR',
    billing_cycle ENUM('monthly','quarterly','annual'),
    start_date DATE,
    end_date DATE,
    auto_renew BOOLEAN DEFAULT TRUE,
    cancelled_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant (tenant_id),
    INDEX idx_status (status)
);

-- Usage tracking table
CREATE TABLE tenant_usage (
    id BIGINT AUTO_INCREMENT PRIMARY KEY,
    tenant_id CHAR(36) NOT NULL,
    metric_name VARCHAR(100) NOT NULL,
    metric_value BIGINT DEFAULT 0,
    recorded_at DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    UNIQUE KEY unique_tenant_metric_date (tenant_id, metric_name, recorded_at),
    INDEX idx_tenant_metric (tenant_id, metric_name)
);
```

### Existing Tables Need tenant_id Column

```sql
-- Add tenant_id to all existing tables
ALTER TABLE users ADD COLUMN tenant_id CHAR(36) AFTER id;
ALTER TABLE customers ADD COLUMN tenant_id CHAR(36) AFTER id;
ALTER TABLE customer_insurances ADD COLUMN tenant_id CHAR(36) AFTER id;
ALTER TABLE insurance_companies ADD COLUMN tenant_id CHAR(36) AFTER id;
-- ... and so on for all tables

-- Add foreign keys
ALTER TABLE users ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
ALTER TABLE customers ADD FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE;
-- ... and so on

-- Add indexes for performance
ALTER TABLE users ADD INDEX idx_tenant (tenant_id);
ALTER TABLE customers ADD INDEX idx_tenant (tenant_id);
-- ... and so on
```

---

## 🚀 Implementation Phases

### Phase 1: Foundation (8-10 weeks)

**Week 1-2: Database & Models**
- [ ] Create tenants table and model
- [ ] Add tenant_id to all existing tables
- [ ] Create tenant_settings table
- [ ] Implement TenantSettingService
- [ ] Add tenant scoping to all queries (global scope)

**Week 3-4: Authentication & Multi-tenancy**
- [ ] Implement tenant identification (subdomain/domain)
- [ ] Update authentication to be tenant-aware
- [ ] Add tenant context to all requests
- [ ] Implement tenant middleware
- [ ] Add tenant switching for super admins

**Week 5-6: Basic White-labeling**
- [ ] Implement branding settings (20 settings)
- [ ] Dynamic logo/color loading
- [ ] Custom login pages
- [ ] Tenant-specific email templates

**Week 7-8: Subscription Foundation**
- [ ] Create subscriptions table
- [ ] Implement subscription plans
- [ ] Add usage tracking
- [ ] Implement plan limits

**Week 9-10: Testing & Refinement**
- [ ] Test data isolation
- [ ] Test tenant switching
- [ ] Performance optimization
- [ ] Security audit

### Phase 2: Advanced Features (6-8 weeks)

**Week 11-12: Usage Quotas**
- [ ] Implement usage tracking service
- [ ] Add quota enforcement
- [ ] Create usage dashboard
- [ ] Add upgrade prompts

**Week 13-14: Custom Domains**
- [ ] Domain verification system
- [ ] SSL certificate management
- [ ] DNS configuration UI
- [ ] Domain routing

**Week 15-16: API Access**
- [ ] API key generation
- [ ] Rate limiting per tenant
- [ ] Webhook system
- [ ] API documentation

**Week 17-18: Advanced Features**
- [ ] Feature flag system
- [ ] Advanced reporting
- [ ] Bulk operations
- [ ] Data export

### Phase 3: Enterprise Features (8-10 weeks)

**Week 19-21: SSO & Advanced Auth**
- [ ] Google Workspace SSO
- [ ] Microsoft 365 SSO
- [ ] SAML integration
- [ ] Advanced security settings

**Week 22-24: Compliance & Audit**
- [ ] GDPR compliance tools
- [ ] Audit trail system
- [ ] Data retention policies
- [ ] Compliance reports

**Week 25-26: Integrations**
- [ ] Payment gateway integration
- [ ] Accounting software sync
- [ ] Third-party APIs
- [ ] Insurance-specific integrations

---

## 📋 Implementation Checklist

### Backend Changes
- [ ] Create tenant model and migrations
- [ ] Add tenant_id to all tables
- [ ] Implement global scope for tenant isolation
- [ ] Create TenantSettingService
- [ ] Implement subscription management
- [ ] Add usage tracking
- [ ] Implement quota enforcement
- [ ] Create API for tenant management
- [ ] Add tenant-aware caching
- [ ] Implement backup per tenant

### Frontend Changes
- [ ] Add tenant selector for super admin
- [ ] Create tenant dashboard
- [ ] Build white-labeling UI
- [ ] Implement subscription management UI
- [ ] Add usage dashboard
- [ ] Create API key management UI
- [ ] Build custom domain setup wizard
- [ ] Add feature toggle UI
- [ ] Implement tenant settings page

### Infrastructure Changes
- [ ] Set up wildcard SSL (*.yourdomain.com)
- [ ] Configure subdomain routing
- [ ] Implement custom domain routing
- [ ] Set up separate database per tier (optional)
- [ ] Configure tenant-specific storage
- [ ] Set up monitoring per tenant
- [ ] Implement rate limiting
- [ ] Configure CDN for multi-tenancy

---

## 💡 Best Practices

### 1. Data Isolation
```php
// ALWAYS scope queries by tenant
// Bad
$customers = Customer::all();

// Good
$customers = Customer::where('tenant_id', tenant_id())->get();

// Better - Use global scope
class Customer extends Model
{
    protected static function booted()
    {
        static::addGlobalScope(new TenantScope);
    }
}
```

### 2. Settings Access
```php
// Use tenant-aware setting service
function get_tenant_setting(string $key, $default = null)
{
    return app(TenantSettingService::class)->get(tenant_id(), $key, $default);
}

// Usage
$primaryColor = get_tenant_setting('brand_primary_color', '#007bff');
```

### 3. Feature Checks
```php
// Check feature availability before use
if (!is_feature_enabled('feature_api_access')) {
    abort(403, 'API access is not enabled for your plan');
}
```

### 4. Quota Enforcement
```php
// Check quotas before operations
if (has_reached_limit('plan_max_customers')) {
    return redirect()->back()->with('error',
        'You have reached your plan limit. Please upgrade.'
    );
}
```

---

## 🎯 Success Metrics

### Technical Metrics
- **Tenant Isolation**: 100% - No data leakage between tenants
- **Performance**: <200ms response time per tenant
- **Uptime**: 99.9% SLA per tenant
- **Security**: Zero tenant data breaches
- **Scalability**: Support 10,000+ tenants

### Business Metrics
- **Conversion Rate**: Free → Paid conversion >25%
- **Churn Rate**: <5% monthly churn
- **Upgrade Rate**: >15% upgrade from lower to higher tiers
- **NPS Score**: >50 (Net Promoter Score)
- **Time to Onboard**: <10 minutes for new tenant

---

## 🔗 Related Documents

- `claudedocs/APP_SETTINGS_USAGE_AUDIT_REPORT.md` - Current app settings audit
- `claudedocs/IMPROVEMENT_CHECKLIST.md` - General improvements
- `claudedocs/TESTING_ISSUES_AND_NEXT_STEPS.md` - Testing infrastructure

---

**Document Version**: 1.0
**Last Updated**: 2025-10-11
**Status**: Strategic Planning Document
**Next Steps**: Prioritize Phase 1 implementation
**Estimated Timeline**: 22-28 weeks for full SaaS transformation
**Estimated Effort**: 3-4 full-time developers

