# Midas Portal - Insurance Management System

> A comprehensive insurance brokerage management platform built with Laravel 10

[![Laravel](https://img.shields.io/badge/Laravel-10.49-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2-blue.svg)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-yellow.svg)]()

---

## 🎯 Overview

Midas Portal is an enterprise-grade insurance management system designed for insurance brokers and agencies. It provides comprehensive tools for customer management, policy lifecycle tracking, quotation comparison, claims processing, and multi-channel customer communication.

### Key Features

- 📋 **Customer Management**: Complete customer lifecycle with family group support
- 🏢 **Policy Management**: Track vehicle, life, and health insurance policies
- 💰 **Quotation System**: Multi-company quote comparison with PDF generation
- 🔔 **Claims Processing**: End-to-end claims workflow with document tracking
- 📧 **Multi-Channel Notifications**: Email, WhatsApp, SMS, and Push notifications
- 🔐 **Advanced Security**: 2FA, device tracking, audit logs, and CSP headers
- 📊 **Reporting & Analytics**: Commission tracking and performance dashboards
- 🌍 **Multi-Portal**: Separate admin and customer portals

---

## 📚 Documentation

### Quick Links

| Document | Description |
|----------|-------------|
| **[📋 Documentation Index](claudedocs/DOCUMENTATION_INDEX.md)** | Central index for all documentation - start here! |
| **[📖 Complete Project Documentation](claudedocs/PROJECT_INDEX.md)** | Comprehensive reference (2,560 lines) covering architecture, database schema, API endpoints, security implementation, services layer, deployment guides, and development workflows |
| **[🎯 Lead Management System](claudedocs/LEAD_MANAGEMENT_COMPLETE.md)** | Complete lead management module documentation with features, API endpoints, and setup guide |

---

## 🚀 Quick Start

### Prerequisites

- PHP 8.2+
- Composer 2.x
- Node.js 18+
- MySQL 8.0+
- Redis (recommended)

### Installation

```bash
# Clone repository
git clone <repository-url> midas-portal
cd midas-portal

# Install dependencies
composer install
npm install

# Environment setup
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate --seed

# Start development server
php artisan serve
npm run dev
```

**Access**:
- Admin Panel: http://localhost:8000
- Customer Portal: http://localhost:8000/customer/login

**Default Credentials**: Set up during seeding (check `database/seeders/`)

---

## 🏗️ System Architecture

```
┌────────────────────────────────────────────────┐
│         PRESENTATION LAYER                     │
│  Admin Panel | Customer Portal | API Endpoints │
└────────────────────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────┐
│       APPLICATION LAYER (Services)             │
│  Business Logic | Notifications | Security     │
└────────────────────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────┐
│        DOMAIN LAYER (Models + Events)          │
│   Eloquent Models | Domain Events              │
└────────────────────────────────────────────────┘
                     ↓
┌────────────────────────────────────────────────┐
│   INFRASTRUCTURE (Repositories + External)     │
│  Data Access | WhatsApp | SMS | Email          │
└────────────────────────────────────────────────┘
```

**Pattern**: Layered Architecture + Repository Pattern + Event-Driven

---

## 🎨 Technology Stack

### Backend
- **Framework**: Laravel 10.49.1
- **Language**: PHP 8.2.12
- **Database**: MySQL 8.4
- **Cache**: Redis
- **Queue**: Redis Queue

### Frontend
- **Build Tool**: Laravel Mix
- **CSS**: Bootstrap 5.3.2
- **JavaScript**: jQuery 3.7.1
- **Charts**: Chart.js

### Security
- **Authentication**: Laravel Sanctum
- **Authorization**: Spatie Permission
- **2FA**: TOTP (Google Authenticator)
- **Bot Protection**: Cloudflare Turnstile
- **Security Headers**: Custom CSP Implementation

### Testing
- **Framework**: Pest PHP 2.36
- **E2E**: Playwright
- **Coverage**: PHPUnit 10.5

### Quality Tools
- **Formatter**: Laravel Pint (PSR-12)
- **Static Analysis**: PHPStan
- **Refactoring**: Rector

---

## 📦 Core Modules

### 1. Lead Management (NEW! ✨)
- Lead capture and tracking (auto lead number generation)
- Activity timeline with 8 activity types
- Lead-to-customer conversion workflow
- Follow-up reminders and scheduling
- Document attachments
- Bulk operations (assign, convert)
- Analytics dashboard with conversion metrics

### 2. Customer Management
- Customer registration with email verification
- Family group management (shared policy viewing)
- Document upload (PAN, Aadhar, GST)
- Birthday/Anniversary tracking
- Notification preferences

### 3. Insurance Policy Management
- Policy issuance and renewal tracking
- Premium calculation with GST
- Commission breakdown (own, transfer, reference)
- NCB management
- Expiry reminders

### 4. Quotation System
- Multi-company quote comparison
- Vehicle details capture
- IDV calculation
- Addon coverage selection
- PDF generation and WhatsApp sharing

### 5. Claims Management
- Claim registration and tracking
- Document collection and verification
- Stage-based workflow
- Liability assessment
- Settlement processing

### 6. Notification System
- **Channels**: Email, WhatsApp, SMS, Push
- **Features**: Template management, variable substitution, delivery tracking
- **Retry Logic**: Exponential backoff (1h, 4h, 24h)
- **Webhooks**: Real-time delivery status updates

---

## 🔒 Security Features

- ✅ Two-Factor Authentication (TOTP)
- ✅ Device Fingerprinting & Trust Management
- ✅ Content Security Policy (CSP) Headers
- ✅ Comprehensive Audit Logging
- ✅ Rate Limiting
- ✅ Secure File Upload (MIME + Header Validation)
- ✅ Password Reset with Token Expiry
- ✅ Session Security with Device Tracking
- ✅ SQL Injection Prevention (Parameterized Queries)
- ✅ XSS Protection (Blade Escaping + CSP)

---

## 📊 Database Schema

### Core Tables (65+)

**Primary Entities**:
- `leads` → Lead management (NEW!)
- `customers` → Customer master data
- `customer_insurances` → Insurance policies
- `quotations` → Quote requests
- `quotation_companies` → Multi-company quotes
- `claims` → Insurance claims
- `notification_logs` → Notification history

**Lead Management**:
- `leads` → Lead master data with 30+ columns
- `lead_sources` → Lead source master
- `lead_statuses` → Workflow statuses
- `lead_activities` → Activity tracking
- `lead_documents` → Document attachments

**Security & Audit**:
- `two_factor_auth` → 2FA records
- `device_tracking` → Device fingerprints
- `audit_logs` → Comprehensive audit trail
- `security_events` → Security monitoring

**Master Data**:
- `branches`, `brokers`, `insurance_companies`
- `policy_types`, `premium_types`, `fuel_types`
- `notification_templates`, `notification_types`

**Key Relationships**:
```
Lead
  ├── belongsTo: LeadSource, LeadStatus
  ├── belongsTo: User (assigned_to)
  ├── belongsTo: Customer (converted_customer_id)
  ├── hasMany: LeadActivity, LeadDocument
  └── conversion → Customer

Customer
  ├── hasMany: CustomerInsurance
  ├── hasMany: Quotation
  ├── hasMany: Claim
  ├── belongsTo: FamilyGroup
  └── hasMany: NotificationLog

CustomerInsurance
  ├── belongsTo: Customer
  ├── belongsTo: InsuranceCompany
  ├── belongsTo: Branch
  └── hasMany: Claim
```

---

## 🛠️ Development

### Useful Commands

```bash
# Development
composer fix              # Format code (Pint)
composer analyze          # Static analysis (PHPStan)
composer refactor         # Check refactoring opportunities

# Testing
php artisan test          # Run all tests
composer test:notifications  # Test notification system

# Database
php artisan migrate:fresh --seed  # Fresh database
php artisan migrate:rollback      # Rollback migration

# Queue
php artisan queue:work    # Process jobs
php artisan queue:failed  # List failed jobs

# Cache
php artisan optimize:clear  # Clear all caches
```

### Coding Standards

- **Style**: PSR-12 (enforced by Laravel Pint)
- **Static Analysis**: PHPStan Level 5
- **Testing**: Pest PHP with 80%+ coverage goal
- **Documentation**: PHPDoc for all public methods

---

## 📈 Performance

### Optimizations Implemented

- ✅ Eager loading to prevent N+1 queries
- ✅ Database indexing on frequently queried columns
- ✅ Redis caching for sessions and cache
- ✅ Queue-based processing for notifications
- ✅ Asset compilation and minification
- ✅ OPcache enabled in production

### Recommended Enhancements

- [ ] Implement Redis for full-page caching
- [ ] Add CDN for static assets
- [ ] Enable HTTP/2 and Brotli compression
- [ ] Database query result caching
- [ ] Lazy loading for images

---

## 🚀 Deployment

### Production Checklist

- [ ] Set `APP_ENV=production` and `APP_DEBUG=false`
- [ ] Configure SSL/TLS certificate
- [ ] Enable security headers (`CSP_ENABLED=true`)
- [ ] Set up automated backups
- [ ] Configure queue workers (Supervisor)
- [ ] Enable Redis for caching
- [ ] Set up monitoring and alerts
- [ ] Test disaster recovery procedure

**See**: [Complete Documentation](claudedocs/PROJECT_INDEX.md#deployment--operations) for detailed deployment instructions

---

## 📝 API Endpoints

### Authentication
```http
POST /login                    # Admin login
POST /customer/login           # Customer login
POST /customer/logout          # Customer logout
```

### Lead Management (NEW!)
```http
GET    /leads                    # List leads
POST   /leads/store              # Create lead
GET    /leads/show/{id}          # View lead details
POST   /leads/{id}/convert-auto  # Auto-convert to customer
POST   /leads/bulk-convert       # Bulk conversion
GET    /leads/dashboard          # Analytics dashboard
```

### Customer Management
```http
GET    /customers              # List customers
POST   /customers/store        # Create customer
PUT    /customers/update/{id}  # Update customer
GET    /customers/export       # Export to Excel
```

### Insurance Policies
```http
GET    /customer_insurances    # List policies
POST   /customer_insurances/store              # Create policy
GET    /customer_insurances/renew/{id}         # Renew policy
POST   /customer_insurances/sendWADocument     # Send via WhatsApp
```

### Quotations
```http
GET    /quotations             # List quotations
POST   /quotations/store       # Create quotation
POST   /quotations/generate-quotes/{id}        # Generate quotes
GET    /quotations/download-pdf/{id}           # Download PDF
```

### Notifications
```http
GET    /notification-templates # List templates
POST   /notification-templates/send-test       # Test notification
GET    /admin/notification-logs                # Notification history
POST   /admin/notification-logs/{id}/resend    # Retry failed
```

**See**: [Complete Documentation](claudedocs/PROJECT_INDEX.md#api-endpoints) for complete API reference (340+ routes documented including lead management)

---

## 🧪 Testing

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --testsuite=Unit
php artisan test --testsuite=Feature

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/Controllers/CustomerControllerTest.php

# Run Playwright E2E tests
npm run test
npm run test:headed
```

**Current Coverage**: Unit (15 files), Feature (12 files), Integration (8 files)

---

## 📞 Support & Contribution

### Getting Help

- **Documentation**: See links at the top of this README
- **Issues**: Report bugs via GitHub Issues
- **Questions**: Contact the development team

### Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit changes (`git commit -m 'feat(module): add amazing feature'`)
4. Push to branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

**Code Review Process**:
- All PRs require at least one approval
- Must pass CI/CD checks (tests, linting)
- Must follow coding standards
- Must include tests for new features

---

## 📄 License

Proprietary - All Rights Reserved

---

## 🙏 Acknowledgments

- Laravel Framework
- Spatie Packages (Permission, Activity Log)
- Laravel Community

---

## 📞 Contact

**Development Team**: WebMonks Development Team
**Organization**: WebMonks
**Support Email**: support@webmonks.in

---

**Built with ❤️ using Laravel**
