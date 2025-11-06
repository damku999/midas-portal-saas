# Environment Configuration Guide

> **Complete guide to environment variables and configuration files for the Midas Portal Insurance Management System**

**Last Updated**: 2025-01-06
**Laravel Version**: 10.49.1

---

## Table of Contents

1. [Overview](#overview)
2. [Environment Variables](#environment-variables)
3. [Core Application Settings](#core-application-settings)
4. [Database Configuration](#database-configuration)
5. [Multi-Tenancy Configuration](#multi-tenancy-configuration)
6. [Email Configuration](#email-configuration)
7. [WhatsApp Configuration](#whatsapp-configuration)
8. [Payment Gateway Configuration](#payment-gateway-configuration)
9. [Security Configuration](#security-configuration)
10. [Cache & Session Configuration](#cache--session-configuration)
11. [File Storage Configuration](#file-storage-configuration)
12. [Notification Configuration](#notification-configuration)
13. [Central Tenant Defaults](#central-tenant-defaults)
14. [Configuration Files Reference](#configuration-files-reference)

---

## Overview

The Midas Portal uses environment variables (`.env` file) and configuration files (`config/` directory) to manage settings across different environments (local, testing, production).

**Configuration Hierarchy**:
1. `.env` file (environment-specific, not committed to Git)
2. `config/*.php` files (application defaults, committed to Git)
3. Central tenant defaults (inherited by all tenants unless overridden)

---

## Environment Variables

### Quick Setup

1. **Copy the example file**:
```bash
cp .env.example .env
```

2. **Generate application key**:
```bash
php artisan key:generate
```

3. **Configure required values** (see sections below)

4. **Cache configuration**:
```bash
php artisan config:cache
```

---

## Core Application Settings

### Application Basics

```env
# Application Name - Appears in emails, page titles
APP_NAME="Midas Portal"

# Environment: local, testing, production
APP_ENV=local

# Application Key - Auto-generated via php artisan key:generate
APP_KEY=base64:your_generated_key_here

# Debug Mode - MUST be false in production
APP_DEBUG=true

# Application URL - Base URL for the application
APP_URL=http://localhost

# Asset URL - CDN or asset hosting URL (optional)
ASSET_URL=
```

**Important**:
- Set `APP_DEBUG=false` in production to avoid exposing sensitive error information
- `APP_URL` should match your domain in production (e.g., `https://app.midastech.in`)

### Logging Configuration

```env
# Log Channel: stack, single, daily, slack, syslog, errorlog
LOG_CHANNEL=stack

# Log Level: debug, info, notice, warning, error, critical, alert, emergency
LOG_LEVEL=debug

# Deprecation Warnings Channel (optional)
LOG_DEPRECATIONS_CHANNEL=null
```

**Channels**:
- `stack`: Multiple channels (configured in `config/logging.php`)
- `daily`: Separate log file per day
- `single`: Single log file
- `slack`: Send logs to Slack webhook

### Activity Logging

```env
# Enable/Disable Spatie Activity Log
ACTIVITY_LOGGER_ENABLED=true
```

When enabled, all model changes are tracked in the `activity_log` table.

---

## Database Configuration

### Primary Database

```env
# Database Connection Type
DB_CONNECTION=mysql

# Database Host
DB_HOST=127.0.0.1

# Database Port
DB_PORT=3306

# Central Database Name
DB_DATABASE=midas_central

# Database Credentials
DB_USERNAME=root
DB_PASSWORD=your_secure_password
```

**Production Recommendations**:
- Use strong passwords (minimum 16 characters, mixed case, numbers, symbols)
- Create dedicated database user with minimal privileges
- Enable SSL/TLS for database connections

### Tenant Database Configuration

Tenant databases are created automatically during tenant creation:
- **Naming Convention**: `tenant_{tenant_id}`
- **Example**: `tenant_abc123`

Tenant databases use the same connection settings as the central database but with different database names.

---

## Multi-Tenancy Configuration

### Domain Configuration

```env
# Default Domain for Tenants
APP_DOMAIN=midastech.in
```

Supported domains are configured in `config/tenancy-domains.php`:

```php
'domains' => [
    'midastech.in' => [
        'label' => 'midastech.in (Production)',
        'enabled' => true,
        'environment' => ['production'],
    ],
    'midastech.testing.in' => [
        'label' => 'midastech.testing.in (Testing)',
        'enabled' => true,
        'environment' => ['local', 'testing'],
    ],
    'localhost:8085' => [
        'label' => 'localhost:8085 (Local)',
        'enabled' => true,
        'environment' => ['local'],
    ],
],
```

### Tenant URL Formats

- **Production**: `https://{subdomain}.midastech.in`
- **Testing**: `https://{subdomain}.midastech.testing.in`
- **Local**: `http://{subdomain}.localhost:8085`

### Central Admin Portal

The central admin portal is accessed via the `/midas-admin` route on the primary domain (NOT on tenant subdomains).

**Public Website Domains**:
- `midastech.in`
- `www.midastech.in`
- `127.0.0.1`
- `localhost`

---

## Email Configuration

### SMTP Configuration

```env
# Mail Driver: smtp, sendmail, mailgun, ses, postmark, log
MAIL_MAILER=smtp

# SMTP Server Hostname
MAIL_HOST=smtp.hostinger.com

# SMTP Port (25, 465, 587, 2525)
MAIL_PORT=465

# SMTP Username (usually your email address)
MAIL_USERNAME=smtp-test@webmonks.in

# SMTP Password
MAIL_PASSWORD=your_smtp_password_here

# Encryption: ssl, tls, or leave empty
MAIL_ENCRYPTION=ssl

# From Address (must be authorized by your SMTP provider)
MAIL_FROM_ADDRESS="smtp-test@webmonks.in"

# From Name
MAIL_FROM_NAME="${APP_NAME}"
```

**Common SMTP Ports**:
- `25`: Standard SMTP (usually blocked by ISPs)
- `465`: SMTP with SSL
- `587`: SMTP with TLS (STARTTLS)
- `2525`: Alternative port

### Alternative Mail Providers

#### Mailgun

```env
MAIL_MAILER=mailgun
MAILGUN_DOMAIN=mg.yourdomain.com
MAILGUN_SECRET=your_mailgun_api_key
MAILGUN_ENDPOINT=api.mailgun.net
```

#### Amazon SES

```env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=your_aws_access_key
AWS_SECRET_ACCESS_KEY=your_aws_secret_key
AWS_DEFAULT_REGION=us-east-1
```

#### Postmark

```env
MAIL_MAILER=postmark
POSTMARK_TOKEN=your_postmark_api_token
```

---

## WhatsApp Configuration

### BotMasterSender API Configuration

```env
# WhatsApp Sender Phone Number (without + or spaces)
# Example: 919800071314
CENTRAL_WHATSAPP_SENDER=919800071314

# WhatsApp API Authentication Token
CENTRAL_WHATSAPP_AUTH_TOKEN=your_api_token_here

# WhatsApp API Base URL
CENTRAL_WHATSAPP_BASE_URL="https://api.botmastersender.com/api/v1/"
```

**WhatsApp Phone Number Format**:
- Country code + number without spaces or special characters
- India example: `919800071314` (91 + 9800071314)
- US example: `14155551234` (1 + 4155551234)

### WhatsApp Features

The system uses WhatsApp for:
- Policy renewal reminders
- Claim document requests
- Lead campaign messages
- Customer onboarding notifications
- Quotation sharing

---

## Payment Gateway Configuration

### Razorpay Configuration

```env
# Razorpay API Key ID
RAZORPAY_KEY=rzp_test_your_key_id

# Razorpay API Secret Key
RAZORPAY_SECRET=your_razorpay_secret_key

# Razorpay Webhook Secret (for webhook signature verification)
RAZORPAY_WEBHOOK_SECRET=your_webhook_secret
```

**Test vs Live Keys**:
- Test keys start with `rzp_test_`
- Live keys start with `rzp_live_`
- Always use test keys in non-production environments

### Stripe Configuration

```env
# Stripe Publishable Key
STRIPE_KEY=pk_test_your_publishable_key

# Stripe Secret Key
STRIPE_SECRET=sk_test_your_secret_key

# Stripe Webhook Secret (for webhook signature verification)
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
```

**Test vs Live Keys**:
- Test publishable keys start with `pk_test_`
- Test secret keys start with `sk_test_`
- Live keys start with `pk_live_` and `sk_live_`

### Payment Webhook Setup

1. **Razorpay Webhooks**:
   - URL: `https://yourdomain.com/webhooks/payments/razorpay`
   - Events to listen: `payment.captured`, `payment.failed`
   - Secret: Copy from Razorpay dashboard → Settings → Webhooks

2. **Stripe Webhooks**:
   - URL: `https://yourdomain.com/webhooks/payments/stripe`
   - Events to listen: `payment_intent.succeeded`, `payment_intent.payment_failed`
   - Secret: Copy from Stripe dashboard → Developers → Webhooks

---

## Security Configuration

### Content Security Policy (CSP)

```env
# Enable Content Security Policy
CSP_ENABLED=true

# Report-Only Mode (logs violations without blocking)
CSP_REPORT_ONLY=false

# Log CSP Violations to Database
SECURITY_LOG_CSP_VIOLATIONS=true
```

**Production Recommendation**: Enable CSP with `CSP_ENABLED=true` and `CSP_REPORT_ONLY=false`

### Cloudflare Turnstile (CAPTCHA)

```env
# Turnstile Site Key (public)
TURNSTILE_SITE_KEY=0x4AAAAABCDEFghijklmnop

# Turnstile Secret Key (private)
TURNSTILE_SECRET_KEY=0x4AAAAAQRSTUVwxyz123456
```

Used for:
- Login forms
- Registration forms
- Contact forms
- Password reset forms

### Session Configuration

```env
# Session Driver: file, cookie, database, apc, memcached, redis, array
SESSION_DRIVER=file

# Session Lifetime (in minutes)
SESSION_LIFETIME=120
```

**Session Drivers**:
- `file`: Store sessions in `storage/framework/sessions` (default)
- `database`: Store sessions in database table
- `redis`: Store sessions in Redis (best for production)
- `memcached`: Store sessions in Memcached

---

## Cache & Session Configuration

### Cache Configuration

```env
# Cache Driver: file, database, apc, memcached, redis, array
CACHE_DRIVER=file
```

**Cache Drivers**:
- `file`: File-based cache (default, suitable for single-server)
- `redis`: Redis cache (recommended for production, multi-server)
- `memcached`: Memcached cache (alternative to Redis)
- `database`: Database cache (not recommended for high traffic)

### Redis Configuration

```env
# Redis Host
REDIS_HOST=127.0.0.1

# Redis Password (if required)
REDIS_PASSWORD=null

# Redis Port
REDIS_PORT=6379
```

**When to use Redis**:
- High-traffic applications
- Multi-server deployments
- Real-time features
- Session sharing across multiple servers

### Queue Configuration

```env
# Queue Connection: sync, database, beanstalkd, sqs, redis
QUEUE_CONNECTION=sync
```

**Queue Connections**:
- `sync`: Synchronous (no queue, tasks run immediately) - Development only
- `database`: Database queue (suitable for low-medium traffic)
- `redis`: Redis queue (recommended for production)

**Production Recommendation**: Use `redis` queue with supervisor for queue workers

---

## File Storage Configuration

### Filesystem Driver

```env
# Filesystem Driver: local, public, s3
FILESYSTEM_DRIVER=local
```

**Drivers**:
- `local`: Local storage (development, single-server)
- `public`: Public local storage (accessible via web)
- `s3`: Amazon S3 (recommended for production, scalable)

### AWS S3 Configuration

```env
# AWS Access Key ID
AWS_ACCESS_KEY_ID=your_aws_access_key_id

# AWS Secret Access Key
AWS_SECRET_ACCESS_KEY=your_aws_secret_access_key

# AWS Region
AWS_DEFAULT_REGION=us-east-1

# S3 Bucket Name
AWS_BUCKET=your_bucket_name

# Use Path-Style Endpoint (for S3-compatible services)
AWS_USE_PATH_STYLE_ENDPOINT=false
```

**File Storage Features**:
- Tenant logo storage (separate folder per tenant)
- Policy document uploads
- Claim document uploads
- Customer document uploads
- PDF generation storage (quotations, policies)

---

## Notification Configuration

### Broadcast Driver

```env
# Broadcast Driver: pusher, redis, log, null
BROADCAST_DRIVER=log
```

Used for real-time notifications and events.

### Pusher Configuration

```env
# Pusher App ID
PUSHER_APP_ID=your_app_id

# Pusher App Key (public)
PUSHER_APP_KEY=your_app_key

# Pusher App Secret (private)
PUSHER_APP_SECRET=your_app_secret

# Pusher Cluster
PUSHER_APP_CLUSTER=mt1

# Mix Pusher (for frontend)
MIX_PUSHER_APP_KEY="${PUSHER_APP_KEY}"
MIX_PUSHER_APP_CLUSTER="${PUSHER_APP_CLUSTER}"
```

---

## Central Tenant Defaults

These environment variables provide default settings inherited by all tenants unless overridden during tenant creation.

### Branding Assets

```env
# Logo Path (relative to public directory)
CENTRAL_LOGO_PATH=images/logo.png

# Favicon Path (relative to public directory)
CENTRAL_FAVICON_PATH=images/logo-icon@2000x.png

# Logo Alt Text
CENTRAL_LOGO_ALT="Midas Tech"
```

### Theme Colors

```env
# Primary Color (Hex format with #)
CENTRAL_PRIMARY_COLOR="#17a2b8"

# Secondary Color
CENTRAL_SECONDARY_COLOR="#6c757d"

# Success Color
CENTRAL_SUCCESS_COLOR="#28a745"

# Info Color
CENTRAL_INFO_COLOR="#5fd0e3"

# Warning Color
CENTRAL_WARNING_COLOR="#f6c23e"

# Danger Color
CENTRAL_DANGER_COLOR="#e74a3b"
```

### Email Defaults

```env
# Default From Address (for tenant emails)
CENTRAL_MAIL_FROM=support@midastech.in

# Default From Name (for tenant emails)
CENTRAL_MAIL_FROM_NAME="Midas Portal"
```

### Localization Defaults

```env
# Timezone (IANA timezone identifier)
CENTRAL_TIMEZONE="Asia/Kolkata"

# Locale (language code)
CENTRAL_LOCALE=en

# Currency Code (ISO 4217)
CENTRAL_CURRENCY=INR

# Currency Symbol
CENTRAL_CURRENCY_SYMBOL="₹"
```

### Footer Configuration

```env
# Developer/Company Name (appears in footer)
CENTRAL_DEVELOPER_NAME="Midas Tech"

# Developer/Company URL (link in footer)
CENTRAL_DEVELOPER_URL="https://midastech.in"
```

### Tenant Seeding Configuration

```env
# Seed sample business data during tenant creation (true/false)
# true: Seeds sample brokers, RMs, reference users, insurance companies
# false: Only seeds essential master data (recommended for production)
TENANT_SEED_SAMPLE_DATA=false
```

**When to use**:
- `true`: Development and testing environments (quick setup with sample data)
- `false`: Production environments (tenants enter their own data)

---

## Configuration Files Reference

### Core Configuration Files

| File | Purpose | Key Settings |
|------|---------|--------------|
| `config/app.php` | Application settings | Timezone, locale, providers, aliases |
| `config/database.php` | Database connections | MySQL, SQLite, PostgreSQL configs |
| `config/mail.php` | Email configuration | SMTP, Mailgun, SES, Postmark |
| `config/services.php` | Third-party services | Razorpay, Stripe, Turnstile, AWS |
| `config/session.php` | Session management | Driver, lifetime, cookie settings |
| `config/cache.php` | Cache configuration | Drivers, prefixes, stores |
| `config/queue.php` | Queue configuration | Connections, failed job settings |
| `config/filesystems.php` | File storage | Local, S3, public disks |

### Tenancy Configuration Files

| File | Purpose | Key Settings |
|------|---------|--------------|
| `config/tenancy.php` | Multi-tenancy settings | Database naming, bootstrappers, features |
| `config/tenancy-domains.php` | Supported domains | Domain list, environment restrictions |

### Security & Authentication Files

| File | Purpose | Key Settings |
|------|---------|--------------|
| `config/auth.php` | Authentication guards | Staff guard, customer guard, providers |
| `config/security.php` | Security settings | CSP rules, headers, XSS protection |
| `config/sanctum.php` | API authentication | Stateful domains, expiration, middleware |
| `config/permission.php` | Spatie Permission | Table names, cache settings |

### Feature Configuration Files

| File | Purpose | Key Settings |
|------|---------|--------------|
| `config/whatsapp.php` | WhatsApp integration | API endpoints, message templates |
| `config/notifications.php` | Notification channels | Email, WhatsApp, SMS, push settings |
| `config/sms.php` | SMS configuration | SMS provider, credentials |
| `config/push.php` | Push notifications | FCM configuration |
| `config/activitylog.php` | Spatie Activity Log | Enabled models, log name, cleanup |
| `config/excel.php` | Excel export/import | Exports, imports, batch settings |
| `config/log-viewer.php` | Opcodes Log Viewer | Path, files, pattern, middleware |

### Other Configuration Files

| File | Purpose | Key Settings |
|------|---------|--------------|
| `config/constants.php` | Application constants | Business logic constants, enums |
| `config/notification_variables.php` | Template variables | Available variables for templates |
| `config/protection.php` | Rate limiting | Throttle rules, IP whitelisting |
| `config/logging.php` | Log channels | Stack, daily, slack, syslog configs |
| `config/broadcasting.php` | Event broadcasting | Pusher, Redis, log drivers |
| `config/cors.php` | CORS settings | Allowed origins, methods, headers |
| `config/hashing.php` | Password hashing | Bcrypt, Argon2 settings |
| `config/redis.php` | Redis configuration | Client, clusters, options |
| `config/view.php` | View compilation | Paths, compiled path |

---

## Environment-Specific Configurations

### Local Development

```env
APP_ENV=local
APP_DEBUG=true
LOG_LEVEL=debug
QUEUE_CONNECTION=sync
MAIL_MAILER=log
BROADCAST_DRIVER=log
APP_DOMAIN=localhost:8085
```

### Testing Environment

```env
APP_ENV=testing
APP_DEBUG=true
LOG_LEVEL=info
QUEUE_CONNECTION=database
MAIL_MAILER=log
APP_DOMAIN=midastech.testing.in
```

### Production Environment

```env
APP_ENV=production
APP_DEBUG=false
LOG_LEVEL=warning
QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=redis
MAIL_MAILER=smtp
BROADCAST_DRIVER=redis
FILESYSTEM_DRIVER=s3
APP_DOMAIN=midastech.in
CSP_ENABLED=true
CSP_REPORT_ONLY=false
```

---

## Configuration Caching

### Cache Configuration

After modifying `.env` or config files, cache the configuration for better performance:

```bash
php artisan config:cache
```

### Clear Configuration Cache

When making changes, clear the cache first:

```bash
php artisan config:clear
```

### Other Cache Commands

```bash
# Clear all caches
php artisan cache:clear

# Clear route cache
php artisan route:clear

# Clear view cache
php artisan view:clear

# Optimize for production (caches config, routes, views)
php artisan optimize
```

---

## Troubleshooting

### Configuration Not Updating

**Problem**: Changes to `.env` file not reflected in application.

**Solution**:
```bash
php artisan config:clear
php artisan config:cache
```

### Database Connection Errors

**Problem**: `SQLSTATE[HY000] [1045] Access denied for user`

**Check**:
1. Database credentials in `.env`
2. Database user permissions
3. Database server is running
4. Firewall rules (if remote database)

### Email Not Sending

**Problem**: Emails not being sent or failing silently.

**Check**:
1. SMTP credentials in `.env`
2. SMTP port not blocked by firewall
3. `MAIL_FROM_ADDRESS` authorized by SMTP provider
4. Check `storage/logs/laravel.log` for errors
5. Test with `php artisan tinker` and `Mail::raw('Test', function($m){$m->to('test@example.com')->subject('Test');})`

### Queue Jobs Not Processing

**Problem**: Queue jobs stuck in pending state.

**Solution**:
1. Check `QUEUE_CONNECTION` in `.env`
2. Start queue worker: `php artisan queue:work`
3. Use supervisor for production (see DEPLOYMENT.md)

### Cache Issues

**Problem**: Old data still appearing after updates.

**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
```

---

## Security Best Practices

1. **Never commit `.env` file to Git** - It contains sensitive credentials
2. **Use strong passwords** - Minimum 16 characters for production
3. **Enable HTTPS in production** - Use SSL/TLS certificates
4. **Restrict database access** - Use dedicated users with minimal privileges
5. **Rotate API keys regularly** - Especially after team member changes
6. **Use environment-specific keys** - Test keys for dev/staging, live keys for production
7. **Enable CSP in production** - Set `CSP_ENABLED=true`
8. **Disable debug mode in production** - Set `APP_DEBUG=false`
9. **Use secure session drivers** - Redis or database for production
10. **Enable rate limiting** - Configure in `config/protection.php`

---

## Related Documentation

- [DEPLOYMENT.md](../operations/DEPLOYMENT.md) - Deployment procedures and server setup
- [MULTI_PORTAL_ARCHITECTURE.md](../core/MULTI_PORTAL_ARCHITECTURE.md) - Multi-portal routing and domain configuration
- [TROUBLESHOOTING.md](../operations/TROUBLESHOOTING.md) - Common issues and solutions
- [DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md) - Database structure and migrations
- [SUBSCRIPTION_MANAGEMENT.md](../features/SUBSCRIPTION_MANAGEMENT.md) - Subscription configuration
- [PAYMENT_GATEWAY_INTEGRATION.md](../features/PAYMENT_GATEWAY_INTEGRATION.md) - Payment setup details

---

**Last Updated**: 2025-01-06
**Document Version**: 1.0
