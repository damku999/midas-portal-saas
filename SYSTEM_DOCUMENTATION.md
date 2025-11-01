# Midas Portal - Complete System Documentation

**Version**: 1.0
**Last Updated**: November 2025
**Framework**: Laravel 10.49.1 | PHP 8.2.12 | MySQL 8.4

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Architecture](#architecture)
3. [Core Features](#core-features)
4. [Database Schema](#database-schema)
5. [API Documentation](#api-documentation)
6. [Security Implementation](#security-implementation)
7. [Notification System](#notification-system)
8. [Deployment Guide](#deployment-guide)
9. [Developer Guide](#developer-guide)
10. [Troubleshooting](#troubleshooting)

---

## System Overview

### Purpose
Midas Portal is a comprehensive insurance management system designed to handle:
- Customer relationship management
- Insurance policy lifecycle management
- Quotation generation and comparison
- Claims processing and tracking
- Multi-channel notifications (Email, WhatsApp, SMS, Push)
- Family group policy sharing
- Commission tracking and reporting

### System Architecture

```
┌─────────────────────────────────────────────────────┐
│           PRESENTATION LAYER                        │
├─────────────────────────────────────────────────────┤
│  Admin Panel  │  Customer Portal  │  API Endpoints │
└─────────────────────────────────────────────────────┘
                        │
┌─────────────────────────────────────────────────────┐
│         APPLICATION LAYER (Services)                │
├─────────────────────────────────────────────────────┤
│ Business Logic │ Notifications │ Security Services │
└─────────────────────────────────────────────────────┘
                        │
┌─────────────────────────────────────────────────────┐
│          DOMAIN LAYER (Models + Events)             │
├─────────────────────────────────────────────────────┤
│   Eloquent Models   │   Domain Events              │
└─────────────────────────────────────────────────────┘
                        │
┌─────────────────────────────────────────────────────┐
│    INFRASTRUCTURE (Repositories + External APIs)    │
├─────────────────────────────────────────────────────┤
│  Data Access  │  WhatsApp  │  SMS  │  Email        │
└─────────────────────────────────────────────────────┘
```

### Technology Stack

**Backend**:
- Laravel 10.49.1 (PHP Framework)
- PHP 8.2.12
- MySQL 8.4
- Laravel Sanctum (API Authentication)
- Spatie Permission (Role-Based Access Control)
- Spatie Activity Log (Audit Trail)

**Frontend**:
- Laravel Mix (Asset Bundling)
- Bootstrap 5.3.2
- jQuery 3.7.1
- Chart.js (Analytics)
- DataTables (Grid Management)

**Security**:
- 2FA with TOTP (Google Authenticator)
- Device Fingerprinting
- Content Security Policy (CSP)
- Cloudflare Turnstile (Bot Protection)

**Testing**:
- Pest PHP 2.36
- PHPUnit 10.5.36
- Playwright (E2E Testing)

**Quality Tools**:
- Laravel Pint (Code Formatting - PSR-12)
- PHPStan (Static Analysis)
- Rector (Automated Refactoring)

---

## Architecture

### Layered Architecture Pattern

#### 1. Controllers (Presentation Layer)
**Location**: `app/Http/Controllers/`

**Responsibility**: HTTP request/response handling

**Controllers by Domain**:
- **Insurance**: `CustomerInsuranceController`, `QuotationController`, `ClaimController`
- **Customers**: `CustomerController`, `FamilyGroupController`
- **Master Data**: `BranchController`, `BrokerController`, `InsuranceCompanyController`
- **Authentication**: `Auth/CustomerAuthController`, `Auth/LoginController`, `TwoFactorAuthController`
- **Notifications**: `NotificationTemplateController`, `NotificationLogController`
- **Security**: `SecurityController`, `CustomerDeviceController`

**Pattern**:
```php
// Controllers delegate to services
public function store(StoreCustomerRequest $request)
{
    $data = $request->validated();
    $customer = $this->customerService->create($data);
    return redirect()->route('customers.index')
        ->with('success', 'Customer created successfully');
}
```

#### 2. Services (Application Layer)
**Location**: `app/Services/`

**Responsibility**: Business logic orchestration

**Key Services**:
- `CustomerService`: Customer lifecycle management
- `PolicyService`: Insurance policy operations
- `QuotationService`: Quotation generation and comparison
- `ClaimService`: Claims processing workflow
- `NotificationLoggerService`: Multi-channel notification tracking
- `TemplateService`: Notification template management
- `TwoFactorAuthService`: 2FA implementation
- `SecurityAuditService`: Security event tracking

**Pattern**:
```php
// Services contain business logic
public function create(array $data): Customer
{
    DB::transaction(function () use ($data) {
        $customer = $this->repository->create($data);
        event(new CustomerRegistered($customer));
        $this->sendWelcomeNotification($customer);
        return $customer;
    });
}
```

#### 3. Repositories (Infrastructure Layer)
**Location**: `app/Repositories/`

**Responsibility**: Data access abstraction

**Pattern**: Repository Interface → Concrete Implementation

**Example**:
```php
// Interface
interface CustomerRepositoryInterface
{
    public function find(int $id): ?Customer;
    public function create(array $data): Customer;
    public function update(int $id, array $data): Customer;
}

// Implementation
class CustomerRepository implements CustomerRepositoryInterface
{
    public function find(int $id): ?Customer
    {
        return Customer::find($id);
    }
}

// Binding (in RepositoryServiceProvider)
$this->app->bind(
    CustomerRepositoryInterface::class,
    CustomerRepository::class
);
```

#### 4. Models (Domain Layer)
**Location**: `app/Models/`

**Key Models**:
- `Customer`: Customer entity with authentication
- `CustomerInsurance`: Insurance policy records
- `Quotation`: Multi-company quotation comparison
- `Claim`: Insurance claim tracking
- `NotificationLog`: Notification history
- `TwoFactorAuth`: 2FA authentication

**Relationships**:
```php
// Customer → Insurance → Claims
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
  ├── belongsTo: Broker
  └── hasMany: Claim
```

### Event-Driven Architecture

**Events** (`app/Events/`):
```php
// Customer Events
CustomerRegistered
CustomerEmailVerified
CustomerProfileUpdated

// Insurance Events
PolicyCreated
PolicyRenewed
PolicyExpiringWarning

// Quotation Events
QuotationGenerated
QuotationRequested

// Document Events
PDFGenerationRequested
```

**Listeners** (`app/Listeners/`):
- Send notifications
- Update audit logs
- Trigger workflows
- Generate documents

---

## Core Features

### 1. Customer Management

#### Features:
- Customer registration with email verification
- Family group management (shared policies)
- Document upload (PAN, Aadhar, GST)
- Anniversary tracking (birthdays, weddings)
- Notification preferences

#### Customer Types:
- **Retail**: Individual customers
- **Corporate**: Business entities

#### Family Groups:
- Family head can view all family members' policies
- Privacy-safe data masking for non-heads
- Shared policy access with role-based permissions

#### API Endpoints:
```
GET    /customers                     # List all customers
POST   /customers/store               # Create customer
GET    /customers/show/{customer}     # View details
PUT    /customers/update/{customer}   # Update customer
GET    /customers/export              # Export to Excel
POST   /customers/resendOnBoardingWA  # Resend WhatsApp onboarding
```

### 2. Insurance Policy Management

#### Policy Types:
- **Vehicle Insurance**: Two-wheeler, four-wheeler, commercial
- **Life Insurance**: Term, endowment, pension plans
- **Health Insurance**: Individual, family floater

#### Features:
- Policy issuance and renewal tracking
- Premium calculation with GST
- Commission tracking (own, transfer, reference)
- NCB (No Claim Bonus) management
- Expiry reminders via WhatsApp/Email
- Document storage and retrieval

#### Premium Calculation:
```
Total Premium = OD Premium + TP Premium + Addon Premiums
GST Amount = Total Premium × 18%
Final Premium = Total Premium + GST + Roadside Assistance
```

#### Commission Breakdown:
- **My Commission**: Direct earnings
- **Transfer Commission**: Shared with partners
- **Reference Commission**: Paid to referrers
- **Actual Earnings**: Net after all deductions

#### API Endpoints:
```
GET    /customer_insurances                    # List policies
POST   /customer_insurances/store              # Create policy
PUT    /customer_insurances/update/{id}        # Update policy
GET    /customer_insurances/renew/{id}         # Renew policy
POST   /customer_insurances/storeRenew/{id}    # Save renewal
GET    /customer_insurances/sendWADocument     # Send via WhatsApp
```

### 3. Quotation System

#### Multi-Company Comparison:
- Compare quotes from multiple insurance companies
- Side-by-side premium comparison
- Addon coverage analysis
- Recommendation engine

#### Quotation Features:
- Vehicle details capture (make, model, RTO, fuel type)
- IDV calculation (Insured Declared Value)
- Addon coverage selection
- PDF quotation generation
- WhatsApp sharing

#### Quotation Companies Table:
Each quotation can have multiple company quotes with:
- Premium breakdown (OD, TP, Net, GST)
- Addon coverage details
- Recommendation flag
- Benefits and exclusions
- Ranking for comparison

#### API Endpoints:
```
GET    /quotations                         # List quotations
POST   /quotations/store                   # Create quotation
POST   /quotations/generate-quotes/{id}    # Generate multi-company quotes
GET    /quotations/show/{id}               # View quotation
GET    /quotations/download-pdf/{id}       # Download PDF
POST   /quotations/send-whatsapp/{id}      # Send via WhatsApp
```

### 4. Claims Management

#### Claim Workflow:
1. **Claim Registration**: Customer files claim
2. **Document Collection**: Required documents uploaded
3. **Claim Processing**: Stage-based workflow
4. **Liability Assessment**: Determine coverage
5. **Settlement**: Payment processing

#### Claim Stages:
- Claim Registered
- Documents Submitted
- Under Investigation
- Approved/Rejected
- Settlement Initiated
- Settled

#### Document Tracking:
- Required documents checklist
- Submission status
- Document verification
- Pending documents alerts

#### Liability Types:
- Own Damage (OD)
- Third Party (TP)
- Both

#### API Endpoints:
```
GET    /insurance-claims                      # List claims
POST   /insurance-claims/store                # Register claim
GET    /insurance-claims/show/{id}            # View claim details
POST   /insurance-claims/stages/{id}/add      # Add stage
POST   /insurance-claims/liability/{id}/update # Update liability
POST   /insurance-claims/whatsapp/...         # WhatsApp notifications
```

### 5. Notification System

#### Channels:
- **Email**: SMTP integration (Hostinger)
- **WhatsApp**: API integration with delivery tracking
- **SMS**: Bulk SMS provider integration
- **Push**: Firebase Cloud Messaging for mobile apps

#### Template Management:
- Dynamic variable substitution
- Multi-channel support (same template → all channels)
- Version control (track template changes)
- Test mode with validation
- Available variables registry

#### Template Variables:
```
Customer Variables:
- {{customer.name}}
- {{customer.email}}
- {{customer.mobile_number}}
- {{customer.date_of_birth}}

Policy Variables:
- {{policy.policy_no}}
- {{policy.premium_amount}}
- {{policy.expired_date}}
- {{policy.insurance_company.name}}

Quotation Variables:
- {{quotation.quote_number}}
- {{quotation.total_premium}}
- {{quotation.vehicle_details}}

Claim Variables:
- {{claim.claim_number}}
- {{claim.claim_date}}
- {{claim.status}}
```

#### Notification Flow:
```
1. Event Triggered (PolicyCreated, QuotationGenerated, etc.)
2. Template Selection (based on notification type)
3. Variable Resolution (populate template with data)
4. Multi-Channel Dispatch (Email, WhatsApp, SMS)
5. Delivery Tracking (sent, delivered, read, failed)
6. Retry Logic (exponential backoff: 1h, 4h, 24h)
```

#### Notification Types:
- Welcome & Onboarding
- Policy Issued
- Policy Expiry Reminder (30, 15, 7 days)
- Birthday Wishes
- Anniversary Greetings
- Claim Status Updates
- Quotation Shared
- Payment Reminders

#### API Endpoints:
```
GET    /notification-templates                # List templates
POST   /notification-templates/store          # Create template
POST   /notification-templates/preview        # Preview with variables
POST   /notification-templates/send-test      # Send test notification
GET    /admin/notification-logs               # View notification history
POST   /admin/notification-logs/{id}/resend   # Retry failed notification
```

---

## Database Schema

### Core Tables

#### customers
**Purpose**: Customer master data
```sql
Columns:
- id (PK)
- name, email, mobile_number
- date_of_birth, wedding_anniversary_date, engagement_anniversary_date
- type (Retail/Corporate)
- family_group_id (FK → family_groups)
- password, email_verified_at (for portal access)
- notification_preferences (JSON)
- pan_card_number, aadhar_card_number, gst_number
- status, created_at, updated_at, deleted_at

Relationships:
- belongsTo: FamilyGroup
- hasMany: CustomerInsurance, Quotation, Claim, NotificationLog
- hasMany: CustomerDevice (for push notifications)
```

#### customer_insurances
**Purpose**: Insurance policy records
```sql
Columns:
- id (PK)
- customer_id (FK → customers)
- branch_id, broker_id, insurance_company_id
- policy_type_id, premium_type_id, fuel_type_id
- policy_no, registration_no, rto, make_model
- issue_date, start_date, expired_date, tp_expiry_date
- od_premium, tp_premium, net_premium, gst, final_premium_with_gst
- my_commission_%, my_commission_amount
- transfer_commission_%, transfer_commission_amount
- reference_commission_%, reference_commission_amount
- ncb_percentage, mode_of_payment
- policy_document_path
- is_renewed, renewed_date, new_insurance_id

Indexes:
- customer_id, insurance_company_id
- expired_date (for renewal reminders)
- status
```

#### quotations
**Purpose**: Multi-company quotation comparison
```sql
Columns:
- id (PK)
- customer_id (FK)
- quote_number (unique)
- vehicle_registration_no, make_model, mfg_year
- fuel_type_id, rto
- quotation_date, valid_until
- status (Draft, Generated, Shared, Converted)

Relationships:
- belongsTo: Customer
- hasMany: QuotationCompany (multiple insurance company quotes)
```

#### quotation_companies
**Purpose**: Individual company quotes within a quotation
```sql
Columns:
- id (PK)
- quotation_id (FK → quotations)
- insurance_company_id (FK)
- quote_number (unique per company)
- policy_type, policy_tenure_years
- idv_vehicle, idv_trailer, idv_accessories
- basic_od_premium, tp_premium, ncb_percentage
- addon_covers_breakdown (JSON)
- total_addon_premium
- net_premium, sgst_amount, cgst_amount, total_premium
- is_recommended, recommendation_note
- ranking (for comparison view)
- benefits, exclusions (TEXT)
```

#### claims
**Purpose**: Insurance claim tracking
```sql
Columns:
- id (PK)
- customer_insurance_id (FK)
- customer_id (FK)
- claim_number (unique)
- claim_date, incident_date
- claim_type (OD, TP, Both)
- status (Registered, Under Review, Approved, Rejected, Settled)
- estimated_amount, approved_amount, settled_amount
- description, notes

Relationships:
- belongsTo: CustomerInsurance, Customer
- hasMany: ClaimDocument, ClaimStage, ClaimLiabilityDetail
```

#### notification_logs
**Purpose**: Multi-channel notification history
```sql
Columns:
- id (PK)
- notifiable_type, notifiable_id (polymorphic)
- notification_type_id (FK)
- template_id (FK)
- channel (email, whatsapp, sms, push)
- recipient (email/phone)
- subject, message_content
- variables_used (JSON)
- status (pending, sent, delivered, read, failed)
- sent_at, delivered_at, read_at
- error_message, retry_count, next_retry_at
- api_response (JSON)

Indexes:
- status, channel, sent_at
- notifiable_type + notifiable_id
```

#### notification_templates
**Purpose**: Reusable notification templates
```sql
Columns:
- id (PK)
- notification_type_id (FK)
- name, code (unique)
- channel (email, whatsapp, sms, push)
- subject, template_content
- available_variables (JSON)
- is_active, version_number

Relationships:
- hasMany: NotificationTemplateVersion (version history)
```

### Security Tables

#### two_factor_auth
**Purpose**: 2FA authentication records
```sql
Columns:
- id (PK)
- authenticatable_type, authenticatable_id (polymorphic)
- secret (encrypted TOTP secret)
- recovery_codes (encrypted JSON)
- enabled_at, confirmed_at
- is_active
- backup_method, backup_destination
```

#### device_tracking
**Purpose**: Device fingerprinting and trust management
```sql
Columns:
- id (PK)
- trackable_type, trackable_id (polymorphic)
- device_id (unique fingerprint)
- device_name, device_type, browser, os
- user_agent, fingerprint_data (JSON)
- trust_score (0-100)
- is_trusted, trusted_at, trust_expires_at
- login_count, failed_login_attempts
- is_blocked, blocked_reason, blocked_at

Indexes:
- device_id (unique)
- trust_score + is_trusted
```

#### audit_logs
**Purpose**: Comprehensive activity audit trail
```sql
Columns:
- id (PK)
- auditable_type, auditable_id (polymorphic)
- actor_type, actor_id (who performed action)
- action, event, event_category
- properties, old_values, new_values (JSON)
- ip_address, user_agent, session_id
- occurred_at
- risk_score (decimal), risk_level, risk_factors (JSON)
- is_suspicious

Indexes:
- action, event_category, occurred_at
- is_suspicious
- risk_level
```

---

## API Documentation

### Authentication

#### Admin Authentication
**Guard**: `web`
**Middleware**: `auth`

```http
POST /login
Content-Type: application/x-www-form-urlencoded

email=admin@example.com&password=secret
```

#### Customer Authentication
**Guard**: `customer`
**Middleware**: `auth:customer`

```http
POST /customer/login
Content-Type: application/x-www-form-urlencoded

email=customer@example.com&password=secret
```

#### API Token Authentication (Sanctum)
**Middleware**: `auth:sanctum`

```http
GET /api/customer/devices
Authorization: Bearer {token}
```

### Customer Device API

#### Register Device
```http
POST /api/customer/device/register
Authorization: Bearer {token}
Content-Type: application/json

{
  "device_id": "unique-device-fingerprint",
  "device_name": "iPhone 14 Pro",
  "device_type": "mobile",
  "fcm_token": "firebase-cloud-messaging-token",
  "platform": "iOS 17.0"
}

Response 201:
{
  "success": true,
  "device": {
    "id": 1,
    "device_id": "...",
    "is_active": true,
    "created_at": "2025-11-01T10:00:00Z"
  }
}
```

#### Send Heartbeat
```http
POST /api/customer/device/heartbeat
Authorization: Bearer {token}
Content-Type: application/json

{
  "device_id": "unique-device-fingerprint",
  "location": {
    "latitude": 19.0760,
    "longitude": 72.8777
  }
}

Response 200:
{
  "success": true,
  "last_seen": "2025-11-01T10:05:00Z"
}
```

### Notification API

#### Get Notification History
```http
GET /admin/notification-logs?channel=whatsapp&status=sent&per_page=50
Authorization: Cookie (session)

Response 200:
{
  "data": [
    {
      "id": 123,
      "channel": "whatsapp",
      "recipient": "+919876543210",
      "status": "delivered",
      "sent_at": "2025-11-01T09:00:00Z",
      "delivered_at": "2025-11-01T09:00:05Z",
      "template": {
        "name": "Policy Expiry Reminder"
      }
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 50,
    "total": 1250
  }
}
```

#### Resend Failed Notification
```http
POST /admin/notification-logs/123/resend
Authorization: Cookie (session)

Response 200:
{
  "success": true,
  "message": "Notification queued for retry",
  "retry_count": 2
}
```

### Webhook Endpoints

#### WhatsApp Delivery Status
```http
POST /webhooks/whatsapp/delivery-status
Content-Type: application/json

{
  "notification_log_id": 123,
  "status": "delivered",
  "provider_message_id": "wamid.xxx",
  "delivered_at": "2025-11-01T09:00:05Z"
}

Response 200:
{
  "success": true,
  "message": "Status updated"
}
```

#### Email Delivery Status
```http
POST /webhooks/email/delivery-status
Content-Type: application/json

{
  "notification_log_id": 456,
  "status": "delivered",
  "smtp_message_id": "<message@example.com>",
  "delivered_at": "2025-11-01T09:01:00Z"
}
```

---

**This is Part 1 of the System Documentation. The document continues with:**
- Security Implementation Details
- Deployment Guide
- Developer Onboarding
- Troubleshooting Guide

**See**: `SECURITY_DOCUMENTATION.md`, `DEPLOYMENT_GUIDE.md`, `DEVELOPER_GUIDE.md`
