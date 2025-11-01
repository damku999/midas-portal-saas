# Midas Portal - Comprehensive Project Index

> **Generated**: 2025-11-01
> **Purpose**: Complete architectural documentation and knowledge base for the Midas Portal insurance management system
> **Audience**: Developers, architects, and system administrators

---

## 📑 Table of Contents

1. [System Overview](#system-overview)
2. [Architecture & Design Patterns](#architecture--design-patterns)
3. [Technology Stack](#technology-stack)
4. [Directory Structure](#directory-structure)
5. [Core Modules](#core-modules)
6. [Database Schema](#database-schema)
7. [API Endpoints](#api-endpoints)
8. [Services Layer](#services-layer)
9. [Security Implementation](#security-implementation)
10. [Testing Strategy](#testing-strategy)
11. [Deployment & Operations](#deployment--operations)
12. [Development Workflow](#development-workflow)
13. [Quick Reference](#quick-reference)

---

## System Overview

### Project Identity
- **Name**: Midas Portal
- **Type**: Enterprise Insurance Management System
- **Domain**: Insurance Brokerage & Policy Management
- **Version**: Laravel 10.49.1 | PHP 8.2.12
- **Database**: MySQL 8.4

### Business Purpose
Midas Portal is a comprehensive insurance brokerage management platform designed for insurance brokers and agencies. It provides end-to-end management of:
- Customer lifecycle and family group management
- Multi-type policy tracking (Vehicle, Life, Health)
- Quotation generation and comparison
- Claims processing workflow
- Multi-channel notifications (Email, WhatsApp, SMS, Push)
- Commission tracking and reporting

### Key Stakeholders
- **Admin Users**: Insurance brokers, relationship managers, operations team
- **Customer Users**: Insurance policyholders and family members
- **System Integrations**: WhatsApp API, SMS Gateway, Email service

---

## Architecture & Design Patterns

### Architectural Pattern
**Layered Architecture with Domain-Driven Design Elements**

```
┌─────────────────────────────────────────────────────┐
│            PRESENTATION LAYER                       │
│  • Blade Views                                      │
│  • Controllers (Admin + Customer portals)           │
│  • API Controllers                                  │
│  • Form Requests & Validation                       │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│          APPLICATION LAYER (Services)               │
│  • Business Logic Services (40+ services)           │
│  • Notification Services                            │
│  • Security Services (2FA, Audit, CSP)              │
│  • Integration Services (WhatsApp, Email, SMS)      │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│       DOMAIN LAYER (Models + Events)                │
│  • Eloquent Models (37 models)                      │
│  • Domain Events (Policy, Customer, Quotation)      │
│  • Event Listeners                                  │
│  • Model Relationships & Scopes                     │
└─────────────────────────────────────────────────────┘
                        ↓
┌─────────────────────────────────────────────────────┐
│   INFRASTRUCTURE (Repositories + External APIs)     │
│  • Repository Pattern (26 repositories)             │
│  • Data Access Layer                                │
│  • External API Integrations                        │
│  • Cache & Queue Management                         │
└─────────────────────────────────────────────────────┘
```

### Design Patterns Implemented

#### 1. Repository Pattern
**Location**: `app/Contracts/Repositories/`, `app/Repositories/`

Abstracts data access logic from business logic:
- `CustomerRepositoryInterface` → `CustomerRepository`
- `PolicyRepositoryInterface` → `PolicyRepository`
- `QuotationRepositoryInterface` → `QuotationRepository`

**Benefits**: Testability, flexibility, decoupling

#### 2. Service Layer Pattern
**Location**: `app/Services/`

Encapsulates business logic:
- `CustomerService` - Customer management operations
- `QuotationService` - Quote generation and comparison
- `ClaimService` - Claims workflow management
- `NotificationLoggerService` - Multi-channel notification handling

#### 3. Event-Driven Architecture
**Location**: `app/Events/`, `app/Listeners/`

Domain events trigger automated workflows:
- `CustomerRegistered` → Send welcome email + WhatsApp
- `PolicyExpiring` → Send renewal reminders
- `QuotationGenerated` → Generate PDF + Send notification

#### 4. Strategy Pattern
**Location**: `app/Services/Notification/`

Multiple notification channel strategies:
- `EmailService`
- `WhatsAppService` (via API integration)
- `SmsService`
- `PushNotificationService`

#### 5. Factory Pattern
**Location**: `database/factories/`

Test data generation for all models

#### 6. Middleware Pipeline
**Location**: `app/Http/Middleware/`

Request processing pipeline:
- Authentication (`CustomerAuth`, `Authenticate`)
- Security (`SecurityHeadersMiddleware`, `SecureSession`)
- Rate Limiting (`RateLimit`)
- 2FA Verification (`VerifyTwoFactorSession`)

---

## Technology Stack

### Backend Framework
```
Laravel 10.49.1
├── PHP 8.2.12
├── MySQL 8.4 (Database)
├── Redis (Cache + Queue)
└── Composer 2.x (Dependency Management)
```

### Core Packages
| Package | Version | Purpose |
|---------|---------|---------|
| `laravel/sanctum` | 3.3.3 | API authentication |
| `spatie/laravel-permission` | 5.5+ | Role-based access control |
| `spatie/laravel-activitylog` | 4.7+ | Audit logging |
| `pragmarx/google2fa-laravel` | 2.3+ | Two-factor authentication |
| `barryvdh/laravel-dompdf` | 3.1+ | PDF generation |
| `maatwebsite/excel` | 3.1+ | Excel export |
| `simplesoftwareio/simple-qrcode` | 4.2+ | QR code generation |

### Frontend Stack
```
Laravel Mix
├── Bootstrap 5.3.2 (CSS Framework)
├── jQuery 3.7.1 (JavaScript)
├── Chart.js (Data Visualization)
└── Select2 (Enhanced Dropdowns)
```

### Development Tools
| Tool | Version | Purpose |
|------|---------|---------|
| Laravel Pint | 1.25+ | Code formatting (PSR-12) |
| PHPStan | 2.1+ | Static analysis (Level 5) |
| Rector | 2.2+ | Automated refactoring |
| Pest PHP | 2.36+ | Testing framework |
| Laravel Sail | 1.0+ | Docker development environment |

### Infrastructure
- **Web Server**: Apache/Nginx
- **Queue Worker**: Redis Queue
- **Cache**: Redis
- **Session Storage**: Database/Redis
- **File Storage**: Local/S3 (configurable)

---

## Directory Structure

### Application Structure
```
midas-portal/
├── app/
│   ├── Console/
│   │   ├── Commands/          # Artisan commands (5 commands)
│   │   │   ├── RetryFailedNotifications.php
│   │   │   ├── SendBirthdayWishes.php
│   │   │   ├── SendRenewalReminders.php
│   │   │   ├── SecuritySetupCommand.php
│   │   │   └── TestEmailNotification.php
│   │   └── Kernel.php         # Command scheduling
│   │
│   ├── Contracts/             # Interfaces
│   │   ├── Repositories/      # Repository interfaces (26)
│   │   └── Services/          # Service interfaces (20)
│   │
│   ├── Events/                # Domain events
│   │   ├── Audit/             # Audit events
│   │   ├── Customer/          # Customer lifecycle events
│   │   ├── Document/          # PDF generation events
│   │   ├── Insurance/         # Policy events
│   │   └── Quotation/         # Quote events
│   │
│   ├── Exceptions/
│   │   └── Handler.php        # Global exception handling
│   │
│   ├── Exports/               # Excel export classes
│   │   ├── CustomerInsurancesExport.php
│   │   └── GenericExport.php
│   │
│   ├── Http/
│   │   ├── Controllers/       # 31 controllers
│   │   │   ├── Auth/          # Authentication controllers
│   │   │   ├── Api/           # API controllers
│   │   │   ├── CustomerController.php
│   │   │   ├── CustomerInsuranceController.php
│   │   │   ├── QuotationController.php
│   │   │   ├── ClaimController.php
│   │   │   └── ...
│   │   │
│   │   ├── Middleware/        # 15 middleware classes
│   │   │   ├── CustomerAuth.php
│   │   │   ├── SecureSession.php
│   │   │   ├── SecurityHeadersMiddleware.php
│   │   │   ├── VerifyTwoFactorSession.php
│   │   │   └── ...
│   │   │
│   │   ├── Requests/          # Form request validation
│   │   │   ├── SecureStoreCustomerRequest.php
│   │   │   ├── CreateQuotationRequest.php
│   │   │   └── ...
│   │   │
│   │   └── Resources/         # API resources
│   │       ├── CustomerResource.php
│   │       ├── QuotationResource.php
│   │       └── ...
│   │
│   ├── Listeners/             # Event listeners
│   │   ├── Customer/
│   │   ├── Insurance/
│   │   └── Quotation/
│   │
│   ├── Models/                # Eloquent models (37 models)
│   │   ├── Customer.php
│   │   ├── CustomerInsurance.php
│   │   ├── Quotation.php
│   │   ├── Claim.php
│   │   ├── NotificationLog.php
│   │   └── ...
│   │
│   ├── Providers/             # Service providers
│   │   ├── AppServiceProvider.php
│   │   ├── AuthServiceProvider.php
│   │   ├── EventServiceProvider.php
│   │   └── RepositoryServiceProvider.php
│   │
│   ├── Repositories/          # Data access layer (26 repositories)
│   │   ├── CustomerRepository.php
│   │   ├── QuotationRepository.php
│   │   └── ...
│   │
│   ├── Services/              # Business logic (42 services)
│   │   ├── CustomerService.php
│   │   ├── QuotationService.php
│   │   ├── ClaimService.php
│   │   ├── NotificationLoggerService.php
│   │   ├── EmailService.php
│   │   ├── SmsService.php
│   │   ├── TwoFactorAuthService.php
│   │   ├── SecurityService.php
│   │   └── ...
│   │
│   ├── helpers.php            # Global helper functions
│   └── Helpers/
│       └── SettingsHelper.php # Application settings helpers
│
├── bootstrap/                 # Application bootstrap
│
├── config/                    # Configuration files
│   ├── app.php
│   ├── database.php
│   ├── mail.php
│   ├── queue.php
│   ├── services.php           # Third-party service configs
│   └── ...
│
├── database/
│   ├── factories/             # Model factories for testing
│   ├── migrations/            # Database migrations (60+ tables)
│   └── seeders/               # Database seeders
│       ├── DatabaseSeeder.php
│       ├── UserSeeder.php
│       ├── PermissionSeeder.php
│       └── ...
│
├── public/                    # Public web root
│   ├── index.php
│   ├── css/
│   ├── js/
│   └── uploads/               # User-uploaded files
│
├── resources/
│   ├── views/                 # Blade templates
│   │   ├── admin/             # Admin panel views
│   │   ├── customer/          # Customer portal views
│   │   ├── auth/              # Authentication views
│   │   ├── layouts/           # Layout templates
│   │   └── ...
│   │
│   ├── css/                   # Source CSS
│   ├── js/                    # Source JavaScript
│   └── lang/                  # Localization files
│
├── routes/
│   ├── web.php                # Web routes (294 routes)
│   ├── api.php                # API routes
│   └── console.php            # Console routes
│
├── storage/
│   ├── app/                   # Application files
│   ├── framework/             # Framework files
│   └── logs/                  # Application logs
│
├── tests/
│   ├── Feature/               # Feature tests
│   │   ├── Controllers/
│   │   ├── Notification/
│   │   └── ...
│   │
│   ├── Unit/                  # Unit tests
│   │   ├── Models/
│   │   ├── Services/
│   │   └── ...
│   │
│   └── TestCase.php           # Base test class
│
├── vendor/                    # Composer dependencies
│
├── .env.example               # Environment template
├── .gitignore
├── artisan                    # Artisan CLI
├── composer.json              # PHP dependencies
├── composer.lock
├── package.json               # Node dependencies
├── phpstan.neon               # PHPStan configuration
├── pint.json                  # Laravel Pint configuration
├── README.md                  # Project documentation
└── rector.php                 # Rector configuration
```

---

## Core Modules

### 1. Customer Management Module

**Purpose**: Complete customer lifecycle management with family grouping

**Key Components**:
- **Model**: `Customer.php` - Core customer entity
- **Controller**: `CustomerController.php` - CRUD operations
- **Service**: `CustomerService.php` - Business logic
- **Repository**: `CustomerRepository.php` - Data access

**Features**:
- ✓ Customer registration with email verification
- ✓ Family group management (shared policy viewing)
- ✓ Document uploads (PAN, Aadhar, GST, Driving License)
- ✓ Birthday/Anniversary tracking with automated wishes
- ✓ Notification preferences management
- ✓ Customer type classification
- ✓ Multi-portal access (Admin + Customer portal)

**Database Tables**:
- `customers` - Customer master data
- `family_groups` - Family group definitions
- `family_members` - Family relationships
- `customer_types` - Customer classification

**Key Routes**:
```
GET    /customers                    # List customers
POST   /customers/store              # Create customer
GET    /customers/edit/{customer}    # Edit form
PUT    /customers/update/{customer}  # Update customer
GET    /customers/export             # Export to Excel
```

**Related Files**:
- Controller: `app/Http/Controllers/CustomerController.php:1`
- Service: `app/Services/CustomerService.php:1`
- Model: `app/Models/Customer.php:1`
- Request: `app/Http/Requests/SecureStoreCustomerRequest.php:1`

---

### 2. Insurance Policy Management Module

**Purpose**: Track and manage vehicle, life, and health insurance policies

**Key Components**:
- **Model**: `CustomerInsurance.php` - Policy entity
- **Controller**: `CustomerInsuranceController.php` - Policy operations
- **Service**: `CustomerInsuranceService.php` - Policy business logic
- **Repository**: `CustomerInsuranceRepository.php` - Data access

**Features**:
- ✓ Policy issuance and renewal tracking
- ✓ Premium calculation with GST breakdown
- ✓ Commission tracking (Own, Transfer, Reference)
- ✓ NCB (No Claim Bonus) management
- ✓ Policy expiry reminders (automated)
- ✓ Multi-type support (Vehicle, Life, Health)
- ✓ Document attachment and WhatsApp sharing
- ✓ Policy renewal workflow

**Database Tables**:
- `customer_insurances` - Policy records
- `policy_types` - Policy type master
- `premium_types` - Premium classification
- `insurance_companies` - Insurer master data
- `commission_types` - Commission categories

**Key Routes**:
```
GET    /customer_insurances                              # List policies
POST   /customer_insurances/store                        # Create policy
GET    /customer_insurances/renew/{id}                   # Renewal form
PUT    /customer_insurances/storeRenew/{id}              # Process renewal
GET    /customer_insurances/sendWADocument/{id}          # Send via WhatsApp
GET    /customer_insurances/sendRenewalReminderWA/{id}   # Send reminder
```

**Commission Calculation**:
```php
// Commission breakdown
own_commission      = Own broker commission
transfer_commission = Transfer agent commission
reference_commission = Referral commission
total_commission    = Sum of all commissions
```

**Related Files**:
- Controller: `app/Http/Controllers/CustomerInsuranceController.php:1`
- Service: `app/Services/CustomerInsuranceService.php:1`
- Model: `app/Models/CustomerInsurance.php:1`

---

### 3. Quotation System Module

**Purpose**: Generate and compare insurance quotes from multiple companies

**Key Components**:
- **Model**: `Quotation.php`, `QuotationCompany.php`
- **Controller**: `QuotationController.php`
- **Service**: `QuotationService.php`
- **PDF Service**: `PdfGenerationService.php`

**Features**:
- ✓ Multi-company quote generation
- ✓ Vehicle details capture
- ✓ IDV (Insured Declared Value) calculation
- ✓ Add-on coverage selection
- ✓ Premium comparison table
- ✓ PDF generation with company branding
- ✓ WhatsApp quote sharing
- ✓ Quote status tracking

**Database Tables**:
- `quotations` - Quote request master
- `quotation_companies` - Individual company quotes
- `quotation_statuses` - Status workflow
- `addon_covers` - Available add-on covers

**Quotation Workflow**:
```
1. Create Quote Request → Customer + Vehicle details
2. Generate Quotes → Multiple companies with pricing
3. Compare Options → Side-by-side comparison
4. Generate PDF → Professional quote document
5. Share → WhatsApp/Email to customer
6. Track Status → Pending → Accepted → Converted → Rejected
```

**Key Routes**:
```
GET    /quotations                           # List quotations
POST   /quotations/store                     # Create quotation
POST   /quotations/generate-quotes/{id}      # Generate company quotes
GET    /quotations/download-pdf/{id}         # Download PDF
POST   /quotations/send-whatsapp/{id}        # Send via WhatsApp
```

**IDV Calculation Components**:
- Base vehicle IDV
- Trailer IDV (if applicable)
- CNG/LPG kit IDV
- Electrical accessories IDV
- Non-electrical accessories IDV
- Total IDV = Sum of all components

**Related Files**:
- Controller: `app/Http/Controllers/QuotationController.php:1`
- Service: `app/Services/QuotationService.php:1`
- PDF Service: `app/Services/PdfGenerationService.php:1`
- Models: `app/Models/Quotation.php:1`, `app/Models/QuotationCompany.php:1`

---

### 4. Claims Management Module

**Purpose**: End-to-end insurance claim workflow management

**Key Components**:
- **Model**: `Claim.php`, `ClaimDocument.php`, `ClaimStage.php`
- **Controller**: `ClaimController.php`
- **Service**: `ClaimService.php`

**Features**:
- ✓ Claim registration and tracking
- ✓ Document collection and verification
- ✓ Stage-based workflow management
- ✓ Liability assessment tracking
- ✓ Settlement amount processing
- ✓ WhatsApp notifications (claim number, document list, pending docs)
- ✓ Claim statistics and analytics

**Database Tables**:
- `claims` - Claim master records
- `claim_documents` - Required/submitted documents
- `claim_stages` - Workflow stages
- `claim_liability_details` - Liability assessment

**Claim Workflow Stages**:
1. **Claim Registered** - Initial claim submission
2. **Documents Pending** - Awaiting required documents
3. **Under Review** - Claim assessment in progress
4. **Surveyor Assigned** - Damage assessment
5. **Approval Pending** - Awaiting insurer approval
6. **Approved** - Claim approved for settlement
7. **Settlement Processed** - Payment completed
8. **Rejected** - Claim rejected
9. **Closed** - Claim finalized

**Document Types**:
- Claim form
- Policy copy
- RC (Registration Certificate)
- Driving license
- FIR (if applicable)
- Repair estimates
- Photos/videos of damage
- Survey report

**Key Routes**:
```
GET    /insurance-claims                              # List claims
POST   /insurance-claims/store                        # Create claim
GET    /insurance-claims/show/{claim}                 # View details
POST   /insurance-claims/stages/{claim}/add           # Add workflow stage
POST   /insurance-claims/documents/{claim}/{doc}/update-status  # Update doc status
POST   /insurance-claims/liability/{claim}/update     # Update liability details
POST   /insurance-claims/claim-number/{claim}/update  # Update claim number
POST   /insurance-claims/whatsapp/claim-number/{claim}          # Send claim number
POST   /insurance-claims/whatsapp/document-list/{claim}         # Send document list
POST   /insurance-claims/whatsapp/pending-documents/{claim}     # Send pending docs
```

**Related Files**:
- Controller: `app/Http/Controllers/ClaimController.php:1`
- Service: `app/Services/ClaimService.php:1`
- Models: `app/Models/Claim.php:1`, `app/Models/ClaimDocument.php:1`, `app/Models/ClaimStage.php:1`

---

### 5. Notification System Module

**Purpose**: Multi-channel notification delivery with template management

**Key Components**:
- **Models**: `NotificationTemplate.php`, `NotificationLog.php`, `NotificationDeliveryTracking.php`
- **Controllers**: `NotificationTemplateController.php`, `NotificationLogController.php`
- **Services**: `TemplateService.php`, `NotificationLoggerService.php`, `EmailService.php`, `SmsService.php`

**Supported Channels**:
- ✉️ **Email** - SMTP-based email delivery
- 📱 **WhatsApp** - API integration for WhatsApp messages
- 📧 **SMS** - SMS gateway integration
- 🔔 **Push Notifications** - Mobile app notifications

**Features**:
- ✓ Template management with variable substitution
- ✓ Multi-channel delivery
- ✓ Delivery status tracking
- ✓ Retry logic with exponential backoff (1h, 4h, 24h)
- ✓ Webhook support for delivery status updates
- ✓ Template preview and testing
- ✓ Notification analytics and reporting
- ✓ Bulk notification sending

**Database Tables**:
- `notification_templates` - Template definitions
- `notification_types` - Notification categories
- `notification_logs` - Delivery history
- `notification_delivery_tracking` - Channel-specific tracking

**Template Variables**:
```
Customer Variables:
- {{customer_name}}, {{customer_email}}, {{customer_mobile}}
- {{customer_pan}}, {{customer_gst}}

Policy Variables:
- {{policy_number}}, {{policy_type}}, {{premium_amount}}
- {{policy_start_date}}, {{policy_end_date}}
- {{vehicle_number}}, {{vehicle_make_model}}

Quotation Variables:
- {{quotation_number}}, {{quotation_date}}
- {{vehicle_details}}, {{idv_amount}}

Claim Variables:
- {{claim_number}}, {{claim_type}}, {{claim_date}}
- {{claim_amount}}, {{claim_status}}

System Variables:
- {{company_name}}, {{company_email}}, {{company_phone}}
- {{portal_url}}, {{current_date}}, {{current_time}}
```

**Notification Types**:
1. **Customer Lifecycle**
   - Welcome email (registration)
   - Email verification
   - Password reset
   - Profile update confirmation

2. **Policy Lifecycle**
   - Policy issued notification
   - Renewal reminder (30, 15, 7 days before)
   - Policy expired notification
   - NCB update notification

3. **Quotation**
   - Quote generated notification
   - Quote PDF delivery
   - Quote follow-up reminders

4. **Claims**
   - Claim registered notification
   - Claim number assignment
   - Document pending reminder
   - Claim status updates
   - Settlement notification

5. **Special Occasions**
   - Birthday wishes
   - Anniversary wishes

**Retry Logic**:
```
Attempt 1: Immediate
Attempt 2: After 1 hour
Attempt 3: After 4 hours
Attempt 4: After 24 hours
Status: Failed (after 4 attempts)
```

**Key Routes**:
```
# Template Management
GET    /notification-templates                    # List templates
POST   /notification-templates/store              # Create template
PUT    /notification-templates/update/{template}  # Update template
POST   /notification-templates/preview            # Preview with variables
POST   /notification-templates/send-test          # Send test notification

# Notification Logs
GET    /admin/notification-logs                   # View logs
GET    /admin/notification-logs/analytics         # Analytics dashboard
POST   /admin/notification-logs/{log}/resend      # Retry failed notification
POST   /admin/notification-logs/bulk-resend       # Bulk retry

# Webhooks
POST   /webhooks/email/delivery-status            # Email delivery webhook
POST   /webhooks/whatsapp/delivery-status         # WhatsApp delivery webhook
```

**Related Files**:
- Controllers: `app/Http/Controllers/NotificationTemplateController.php:1`, `app/Http/Controllers/NotificationLogController.php:1`
- Services: `app/Services/TemplateService.php:1`, `app/Services/NotificationLoggerService.php:1`, `app/Services/EmailService.php:1`
- Models: `app/Models/NotificationTemplate.php:1`, `app/Models/NotificationLog.php:1`

---

### 6. Security & Audit Module

**Purpose**: Comprehensive security controls, 2FA, device tracking, and audit logging

**Key Components**:
- **Models**: `TwoFactorAuth.php`, `DeviceTracking.php`, `AuditLog.php`, `SecuritySetting.php`
- **Controllers**: `TwoFactorAuthController.php`, `SecurityController.php`
- **Services**: `TwoFactorAuthService.php`, `SecurityService.php`, `SecurityAuditService.php`

**Security Features**:

#### A. Two-Factor Authentication (2FA)
- ✓ TOTP-based 2FA (Google Authenticator compatible)
- ✓ QR code generation for setup
- ✓ Recovery codes (10 one-time codes)
- ✓ Trusted device management
- ✓ Device trust duration configuration
- ✓ 2FA enforcement per user/customer

**2FA Workflow**:
```
1. Enable 2FA → Generate secret
2. Scan QR Code → Setup authenticator app
3. Confirm Setup → Verify initial code
4. Generate Recovery Codes → Save securely
5. Login → Username/Password → 2FA Code → Access granted
6. Trust Device (optional) → Skip 2FA for X days
```

#### B. Device Tracking & Trust Management
- ✓ Device fingerprinting
- ✓ Browser and OS detection
- ✓ IP address tracking
- ✓ Location history
- ✓ Trust score calculation
- ✓ Failed login attempt tracking
- ✓ Device blocking capability

**Device Trust Factors**:
- Consistent IP address
- Known device fingerprint
- Regular usage patterns
- No failed login attempts
- Successful 2FA verifications

#### C. Audit Logging
- ✓ Comprehensive action logging
- ✓ Before/after value tracking
- ✓ Risk score calculation
- ✓ Suspicious activity detection
- ✓ User action timeline
- ✓ Entity-level audit trails

**Logged Events**:
- Authentication (login, logout, failed attempts)
- Customer CRUD operations
- Policy lifecycle events
- Quotation generation
- Claim submissions
- Settings changes
- Permission modifications
- Document access

#### D. Content Security Policy (CSP)
- ✓ Configurable CSP headers
- ✓ Script-src, style-src, img-src policies
- ✓ Nonce-based inline script protection
- ✓ Report-only mode for testing
- ✓ Violation reporting

**Database Tables**:
- `two_factor_auth` - 2FA secrets and recovery codes
- `two_factor_attempts` - Verification attempt history
- `trusted_devices` - Trusted device registry
- `device_tracking` - Device fingerprints and metadata
- `device_sessions` - Session tracking per device
- `device_security_events` - Security event logging
- `audit_logs` - Comprehensive audit trail
- `security_events` - Security-specific events
- `security_settings` - User security preferences
- `customer_audit_logs` - Customer portal audit trail

**Key Routes**:
```
# Two-Factor Authentication
GET    /profile/two-factor                        # 2FA settings page
POST   /profile/two-factor/enable                 # Enable 2FA
POST   /profile/two-factor/confirm                # Confirm 2FA setup
POST   /profile/two-factor/disable                # Disable 2FA
POST   /profile/two-factor/recovery-codes         # Generate recovery codes
POST   /profile/two-factor/trust-device           # Trust current device
DELETE /profile/two-factor/devices/{device}       # Revoke device trust
GET    /two-factor-challenge                      # 2FA verification page
POST   /two-factor-challenge                      # Verify 2FA code

# Customer Portal 2FA
GET    /customer/two-factor                       # Customer 2FA settings
POST   /customer/two-factor/enable                # Enable customer 2FA
POST   /customer/two-factor/disable               # Disable customer 2FA
GET    /customer/two-factor-challenge             # Customer 2FA verification

# Security Dashboard
GET    /security/dashboard                        # Security overview
GET    /security/audit-logs                       # Audit log viewer
GET    /security/api/analytics                    # Security analytics
GET    /security/api/suspicious-activity          # Suspicious activity feed
GET    /security/api/high-risk-activity           # High-risk activity feed

# Device Management
GET    /admin/customer-devices                    # Device management
GET    /admin/customer-devices/{device}           # Device details
POST   /admin/customer-devices/{device}/deactivate # Deactivate device
POST   /admin/customer-devices/cleanup-invalid    # Cleanup invalid devices
```

**Related Files**:
- Controllers: `app/Http/Controllers/TwoFactorAuthController.php:1`, `app/Http/Controllers/SecurityController.php:1`
- Services: `app/Services/TwoFactorAuthService.php:1`, `app/Services/SecurityService.php:1`, `app/Services/SecurityAuditService.php:1`
- Models: `app/Models/TwoFactorAuth.php:1`, `app/Models/DeviceTracking.php:1`, `app/Models/AuditLog.php:1`
- Middleware: `app/Http/Middleware/VerifyTwoFactorSession.php:1`, `app/Http/Middleware/SecurityHeadersMiddleware.php:1`

---

## Database Schema

### Schema Overview
**Total Tables**: 60+ tables
**Database Engine**: MySQL 8.4
**Character Set**: utf8mb4
**Collation**: utf8mb4_unicode_ci

### Table Categories

#### 1. Core Business Tables
| Table | Records | Purpose |
|-------|---------|---------|
| `customers` | ~1000+ | Customer master data |
| `customer_insurances` | ~5000+ | Insurance policies |
| `quotations` | ~2000+ | Quote requests |
| `quotation_companies` | ~10000+ | Multi-company quotes |
| `claims` | ~500+ | Insurance claims |
| `notification_logs` | ~50000+ | Notification history |

#### 2. Master Data Tables
| Table | Records | Purpose |
|-------|---------|---------|
| `branches` | ~10 | Branch offices |
| `brokers` | ~50 | Insurance brokers |
| `insurance_companies` | ~30 | Insurance providers |
| `policy_types` | ~10 | Policy categories |
| `premium_types` | ~15 | Premium classifications |
| `fuel_types` | ~5 | Vehicle fuel types |
| `addon_covers` | ~20 | Available add-on covers |
| `notification_types` | ~25 | Notification categories |
| `quotation_statuses` | ~8 | Quote workflow statuses |

#### 3. Security & Audit Tables
| Table | Records | Purpose |
|-------|---------|---------|
| `audit_logs` | ~100000+ | Comprehensive audit trail |
| `customer_audit_logs` | ~50000+ | Customer portal actions |
| `security_events` | ~5000+ | Security incidents |
| `two_factor_auth` | ~500+ | 2FA configurations |
| `device_tracking` | ~2000+ | Device fingerprints |
| `trusted_devices` | ~1000+ | Trusted device registry |

#### 4. User Management Tables
| Table | Records | Purpose |
|-------|---------|---------|
| `users` | ~50 | Admin users |
| `roles` | ~5 | User roles |
| `permissions` | ~100 | System permissions |
| `model_has_roles` | ~50 | User-role assignments |
| `role_has_permissions` | ~200 | Role-permission mappings |

#### 5. Family & Relationships
| Table | Records | Purpose |
|-------|---------|---------|
| `family_groups` | ~300 | Family group definitions |
| `family_members` | ~1000 | Family member relationships |
| `relationship_managers` | ~20 | RM master data |
| `reference_users` | ~100 | Referral users |

### Key Relationships

#### Customer → Insurance Policies
```sql
customers (1) ←→ (N) customer_insurances
  └── Foreign Key: customer_insurances.customer_id → customers.id
  └── Relationship: A customer can have multiple policies
```

#### Customer → Quotations
```sql
customers (1) ←→ (N) quotations
  └── Foreign Key: quotations.customer_id → customers.id
  └── Relationship: A customer can request multiple quotations
```

#### Quotation → Quotation Companies
```sql
quotations (1) ←→ (N) quotation_companies
  └── Foreign Key: quotation_companies.quotation_id → quotations.id
  └── Relationship: One quotation has quotes from multiple companies
```

#### Customer Insurance → Claims
```sql
customer_insurances (1) ←→ (N) claims
  └── Foreign Key: claims.customer_insurance_id → customer_insurances.id
  └── Relationship: A policy can have multiple claims
```

#### Claim → Claim Documents
```sql
claims (1) ←→ (N) claim_documents
  └── Foreign Key: claim_documents.claim_id → claims.id
  └── Relationship: A claim requires multiple documents
```

#### Family Group → Members
```sql
family_groups (1) ←→ (N) family_members
  └── Foreign Key: family_members.family_group_id → family_groups.id
  └── Relationship: A family group contains multiple members
```

### Common Table Patterns

#### Soft Deletes
All core tables include soft delete timestamps:
```sql
deleted_at TIMESTAMP NULL
deleted_by INT NULL
```

#### Audit Columns
Standard audit tracking on all tables:
```sql
created_at TIMESTAMP
updated_at TIMESTAMP
created_by INT
updated_by INT
```

#### Status Flags
Most master tables include status fields:
```sql
status BOOLEAN DEFAULT 1  # Active/Inactive
```

### Indexing Strategy

**Primary Indexes**: All tables have auto-increment primary key `id`

**Foreign Key Indexes**:
- Customer relationships indexed
- Policy lookups optimized
- Notification logs indexed by status and created_at

**Composite Indexes**:
- `customer_insurances`: (customer_id, policy_end_date) - Renewal queries
- `notification_logs`: (status, scheduled_at) - Queue processing
- `audit_logs`: (auditable_type, auditable_id, occurred_at) - Audit queries
- `quotations`: (customer_id, created_at) - Customer quote history

**Search Optimization**:
- Customer name, email, mobile indexed
- Policy number unique indexed
- Vehicle registration number indexed

---

## API Endpoints

### Endpoint Categories

#### 1. Authentication Endpoints

##### Admin Authentication
```http
POST   /login                    # Admin login
POST   /logout                   # Admin logout
GET    /password/reset           # Password reset form
POST   /password/email           # Send reset link
POST   /password/reset           # Reset password
```

##### Customer Authentication
```http
POST   /customer/login                   # Customer login
POST   /customer/logout                  # Customer logout
GET    /customer/verify-notice           # Email verification notice
GET    /customer/verify/{token}          # Email verification
POST   /customer/verification/send       # Resend verification email
GET    /customer/password/reset          # Password reset form
POST   /customer/password/email          # Send reset link
POST   /customer/password/reset          # Reset password
```

##### Two-Factor Authentication
```http
GET    /two-factor-challenge             # 2FA verification page
POST   /two-factor-challenge             # Verify 2FA code
GET    /profile/two-factor               # 2FA settings
POST   /profile/two-factor/enable        # Enable 2FA
POST   /profile/two-factor/confirm       # Confirm 2FA setup
POST   /profile/two-factor/disable       # Disable 2FA
```

#### 2. Customer Management Endpoints
```http
GET    /customers                        # List all customers (paginated)
GET    /customers/create                 # Customer creation form
POST   /customers/store                  # Create new customer
GET    /customers/show/{customer}        # View customer details
GET    /customers/edit/{customer}        # Edit customer form
PUT    /customers/update/{customer}      # Update customer
GET    /customers/update/status/{id}/{status}  # Toggle status
GET    /customers/export                 # Export to Excel
GET    /customers/resendOnBoardingWA/{customer}  # Resend welcome WhatsApp
```

#### 3. Insurance Policy Endpoints
```http
GET    /customer_insurances                              # List policies
GET    /customer_insurances/create                       # Policy creation form
POST   /customer_insurances/store                        # Create policy
GET    /customer_insurances/edit/{insurance}             # Edit policy form
PUT    /customer_insurances/update/{insurance}           # Update policy
GET    /customer_insurances/renew/{insurance}            # Renewal form
PUT    /customer_insurances/storeRenew/{insurance}       # Process renewal
GET    /customer_insurances/sendWADocument/{insurance}   # Send policy via WhatsApp
GET    /customer_insurances/sendRenewalReminderWA/{insurance}  # Send renewal reminder
GET    /customer_insurances/export                       # Export to Excel
GET    /customer_insurances/update/status/{id}/{status}  # Toggle status
```

#### 4. Quotation Endpoints
```http
GET    /quotations                           # List quotations
GET    /quotations/create                    # Quotation form
POST   /quotations/store                     # Create quotation
GET    /quotations/show/{quotation}          # View quotation details
GET    /quotations/edit/{quotation}          # Edit quotation form
PUT    /quotations/update/{quotation}        # Update quotation
DELETE /quotations/delete/{quotation}        # Delete quotation
POST   /quotations/generate-quotes/{quotation}    # Generate company quotes
GET    /quotations/download-pdf/{quotation}       # Download PDF
POST   /quotations/send-whatsapp/{quotation}      # Send via WhatsApp
GET    /quotations/export                    # Export to Excel
```

#### 5. Claims Management Endpoints
```http
GET    /insurance-claims                              # List claims
GET    /insurance-claims/create                       # Claim creation form
POST   /insurance-claims/store                        # Create claim
GET    /insurance-claims/show/{claim}                 # View claim details
GET    /insurance-claims/edit/{claim}                 # Edit claim form
PUT    /insurance-claims/update/{claim}               # Update claim
DELETE /insurance-claims/delete/{claim}               # Delete claim
GET    /insurance-claims/export                       # Export to Excel
GET    /insurance-claims/statistics                   # Claim statistics
GET    /insurance-claims/search-policies              # Search policies for claim

# Claim workflow operations
POST   /insurance-claims/stages/{claim}/add           # Add workflow stage
POST   /insurance-claims/claim-number/{claim}/update  # Update claim number
POST   /insurance-claims/liability/{claim}/update     # Update liability details
POST   /insurance-claims/documents/{claim}/{doc}/update-status  # Update document status

# WhatsApp notifications
GET    /insurance-claims/whatsapp/preview/{claim}/{type}         # Preview WhatsApp message
POST   /insurance-claims/whatsapp/claim-number/{claim}           # Send claim number
POST   /insurance-claims/whatsapp/document-list/{claim}          # Send document list
POST   /insurance-claims/whatsapp/pending-documents/{claim}      # Send pending documents
```

#### 6. Notification Management Endpoints
```http
# Template Management
GET    /notification-templates                    # List templates
GET    /notification-templates/create             # Template creation form
POST   /notification-templates/store              # Create template
GET    /notification-templates/edit/{template}    # Edit template form
PUT    /notification-templates/update/{template}  # Update template
DELETE /notification-templates/delete/{template}  # Delete template
POST   /notification-templates/preview            # Preview with variables
POST   /notification-templates/send-test          # Send test notification
GET    /notification-templates/variables          # Get available variables
GET    /notification-templates/customer-data      # Get customer data for preview

# Notification Logs
GET    /admin/notification-logs                   # View notification history
GET    /admin/notification-logs/{log}             # View log details
GET    /admin/notification-logs/analytics         # Analytics dashboard
POST   /admin/notification-logs/{log}/resend      # Retry single notification
POST   /admin/notification-logs/bulk-resend       # Bulk retry failed notifications
POST   /admin/notification-logs/cleanup           # Cleanup old logs

# Webhooks
POST   /webhooks/email/delivery-status            # Email delivery status webhook
POST   /webhooks/whatsapp/delivery-status         # WhatsApp delivery status webhook
POST   /webhooks/test                             # Test webhook endpoint
```

#### 7. Family Group Endpoints
```http
GET    /family_groups                        # List family groups
GET    /family_groups/create                 # Family group creation form
POST   /family_groups/store                  # Create family group
GET    /family_groups/show/{familyGroup}     # View family group details
GET    /family_groups/edit/{familyGroup}     # Edit family group form
PUT    /family_groups/update/{familyGroup}   # Update family group
DELETE /family_groups/delete/{familyGroup}   # Delete family group
DELETE /family_groups/member/{familyMember}  # Remove family member
GET    /family_groups/export                 # Export to Excel
GET    /family_groups/update/status/{id}/{status}  # Toggle status
```

#### 8. Security & Device Management Endpoints
```http
# Device Management
GET    /admin/customer-devices                    # List customer devices
GET    /admin/customer-devices/{device}           # View device details
POST   /admin/customer-devices/{device}/deactivate    # Deactivate device
POST   /admin/customer-devices/cleanup-invalid    # Cleanup invalid devices

# Device API
GET    /api/customer/devices                      # List customer's devices (API)
POST   /api/customer/device/register              # Register new device
POST   /api/customer/device/heartbeat             # Device heartbeat
PUT    /api/customer/device/update                # Update device info
POST   /api/customer/device/unregister            # Unregister device
POST   /api/customer/device/{device}/deactivate   # Deactivate device (API)

# Security Dashboard
GET    /security/dashboard                        # Security overview
GET    /security/audit-logs                       # Audit log viewer
GET    /security/api/analytics                    # Security analytics
GET    /security/api/alerts                       # Security alerts
GET    /security/api/metrics-widget               # Metrics widget data
GET    /security/api/suspicious-activity          # Suspicious activity feed
GET    /security/api/high-risk-activity           # High-risk activity feed
GET    /security/api/user/{userId}/activity       # User activity timeline
GET    /security/api/entity/{entityId}/activity   # Entity activity timeline
GET    /security/api/report                       # Generate security report
GET    /security/export-logs                      # Export audit logs
```

#### 9. Customer Portal Endpoints
```http
GET    /customer/dashboard                    # Customer dashboard
GET    /customer/profile                      # Customer profile
GET    /customer/policies                     # View policies
GET    /customer/policies/{policy}            # Policy details
GET    /customer/policies/{policy}/download   # Download policy document
GET    /customer/quotations                   # View quotations
GET    /customer/quotations/{quotation}       # Quotation details
GET    /customer/quotations/{quotation}/download  # Download quotation PDF
GET    /customer/view-claims                  # View claims
GET    /customer/view-claims/{claim}          # Claim details
GET    /customer/change-password              # Change password form
POST   /customer/change-password              # Update password

# Family Member Access
GET    /customer/family-member/{member}/profile          # View family member profile
GET    /customer/family-member/{member}/change-password  # Family member password form
PUT    /customer/family-member/{member}/password         # Update family member password
POST   /customer/family-member/{member}/disable-2fa      # Disable family member 2FA
```

#### 10. Master Data Endpoints

##### Branches
```http
GET    /branches                              # List branches
GET    /branches/create                       # Branch creation form
POST   /branches/store                        # Create branch
GET    /branches/edit/{branch}                # Edit branch form
PUT    /branches/update/{branch}              # Update branch
GET    /branches/update/status/{id}/{status}  # Toggle status
GET    /branches/export                       # Export to Excel
```

##### Insurance Companies
```http
GET    /insurance_companies                              # List companies
GET    /insurance_companies/create                       # Company creation form
POST   /insurance_companies/store                        # Create company
GET    /insurance_companies/edit/{company}               # Edit company form
PUT    /insurance_companies/update/{company}             # Update company
GET    /insurance_companies/update/status/{id}/{status}  # Toggle status
GET    /insurance_companies/export                       # Export to Excel
```

##### Brokers
```http
GET    /brokers                              # List brokers
GET    /brokers/create                       # Broker creation form
POST   /brokers/store                        # Create broker
GET    /brokers/edit/{broker}                # Edit broker form
PUT    /brokers/update/{broker}              # Update broker
GET    /brokers/update/status/{id}/{status}  # Toggle status
GET    /brokers/export                       # Export to Excel
```

#### 11. Reports & Analytics Endpoints
```http
GET    /reports                              # Reports page
POST   /reports                              # Generate report
GET    /reports/export                       # Export report
GET    /reports/load/columns/{report_name}   # Load saved report columns
POST   /reports/selected/columns             # Save report column selection
```

#### 12. Health & Monitoring Endpoints
```http
GET    /health                    # Basic health check
GET    /health/detailed           # Detailed health status
GET    /health/liveness           # Liveness probe
GET    /health/readiness          # Readiness probe
GET    /monitoring/logs           # Application logs
GET    /monitoring/metrics        # System metrics
GET    /monitoring/performance    # Performance metrics
GET    /monitoring/resources      # Resource usage
```

#### 13. User & Role Management Endpoints
```http
# Users
GET    /users                              # List users
GET    /users/create                       # User creation form
POST   /users/store                        # Create user
GET    /users/edit/{user}                  # Edit user form
PUT    /users/update/{user}                # Update user
GET    /users/update/status/{id}/{status}  # Toggle status
GET    /users/export                       # Export to Excel

# Roles
GET    /roles                    # List roles
GET    /roles/create             # Role creation form
POST   /roles                    # Create role
GET    /roles/{role}             # View role
GET    /roles/{role}/edit        # Edit role form
PUT    /roles/{role}             # Update role
DELETE /roles/{role}             # Delete role

# Permissions
GET    /permissions              # List permissions
GET    /permissions/create       # Permission creation form
POST   /permissions              # Create permission
GET    /permissions/{permission} # View permission
GET    /permissions/{permission}/edit  # Edit permission form
PUT    /permissions/{permission}       # Update permission
DELETE /permissions/{permission}       # Delete permission
```

### API Response Format

**Success Response**:
```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data
  }
}
```

**Error Response**:
```json
{
  "success": false,
  "message": "Error message",
  "errors": {
    "field": ["Validation error message"]
  }
}
```

---

## Services Layer

### Service Architecture

The application uses a comprehensive service layer to encapsulate business logic, keeping controllers thin and focused on HTTP concerns.

### Core Services (42 Services Total)

#### 1. Business Domain Services

##### CustomerService
**Location**: `app/Services/CustomerService.php`
**Interface**: `app/Contracts/Services/CustomerServiceInterface.php`

**Responsibilities**:
- Customer CRUD operations
- Family group management
- Document upload handling
- Birthday/Anniversary tracking
- Email verification processing
- Customer onboarding workflow

**Key Methods**:
```php
public function createCustomer(array $data): Customer
public function updateCustomer(Customer $customer, array $data): Customer
public function sendOnboardingWhatsApp(Customer $customer): void
public function sendBirthdayWishes(Customer $customer): void
public function verifyEmail(string $token): bool
```

##### CustomerInsuranceService
**Location**: `app/Services/CustomerInsuranceService.php`
**Interface**: `app/Contracts/Services/CustomerInsuranceServiceInterface.php`

**Responsibilities**:
- Policy creation and management
- Premium calculations
- Commission breakdowns
- Renewal processing
- Policy expiry tracking
- Document generation and sharing

**Key Methods**:
```php
public function createPolicy(array $data): CustomerInsurance
public function renewPolicy(CustomerInsurance $policy, array $data): CustomerInsurance
public function calculatePremium(array $data): array
public function sendPolicyDocument(CustomerInsurance $policy): void
public function sendRenewalReminder(CustomerInsurance $policy): void
```

##### QuotationService
**Location**: `app/Services/QuotationService.php`
**Interface**: `app/Contracts/Services/QuotationServiceInterface.php`

**Responsibilities**:
- Quotation creation
- Multi-company quote generation
- IDV calculations
- Premium comparisons
- PDF generation
- WhatsApp delivery

**Key Methods**:
```php
public function createQuotation(array $data): Quotation
public function generateCompanyQuotes(Quotation $quotation, array $companies): Collection
public function calculateIDV(array $vehicleData): array
public function generatePDF(Quotation $quotation): string
public function sendToWhatsApp(Quotation $quotation): void
```

##### ClaimService
**Location**: `app/Services/ClaimService.php`
**Interface**: `app/Contracts/Services/ClaimServiceInterface.php`

**Responsibilities**:
- Claim registration
- Workflow stage management
- Document tracking
- Liability assessment
- WhatsApp notifications
- Claim statistics

**Key Methods**:
```php
public function createClaim(array $data): Claim
public function addStage(Claim $claim, array $stageData): ClaimStage
public function updateLiability(Claim $claim, array $liability): void
public function updateDocumentStatus(ClaimDocument $document, string $status): void
public function sendClaimNumberWhatsApp(Claim $claim): void
public function sendPendingDocumentsWhatsApp(Claim $claim): void
```

#### 2. Notification Services

##### NotificationLoggerService
**Location**: `app/Services/NotificationLoggerService.php`

**Responsibilities**:
- Multi-channel notification orchestration
- Delivery tracking
- Retry logic implementation
- Webhook processing
- Analytics and reporting

**Key Methods**:
```php
public function send(array $data): NotificationLog
public function retry(NotificationLog $log): void
public function bulkRetry(Collection $logs): void
public function trackDelivery(NotificationLog $log, array $status): void
public function getAnalytics(array $filters): array
```

##### TemplateService
**Location**: `app/Services/TemplateService.php`

**Responsibilities**:
- Template management
- Variable substitution
- Template preview generation
- Validation of template syntax

**Key Methods**:
```php
public function render(NotificationTemplate $template, array $variables): string
public function preview(NotificationTemplate $template, array $sampleData): string
public function validateTemplate(string $content): array
public function getAvailableVariables(string $notificationType): array
```

##### EmailService
**Location**: `app/Services/EmailService.php`

**Responsibilities**:
- Email delivery via SMTP
- Email queue management
- Attachment handling
- Delivery status tracking

**Key Methods**:
```php
public function send(string $to, string $subject, string $body, array $attachments = []): bool
public function sendBulk(array $recipients, string $subject, string $body): array
public function trackDelivery(string $messageId): array
```

##### SmsService
**Location**: `app/Services/SmsService.php`

**Responsibilities**:
- SMS delivery via gateway
- SMS template processing
- Delivery confirmation
- Balance tracking

**Key Methods**:
```php
public function send(string $mobile, string $message): bool
public function sendBulk(array $recipients, string $message): array
public function getBalance(): float
```

##### WhatsAppService (MarketingWhatsAppService)
**Location**: `app/Services/MarketingWhatsAppService.php`

**Responsibilities**:
- WhatsApp message delivery via API
- Media attachment support (PDF, images)
- Template message sending
- Delivery status webhooks

**Key Methods**:
```php
public function sendMessage(string $mobile, string $message): bool
public function sendDocument(string $mobile, string $documentPath, string $caption): bool
public function sendTemplate(string $mobile, string $templateName, array $variables): bool
```

#### 3. Security Services

##### TwoFactorAuthService
**Location**: `app/Services/TwoFactorAuthService.php`

**Responsibilities**:
- 2FA secret generation
- QR code generation
- Code verification
- Recovery code management
- Trusted device management

**Key Methods**:
```php
public function enable(Authenticatable $user): array
public function confirm(Authenticatable $user, string $code): bool
public function verify(Authenticatable $user, string $code): bool
public function generateRecoveryCodes(Authenticatable $user): array
public function trustDevice(Authenticatable $user, string $deviceId): void
```

##### SecurityService
**Location**: `app/Services/SecurityService.php`

**Responsibilities**:
- Device fingerprinting
- Trust score calculation
- Security event logging
- Suspicious activity detection
- Device blocking

**Key Methods**:
```php
public function trackDevice(Authenticatable $user, Request $request): DeviceTracking
public function calculateTrustScore(DeviceTracking $device): int
public function detectSuspiciousActivity(DeviceTracking $device): bool
public function blockDevice(DeviceTracking $device, string $reason): void
```

##### SecurityAuditService
**Location**: `app/Services/SecurityAuditService.php`

**Responsibilities**:
- Comprehensive audit logging
- Risk score calculation
- Audit trail querying
- Security analytics
- Report generation

**Key Methods**:
```php
public function log(string $event, Auditable $subject, array $data): AuditLog
public function calculateRiskScore(array $eventData): float
public function getActivityTimeline(Auditable $subject): Collection
public function generateSecurityReport(array $filters): array
```

##### ContentSecurityPolicyService
**Location**: `app/Services/ContentSecurityPolicyService.php`

**Responsibilities**:
- CSP header generation
- Nonce management
- Policy configuration
- Violation reporting

**Key Methods**:
```php
public function generateHeaders(): array
public function getNonce(): string
public function addScriptSource(string $source): void
public function reportViolation(array $violationData): void
```

#### 4. Infrastructure Services

##### CacheService
**Location**: `app/Services/CacheService.php`

**Responsibilities**:
- Cache management
- Cache key generation
- Cache invalidation strategies
- TTL management

**Key Methods**:
```php
public function remember(string $key, int $ttl, Closure $callback): mixed
public function invalidate(string $pattern): void
public function flush(): void
```

##### FileUploadService / SecureFileUploadService
**Location**: `app/Services/SecureFileUploadService.php`

**Responsibilities**:
- File validation (MIME, size, extension)
- Secure file storage
- File sanitization
- Thumbnail generation
- File deletion

**Key Methods**:
```php
public function upload(UploadedFile $file, string $directory): string
public function validate(UploadedFile $file, array $rules): bool
public function delete(string $path): bool
```

##### PdfGenerationService
**Location**: `app/Services/PdfGenerationService.php`

**Responsibilities**:
- PDF generation from HTML
- Template rendering
- PDF customization (header, footer)
- PDF storage and delivery

**Key Methods**:
```php
public function generateQuotationPdf(Quotation $quotation): string
public function generatePolicyPdf(CustomerInsurance $policy): string
public function generateClaimPdf(Claim $claim): string
```

##### ExcelExportService
**Location**: `app/Services/ExcelExportService.php`

**Responsibilities**:
- Excel export generation
- Data formatting
- Multi-sheet exports
- Custom styling

**Key Methods**:
```php
public function export(Collection $data, string $filename, array $columns): string
public function exportCustomers(array $filters): string
public function exportPolicies(array $filters): string
```

##### LoggingService
**Location**: `app/Services/LoggingService.php`

**Responsibilities**:
- Application logging
- Log level management
- Contextual logging
- Log rotation

**Key Methods**:
```php
public function info(string $message, array $context = []): void
public function error(string $message, array $context = []): void
public function warning(string $message, array $context = []): void
```

##### HealthCheckService
**Location**: `app/Services/HealthCheckService.php`

**Responsibilities**:
- System health monitoring
- Database connectivity checks
- Redis connectivity checks
- External service checks
- Performance metrics

**Key Methods**:
```php
public function checkHealth(): array
public function checkDatabase(): bool
public function checkRedis(): bool
public function getPerformanceMetrics(): array
```

#### 5. Master Data Services

These services follow a common pattern for master data CRUD operations:

- **BranchService** - Branch management
- **BrokerService** - Broker management
- **InsuranceCompanyService** - Insurance company management
- **PolicyTypeService** - Policy type management
- **PremiumTypeService** - Premium type management
- **FuelTypeService** - Fuel type management
- **AddonCoverService** - Add-on cover management
- **ReferenceUserService** - Reference user management
- **RelationshipManagerService** - RM management

**Common Pattern**:
```php
public function getAll(): Collection
public function create(array $data): Model
public function update(Model $model, array $data): Model
public function updateStatus(int $id, bool $status): bool
public function delete(int $id): bool
public function export(array $filters): string
```

#### 6. Administrative Services

##### UserService
**Location**: `app/Services/UserService.php`

**Responsibilities**:
- User account management
- Role assignment
- Password management
- User activity tracking

##### RoleService
**Location**: `app/Services/RoleService.php`

**Responsibilities**:
- Role creation and management
- Permission assignment
- Role hierarchy

##### PermissionService
**Location**: `app/Services/PermissionService.php`

**Responsibilities**:
- Permission management
- Permission checking
- Guard management

##### ReportService
**Location**: `app/Services/ReportService.php`

**Responsibilities**:
- Custom report generation
- Column selection management
- Report export
- Report caching

##### AppSettingService
**Location**: `app/Services/AppSettingService.php`

**Responsibilities**:
- Application settings management
- Setting encryption/decryption
- Setting caching
- Setting validation

---

## Security Implementation

### Security Layers

#### 1. Authentication & Authorization

**Multi-Guard Authentication**:
```php
// Admin guard (web)
auth()->guard('web')->check()

// Customer guard (customer)
auth()->guard('customer')->check()
```

**Role-Based Access Control (RBAC)**:
- Powered by Spatie Laravel Permission
- Fine-grained permission system
- Role inheritance
- Permission caching

**Permission Examples**:
```php
// Check permission
$user->can('view-customers')
$user->hasRole('admin')

// Blade directive
@can('edit-customers')
    <!-- Edit button -->
@endcan
```

#### 2. Two-Factor Authentication (2FA)

**TOTP Implementation**:
- Google Authenticator compatible
- 30-second time window
- 6-digit codes
- QR code setup

**Recovery Codes**:
- 10 one-time use codes
- Encrypted storage
- Regeneration on demand

**Trusted Devices**:
- 30-day trust duration (configurable)
- Device fingerprinting
- Automatic trust expiration

#### 3. Device Tracking & Fingerprinting

**Tracked Attributes**:
- User agent string
- Browser type and version
- Operating system
- Platform (desktop/mobile)
- Screen resolution
- Hardware info (when available)
- IP address
- Location data

**Trust Score Calculation**:
```php
Trust Score = 100
- (Failed login attempts × 10)
- (IP changes × 5)
- (Unknown location × 15)
+ (Successful logins × 2)
+ (Consistent usage pattern × 10)
```

**Device Security Events**:
- New device login
- Failed authentication
- Location change
- Suspicious activity
- Device blocking

#### 4. Content Security Policy (CSP)

**CSP Directives**:
```php
Content-Security-Policy:
  default-src 'self';
  script-src 'self' 'nonce-{random}' https://cdn.jsdelivr.net;
  style-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;
  img-src 'self' data: https:;
  font-src 'self' data: https://cdn.jsdelivr.net;
  connect-src 'self';
  frame-ancestors 'none';
  base-uri 'self';
  form-action 'self';
```

**Nonce Management**:
- Random nonce per request
- Automatic nonce injection
- Blade directive support: `@nonce`

#### 5. Audit Logging

**Logged Events**:
- Authentication (login, logout, failed attempts)
- CRUD operations (create, update, delete)
- Permission changes
- Settings modifications
- Document access
- Sensitive data access

**Audit Log Structure**:
```php
[
    'auditable_type' => 'App\\Models\\Customer',
    'auditable_id' => 123,
    'actor_type' => 'App\\Models\\User',
    'actor_id' => 1,
    'action' => 'update',
    'event' => 'customer.updated',
    'event_category' => 'customer_management',
    'old_values' => [...],
    'new_values' => [...],
    'ip_address' => '192.168.1.1',
    'user_agent' => '...',
    'risk_score' => 0.3,
    'risk_level' => 'low',
]
```

#### 6. Input Validation & Sanitization

**Form Request Validation**:
- All input validated through Form Requests
- Custom validation rules
- Secure defaults

**File Upload Security**:
- MIME type validation
- File size limits
- Extension whitelist
- Virus scanning (optional)
- Secure storage paths

**SQL Injection Prevention**:
- Parameterized queries (Eloquent ORM)
- Query builder with bindings
- No raw SQL queries without bindings

**XSS Prevention**:
- Blade template escaping by default
- HTML Purifier for rich text
- CSP headers

#### 7. Rate Limiting

**Rate Limit Rules**:
```php
// Login endpoints
'login' => 5 attempts per minute

// API endpoints
'api' => 60 requests per minute

// Global
'global' => 1000 requests per minute
```

**Throttling Strategies**:
- Per user rate limiting
- Per IP rate limiting
- Per route rate limiting
- Custom throttle middleware

#### 8. Session Security

**Session Configuration**:
```php
'driver' => 'database',  // Secure session storage
'lifetime' => 120,       // 2-hour timeout
'expire_on_close' => false,
'secure' => true,        // HTTPS only
'http_only' => true,     // No JavaScript access
'same_site' => 'lax',    // CSRF protection
```

**Session Hijacking Prevention**:
- Session regeneration on login
- IP address tracking
- User agent validation
- Device fingerprinting

#### 9. Password Security

**Password Requirements**:
- Minimum 8 characters
- At least one uppercase letter
- At least one lowercase letter
- At least one number
- At least one special character

**Password Hashing**:
- bcrypt algorithm
- Automatic rehashing on login

**Password Reset Security**:
- Token expiration (60 minutes)
- One-time use tokens
- Rate limiting on reset requests

#### 10. API Security

**Laravel Sanctum**:
- Token-based authentication
- Token expiration
- Token abilities/scopes
- Revocable tokens

**API Rate Limiting**:
- 60 requests per minute per user
- Throttle by token
- Custom limits per endpoint

---

## Testing Strategy

### Testing Framework

**Primary Framework**: Pest PHP 2.36
**Fallback**: PHPUnit 10.5
**Architecture Testing**: Pest Plugin Arch

### Test Structure

```
tests/
├── Feature/                    # Integration tests
│   ├── Controllers/            # Controller tests
│   ├── Notification/           # Notification system tests
│   ├── Security/               # Security feature tests
│   └── ...
│
├── Unit/                       # Unit tests
│   ├── Models/                 # Model tests
│   ├── Services/               # Service tests
│   ├── Notification/           # Notification component tests
│   └── ...
│
├── Integration/                # Integration tests
│   ├── WhatsAppIntegration/
│   ├── EmailIntegration/
│   └── ...
│
└── TestCase.php               # Base test class
```

### Test Coverage

**Current Coverage**:
- Unit Tests: 15 files
- Feature Tests: 12 files
- Integration Tests: 8 files

**Coverage Goals**:
- Overall: 80%+
- Critical paths: 95%+
- Services: 90%+
- Models: 85%+

### Testing Commands

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

# Run notification tests
composer test:notifications

# Run with parallel execution
php artisan test --parallel
```

### Key Test Examples

#### Unit Test Example
```php
test('customer service creates customer correctly', function () {
    $data = [
        'first_name' => 'John',
        'last_name' => 'Doe',
        'email' => 'john@example.com',
    ];

    $customer = app(CustomerService::class)->createCustomer($data);

    expect($customer)
        ->toBeInstanceOf(Customer::class)
        ->first_name->toBe('John')
        ->email->toBe('john@example.com');
});
```

#### Feature Test Example
```php
test('authenticated user can create customer', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post('/customers/store', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
        ]);

    $response->assertRedirect()
        ->assertSessionHas('success');

    $this->assertDatabaseHas('customers', [
        'email' => 'john@example.com',
    ]);
});
```

### Test Data Management

**Factories**: All models have corresponding factories
**Seeders**: Test database seeders available
**RefreshDatabase**: Used in all tests for clean state

---

## Deployment & Operations

### Server Requirements

**Minimum Requirements**:
- PHP 8.2+
- MySQL 8.0+
- Redis 6.0+
- 2 CPU cores
- 4 GB RAM
- 20 GB storage

**Recommended Production**:
- PHP 8.2+ with OPcache
- MySQL 8.4
- Redis 7.0+
- 4+ CPU cores
- 8+ GB RAM
- 50+ GB SSD storage
- SSL certificate

### Environment Configuration

**Key Environment Variables**:
```env
APP_NAME="Midas Portal"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://portal.example.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=midas_portal
DB_USERNAME=midas_user
DB_PASSWORD=secure_password

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

QUEUE_CONNECTION=redis
CACHE_DRIVER=redis
SESSION_DRIVER=database

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=noreply@example.com
MAIL_PASSWORD=mail_password
MAIL_ENCRYPTION=tls

# WhatsApp Configuration
WHATSAPP_API_URL=https://api.whatsapp.com/v1
WHATSAPP_API_TOKEN=your_token

# Security
CSP_ENABLED=true
TWO_FACTOR_ENABLED=true
DEVICE_TRACKING_ENABLED=true
```

### Deployment Workflow

**Production Deployment Steps**:
```bash
# 1. Pull latest code
git pull origin main

# 2. Install dependencies
composer install --no-dev --optimize-autoloader
npm install --production

# 3. Run migrations
php artisan migrate --force

# 4. Clear and cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize

# 5. Build frontend assets
npm run build

# 6. Set permissions
chmod -R 755 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

# 7. Restart services
php artisan queue:restart
sudo systemctl restart php8.2-fpm
sudo systemctl reload nginx
```

### Queue Workers

**Supervisor Configuration** (`/etc/supervisor/conf.d/midas-portal-worker.conf`):
```ini
[program:midas-portal-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/midas-portal/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/midas-portal/storage/logs/worker.log
stopwaitsecs=3600
```

**Start Workers**:
```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start midas-portal-worker:*
```

### Scheduled Tasks

**Cron Configuration** (`/etc/cron.d/midas-portal`):
```cron
* * * * * cd /var/www/midas-portal && php artisan schedule:run >> /dev/null 2>&1
```

**Scheduled Commands**:
- `SendRenewalReminders` - Daily at 9:00 AM
- `SendBirthdayWishes` - Daily at 8:00 AM
- `RetryFailedNotifications` - Every hour
- `CleanupOldLogs` - Daily at 2:00 AM
- `BackupDatabase` - Daily at 3:00 AM

### Monitoring & Logs

**Log Locations**:
```
storage/logs/laravel.log      # Application logs
storage/logs/worker.log        # Queue worker logs
storage/logs/scheduler.log     # Scheduled task logs
/var/log/nginx/access.log      # Web server access logs
/var/log/nginx/error.log       # Web server error logs
```

**Log Rotation** (`/etc/logrotate.d/midas-portal`):
```
/var/www/midas-portal/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0644 www-data www-data
    sharedscripts
}
```

**Health Monitoring**:
```bash
# Check application health
curl https://portal.example.com/health

# Check detailed status
curl https://portal.example.com/health/detailed
```

### Backup Strategy

**Database Backup**:
```bash
# Daily backup script
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/var/backups/midas-portal"
DB_NAME="midas_portal"

mysqldump -u backup_user -p'backup_password' $DB_NAME | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Keep last 30 days
find $BACKUP_DIR -name "db_*.sql.gz" -mtime +30 -delete
```

**File Backup**:
```bash
# Backup uploads directory
tar -czf /var/backups/midas-portal/uploads_$DATE.tar.gz /var/www/midas-portal/public/uploads
```

---

## Development Workflow

### Local Setup

**Prerequisites**:
- PHP 8.2+
- Composer 2.x
- Node.js 18+
- MySQL 8.0+
- Redis (optional for local)

**Setup Steps**:
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

### Coding Standards

**PHP Standards**:
- PSR-12 coding style
- Laravel best practices
- Type hints for all parameters and returns
- PHPDoc for all public methods

**Code Formatting**:
```bash
# Format code
composer fix
vendor/bin/pint

# Check formatting
vendor/bin/pint --test
```

**Static Analysis**:
```bash
# Run PHPStan
composer analyze
vendor/bin/phpstan analyse --memory-limit=2G

# Full analysis
composer analyze:full
```

**Refactoring**:
```bash
# Check refactoring opportunities
composer refactor

# Apply refactoring
composer refactor:apply
```

### Git Workflow

**Branch Naming**:
- `feature/feature-name` - New features
- `bugfix/issue-description` - Bug fixes
- `hotfix/critical-issue` - Production hotfixes
- `refactor/component-name` - Code refactoring

**Commit Message Format**:
```
type(scope): Short description

Longer description if needed

Fixes #123
```

**Types**: feat, fix, docs, style, refactor, test, chore

**Example**:
```
feat(notification): Add WhatsApp template support

- Add template selection to notification form
- Implement variable substitution
- Add preview functionality

Fixes #234
```

### Pull Request Process

1. **Create Feature Branch**: `git checkout -b feature/new-feature`
2. **Make Changes**: Implement feature with tests
3. **Format Code**: Run `composer fix`
4. **Run Tests**: `php artisan test`
5. **Static Analysis**: `composer analyze`
6. **Commit Changes**: Follow commit message format
7. **Push Branch**: `git push origin feature/new-feature`
8. **Open PR**: Create PR with description
9. **Code Review**: Address review comments
10. **Merge**: Squash and merge to main

### Database Migrations

**Creating Migrations**:
```bash
# Create migration
php artisan make:migration create_table_name --create=table_name

# Create migration for modification
php artisan make:migration add_column_to_table --table=table_name
```

**Running Migrations**:
```bash
# Run pending migrations
php artisan migrate

# Rollback last batch
php artisan migrate:rollback

# Rollback all migrations
php artisan migrate:reset

# Fresh migration with seed
php artisan migrate:fresh --seed
```

### Useful Commands

**Development**:
```bash
composer dev                  # Clear cache + npm run dev + serve
composer dev-setup            # Fresh setup with migrations
composer fix                  # Format code
composer fix:quick            # Quick cache clear
composer check                # Full project check
```

**Testing**:
```bash
php artisan test              # Run all tests
php artisan test --coverage   # With coverage
composer test:notifications   # Test notification system
```

**Analysis**:
```bash
composer analyze              # Run static analysis
composer analyze:full         # Full analysis with audit
```

**Optimization**:
```bash
php artisan optimize          # Optimize for production
php artisan optimize:clear    # Clear all caches
```

---

## Quick Reference

### Frequently Used Commands

```bash
# Development
php artisan serve                      # Start dev server
npm run dev                            # Watch assets
php artisan tinker                     # REPL environment

# Database
php artisan migrate                    # Run migrations
php artisan db:seed                    # Run seeders
php artisan migrate:fresh --seed      # Fresh database

# Queue
php artisan queue:work                 # Process jobs
php artisan queue:failed               # List failed jobs
php artisan queue:retry all            # Retry failed jobs

# Cache
php artisan cache:clear                # Clear application cache
php artisan config:clear               # Clear config cache
php artisan route:clear                # Clear route cache
php artisan view:clear                 # Clear view cache
php artisan optimize:clear             # Clear all caches

# Testing
php artisan test                       # Run tests
php artisan test --coverage            # With coverage
php artisan test --parallel            # Parallel execution
```

### Key File Locations

**Configuration**:
- Application config: `config/app.php`
- Database config: `config/database.php`
- Mail config: `config/mail.php`
- Queue config: `config/queue.php`

**Routes**:
- Web routes: `routes/web.php`
- API routes: `routes/api.php`

**Views**:
- Admin layouts: `resources/views/layouts/`
- Customer views: `resources/views/customer/`
- Email templates: `resources/views/emails/`

**Assets**:
- JavaScript: `resources/js/`
- CSS: `resources/css/`
- Public assets: `public/`

### Common Tasks

**Add New Route**:
1. Add route in `routes/web.php`
2. Create controller method
3. Create view in `resources/views/`
4. Test functionality

**Create New Model**:
```bash
php artisan make:model ModelName -mcr
# Creates: Model, Migration, Controller, Resource
```

**Add New Service**:
1. Create interface in `app/Contracts/Services/`
2. Create service in `app/Services/`
3. Register in `app/Providers/AppServiceProvider.php`

**Add New Notification**:
1. Create template in notification_templates
2. Define variables in TemplateService
3. Implement sending logic
4. Test with preview and send-test

### Troubleshooting

**Common Issues**:

1. **Class not found**: `composer dump-autoload`
2. **Route not found**: `php artisan route:clear`
3. **Config cached**: `php artisan config:clear`
4. **View not rendering**: `php artisan view:clear`
5. **Permission denied**: `chmod -R 775 storage bootstrap/cache`
6. **Queue not processing**: Check supervisor status

**Debug Mode**:
```php
// Enable in .env
APP_DEBUG=true

// View error details
tail -f storage/logs/laravel.log

// Database query logging
DB::enableQueryLog();
// ... run queries ...
dd(DB::getQueryLog());
```

---

## Documentation Cross-References

### Related Documentation
- **System Documentation**: `SYSTEM_DOCUMENTATION.md` - Complete system features and architecture
- **Deployment Guide**: `DEPLOYMENT_GUIDE.md` - Production deployment instructions
- **Developer Guide**: `DEVELOPER_GUIDE.md` - Local setup and development workflow
- **README**: `README.md` - Project overview and quick start

### External Resources
- [Laravel 10 Documentation](https://laravel.com/docs/10.x)
- [Pest PHP Documentation](https://pestphp.com/)
- [Laravel Sanctum](https://laravel.com/docs/10.x/sanctum)
- [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission/v5)

---

## Index Metadata

**Document Version**: 1.0
**Last Updated**: 2025-11-01
**Generated By**: Claude Code /sc:index
**Project Version**: Laravel 10.49.1
**Maintained By**: Midas Portal Development Team

---

**End of Project Index**
