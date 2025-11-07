# Midas Portal - Features Overview

**Version**: 3.0
**Last Updated**: 2025-01-07
**Status**: Production Ready

Complete feature overview for the Midas Portal multi-tenant SaaS insurance management system.

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Core Features](#core-features)
3. [Business Operations](#business-operations)
4. [Technical Features](#technical-features)
5. [Security & Compliance](#security--compliance)
6. [Integration & Automation](#integration--automation)

---

## System Overview

### 4-Portal Architecture

Midas Portal operates as a multi-tenant SaaS platform with four distinct portals, each serving different user groups:

1. **Public Website Portal** (`www.midastech.in`)
   - Marketing and brand presence
   - Lead capture forms
   - Feature showcase and pricing
   - Contact and inquiry management

2. **Central Admin Portal** (`midastech.in`)
   - Platform administration (Midas team)
   - Tenant management and provisioning
   - Subscription and billing oversight
   - System-wide analytics and reporting

3. **Tenant Staff Portal** (`tenant.midastech.in`)
   - Insurance business operations
   - Customer and policy management
   - Claims processing and quotations
   - Staff collaboration tools

4. **Customer Portal** (`tenant.midastech.in/customer`)
   - Self-service customer access
   - Policy viewing and downloads
   - Claims submission and tracking
   - Document management

**📖 Deep Dive**: [core/MULTI_PORTAL_ARCHITECTURE.md](core/MULTI_PORTAL_ARCHITECTURE.md) - Complete portal architecture, routing, authentication flows, and isolation mechanisms.

### Multi-Tenancy Model

- **Central Database**: Stores tenants, subscriptions, plans, payments
- **Tenant Databases**: Isolated per tenant (customers, policies, claims, quotations)
- **Domain-Based Identification**: Automatic tenant detection via subdomain
- **Complete Data Isolation**: Zero cross-tenant data leakage
- **Automatic Provisioning**: Database creation, migration, and seeding on signup

**📖 Deep Dive**: [core/DATABASE_SCHEMA.md](core/DATABASE_SCHEMA.md) - Complete database architecture with 72 migrations, ER diagrams, and relationships.

---

## Core Features

### 1. Subscription Management

Complete SaaS subscription system with trials, upgrades, and usage tracking.

**Key Capabilities**:
- **Plans**: Starter ($29), Professional ($79), Enterprise ($199)
- **Trial System**: 14-day free trials with automated conversion
- **Usage Tracking**: Real-time monitoring of customers, policies, and users
- **Limit Enforcement**: Automatic blocking when limits exceeded
- **Billing Automation**: Recurring charges via Razorpay/Stripe
- **MRR Tracking**: Monthly Recurring Revenue analytics

**Features**:
- Trial expiry notifications (3 reminders: 3 days, 1 day, expiry)
- Grace period (3 days post-expiry)
- Plan upgrades/downgrades
- Proration for mid-cycle changes
- Automatic subscription suspension
- Usage reset on billing cycle

**📖 Full Documentation**: [features/SUBSCRIPTION_MANAGEMENT.md](features/SUBSCRIPTION_MANAGEMENT.md)
**📖 Trial System**: [features/TRIAL_CONVERSION_SYSTEM.md](features/TRIAL_CONVERSION_SYSTEM.md)

### 2. Payment Gateway Integration

Dual payment gateway support with webhook automation and failover.

**Supported Gateways**:
- **Razorpay** (Primary) - Cards, UPI, Net Banking, Wallets
- **Stripe** (Fallback) - International payments

**Features**:
- Webhook signature verification
- Automatic payment reconciliation
- Retry logic for failed payments
- Refund processing
- Payment history and invoicing
- Gateway failover on downtime
- Test mode for development

**📖 Full Documentation**: [features/PAYMENT_GATEWAY_INTEGRATION.md](features/PAYMENT_GATEWAY_INTEGRATION.md)
**📖 Deployment Setup**: [operations/DEPLOYMENT.md](operations/DEPLOYMENT.md) - Section 6: Payment Gateway Configuration

### 3. Security & Authentication

Enterprise-grade security with multi-factor authentication and audit trails.

**Two-Factor Authentication (2FA)**:
- TOTP-based authentication (Google Authenticator, Authy)
- QR code setup flow
- Recovery codes (10 single-use codes)
- Device trust management
- Configurable enforcement (required/optional)

**Device Tracking**:
- Fingerprint-based device identification
- Trusted device management
- Suspicious activity detection
- IP tracking and geolocation
- Browser and OS identification

**Audit Logging**:
- Three-tier system (AuditLog, CustomerAuditLog, SecurityAuditLog)
- Automatic event tracking
- Risk scoring and pattern detection
- Compliance reporting (GDPR, ISO 27001)
- Tamper-proof logs

**📖 Full Documentation**:
- [features/TWO_FACTOR_AUTHENTICATION.md](features/TWO_FACTOR_AUTHENTICATION.md)
- [features/DEVICE_TRACKING.md](features/DEVICE_TRACKING.md)
- [features/AUDIT_LOGGING.md](features/AUDIT_LOGGING.md)

### 4. Multi-Channel Notifications

Unified notification system across Email, WhatsApp, SMS, and Push.

**Channels**:
- **Email**: Transactional and marketing emails
- **WhatsApp**: Business API integration with templates
- **SMS**: OTP and alerts via Twilio
- **Push**: Browser notifications (planned)

**Features**:
- Template management with variables
- Delivery tracking and status
- Retry logic with exponential backoff
- Queued delivery for bulk operations
- Webhook integration
- Analytics and reporting

**📖 Full Documentation**: [features/NOTIFICATION_SYSTEM.md](features/NOTIFICATION_SYSTEM.md)

### 5. File Storage & Management

Multi-tenant file storage with isolation and usage tracking.

**Features**:
- **Tenant Isolation**: Separate directories per tenant
- **Storage Quotas**: Per-plan limits (10GB, 50GB, 500GB)
- **File Types**: Documents, images, PDFs (validation enforced)
- **Usage Tracking**: Real-time storage monitoring
- **Automatic Cleanup**: Orphaned file removal
- **Security**: Pre-signed URLs, access control

**📖 Full Documentation**: [features/FILE_STORAGE_MULTI_TENANCY.md](features/FILE_STORAGE_MULTI_TENANCY.md)

---

## Business Operations

### Customer Management

Complete customer lifecycle management with family groups and document tracking.

**Core Features**:
- Customer CRUD with validation
- Family group management (shared policy access)
- Document uploads (KYC, identity, address proof)
- Verification workflows
- Customer portal access
- Password management
- Search and export

**📖 Full Documentation**: [modules/CUSTOMER_MANAGEMENT.md](modules/CUSTOMER_MANAGEMENT.md)

### Lead Management

CRM system for lead capture, tracking, and conversion.

**Core Features**:
- Auto lead numbering (`LD-YYYYMM-XXXX`)
- 6-stage workflow (New → Contacted → Quotation → Interested → Converted/Lost)
- Priority management (Low/Medium/High)
- Activity tracking (8 types: call, email, meeting, follow-up)
- Document management
- Automated conversion to customers
- WhatsApp campaigns
- Real-time analytics

**📖 Full Documentation**: [modules/LEAD_MANAGEMENT.md](modules/LEAD_MANAGEMENT.md)

### Policy Management

Insurance policy issuance, renewal, and lifecycle management.

**Core Features**:
- Policy issuance with auto-numbering
- Renewal tracking and reminders
- NCB (No Claim Bonus) management (20%-50% scale)
- Commission calculation (own/transfer/reference)
- Premium calculations with breakdown
- Expiry automation (reminders at 30/15/7 days)
- Policy document generation
- Renewal linking

**📖 Full Documentation**: [modules/POLICY_MANAGEMENT.md](modules/POLICY_MANAGEMENT.md)

### Quotation System

Multi-company quotation engine with PDF generation and sharing.

**Core Features**:
- Multi-company comparison (up to 10 companies)
- IDV calculation (5 components: Base, Age, Depreciation, Condition, Market)
- 9 addon covers with smart pricing
- Premium formula calculations
- Company ranking and recommendations
- PDF generation with branding
- WhatsApp/Email sharing
- Quote reference system (`QT/YY/00000001`)

**📖 Full Documentation**: [modules/QUOTATION_SYSTEM.md](modules/QUOTATION_SYSTEM.md)

### Claims Management

End-to-end claims processing with document tracking and workflow automation.

**Core Features**:
- Claim registration with auto-numbering (`CLM{YYYY}{MM}{0001}`)
- 30 document types (10 Health, 20 Vehicle)
- Stage management (Registered → Under Review → Approved → Settled/Rejected)
- Document completion tracking
- Liability calculations
- Cashless vs Reimbursement workflows
- WhatsApp/Email notifications
- Settlement tracking

**📖 Full Documentation**: [modules/CLAIMS_MANAGEMENT.md](modules/CLAIMS_MANAGEMENT.md)

### User & Role Management

Comprehensive role-based access control (RBAC) system.

**Core Features**:
- User management (staff, managers, admins)
- Role-based permissions (Spatie Permission package)
- Permission inheritance
- Custom permissions creation
- Middleware protection
- Blade directives (`@can`, `@role`)
- Audit trail for permission changes

**📖 Full Documentation**: [modules/USER_ROLE_MANAGEMENT.md](modules/USER_ROLE_MANAGEMENT.md)

### Master Data Management

Centralized master data with smart ordering and seeding.

**Data Types**:
- Insurance companies (with logo and branding)
- Policy types (Health, Vehicle, Life, Travel)
- Addon covers (9 types with rates)
- Fuel types (Petrol, Diesel, Electric, CNG)
- Document types (30 types across modules)
- Lead sources and statuses

**📖 Full Documentation**: [modules/MASTER_DATA.md](modules/MASTER_DATA.md)

---

## Technical Features

### Commission Tracking

Multi-level commission system with automatic calculations.

**Commission Types**:
1. **Own Commission**: Direct sales by staff (0.5% - 15%)
2. **Transfer Commission**: Referrals from other staff (0.25% - 7.5%)
3. **Reference Commission**: External referrals (0.1% - 5%)

**Features**:
- Automatic calculation on policy issuance
- Commission reports (monthly, quarterly, annual)
- Individual and team tracking
- Payment status tracking
- Tax calculation support

**📖 Full Documentation**: [features/COMMISSION_TRACKING.md](features/COMMISSION_TRACKING.md)

### Family Group Management

Family grouping for shared policy access and management.

**Features**:
- Family group creation with primary member
- Member additions with relationships (Spouse, Child, Parent, Sibling)
- Shared policy viewing across family
- Document sharing
- Family-wide notifications
- Group-level analytics

**📖 Full Documentation**: [features/FAMILY_GROUP_MANAGEMENT.md](features/FAMILY_GROUP_MANAGEMENT.md)

### PDF Generation

Dynamic PDF generation for quotations, comparisons, and policies.

**Features**:
- DomPDF integration
- Custom branding per tenant
- Quotation PDFs with multi-company comparison
- Policy documents with terms
- Comparison tables with highlighting
- WhatsApp/Email sharing
- Bulk generation support

**📖 Full Documentation**: [features/PDF_GENERATION.md](features/PDF_GENERATION.md)

### App Settings

Encrypted application settings with type support and caching.

**Features**:
- Encrypted storage for sensitive settings
- Type support (string, integer, boolean, array, JSON)
- Category organization
- Helper functions (`app_setting()`, `set_app_setting()`)
- Cache management
- Audit trail for changes

**📖 Full Documentation**: [features/APP_SETTINGS.md](features/APP_SETTINGS.md)

---

## Security & Compliance

### Protection System

Multi-layer security protecting critical records from unauthorized actions.

**Protected Records**:
- Super-Admin accounts
- Webmonks domain emails (`*@webmonks.in`)
- System-critical data

**Protection Rules**:
- Cannot be deleted (soft or hard delete)
- Cannot be deactivated
- Email cannot be modified
- All violations logged and audited

**Scope**: Users, Customers, Leads, Brokers, Branches, Insurance Companies

### Compliance Features

**GDPR Compliance**:
- Right to access (data export)
- Right to erasure (data deletion)
- Data portability
- Consent management
- Privacy policy acceptance

**ISO 27001**:
- Access control
- Audit logging
- Encryption at rest and in transit
- Incident response
- Regular security audits

**PCI DSS** (Payment Card Industry):
- No card data storage
- Tokenized payments via gateways
- Secure transmission (HTTPS only)
- Access logging

---

## Integration & Automation

### Service Layer Architecture

51 services providing business logic abstraction and reusability.

**Service Categories**:
1. **Core Services** (7): Base, Tenant Creation, Usage Tracking
2. **Business Services** (12): Customer, Lead, Policy, Quotation, Claims
3. **Finance Services** (4): Payment, Commission, Billing
4. **Communication Services** (5): Email, WhatsApp, SMS, Notification
5. **Security Services** (8): Authentication, Authorization, Audit, Device Tracking
6. **Utility Services** (15): PDF, File Storage, Cache, Queue

**📖 Full Documentation**: [core/SERVICE_LAYER.md](core/SERVICE_LAYER.md)

### Middleware Stack

21 middleware providing request filtering, authentication, and security.

**Middleware Types**:
- **Authentication**: Multi-guard support (staff, customer, admin)
- **Tenancy**: Domain-based tenant identification
- **Authorization**: Role and permission checks
- **Security**: CSP, CORS, Turnstile verification
- **Utility**: Cache, Rate limiting, Maintenance mode

**📖 Full Documentation**: [core/MIDDLEWARE_REFERENCE.md](core/MIDDLEWARE_REFERENCE.md)

### Artisan Commands

11 custom Artisan commands for automation and maintenance.

**Command Categories**:
1. **Subscription Management**: Trial processing, expiry checks
2. **Notifications**: Expiry reminders, follow-ups
3. **Maintenance**: Cache cleanup, orphaned file removal
4. **Tenant Operations**: Migration, seeding, backup

**Scheduling**: All commands configured in `app/Console/Kernel.php` with cron expressions.

**📖 Full Documentation**: [core/ARTISAN_COMMANDS.md](core/ARTISAN_COMMANDS.md)

### API Architecture

455 REST API endpoints across 4 portals with complete authentication.

**API Features**:
- RESTful design
- JWT authentication
- Rate limiting
- Pagination support
- Search and filtering
- CORS configuration
- Error handling with standard codes
- Request validation

**📖 Full Documentation**: [API_REFERENCE.md](API_REFERENCE.md)

---

## Quick Feature Lookup

### By Business Function

**Sales & Marketing**:
- [Lead Management](#lead-management) → [modules/LEAD_MANAGEMENT.md](modules/LEAD_MANAGEMENT.md)
- [Quotation System](#quotation-system) → [modules/QUOTATION_SYSTEM.md](modules/QUOTATION_SYSTEM.md)
- [Commission Tracking](#commission-tracking) → [features/COMMISSION_TRACKING.md](features/COMMISSION_TRACKING.md)

**Operations**:
- [Customer Management](#customer-management) → [modules/CUSTOMER_MANAGEMENT.md](modules/CUSTOMER_MANAGEMENT.md)
- [Policy Management](#policy-management) → [modules/POLICY_MANAGEMENT.md](modules/POLICY_MANAGEMENT.md)
- [Claims Management](#claims-management) → [modules/CLAIMS_MANAGEMENT.md](modules/CLAIMS_MANAGEMENT.md)

**Finance**:
- [Subscription Management](#1-subscription-management) → [features/SUBSCRIPTION_MANAGEMENT.md](features/SUBSCRIPTION_MANAGEMENT.md)
- [Payment Gateway](#2-payment-gateway-integration) → [features/PAYMENT_GATEWAY_INTEGRATION.md](features/PAYMENT_GATEWAY_INTEGRATION.md)

**Administration**:
- [User & Role Management](#user--role-management) → [modules/USER_ROLE_MANAGEMENT.md](modules/USER_ROLE_MANAGEMENT.md)
- [Master Data](#master-data-management) → [modules/MASTER_DATA.md](modules/MASTER_DATA.md)

### By Technical Domain

**Security**:
- [Two-Factor Authentication](#3-security--authentication) → [features/TWO_FACTOR_AUTHENTICATION.md](features/TWO_FACTOR_AUTHENTICATION.md)
- [Device Tracking](#3-security--authentication) → [features/DEVICE_TRACKING.md](features/DEVICE_TRACKING.md)
- [Audit Logging](#3-security--authentication) → [features/AUDIT_LOGGING.md](features/AUDIT_LOGGING.md)

**Communication**:
- [Notifications](#4-multi-channel-notifications) → [features/NOTIFICATION_SYSTEM.md](features/NOTIFICATION_SYSTEM.md)
- [PDF Generation](#pdf-generation) → [features/PDF_GENERATION.md](features/PDF_GENERATION.md)

**Infrastructure**:
- [Service Layer](#service-layer-architecture) → [core/SERVICE_LAYER.md](core/SERVICE_LAYER.md)
- [Middleware](#middleware-stack) → [core/MIDDLEWARE_REFERENCE.md](core/MIDDLEWARE_REFERENCE.md)
- [Artisan Commands](#artisan-commands) → [core/ARTISAN_COMMANDS.md](core/ARTISAN_COMMANDS.md)
- [API](#api-architecture) → [API_REFERENCE.md](API_REFERENCE.md)

---

## Related Documentation

### Core Documentation
- [ARCHITECTURE.md](ARCHITECTURE.md) - System architecture overview
- [core/MULTI_PORTAL_ARCHITECTURE.md](core/MULTI_PORTAL_ARCHITECTURE.md) - 4-portal system deep-dive
- [core/DATABASE_SCHEMA.md](core/DATABASE_SCHEMA.md) - Complete database schema
- [API_REFERENCE.md](API_REFERENCE.md) - Complete API reference (455 routes)

### Setup & Operations
- [setup/ENVIRONMENT_CONFIGURATION.md](setup/ENVIRONMENT_CONFIGURATION.md) - Configuration guide
- [operations/DEPLOYMENT.md](operations/DEPLOYMENT.md) - Production deployment
- [operations/TROUBLESHOOTING.md](operations/TROUBLESHOOTING.md) - Issue resolution

### Complete Index
- [README.md](README.md) - Complete documentation index (38 files)

---

**Last Updated**: 2025-01-07
**Version**: 3.0
**For**: Midas Portal Multi-Tenant SaaS Insurance Management System
