# Insurance Admin Panel - Complete System Architecture

**Project:** Insurance Management System (Admin Panel + Customer Portal)
**Framework:** Laravel 10.x
**PHP Version:** 8.1+
**Database:** MySQL
**Generated:** 2025-10-06

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Complete Module Inventory](#complete-module-inventory)
3. [Authentication & Authorization Architecture](#authentication--authorization-architecture)
4. [Database Architecture](#database-architecture)
5. [Integration Points](#integration-points)
6. [Background Jobs & Automation](#background-jobs--automation)
7. [Security Architecture](#security-architecture)
8. [Application Configuration System](#application-configuration-system)
9. [Data Flow Diagrams](#data-flow-diagrams)
10. [Technology Stack](#technology-stack)
11. [Future Enhancement Opportunities](#future-enhancement-opportunities)

---

## System Overview

### Purpose
A comprehensive insurance management platform designed for insurance brokers/agents to manage policies, customers, quotations, claims, and business operations across multiple insurance types (Vehicle, Health, Life).

### Architecture Pattern
- **Pattern:** Monolithic MVC with Service Layer
- **Frontend:** Blade Templates + Bootstrap + jQuery
- **Backend:** Laravel 10 with Repository Pattern (partial)
- **API Strategy:** Web-based (no public API, internal services only)

### Dual Portal Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    Insurance Admin Panel                     │
├──────────────────────┬──────────────────────────────────────┤
│   ADMIN PORTAL       │        CUSTOMER PORTAL               │
│   (Guard: web)       │        (Guard: customer)             │
├──────────────────────┼──────────────────────────────────────┤
│ - Full CRUD access   │ - View-only policies                 │
│ - Reports & Analytics│ - View quotations                    │
│ - User management    │ - View claims                        │
│ - System settings    │ - Profile management                 │
│ - Claims processing  │ - Family member management           │
│ - WhatsApp campaigns │ - 2FA setup                          │
│ - PDF generation     │ - Password management                │
└──────────────────────┴──────────────────────────────────────┘
```

---

## Complete Module Inventory

### Core Business Modules (15)

#### 1. **Customer Management**
- **Purpose:** Manage retail and corporate insurance customers
- **Controller:** `CustomerController`
- **Model:** `Customer` (Authenticatable)
- **Database:** `customers` table
- **Key Features:**
  - Dual customer types (Retail/Corporate)
  - Document management (PAN, Aadhar, GST)
  - Anniversary tracking (Wedding, Engagement, Birthday)
  - Family group integration
  - Customer portal authentication
  - Email verification & password reset
  - Privacy-safe data masking
- **Relationships:**
  - HasMany: CustomerInsurance, Quotation, Claim
  - BelongsTo: FamilyGroup, CustomerType
  - HasOne: FamilyMember
- **Unique Features:**
  - Auto-generated passwords with forced change
  - Masked PAN/mobile display for privacy
  - Date format conversion (UI: d/m/Y, DB: Y-m-d)
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 2. **Insurance Policy Management (Customer Insurance)**
- **Purpose:** Manage active insurance policies across all types
- **Controller:** `CustomerInsuranceController`
- **Model:** `CustomerInsurance`
- **Database:** `customer_insurances` table
- **Key Features:**
  - Multi-type insurance (Vehicle, Health, Life)
  - Premium calculations (OD, TP, GST breakdown)
  - Commission tracking (My, Transfer, Reference)
  - NCB percentage tracking
  - Policy document management
  - Renewal workflows
  - WhatsApp document delivery
  - Renewal reminder automation
- **Relationships:**
  - BelongsTo: Customer, Branch, Broker, RelationshipManager, InsuranceCompany, PolicyType, PremiumType, FuelType
  - HasMany: Claim
- **Commission Structure:**
  - My Commission (%)
  - Transfer Commission (%)
  - Reference Commission (%)
  - Actual Earnings calculation
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 3. **Claims Management**
- **Purpose:** Track and manage insurance claims from initiation to closure
- **Controller:** `ClaimController`
- **Model:** `Claim`
- **Database:** `claims`, `claim_stages`, `claim_documents`, `claim_liability_details` tables
- **Key Features:**
  - Auto-generated claim numbers (CLM{YYYY}{MM}{0001})
  - Multi-stage claim tracking with history
  - Document checklist management (20 docs for Vehicle, 10 for Health)
  - Liability detail tracking (for Vehicle claims)
  - WhatsApp notifications (Document list, Pending docs, Claim number)
  - Email notification system (optional per claim)
  - Document submission tracking
  - Progress percentage calculation
- **Claim Workflows:**
  1. Initial Registration → Documents Required → Documents Collection → Surveyor Assignment → Approval → Settlement → Closure
- **Document Types:**
  - **Health Insurance:** 10 documents (Patient details, Admission info, Doctor details, etc.)
  - **Vehicle Insurance:** 20 documents (Claim form, Policy copy, RC, DL, Fitness, etc.)
- **WhatsApp Templates:**
  - Initial document list
  - Pending documents reminder
  - Claim number notification
  - Stage update notifications
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 4. **Quotation Management**
- **Purpose:** Generate and manage insurance quotations for customers
- **Controller:** `QuotationController`
- **Model:** `Quotation`, `QuotationCompany`
- **Database:** `quotations`, `quotation_companies`, `quotation_statuses` tables
- **Key Features:**
  - Multi-company quote comparison
  - Dynamic quote generation form
  - PDF quotation generation
  - WhatsApp quote delivery
  - Quote status tracking (Pending, Sent, Accepted, Rejected)
  - Vehicle-specific fields (Registration, Make/Model, Fuel Type)
  - Life insurance fields (Plan name, Premium term, Sum insured)
- **Quote Types:**
  - Vehicle Insurance Quotes
  - Health Insurance Quotes
  - Life Insurance Quotes
- **PDF Generation:** Branded PDF with company comparison table
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 5. **Family Group Management**
- **Purpose:** Group customers into families with hierarchical access control
- **Controller:** `FamilyGroupController`
- **Model:** `FamilyGroup`, `FamilyMember`
- **Database:** `family_groups`, `family_members` tables
- **Key Features:**
  - Family head designation
  - Member relationship tracking (Father, Mother, Son, Daughter, Spouse, etc.)
  - Centralized family policy viewing (for heads)
  - Family member password management (by head)
  - Family member 2FA management (by head)
  - Privacy-safe data display for non-heads
- **Access Control:**
  - Family Head: View all family policies, manage member passwords/2FA
  - Family Member: View only own policies
- **Middleware:** `VerifyFamilyAccess`
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 6. **Broker Management**
- **Purpose:** Manage insurance brokers/agents
- **Controller:** `BrokerController`
- **Model:** `Broker`
- **Database:** `brokers` table
- **Key Features:**
  - Broker profile management
  - License number tracking
  - Contact information
  - Status management
- **Relationships:** HasMany CustomerInsurance
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 7. **Insurance Company Management**
- **Purpose:** Master data for insurance providers
- **Controller:** `InsuranceCompanyController`
- **Model:** `InsuranceCompany`
- **Database:** `insurance_companies` table
- **Key Features:**
  - Company profiles
  - Logo management
  - IRDA registration tracking
  - Contact information
- **Relationships:** HasMany CustomerInsurance, QuotationCompany
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 8. **Reference User Management**
- **Purpose:** Track referral sources for customer acquisition
- **Controller:** `ReferenceUsersController`
- **Model:** `ReferenceUser`
- **Database:** `reference_users` table
- **Key Features:**
  - Referral source tracking
  - Commission allocation
  - Performance tracking
- **Relationships:** Linked to CustomerInsurance via `reference_by`
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 9. **Relationship Manager Management**
- **Purpose:** Manage RMs assigned to customers
- **Controller:** `RelationshipManagerController`
- **Model:** `RelationshipManager`
- **Database:** `relationship_managers` table
- **Key Features:**
  - RM assignment to policies
  - Contact management
  - Performance tracking
- **Relationships:** HasMany CustomerInsurance
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 10. **Branch Management**
- **Purpose:** Manage office branches/locations
- **Controller:** `BranchController`
- **Model:** `Branch`
- **Database:** `branches` table
- **Key Features:**
  - Multi-branch support
  - Location tracking
  - Contact information
- **Relationships:** HasMany CustomerInsurance
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 11. **Policy Type Management**
- **Purpose:** Master data for insurance policy categories
- **Controller:** `PolicyTypeController`
- **Model:** `PolicyType`
- **Database:** `policy_types` table
- **Key Features:**
  - Policy categorization (Vehicle, Health, Life, Travel, etc.)
  - Status management
- **Relationships:** HasMany CustomerInsurance, Quotation
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 12. **Premium Type Management**
- **Purpose:** Define premium calculation types
- **Controller:** `PremiumTypeController`
- **Model:** `PremiumType`
- **Database:** `premium_types` table
- **Key Features:**
  - Premium type categorization
  - Vehicle insurance indicator
  - Status management
- **Relationships:** HasMany CustomerInsurance, Quotation
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 13. **Fuel Type Management**
- **Purpose:** Vehicle fuel type master data
- **Controller:** `FuelTypeController`
- **Model:** `FuelType`
- **Database:** `fuel_types` table
- **Key Features:**
  - Fuel type options (Petrol, Diesel, CNG, Electric, etc.)
  - Used in vehicle insurance
- **Relationships:** HasMany CustomerInsurance
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 14. **Add-on Cover Management**
- **Purpose:** Manage optional insurance add-ons
- **Controller:** `AddonCoverController`
- **Model:** `AddonCover`
- **Database:** `addon_covers` table
- **Key Features:**
  - Add-on cover options
  - Pricing information
  - Status management
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 15. **Advanced Reporting System**
- **Purpose:** Generate custom business intelligence reports
- **Controller:** `ReportController`
- **Model:** `Report`
- **Database:** `reports` table
- **Key Features:**
  - Dynamic column selection (user-customizable)
  - Saved report configurations per user
  - Multi-entity reporting (Customers, Policies, Claims, Quotations, etc.)
  - Date range filtering
  - Excel export with custom columns
  - Report templates saved to DB
- **Report Types:**
  - Customer Reports
  - Policy Reports
  - Claims Reports
  - Commission Reports
  - Renewal Reports
- **Export:** Yes (Excel with custom columns)
- **Status:** Production-ready

---

### Administration & Security Modules (10)

#### 16. **User Management**
- **Purpose:** Admin user account management
- **Controller:** `UserController`
- **Model:** `User` (Authenticatable)
- **Database:** `users` table
- **Key Features:**
  - Role-based access control (via Spatie)
  - Admin/TA/TP roles
  - Two-factor authentication support
  - Security settings
  - Activity logging
- **Authentication:** Uses `web` guard
- **Relationships:** HasMany Reports
- **Export:** Yes (Excel)
- **Status:** Production-ready

#### 17. **Role & Permission Management**
- **Purpose:** RBAC implementation
- **Controllers:** `RolesController`, `PermissionsController`
- **Package:** Spatie Laravel Permission
- **Database:** `roles`, `permissions`, `model_has_roles`, `model_has_permissions`, `role_has_permissions` tables
- **Key Features:**
  - Dynamic role creation
  - Granular permission assignment
  - Role hierarchies
  - Guard-specific permissions (web vs customer)
- **Status:** Production-ready

#### 18. **Two-Factor Authentication (2FA)**
- **Purpose:** Enhanced security for admin and customer accounts
- **Controller:** `TwoFactorAuthController`
- **Models:** `TwoFactorAuth`, `TrustedDevice`, `TwoFactorAttempt`
- **Package:** pragmarx/google2fa-laravel
- **Database:** `two_factor_auths`, `trusted_devices`, `two_factor_attempts` tables
- **Key Features:**
  - QR code generation for TOTP setup
  - Recovery codes (10 single-use codes)
  - Trusted device management (30-day trust)
  - Failed attempt tracking
  - Device fingerprinting
  - Separate implementation for Customer portal
- **Customer-Specific:**
  - Models: `CustomerTwoFactorAuth`, `CustomerTrustedDevice` (in Customer namespace)
  - Trait: `HasCustomerTwoFactorAuth`
  - Family head can disable 2FA for family members
- **Middleware:** `VerifyTwoFactorSession` (checks post-login 2FA)
- **Status:** Production-ready

#### 19. **Security Monitoring & Audit**
- **Purpose:** Track security events and user activity
- **Controller:** `SecurityController`
- **Models:** `AuditLog`, `CustomerAuditLog`, `SecuritySetting`, `DeviceTracking`
- **Database:** `audit_logs`, `customer_audit_logs`, `security_settings`, `device_tracking` tables
- **Key Features:**
  - Comprehensive audit trail (using Spatie Activity Log)
  - Security event logging (login, logout, failed attempts, etc.)
  - Risk score calculation
  - Suspicious activity detection
  - High-risk activity alerts
  - User activity timeline
  - Entity change tracking
  - Security analytics dashboard
  - Device tracking across sessions
- **Audit Actions:**
  - login, logout, failed_login, password_change, 2fa_enabled, 2fa_disabled
  - create, update, delete, view, export, status_change
- **Traits:**
  - `Auditable` (for models)
  - `HasSecuritySettings` (for User)
- **Export:** Audit log export
- **Status:** Production-ready

#### 20. **App Settings Management**
- **Purpose:** Dynamic application configuration without code changes
- **Controller:** `AppSettingController`
- **Model:** `AppSetting`
- **Service:** `AppSettingService`
- **Provider:** `DynamicConfigServiceProvider`
- **Database:** `app_settings` table
- **Key Features:**
  - 70+ configurable settings across 8 categories
  - Database-driven configuration
  - Encrypted sensitive values (passwords, tokens)
  - Category-based organization
  - Type validation (string, integer, boolean, json)
  - Cache-optimized (1 hour TTL)
  - Runtime config injection
  - Decryption API endpoint for secure value retrieval
- **Categories:**
  1. **Application** (14 settings): App name, timezone, locale, currency, date/time formats, pagination
  2. **WhatsApp** (3 settings): Sender ID, Base URL, Auth token
  3. **Mail** (9 settings): SMTP configuration, from address/name, encryption
  4. **Notifications** (5 settings): Email/WhatsApp toggles, renewal reminder days, birthday wishes
  5. **Security** (12 settings): Session timeout, password policy, 2FA enforcement, rate limits
  6. **Business** (8 settings): Commission rates, tax rates, grace periods
  7. **UI** (10 settings): Themes, layouts, dashboard widgets
  8. **Integration** (9 settings): API keys, webhooks, external service configs
- **Helper Functions:**
  - `get_app_setting($key, $default)`
  - `is_whatsapp_notification_enabled()`
  - `is_email_notification_enabled()`
  - `get_renewal_reminder_days()`
  - `is_birthday_wishes_enabled()`
- **Seeder:** `AppSettingsSeeder` with Indian-style defaults
- **Status:** Production-ready
- **Documentation:** `APP_SETTINGS_USAGE_AUDIT.md`, `NOTIFICATION_SETTINGS_IMPLEMENTATION.md`

#### 21. **Health Check & Monitoring**
- **Purpose:** System health monitoring and diagnostics
- **Controller:** `HealthController`
- **Service:** `HealthCheckService`
- **Routes:**
  - `/health` - Basic health check
  - `/health/detailed` - Detailed system status
  - `/health/liveness` - Kubernetes liveness probe
  - `/health/readiness` - Kubernetes readiness probe
  - `/monitoring/metrics` - Performance metrics (Admin only)
  - `/monitoring/performance` - Performance analysis (Admin only)
  - `/monitoring/resources` - Resource usage (Admin only)
  - `/monitoring/logs` - Log viewer (Admin only)
- **Key Features:**
  - Database connectivity check
  - Cache system check
  - Queue system check
  - Storage writability check
  - External API health (WhatsApp)
  - Performance metrics collection
  - Resource usage monitoring
- **Status:** Production-ready

#### 22. **Marketing WhatsApp Campaigns**
- **Purpose:** Bulk WhatsApp message campaigns
- **Controller:** `MarketingWhatsAppController`
- **Service:** `MarketingWhatsAppService`
- **Key Features:**
  - Customer segment selection
  - Message template preview
  - Bulk message sending
  - Campaign tracking
  - Rate limiting compliance
- **Segments:**
  - All active customers
  - Customers with expiring policies
  - Birthday/Anniversary customers
  - Custom filtered segments
- **Status:** Production-ready

#### 23. **Profile Management**
- **Purpose:** User/Customer profile editing
- **Controller:** `HomeController` (Admin), `CustomerAuthController` (Customer)
- **Key Features:**
  - Profile information update
  - Password change with validation
  - Avatar/photo upload
  - Notification preferences
- **Security:**
  - Current password verification required
  - Password complexity enforcement
  - Rate limiting on password changes
- **Status:** Production-ready

#### 24. **Common Utilities**
- **Controller:** `CommonController`
- **Purpose:** Shared functionality across modules
- **Key Features:**
  - Soft delete handler (`deleteCommon` - works across all models)
  - File upload utilities
  - Data validation helpers
- **Status:** Production-ready

#### 25. **Customer Portal Dashboard**
- **Purpose:** Customer-specific dashboard and navigation
- **Controller:** `CustomerAuthController`
- **Routes:** Defined in `routes/customer.php`
- **Key Features:**
  - Dashboard with policy summary
  - Policy listing and details
  - Quotation viewing
  - Claims viewing
  - Profile management
  - Password change
  - 2FA management
  - Family member management (for heads)
  - Document downloads
- **Middleware Stack:**
  - `customer.auth` - Customer authentication
  - `customer.timeout` - Session timeout (15 minutes idle)
  - `customer.family` - Family access verification
  - `throttle` - Rate limiting
- **Rate Limits:**
  - Login: 10/minute
  - Password reset: 5/minute
  - Email verification: 3/minute
  - General routes: 60/minute
  - Downloads: 10/minute
- **Status:** Production-ready

---

### Service Layer Architecture (35+ Services)

The application uses a comprehensive service layer pattern for business logic separation:

#### Core Business Services
1. **CustomerService** - Customer lifecycle management
2. **CustomerInsuranceService** - Policy management logic
3. **ClaimService** - Claims workflow orchestration
4. **QuotationService** - Quote generation and management
5. **FamilyGroupService** - Family relationship logic
6. **BrokerService** - Broker operations
7. **InsuranceCompanyService** - Company management
8. **PolicyService** - Policy-specific operations
9. **PolicyTypeService** - Policy type management
10. **PremiumTypeService** - Premium calculation logic
11. **FuelTypeService** - Fuel type operations
12. **AddonCoverService** - Add-on management
13. **ReferenceUserService** - Referral tracking
14. **RelationshipManagerService** - RM assignment logic
15. **BranchService** - Branch operations

#### Administration Services
16. **UserService** - User account management
17. **RoleService** - Role management
18. **PermissionService** - Permission logic
19. **AppSettingService** - Dynamic configuration (with cache)

#### Security Services
20. **TwoFactorAuthService** - 2FA implementation (Admin)
21. **CustomerTwoFactorAuthService** - 2FA for customers
22. **SecurityService** - Security monitoring
23. **SecurityAuditService** - Audit log analysis
24. **AuditService** - Activity tracking

#### Communication Services
25. **MarketingWhatsAppService** - Bulk messaging
26. **WhatsAppApiTrait** - WhatsApp API integration

#### Infrastructure Services
27. **FileUploadService** - File handling
28. **SecureFileUploadService** - Secure upload with validation
29. **PdfGenerationService** - PDF creation (DomPDF)
30. **ExcelExportService** - Excel generation (Maatwebsite)
31. **ReportService** - Report generation logic
32. **HealthCheckService** - System monitoring
33. **CacheService** - Cache operations
34. **LoggingService** - Structured logging
35. **ErrorTrackingService** - Error monitoring
36. **ContentSecurityPolicyService** - CSP header management

---

### Shared Traits (8)

1. **WhatsAppApiTrait** - WhatsApp API integration with config-driven credentials
2. **ExportableTrait** - Excel export functionality
3. **Auditable** - Activity logging for models
4. **HasTwoFactorAuth** - 2FA for Admin users
5. **HasCustomerTwoFactorAuth** - 2FA for Customers
6. **HasSecuritySettings** - Security configuration
7. **TableRecordObserver** - Auto-tracking created_by/updated_by/deleted_by
8. **HelperTrait** - Common utility methods

---

### Background Jobs & Scheduled Commands (3)

1. **SendRenewalReminders** (`send:renewal-reminders`)
   - Sends WhatsApp reminders for policies expiring in configured days (default: 30, 15, 7, 1)
   - Configurable via App Settings
   - Batch processing (100 at a time)
   - Tracks sent/skipped status

2. **SendBirthdayWishes** (`send:birthday-wishes`)
   - Sends birthday wishes via WhatsApp
   - Configurable toggle via App Settings
   - Filters active customers with birthdays today
   - Tracks delivery status

3. **SecuritySetupCommand** (`security:setup`)
   - Initializes security settings and policies
   - Sets up default security configuration

**Scheduling:** Configure in `app/Console/Kernel.php` to run daily/hourly as needed.

---

### Helper Files (2)

1. **app/helpers.php** - Global helper functions
2. **app/Helpers/SettingsHelper.php** - App Settings helper functions
   - `get_app_setting($key, $default)`
   - `is_whatsapp_notification_enabled()`
   - `is_email_notification_enabled()`
   - `get_renewal_reminder_days()`
   - `is_birthday_wishes_enabled()`
3. **app/Helpers/DateHelper.php** - Date formatting utilities
   - `formatDateForUi($date)` - Converts Y-m-d to d/m/Y
   - `formatDateForDatabase($date)` - Converts d/m/Y to Y-m-d

---

## Authentication & Authorization Architecture

### Multi-Guard System

```
┌─────────────────────────────────────────────────────────────┐
│                    Authentication Guards                     │
├──────────────────────┬──────────────────────────────────────┤
│     WEB GUARD        │       CUSTOMER GUARD                 │
├──────────────────────┼──────────────────────────────────────┤
│ Provider: users      │ Provider: customers                  │
│ Model: User          │ Model: Customer                      │
│ Driver: session      │ Driver: session                      │
│ Login: /login        │ Login: /customer/login               │
│ Logout: /logout      │ Logout: /customer/logout             │
├──────────────────────┼──────────────────────────────────────┤
│ Middleware Stack:    │ Middleware Stack:                    │
│ - auth (web)         │ - customer.auth                      │
│ - role               │ - customer.timeout (15 min idle)     │
│ - permission         │ - customer.family                    │
│ - enhanced.auth      │ - customer.secure                    │
└──────────────────────┴──────────────────────────────────────┘
```

### Authentication Flow

#### Admin Authentication (Web Guard)
```
1. User visits /login
2. LoginController@showLoginForm
3. POST /login with credentials
4. Auth::guard('web')->attempt()
5. Check 2FA requirement
   - If enabled → Redirect to /two-factor-challenge
   - If not → Redirect to /home
6. 2FA Challenge (if required)
   - Verify TOTP or recovery code
   - Check trusted device
   - On success → session('2fa_verified') = true
7. Access granted to admin panel
```

#### Customer Authentication (Customer Guard)
```
1. Customer visits /customer/login
2. CustomerAuthController@showLoginForm
3. POST /customer/login with credentials
4. Auth::guard('customer')->attempt()
5. Check password change requirement
   - If must_change_password → Redirect to change password
6. Check email verification
   - If not verified → Redirect to verification notice
7. Check 2FA requirement
   - If enabled → Redirect to /customer/two-factor-challenge
   - If not → Redirect to /customer/dashboard
8. 2FA Challenge (if required)
   - Verify TOTP or recovery code
   - Check trusted device
   - On success → session('customer_2fa_verified') = true
9. Access granted to customer portal
```

### Authorization Architecture

#### Role-Based Access Control (RBAC)
- **Package:** Spatie Laravel Permission v5.5
- **Implementation:** Guard-aware permissions

**Admin Roles:**
- Super Admin (full access)
- Admin (limited access)
- TA/TP (task-specific access)

**Customer Roles:**
- Customer (basic access)
- Family Head (extended access to family data)

**Permission Structure:**
```
{module}.{action}
Examples:
- customers.view
- customers.create
- customers.edit
- customers.delete
- customers.export
- reports.view
- settings.manage
```

#### Middleware Stack

**Global Middleware:**
- `TrustProxies`
- `HandleCors`
- `PreventRequestsDuringMaintenance`
- `ValidatePostSize`
- `TrimStrings`
- `ConvertEmptyStringsToNull`
- `SecurityHeadersMiddleware` - CSP, XSS, HSTS headers

**Web Middleware Group:**
- `EncryptCookies`
- `AddQueuedCookiesToResponse`
- `StartSession`
- `ShareErrorsFromSession`
- `VerifyCsrfToken`
- `SubstituteBindings`

**Route Middleware:**
- `auth` - Web guard authentication
- `customer.auth` - Customer guard authentication
- `role` - Spatie role check
- `permission` - Spatie permission check
- `customer.family` - Family access verification
- `customer.timeout` - Session timeout enforcement (15 min)
- `customer.secure` - Enhanced security checks
- `enhanced.auth` - Enhanced authorization
- `throttle` - Rate limiting
- `verified` - Email verification check

---

## Database Architecture

### Database Schema Overview

**Total Tables:** 52 (based on migrations count)

### Core Entity Tables (15)

1. **users** - Admin user accounts
   - Primary key: id
   - Authentication fields: email, password, remember_token
   - Profile: first_name, last_name, mobile_number
   - Status: status, email_verified_at
   - Audit: created_by, updated_by, deleted_by, deleted_at

2. **customers** - Customer accounts (Retail/Corporate)
   - Primary key: id
   - Authentication: email, password, remember_token
   - Profile: name, mobile_number, date_of_birth
   - Documents: pan_card_number/path, aadhar_card_number/path, gst_number/path
   - Dates: wedding_anniversary_date, engagement_anniversary_date
   - Password management: password_changed_at, must_change_password, password_reset_token, password_reset_expires_at
   - Email verification: email_verified_at, email_verification_token
   - Family: family_group_id
   - Type: type (Retail/Corporate)
   - Audit: created_by, updated_by, deleted_by, deleted_at

3. **customer_types** - Customer categorization master
   - Type definitions (Retail, Corporate)

4. **family_groups** - Family grouping
   - Primary key: id
   - Fields: name, family_head_id, status
   - Relationships: family_head_id → customers.id

5. **family_members** - Family member mapping
   - Primary key: id
   - Fields: family_group_id, customer_id, relationship, is_head
   - Relationships: Links customers to family_groups

6. **customer_insurances** - Insurance policies
   - Primary key: id
   - Foreign keys: customer_id, branch_id, broker_id, relationship_manager_id, insurance_company_id, policy_type_id, premium_type_id, fuel_type_id, reference_by
   - Policy details: policy_no, registration_no, rto, make_model
   - Dates: issue_date, start_date, expired_date, tp_expiry_date, maturity_date
   - Financial: od_premium, tp_premium, net_premium, gst, final_premium_with_gst
   - Commission: my_commission_%, my_commission_amount, transfer_commission_%, reference_commission_%
   - Vehicle: gross_vehicle_weight, mfg_year, ncb_percentage
   - Life: plan_name, premium_paying_term, policy_term, sum_insured, pension_amount_yearly
   - Status: insurance_status, is_renewed, status
   - Documents: policy_document_path

7. **quotations** - Insurance quotations
   - Primary key: id
   - Foreign keys: customer_id, policy_type_id, premium_type_id, fuel_type_id
   - Vehicle: registration_no, make_model, rto, date_of_registration
   - Life: plan_name, sum_insured, premium_paying_term, policy_term
   - Status: quotation_status_id

8. **quotation_companies** - Quote comparison data
   - Primary key: id
   - Foreign keys: quotation_id, insurance_company_id
   - Pricing: premium_amount, gst_amount, total_amount

9. **quotation_statuses** - Quote status master
   - Statuses: Pending, Sent, Accepted, Rejected, Expired

10. **claims** - Insurance claims
    - Primary key: id
    - Fields: claim_number, customer_id, customer_insurance_id, insurance_type, incident_date
    - Communication: whatsapp_number, send_email_notifications
    - Status: status

11. **claim_stages** - Claim workflow stages
    - Primary key: id
    - Fields: claim_id, stage_name, description, is_current, is_completed, stage_date, notes

12. **claim_documents** - Claim document checklist
    - Primary key: id
    - Fields: claim_id, document_name, description, is_required, is_submitted, document_path, submitted_date

13. **claim_liability_details** - Vehicle claim liability info
    - Primary key: id
    - Fields: claim_id, liability_type, third_party_name, third_party_contact, estimated_amount, description

14. **insurance_companies** - Insurance provider master
    - Fields: name, logo_path, irda_registration, contact_person, email, phone

15. **branches** - Office branch master
    - Fields: name, code, address, city, state, pincode, contact_person, phone, email

### Relationship Tables (9)

16. **brokers** - Insurance brokers
17. **relationship_managers** - RMs assigned to policies
18. **reference_users** - Referral sources
19. **policy_types** - Policy category master
20. **premium_types** - Premium type master (with is_vehicle flag)
21. **fuel_types** - Vehicle fuel type master
22. **addon_covers** - Optional insurance add-ons
23. **commission_types** - Commission categorization
24. **reports** - Saved report configurations

### Security & Audit Tables (12)

25. **audit_logs** - Security audit trail
    - Fields: user_id, action, entity_type, entity_id, old_values, new_values, ip_address, user_agent, risk_score

26. **customer_audit_logs** - Customer-specific audit trail
    - Same structure as audit_logs but for customer guard

27. **two_factor_auths** - 2FA configuration (Admin)
    - Fields: user_id, secret, recovery_codes, enabled, confirmed_at

28. **two_factor_attempts** - 2FA attempt tracking
    - Fields: user_id, ip_address, success, failed_reason

29. **trusted_devices** - Trusted device registry
    - Fields: user_id, device_fingerprint, device_name, ip_address, user_agent, last_used_at, expires_at

30. **security_settings** - User-specific security config
    - Fields: user_id, 2fa_enabled, session_timeout, password_expires_at

31. **device_tracking** - Device session tracking
    - Fields: user_id, session_id, device_fingerprint, ip_address, user_agent, last_activity

32. **Customer 2FA Tables** (Customer Portal):
    - `customer_two_factor_auths` (in App\Models\Customer namespace)
    - `customer_trusted_devices`
    - Similar structure to admin 2FA tables

33. **sessions** - Laravel session storage
    - For database-backed sessions

34. **password_resets** - Password reset tokens (shared by both guards)

35. **security_events** - Security event logging

36. **activity_log** - Spatie Activity Log (comprehensive change tracking)

### Configuration Tables (2)

37. **app_settings** - Dynamic application configuration
    - Fields: key, value, type, category, description, is_encrypted, is_active
    - Categories: application, whatsapp, mail, notifications, security, business, ui, integration

### RBAC Tables (5) - Spatie Permission

38. **roles** - Role definitions
39. **permissions** - Permission definitions
40. **model_has_roles** - Polymorphic role assignments
41. **model_has_permissions** - Polymorphic permission assignments
42. **role_has_permissions** - Role-permission pivot

### Infrastructure Tables (3)

43. **failed_jobs** - Failed queue jobs
44. **migrations** - Laravel migration tracking
45. **personal_access_tokens** - Laravel Sanctum tokens (if API needed)

### Additional Tables (estimated)

46-52. Additional support tables for specific features

### Entity Relationship Diagram (Major Entities)

```
┌─────────────┐
│   Customer  │──────┐
└─────────────┘      │
      │              │
      │ 1:N          │ 1:N
      │              │
      ▼              ▼
┌──────────────────────────┐        ┌─────────────┐
│   CustomerInsurance      │───────▶│   Claim     │
└──────────────────────────┘  1:N   └─────────────┘
      │                                    │
      │ N:1                                │ 1:N
      │                                    ▼
      ▼                              ┌──────────────┐
┌──────────────────┐                │  ClaimStage  │
│ InsuranceCompany │                └──────────────┘
└──────────────────┘                      │
      │                                    │ 1:N
      │ 1:N                                ▼
      │                              ┌─────────────────┐
      ▼                              │ ClaimDocument   │
┌────────────────────┐               └─────────────────┘
│ QuotationCompany   │
└────────────────────┘
      │ N:1
      │
      ▼
┌─────────────┐
│  Quotation  │──────┐
└─────────────┘      │
                     │ N:1
                     │
                     ▼
               ┌─────────────┐
               │  Customer   │
               └─────────────┘
                     │
                     │ N:1
                     ▼
               ┌──────────────┐
               │ FamilyGroup  │
               └──────────────┘
                     │ 1:N
                     ▼
               ┌──────────────┐
               │ FamilyMember │
               └──────────────┘
```

### Key Database Relationships

**Customer Ecosystem:**
- Customer → CustomerInsurance (1:N)
- Customer → Quotation (1:N)
- Customer → Claim (1:N)
- Customer → FamilyGroup (N:1)
- Customer → FamilyMember (1:1)

**Insurance Policy Relationships:**
- CustomerInsurance → Customer (N:1)
- CustomerInsurance → InsuranceCompany (N:1)
- CustomerInsurance → PolicyType (N:1)
- CustomerInsurance → PremiumType (N:1)
- CustomerInsurance → FuelType (N:1)
- CustomerInsurance → Branch (N:1)
- CustomerInsurance → Broker (N:1)
- CustomerInsurance → RelationshipManager (N:1)
- CustomerInsurance → ReferenceUser (N:1 via reference_by)
- CustomerInsurance → Claim (1:N)

**Claims Relationships:**
- Claim → Customer (N:1)
- Claim → CustomerInsurance (N:1)
- Claim → ClaimStage (1:N)
- Claim → ClaimDocument (1:N)
- Claim → ClaimLiabilityDetail (1:1)

**Family Relationships:**
- FamilyGroup → Customer (family_head_id) (1:1)
- FamilyGroup → FamilyMember (1:N)
- FamilyGroup → Customer (family_group_id) (1:N)

**Security Relationships:**
- User → TwoFactorAuth (1:1)
- User → TrustedDevice (1:N)
- User → AuditLog (1:N)
- Customer → CustomerTwoFactorAuth (1:1)
- Customer → CustomerAuditLog (1:N)

---

## Integration Points

### 1. WhatsApp API Integration

**Provider:** BotMasterSender
**Trait:** `WhatsAppApiTrait`
**Configuration:** Dynamic via App Settings

**Endpoints:**
- Base URL: Configurable (default: https://api.botmastersender.com/api/v1/)
- Action: send
- Method: POST

**Authentication:**
- Sender ID: Configurable
- Auth Token: Encrypted in app_settings

**Use Cases:**
1. Customer onboarding messages
2. Policy document delivery
3. Renewal reminders (automated)
4. Birthday wishes (automated)
5. Quotation delivery
6. Claims document checklist
7. Claims pending documents reminder
8. Claims status updates
9. Marketing campaigns

**Rate Limiting:** Handled by provider

**Error Handling:**
- Connection failures
- Invalid numbers
- API errors
- Retry logic

**Message Templates:**
- Customer onboarding
- Policy renewal (Vehicle/Life variants)
- Birthday wishes
- Claim document lists (Health/Vehicle variants)
- Claim pending documents
- Claim number notification
- Stage updates

### 2. Email System Integration

**Driver:** SMTP (Configurable)
**Configuration:** Dynamic via App Settings

**SMTP Settings (Configurable):**
- Host
- Port
- Encryption (TLS/SSL)
- Username
- Password
- From address
- From name

**Use Cases:**
1. Customer email verification
2. Password reset emails
3. Policy notifications
4. Claim notifications (if enabled per claim)
5. Admin notifications

**Email Templates:**
- Customer verification
- Password reset
- Claim created
- Claim stage update
- Claim number assigned
- Claim document request
- Claim closure

**Mailable Classes:**
- `ClaimNotificationMail` (with dynamic content based on type)

### 3. PDF Generation

**Package:** barryvdh/laravel-dompdf v3.1
**Service:** `PdfGenerationService`

**Generated Documents:**
1. **Quotation PDFs**
   - Multi-company comparison table
   - Branded header/footer
   - Customer information
   - Policy details
   - Premium breakdown

2. **Policy Documents**
   - Policy details
   - Terms and conditions
   - Premium schedule

**Features:**
- Custom page sizes
- Landscape/Portrait orientation
- Embedded images/logos
- Indian Rupee (₹) symbol support
- Date formatting (d/m/Y)

### 4. Excel Export

**Package:** maatwebsite/excel v3.1
**Service:** `ExcelExportService`
**Trait:** `ExportableTrait`

**Exportable Modules:**
- Customers
- Customer Insurances
- Quotations
- Claims
- Brokers
- Insurance Companies
- Reference Users
- Relationship Managers
- Branches
- Policy Types
- Premium Types
- Fuel Types
- Add-on Covers
- Family Groups
- Users
- Reports (with custom column selection)

**Export Format:** XLSX
**Features:**
- Column headers
- Data formatting
- Date formatting (d/m/Y)
- Currency formatting (₹)
- Relationship data inclusion

### 5. File Storage

**Driver:** Local (public disk)
**Service:** `FileUploadService`, `SecureFileUploadService`

**Upload Paths:**
- Customer documents: `storage/app/public/customers/`
- Policy documents: `storage/app/public/policies/`
- Claim documents: `storage/app/public/claims/`
- Insurance company logos: `storage/app/public/companies/`
- Quotation PDFs: `storage/app/public/quotations/`

**Validation:**
- File types (PDF, JPG, PNG, DOCX)
- File size limits
- Virus scanning (via SecureFileUploadService)
- MIME type verification

**Access:** Symlink from `public/storage` → `storage/app/public`

### 6. Cache System

**Driver:** Configurable (file, redis, memcached)
**Service:** `CacheService`

**Cached Data:**
- App Settings (1 hour TTL)
- User permissions (dynamic)
- Role assignments (dynamic)
- Report configurations (dynamic)

**Cache Keys:**
- `app_setting_{key}`
- `app_setting_category_{category}`

**Cache Invalidation:**
- Manual flush on setting update
- TTL expiration
- Artisan cache:clear command

### 7. Activity Logging

**Package:** spatie/laravel-activitylog v4.7
**Models:** All major entities
**Trait:** `LogsActivity`

**Logged Entities:**
- Users
- Customers
- CustomerInsurance
- Quotations
- Claims
- Brokers
- InsuranceCompany
- FamilyGroup
- All master data tables

**Log Data:**
- All attributes (`*`)
- Only dirty attributes (changed values)
- Old values
- New values
- User context
- Timestamp

**Storage:** `activity_log` table

---

## Background Jobs & Automation

### Scheduled Commands

**Configuration:** `app/Console/Kernel.php`

1. **Renewal Reminders**
   - Command: `php artisan send:renewal-reminders`
   - Schedule: Daily at configured time
   - Function: Send WhatsApp reminders for policies expiring in X days
   - Days: Configurable (default: 30, 15, 7, 1)
   - Batch size: 100 policies per chunk
   - Filters: is_renewed = 0, status = 1
   - Output: Sent count, skipped count

2. **Birthday Wishes**
   - Command: `php artisan send:birthday-wishes`
   - Schedule: Daily at configured time (morning)
   - Function: Send WhatsApp birthday wishes
   - Toggle: Configurable via App Settings
   - Filters: DOB matches today (month + day), status = 1, mobile not null
   - Output: Sent count, skipped count

3. **Security Setup**
   - Command: `php artisan security:setup`
   - Type: Manual initialization
   - Function: Setup default security settings and policies

### Queue System

**Driver:** Configurable (sync, database, redis)
**Failed Jobs:** Tracked in `failed_jobs` table

**Potential Queue Jobs:**
- Bulk WhatsApp sending (MarketingWhatsApp)
- PDF generation for large batches
- Excel export for large datasets
- Email sending

**Note:** Currently using synchronous execution, ready for async queue implementation.

---

## Security Architecture

### Security Layers

```
┌─────────────────────────────────────────────────────────────┐
│                     Security Layers                          │
├─────────────────────────────────────────────────────────────┤
│ Layer 1: Network Security                                   │
│ - HTTPS enforcement                                          │
│ - Security headers (CSP, HSTS, X-Frame-Options)             │
│ - CORS configuration                                         │
├─────────────────────────────────────────────────────────────┤
│ Layer 2: Application Security                               │
│ - CSRF protection (VerifyCsrfToken)                         │
│ - SQL injection protection (Eloquent ORM, parameterized)    │
│ - XSS protection (Blade escaping, CSP)                      │
│ - Mass assignment protection (fillable/guarded)             │
├─────────────────────────────────────────────────────────────┤
│ Layer 3: Authentication                                      │
│ - Multi-guard system (web, customer)                        │
│ - Password hashing (bcrypt)                                 │
│ - Session management                                         │
│ - Remember tokens                                            │
├─────────────────────────────────────────────────────────────┤
│ Layer 4: Two-Factor Authentication                          │
│ - TOTP (Google Authenticator compatible)                   │
│ - Recovery codes (10 single-use)                            │
│ - Trusted device management (30-day)                        │
│ - Failed attempt tracking                                    │
├─────────────────────────────────────────────────────────────┤
│ Layer 5: Authorization                                       │
│ - RBAC (Spatie Permission)                                  │
│ - Route middleware (role, permission)                       │
│ - Guard-specific permissions                                 │
│ - Family-based access control                                │
├─────────────────────────────────────────────────────────────┤
│ Layer 6: Data Security                                       │
│ - Encrypted sensitive fields (App Settings)                 │
│ - Soft deletes (data retention)                             │
│ - Masked PII display (PAN, mobile, email)                   │
│ - Secure file uploads                                        │
├─────────────────────────────────────────────────────────────┤
│ Layer 7: Audit & Monitoring                                 │
│ - Comprehensive audit logging                                │
│ - Security event tracking                                    │
│ - Failed login attempt tracking                              │
│ - Risk score calculation                                     │
│ - Suspicious activity detection                              │
└─────────────────────────────────────────────────────────────┘
```

### Security Features

**1. Password Security**
- Hashing: bcrypt (cost factor: 10)
- Complexity: Enforced via validation
- Expiration: Configurable
- Reset: Secure token-based with 1-hour expiry
- History: Password change tracking

**2. Session Security**
- Driver: Database (for tracking)
- Lifetime: Configurable via App Settings
- Timeout: 15 minutes idle (Customer portal)
- Regeneration: On login
- Encryption: Enabled

**3. Rate Limiting**
- Login attempts: 10/minute (Admin), 10/minute (Customer)
- Password reset: 5/minute
- Email verification: 3/minute
- API calls: 60/minute
- Downloads: 10/minute
- 2FA attempts: 6/minute
- General routes: Configurable per route

**4. Data Privacy**
- PAN masking: Show first 3 and last 1 character (e.g., CFD*****8P)
- Mobile masking: Show first 2 and last 2 digits
- Email masking: Show first 2 characters of username
- Family data: Privacy-safe display for non-heads

**5. File Upload Security**
- MIME type validation
- File size limits
- Extension whitelisting
- Virus scanning (SecureFileUploadService)
- Storage path randomization
- Access control

**6. API Security**
- CSRF tokens for all POST/PUT/DELETE
- API rate limiting
- Token-based authentication (Sanctum ready)
- Input validation
- Output sanitization

**7. Security Headers** (SecurityHeadersMiddleware)
- Content-Security-Policy
- X-Content-Type-Options: nosniff
- X-Frame-Options: SAMEORIGIN
- X-XSS-Protection: 1; mode=block
- Strict-Transport-Security (HSTS)
- Referrer-Policy: strict-origin-when-cross-origin

### Audit Trail

**Logged Events:**
- User actions: login, logout, password_change, profile_update
- CRUD operations: create, update, delete, view
- Status changes
- Export operations
- 2FA events: enabled, disabled, verified, failed
- Security events: failed_login, suspicious_activity

**Audit Data:**
- User/Customer ID
- Action performed
- Entity type and ID
- Old values (JSON)
- New values (JSON)
- IP address
- User agent
- Risk score
- Timestamp

**Risk Score Calculation:**
- Failed login: +30
- Password change: +10
- 2FA disabled: +20
- Status change: +5
- Delete operation: +15
- Multiple rapid changes: +25

**Retention:** Configurable (recommended: 1 year)

---

## Application Configuration System

### Static Configuration Files

**Location:** `config/` directory

1. **app.php** - Application core config
2. **auth.php** - Authentication guards and providers
3. **database.php** - Database connections
4. **mail.php** - Email configuration (overridden by App Settings)
5. **session.php** - Session handling
6. **cache.php** - Cache drivers
7. **queue.php** - Queue configuration
8. **whatsapp.php** - WhatsApp API config (overridden by App Settings)

### Dynamic Configuration (App Settings)

**Provider:** `DynamicConfigServiceProvider`
**Boot Process:**
1. Load app_settings from database
2. Override config values at runtime
3. Cache for 1 hour

**Overridden Configurations:**

**Application:**
- `app.name`
- `app.timezone`
- `app.locale`
- `app.currency`
- `app.currency_symbol`
- `app.date_format`
- `app.time_format`
- `app.pagination_default`
- `session.lifetime`

**WhatsApp:**
- `whatsapp.sender_id`
- `whatsapp.base_url`
- `whatsapp.auth_token` (encrypted)

**Mail:**
- `mail.default`
- `mail.from.address`
- `mail.from.name`
- `mail.mailers.smtp.host`
- `mail.mailers.smtp.port`
- `mail.mailers.smtp.encryption`
- `mail.mailers.smtp.username`
- `mail.mailers.smtp.password` (encrypted)

**Notifications:**
- `notifications.email_enabled`
- `notifications.whatsapp_enabled`
- `notifications.renewal_reminder_days`
- `notifications.birthday_wishes_enabled`

**Access Pattern:**
```php
// Via helper function
$value = get_app_setting('app_name', 'Default Name');

// Via config facade
$value = config('app.name'); // Returns dynamic value if set

// Direct service call
$value = AppSettingService::get('key', 'default');
```

---

## Data Flow Diagrams

### 1. Customer Onboarding Flow

```
┌──────────────┐
│ Admin Creates│
│  Customer    │
└──────┬───────┘
       │
       ▼
┌────────────────────────────┐
│ System Generates Password  │
│ (8 chars, alphanumeric)    │
└──────┬─────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Set must_change_password=true│
└──────┬───────────────────────┘
       │
       ▼
┌─────────────────────────────┐
│ Generate Verification Token │
└──────┬──────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Send WhatsApp Onboarding Msg │
│ (Login credentials + link)   │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────┐
│ Customer Receives Creds  │
└──────┬───────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Customer Logs In          │
│ (/customer/login)         │
└──────┬────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Forced Password Change    │
└──────┬────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Email Verification        │
└──────┬────────────────────┘
       │
       ▼
┌───────────────────────────┐
│ Access Customer Portal    │
└───────────────────────────┘
```

### 2. Policy Renewal Flow

```
┌──────────────────────┐
│ Scheduled Command    │
│ (Daily Cron Job)     │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Query Policies Expiring In:  │
│ - 30 days                     │
│ - 15 days                     │
│ - 7 days                      │
│ - 1 day                       │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Filter: is_renewed=0,        │
│         status=1             │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Chunk Policies (100 at time)│
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ For Each Policy:             │
│ - Build reminder message     │
│   (Vehicle/Life variant)     │
│ - Get customer mobile        │
│ - Send WhatsApp              │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Track Results:               │
│ - Sent count                 │
│ - Skipped count              │
│ - Errors                     │
└──────────────────────────────┘
```

### 3. Claim Processing Flow

```
┌──────────────────────┐
│ Admin Creates Claim  │
└──────┬───────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Generate Claim Number        │
│ (CLM{YYYY}{MM}{0001})        │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Create Default Documents     │
│ (Based on Insurance Type)    │
│ - Health: 10 docs            │
│ - Vehicle: 20 docs           │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Create Initial Stage         │
│ "Claim Registered"           │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Send Notifications:          │
│ - WhatsApp document list     │
│ - Email (if enabled)         │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Document Collection Phase    │
│ - Customer submits docs      │
│ - Admin marks as received    │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Send Pending Doc Reminders   │
│ (Manual or Automated)        │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Update Claim Stages:         │
│ - Surveyor Assigned          │
│ - Under Review               │
│ - Approved                   │
│ - Settlement Initiated       │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Insurance Company Updates    │
│ Claim Number (if applicable) │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Send Claim Number            │
│ Notification (WhatsApp/Email)│
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Claim Closure                │
│ (Status update + notification)│
└──────────────────────────────┘
```

### 4. Quotation Generation Flow

```
┌──────────────────────────────┐
│ Admin Creates Quotation      │
│ - Select customer            │
│ - Select policy type         │
│ - Enter vehicle/life details │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Select Insurance Companies   │
│ (Multiple for comparison)    │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Enter Premium for Each       │
│ Company (with GST)           │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Save Quotation               │
│ - Main quotation record      │
│ - QuotationCompany records   │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Generate PDF                 │
│ - Company comparison table   │
│ - Customer details           │
│ - Vehicle/Life details       │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Delivery Options:            │
│ - Download PDF               │
│ - Send via WhatsApp          │
│ - Email (optional)           │
└──────────────────────────────┘
```

### 5. App Settings Configuration Flow

```
┌──────────────────────────────┐
│ Application Boot             │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ DynamicConfigServiceProvider │
│ ::boot() triggered           │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Load Settings by Category:   │
│ - application                │
│ - whatsapp                   │
│ - mail                       │
│ - notifications              │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Check Cache (1 hour TTL)     │
│ Key: app_setting_category_X  │
└──────┬───────────────────────┘
       │
       ├─ Cache Hit ───────┐
       │                    ▼
       │              ┌──────────────┐
       │              │ Return Cached│
       │              └──────────────┘
       │
       ├─ Cache Miss ──────┐
       │                    ▼
       │              ┌───────────────────┐
       │              │ Query Database    │
       │              │ (app_settings)    │
       │              └─────┬─────────────┘
       │                    │
       │                    ▼
       │              ┌───────────────────┐
       │              │ Decrypt if needed │
       │              │ (is_encrypted=1)  │
       │              └─────┬─────────────┘
       │                    │
       │                    ▼
       │              ┌───────────────────┐
       │              │ Cache for 1 hour  │
       │              └─────┬─────────────┘
       │                    │
       └────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Override config() values:    │
│ - config(['app.name' => ...])│
│ - config(['mail.host' => ...])│
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Application Ready            │
│ (Uses dynamic config)        │
└──────────────────────────────┘

┌──────────────────────────────┐
│ Admin Updates Setting        │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ AppSettingService::set()     │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Encrypt if is_encrypted=true │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Save to Database             │
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Clear Cache for Key          │
│ Cache::forget('app_setting_X')│
└──────┬───────────────────────┘
       │
       ▼
┌──────────────────────────────┐
│ Next Request Gets New Value  │
└──────────────────────────────┘
```

---

## Technology Stack

### Backend Framework
- **Laravel:** 10.x (PHP Framework)
- **PHP:** 8.1+
- **Database:** MySQL (production), SQLite (testing)

### Authentication & Authorization
- **Laravel Auth:** Built-in multi-guard authentication
- **Spatie Laravel Permission:** v5.5 (RBAC)
- **Laravel Sanctum:** v3.0 (API tokens - ready for use)
- **Google2FA Laravel:** v2.3 (Two-factor authentication)
- **SimpleSoftwareIO QRCode:** v4.2 (QR code generation)

### Frontend
- **Blade Templates:** Laravel templating engine
- **Bootstrap:** CSS framework
- **jQuery:** JavaScript library
- **DataTables:** Advanced table features
- **Select2:** Enhanced select dropdowns
- **SweetAlert2:** Beautiful alerts/modals

### Document Generation
- **DomPDF:** v3.1 (PDF generation)
- **Maatwebsite Excel:** v3.1 (Excel import/export)

### Utilities
- **Guzzle HTTP:** v7.0+ (HTTP client for APIs)
- **Spatie Activity Log:** v4.7 (Audit trail)
- **OpcodesIO Log Viewer:** v3.8 (Log management)

### Development Tools
- **Laravel IDE Helper:** v2.13 (IDE autocomplete)
- **Laravel Migration Generator:** v4.3 (Reverse engineering)
- **Laravel Sail:** v1.0+ (Docker development)
- **Laravel Tinker:** v2.8 (REPL)

### Testing
- **PHPUnit:** v10.0 (Unit testing)
- **Faker:** v1.9+ (Test data generation)
- **Mockery:** v1.4+ (Mocking)

### Code Quality
- **Laravel Ignition:** v2.0 (Error page)
- **Laravel Collision:** v7.0 (CLI error reporting)

### Production Optimization
- **Laravel Boost:** v1.0 (Performance optimization)
- **OPcache:** Recommended for production

### External APIs
- **BotMasterSender:** WhatsApp messaging API

---

## Future Enhancement Opportunities

### High Priority Enhancements

#### 1. **Customer Self-Service Portal Expansion**
- **Current:** View-only access to policies, quotations, claims
- **Enhancement:**
  - Self-service policy renewals
  - Quotation requests by customers
  - Claim initiation by customers
  - Document upload by customers
  - Chat support integration
  - Mobile app (React Native/Flutter)
- **Benefits:** Reduced admin workload, better customer experience

#### 2. **Payment Gateway Integration**
- **Current:** Manual payment tracking
- **Enhancement:**
  - Razorpay/Paytm integration
  - Online premium payment
  - Automated payment reminders
  - Payment status tracking
  - Refund management
  - EMI options
- **Benefits:** Faster collections, automated reconciliation

#### 3. **Advanced Analytics & Dashboard**
- **Current:** Basic reporting
- **Enhancement:**
  - Business intelligence dashboard
  - Commission analytics
  - Renewal rate tracking
  - Customer lifetime value
  - Policy mix analysis
  - Broker performance metrics
  - Predictive analytics (churn prediction)
- **Benefits:** Data-driven decision making

#### 4. **API Development for Third-Party Integration**
- **Current:** Web-only application
- **Enhancement:**
  - RESTful API with Laravel Sanctum
  - API documentation (Swagger/OpenAPI)
  - Webhook system for events
  - Integration with insurance company APIs
  - CRM integrations (Salesforce, HubSpot)
  - Accounting software integration (Tally, QuickBooks)
- **Benefits:** Ecosystem expansion, automation

#### 5. **Document Management System (DMS)**
- **Current:** Basic file uploads
- **Enhancement:**
  - Version control for documents
  - OCR for document data extraction
  - E-signature integration (DocuSign)
  - Document expiry tracking
  - Automated document categorization
  - Document templates
- **Benefits:** Better compliance, reduced manual work

### Medium Priority Enhancements

#### 6. **Marketing Automation**
- Email drip campaigns
- Segmented WhatsApp broadcasts
- Customer journey automation
- Lead nurturing workflows
- A/B testing for messages

#### 7. **AI/ML Features**
- Chatbot for customer queries
- Premium estimation AI
- Claim fraud detection
- Customer churn prediction
- Personalized policy recommendations

#### 8. **Mobile Responsiveness**
- Progressive Web App (PWA)
- Mobile-optimized admin panel
- Touch-friendly interfaces
- Offline capability

#### 9. **Multi-Language Support**
- i18n implementation
- Hindi, Gujarati, Marathi support
- Language switcher
- Localized notifications

#### 10. **Enhanced Security**
- Biometric authentication (mobile)
- IP whitelisting
- Advanced fraud detection
- Penetration testing automation
- Security compliance certifications (ISO 27001)

### Low Priority / Nice-to-Have

#### 11. **Gamification**
- Customer loyalty points
- Broker leaderboards
- Achievement badges
- Referral rewards program

#### 12. **Social Media Integration**
- Share quotations on social media
- Social login (Google, Facebook)
- Social media marketing campaigns
- Review/testimonial collection

#### 13. **Voice Integration**
- Alexa/Google Assistant integration
- Voice-based policy information
- Voice-based claim status

#### 14. **Blockchain for Transparency**
- Immutable audit trail
- Smart contracts for claims
- Decentralized document storage

#### 15. **IoT Integration**
- Telematics for vehicle insurance (usage-based)
- Health device integration (fitness trackers)
- Smart home integration

---

## System Scalability Considerations

### Current Architecture Limitations
- Synchronous processing (no queue workers)
- Single database server
- Local file storage
- No caching layer beyond Laravel cache

### Scalability Roadmap

**Phase 1: Optimize Current Setup (0-1K users)**
- Enable OPcache
- Implement query optimization
- Add database indexing
- Enable Redis/Memcached cache

**Phase 2: Horizontal Scaling (1K-10K users)**
- Queue workers for background jobs
- Load balancer (Nginx/HAProxy)
- Database read replicas
- CDN for static assets (Cloudflare)
- Object storage (AWS S3/MinIO)

**Phase 3: Distributed Architecture (10K+ users)**
- Microservices for heavy modules
- Elasticsearch for search
- Message queues (RabbitMQ/Kafka)
- Database sharding
- Auto-scaling with Kubernetes

---

## Deployment & Environment

### Recommended Production Setup

**Web Server:** Nginx (reverse proxy) + PHP-FPM
**PHP Version:** 8.1+
**Database:** MySQL 8.0+ or MariaDB 10.6+
**Cache:** Redis 6.0+
**Queue:** Redis-backed queues
**Storage:** S3-compatible object storage
**SSL:** Let's Encrypt (auto-renewal)

**Server Requirements:**
- 2+ CPU cores
- 4GB+ RAM
- 50GB+ SSD storage
- HTTPS enabled
- Firewall configured

**PHP Extensions:**
- BCMath
- Ctype
- Fileinfo
- JSON
- Mbstring
- OpenSSL
- PDO (MySQL)
- Tokenizer
- XML
- GD/Imagick
- Zip

**Cron Jobs:**
```bash
# Laravel scheduler (runs every minute)
* * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1

# Scheduled tasks configured in Kernel.php:
# - send:renewal-reminders (Daily at 9 AM)
# - send:birthday-wishes (Daily at 8 AM)
```

---

## Maintenance & Monitoring

### Health Checks
- `/health` - Basic application health
- `/health/detailed` - Comprehensive system status
- `/health/liveness` - Kubernetes liveness probe
- `/health/readiness` - Kubernetes readiness probe

### Logging
- **Driver:** Stack (daily files + syslog)
- **Location:** `storage/logs/laravel.log`
- **Rotation:** Daily
- **Viewer:** OpcodesIO Log Viewer (`/log-viewer`)

### Backup Strategy (Recommended)
- **Database:** Daily automated backups (7-day retention)
- **Files:** Daily incremental, weekly full
- **Configuration:** Version controlled (.env excluded)
- **Encryption:** Encrypt backups at rest

### Monitoring (Recommended)
- Application performance monitoring (New Relic/Datadog)
- Error tracking (Sentry/Bugsnag)
- Uptime monitoring (UptimeRobot/Pingdom)
- Database query performance
- Queue monitoring

---

## Key Files Reference

### Routes
- `routes/web.php` - Admin panel routes (358 lines)
- `routes/customer.php` - Customer portal routes (221 lines)
- `routes/api.php` - API routes (currently unused)
- `routes/console.php` - Artisan commands
- `routes/channels.php` - Broadcast channels

### Configuration
- `config/auth.php` - Multi-guard authentication setup
- `config/whatsapp.php` - WhatsApp API config (overridden by App Settings)
- `app/Providers/DynamicConfigServiceProvider.php` - Runtime config injection

### Models (30+ models)
- Core: `User`, `Customer`, `CustomerInsurance`, `Claim`, `Quotation`
- Relationships: `Broker`, `InsuranceCompany`, `Branch`, etc.
- Security: `TwoFactorAuth`, `AuditLog`, `TrustedDevice`
- Configuration: `AppSetting`

### Controllers (33 controllers)
- Business: `CustomerController`, `CustomerInsuranceController`, `ClaimController`, `QuotationController`
- Auth: `LoginController`, `CustomerAuthController`, `TwoFactorAuthController`
- Admin: `UserController`, `RolesController`, `AppSettingController`

### Services (35+ services)
- See "Service Layer Architecture" section above

### Middleware (16 middleware)
- Global: `SecurityHeadersMiddleware`, `TrustProxies`, `VerifyCsrfToken`
- Auth: `Authenticate`, `CustomerAuth`, `VerifyTwoFactorSession`
- Custom: `CustomerSessionTimeout`, `VerifyFamilyAccess`, `RateLimit`

### Traits (8 traits)
- See "Shared Traits" section above

### Migrations (52 migrations)
- Database schema definition across multiple files

### Seeders
- `AppSettingsSeeder` - 70+ default settings

---

## Conclusion

This Insurance Admin Panel is a **comprehensive, production-ready system** with:
- **25+ functional modules** covering complete insurance business workflow
- **Dual portal architecture** (Admin + Customer) with separate authentication
- **Enterprise-grade security** with 2FA, audit logging, and RBAC
- **70+ configurable settings** for business flexibility
- **Multi-channel communication** (WhatsApp + Email)
- **Extensive integrations** (PDF, Excel, WhatsApp API)
- **Family-based access control** for grouped customers
- **Complete claims management** with automated workflows
- **Advanced reporting** with custom column selection

**Current State:** Production-ready for insurance brokerage operations
**Architecture:** Scalable monolith ready for microservices evolution
**Security Posture:** Enterprise-grade with comprehensive audit trail
**Extensibility:** Service layer pattern enables easy feature additions

**Total Code Complexity:** ~30,000+ lines of application code across 150+ files

---

**Document Version:** 1.0
**Last Updated:** 2025-10-06
**Maintained By:** System Architect
**Contact:** Project Documentation Team
