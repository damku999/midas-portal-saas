# Insurance Admin Panel - Complete Modules Reference

**Version**: 1.0.0
**Last Updated**: 2025-10-06

This document covers ALL modules in the Insurance Admin Panel system, their features, unique capabilities, and implementation details.

---

## 📑 Table of Contents

1. [Core Business Modules](#core-business-modules)
2. [Master Data Modules](#master-data-modules)
3. [System & Security Modules](#system--security-modules)
4. [Marketing & Communication](#marketing--communication)
5. [Reporting & Analytics](#reporting--analytics)
6. [Customer Portal Modules](#customer-portal-modules)

---

## 🎯 Core Business Modules

### 1. Customer Management (`/customers`)
**Controller**: `CustomerController`
**Model**: `Customer`
**Priority**: 🔴 Critical

#### Features
- Individual customer records with full profile
- Family group linking capability
- Reference user tracking for commission
- Status management (Active/Inactive)
- WhatsApp onboarding message
- Export functionality

#### Unique Capabilities
✨ **Onboarding WhatsApp**: Automated welcome message sent on creation
✨ **Resend Onboarding WA**: Can resend onboarding message anytime
✨ **Family Grouping**: Link customers to family groups for collective management
✨ **Reference Tracking**: Track which reference user brought the customer

#### Key Fields
- Name, Email, Mobile Number
- DOB, Gender, Marital Status
- Address (Line 1, 2, City, State, Pincode)
- Family Group ID (nullable)
- Reference User ID (nullable)
- Status

#### Relationships
- `belongsTo`: FamilyGroup
- `hasMany`: CustomerInsurance, Claims, Quotations
- `belongsTo`: ReferenceUser

#### Routes
```
GET    /customers              - List all
GET    /customers/create       - Create form
POST   /customers/store        - Save new
GET    /customers/show/{id}    - View details
GET    /customers/edit/{id}    - Edit form
PUT    /customers/update/{id}  - Update
GET    /customers/status/{id}/{status} - Toggle status
GET    /customers/export       - Excel export
GET    /customers/resendOnBoardingWA/{id} - Resend WhatsApp
```

---

### 2. Policy Management (`/customer_insurances`)
**Controller**: `CustomerInsuranceController`
**Model**: `CustomerInsurance`
**Priority**: 🔴 Critical

#### Features
- Multi-type insurance policies (Motor, Health, Life, Fire, Marine)
- Premium calculation and tracking
- Addon cover support
- Renewal management with history
- Document storage
- WhatsApp document sharing
- Renewal reminder WhatsApp

#### Unique Capabilities
✨ **WhatsApp Document Sharing**: Send policy documents via WhatsApp
✨ **Renewal Reminders**: Automated WhatsApp reminders (30/15/7/1 days before expiry)
✨ **Policy Renewal**: Renew existing policies with history tracking
✨ **Addon Covers**: Attach multiple addon covers to a single policy
✨ **Premium Breakdown**: Detailed premium calculation (Base + GST + Addon)

#### Key Fields
- Policy Number (unique)
- Customer ID
- Insurance Company ID
- Policy Type ID (Motor/Health/Life/Fire/Marine)
- Premium Type ID
- Start Date, End Date
- Premium Amount, GST Amount
- Addon Covers (JSON)
- Status (Active, Expired, Cancelled)
- Renewal history tracking

#### Relationships
- `belongsTo`: Customer, InsuranceCompany, PolicyType, PremiumType
- `hasMany`: Claims
- `belongsToMany`: AddonCover

#### Routes
```
GET    /customer_insurances              - List all
GET    /customer_insurances/create       - Create form
POST   /customer_insurances/store        - Save new
GET    /customer_insurances/edit/{id}    - Edit form
PUT    /customer_insurances/update/{id}  - Update
GET    /customer_insurances/renew/{id}   - Renew form
PUT    /customer_insurances/storeRenew/{id} - Save renewal
GET    /customer_insurances/sendWADocument/{id} - Send WhatsApp document
GET    /customer_insurances/sendRenewalReminderWA/{id} - Send renewal reminder
GET    /customer_insurances/status/{id}/{status} - Toggle status
GET    /customer_insurances/export       - Excel export
```

---

### 3. Claims Management (`/claims`)
**Controller**: `ClaimController`
**Model**: `Claim`
**Priority**: 🔴 Critical

#### Features
- Claim submission and tracking
- Multi-stage claim workflow
- Document management with status tracking
- Liability details tracking
- WhatsApp notifications (document list, pending docs, claim number)
- Claim statistics dashboard

#### Unique Capabilities
✨ **Multi-Stage Workflow**: Track claim through multiple stages (Surveyor Visit, Assessment, Approval, Payment)
✨ **Document Tracking**: Each document has individual status (Pending, Received, Verified)
✨ **WhatsApp Integration**:
   - Send document checklist
   - Send pending documents reminder
   - Send claim number confirmation
✨ **Liability Tracking**: Track own damage, third party property damage, third party injury
✨ **Claim Number Update**: Update claim number when received from insurance company
✨ **Search Policies**: Ajax search for policies to create claims

#### Key Fields
- Claim Number (unique)
- Customer ID
- Customer Insurance ID (Policy)
- Claim Amount
- Status (Pending, Approved, Rejected, Paid)
- Accident Details (Date, Location, Description)
- Documents Required (JSON array with status)
- Stages (JSON array with timeline)
- Liability Details (JSON)

#### Relationships
- `belongsTo`: Customer, CustomerInsurance
- `morphMany`: ActivityLog

#### Routes
```
GET    /claims                    - List all
GET    /claims/create             - Create form
POST   /claims/store              - Save new
GET    /claims/show/{id}          - View details
GET    /claims/edit/{id}          - Edit form
PUT    /claims/update/{id}        - Update
DELETE /claims/delete/{id}        - Delete
GET    /claims/status/{id}/{status} - Update status
GET    /claims/export             - Excel export
GET    /claims/search-policies    - Ajax search policies
GET    /claims/statistics         - Get claim statistics

# WhatsApp Features
POST   /claims/whatsapp/document-list/{id}      - Send document list
POST   /claims/whatsapp/pending-documents/{id}  - Send pending docs
POST   /claims/whatsapp/claim-number/{id}       - Send claim number
GET    /claims/whatsapp/preview/{id}/{type}     - Preview WhatsApp message

# Document Management
POST   /claims/documents/{claim}/{doc}/update-status - Update document status

# Workflow Management
POST   /claims/stages/{id}/add                  - Add claim stage
POST   /claims/claim-number/{id}/update         - Update claim number
POST   /claims/liability/{id}/update            - Update liability details
```

---

### 4. Quotations (`/quotations`)
**Controller**: `QuotationController`
**Model**: `Quotation`
**Priority**: 🔴 Critical

#### Features
- Professional quotation generation
- Multi-insurance company quotes
- PDF export with branding
- WhatsApp sharing
- Quote conversion tracking

#### Unique Capabilities
✨ **Multiple Quotes**: Generate quotes from multiple insurance companies simultaneously
✨ **PDF Generation**: Professional PDF with logo, breakdown, and terms
✨ **WhatsApp Sharing**: Share quotation PDF directly via WhatsApp
✨ **Dynamic Quote Forms**: Insurance-type-specific quote forms (Motor, Health, Life)
✨ **Quote Comparison**: Side-by-side comparison of different company quotes

#### Key Fields
- Quotation Number (unique)
- Customer ID
- Insurance Type (Motor, Health, Life, Fire, Marine)
- Vehicle/Asset Details (JSON)
- Quotes (JSON array - multiple company quotes)
- Selected Quote ID
- Status (Draft, Sent, Converted, Expired)

#### Relationships
- `belongsTo`: Customer
- `morphMany`: ActivityLog

#### Routes
```
GET    /quotations                        - List all
GET    /quotations/create                 - Create form
POST   /quotations/store                  - Save new
GET    /quotations/show/{id}              - View details
GET    /quotations/edit/{id}              - Edit form
PUT    /quotations/update/{id}            - Update
DELETE /quotations/delete/{id}            - Delete
POST   /quotations/generate-quotes/{id}   - Generate multiple quotes
POST   /quotations/send-whatsapp/{id}     - Send via WhatsApp
GET    /quotations/download-pdf/{id}      - Download PDF
GET    /quotations/get-quote-form         - Get dynamic form HTML
GET    /quotations/export                 - Excel export
```

---

### 5. Family Groups (`/family_groups`)
**Controller**: `FamilyGroupController`
**Model**: `FamilyGroup`
**Priority**: 🟡 High

#### Features
- Group multiple customers under one family
- Primary member designation
- Family-wide policy tracking
- Member management (add/remove)
- Group discounts support

#### Unique Capabilities
✨ **Family Linking**: Link multiple customers as family members
✨ **Primary Member**: Designate primary contact for family
✨ **Group Policies**: Track family floater policies
✨ **Member Removal**: Remove members from family group
✨ **Family Dashboard**: View all policies across family members

#### Key Fields
- Family Name
- Primary Customer ID
- Status
- Total Members (calculated)

#### Relationships
- `hasMany`: Customer (family members)
- `belongsTo`: Customer (primary member)

#### Routes
```
GET    /family_groups              - List all
GET    /family_groups/create       - Create form
POST   /family_groups/store        - Save new
GET    /family_groups/show/{id}    - View details with members
GET    /family_groups/edit/{id}    - Edit form
PUT    /family_groups/update/{id}  - Update
DELETE /family_groups/delete/{id}  - Delete group
GET    /family_groups/status/{id}/{status} - Toggle status
DELETE /family_groups/member/{memberId}     - Remove member
GET    /family_groups/export       - Excel export
```

---

## 📊 Master Data Modules

### 6. Insurance Companies (`/insurance_companies`)
**Controller**: `InsuranceCompanyController`
**Model**: `InsuranceCompany`
**Priority**: 🟡 High

#### Features
- Insurance company master data
- Company details and contact info
- Logo and branding storage
- Status management
- Export functionality

#### Key Fields
- Company Name
- Email, Phone
- Address
- Logo (image upload)
- Status

#### Relationships
- `hasMany`: CustomerInsurance, Quotations

---

### 7. Brokers (`/brokers`)
**Controller**: `BrokerController`
**Model**: `Broker`
**Priority**: 🟡 High

#### Features
- Broker/Agent management
- Commission tracking
- Contact details
- Status management
- Export functionality

#### Key Fields
- Name, Email, Mobile
- Commission Percentage
- Address
- Status

---

### 8. Relationship Managers (`/relationship_managers`)
**Controller**: `RelationshipManagerController`
**Model**: `RelationshipManager`
**Priority**: 🟡 High

#### Features
- RM assignment to customers
- Performance tracking
- Contact management
- Status management
- Export functionality

#### Key Fields
- Name, Email, Mobile
- Employee Code
- Status

---

### 9. Reference Users (`/reference_users`)
**Controller**: `ReferenceUsersController`
**Model**: `ReferenceUser`
**Priority**: 🟡 High

#### Features
- Customer referral tracking
- Commission management
- Reference performance metrics
- Status management
- Export functionality

#### Unique Capabilities
✨ **Referral Tracking**: Track which customers were referred by each reference user
✨ **Commission Calculation**: Automatic commission calculation based on referrals
✨ **Performance Metrics**: Track total referrals and conversions

#### Key Fields
- Name, Email, Mobile
- Commission Percentage
- Total Referrals (calculated)
- Status

#### Relationships
- `hasMany`: Customer (referred customers)

---

### 10. Policy Types (`/policy_types`)
**Controller**: `PolicyTypeController`
**Model**: `PolicyType`
**Priority**: 🟢 Medium

#### Features
- Policy type master (Motor, Health, Life, Fire, Marine)
- Type-specific field configuration
- Status management
- Export functionality

#### Key Fields
- Type Name (Motor, Health, Life, Fire, Marine)
- Description
- Required Fields (JSON)
- Status

---

### 11. Premium Types (`/premium_types`)
**Controller**: `PremiumTypeController`
**Model**: `PremiumType`
**Priority**: 🟢 Medium

#### Features
- Premium type master (Comprehensive, Third Party, etc.)
- Type-specific settings
- Status management
- Export functionality

#### Key Fields
- Type Name
- Description
- Coverage Details
- Status

---

### 12. Addon Covers (`/addon_covers`)
**Controller**: `AddonCoverController`
**Model**: `AddonCover`
**Priority**: 🟢 Medium

#### Features
- Addon cover master data
- Pricing information
- Applicability rules
- Status management
- Export functionality

#### Key Fields
- Cover Name
- Description
- Price/Premium
- Applicable Policy Types
- Status

---

### 13. Fuel Types (`/fuel_types`)
**Controller**: `FuelTypeController`
**Model**: `FuelType`
**Priority**: 🟢 Medium

#### Features
- Fuel type master (Petrol, Diesel, CNG, Electric)
- Premium variation by fuel type
- Status management
- Export functionality

#### Key Fields
- Type Name
- Description
- Premium Multiplier (optional)
- Status

---

### 14. Branches (`/branches`)
**Controller**: `BranchController`
**Model**: `Branch`
**Priority**: 🟢 Medium

#### Features
- Branch/Office management
- Location details
- Contact information
- Status management
- Export functionality

#### Key Fields
- Branch Name
- Code
- Address
- Contact Details
- Status

---

## 🔐 System & Security Modules

### 15. Users (`/users`)
**Controller**: `UserController`
**Model**: `User`
**Priority**: 🔴 Critical

#### Features
- Admin user management
- Role assignment (via Spatie Permission)
- 2FA support
- Status management
- Export functionality

#### Unique Capabilities
✨ **Two-Factor Authentication**: Optional 2FA with QR code setup
✨ **Role-Based Access**: Granular permission control
✨ **Trusted Devices**: Remember trusted devices for 2FA
✨ **Activity Logging**: All user actions logged

#### Key Fields
- Name, Email, Password (hashed)
- Role (via Spatie)
- google2fa_enabled (boolean)
- google2fa_secret (encrypted)
- google2fa_recovery_codes (encrypted)
- trusted_devices (JSON)
- Status

---

### 16. Roles & Permissions (`/roles`, `/permissions`)
**Controllers**: `RolesController`, `PermissionsController`
**Package**: Spatie Laravel Permission
**Priority**: 🔴 Critical

#### Features
- Role management (Admin, Manager, Staff, etc.)
- Permission assignment
- Role-permission mapping
- CRUD operations

#### Unique Capabilities
✨ **Granular Permissions**: Fine-grained access control
✨ **Role Hierarchy**: Inherit permissions from roles
✨ **Dynamic Authorization**: Real-time permission checks

---

### 17. App Settings (`/app_settings`)
**Controller**: `AppSettingController`
**Model**: `AppSetting`
**Priority**: 🔴 Critical

#### Features
- Database-driven configuration
- Encryption support for sensitive settings
- Category-based organization
- Status management (active/inactive)
- Decryption preview (secure)

#### Unique Capabilities
✨ **Encrypted Storage**: AES-256-CBC encryption for sensitive data
✨ **Cache Optimization**: 1-hour TTL cache for performance
✨ **Dynamic Config Loading**: Settings loaded at application boot
✨ **Category Organization**: Settings grouped by category (Application, WhatsApp, Mail, Notifications)
✨ **Type Validation**: Settings have type (string, numeric, boolean, json)

#### Categories
1. **Application** (9 settings) - App name, timezone, locale, currency, date/time formats
2. **WhatsApp** (3 settings) - Sender ID, base URL, auth token (encrypted)
3. **Mail** (8 settings) - SMTP configuration, credentials (encrypted)
4. **Notifications** (4 settings) - Email/WhatsApp toggles, reminder days, birthday wishes

#### Routes
```
GET    /app_settings              - List all
GET    /app_settings/create       - Create form
POST   /app_settings/store        - Save new
GET    /app_settings/show/{id}    - View details
GET    /app_settings/edit/{id}    - Edit form
PUT    /app_settings/update/{id}  - Update
DELETE /app_settings/delete/{id}  - Delete
GET    /app_settings/status/{id}/{status} - Toggle status
GET    /app_settings/{id}/decrypt - View decrypted value (secure)
```

---

### 18. Two-Factor Authentication (`/2fa`)
**Controller**: `TwoFactorAuthController`
**Package**: pragmarx/google2fa-laravel
**Priority**: 🔴 Critical

#### Features
- QR code setup
- Recovery codes (10 codes)
- Trusted device management
- Multi-guard support (Admin + Customer)

#### Unique Capabilities
✨ **Multi-Guard Support**: Works with both admin and customer authentication
✨ **Trusted Devices**: Remember devices for 30 days
✨ **Recovery Codes**: 10 one-time use recovery codes
✨ **Session-Based Guard Detection**: Automatically detects admin vs customer context

#### Routes
```
GET    /2fa                       - 2FA profile page
POST   /2fa/enable                - Enable 2FA and generate secret
POST   /2fa/confirm               - Confirm setup with OTP
POST   /2fa/disable               - Disable 2FA
POST   /2fa/recovery-codes        - Regenerate recovery codes
POST   /2fa/trust-device          - Mark device as trusted
DELETE /2fa/devices/{id}          - Revoke trusted device
GET    /2fa/status                - Get 2FA status

# Challenge Routes (middleware)
GET    /two-factor-challenge      - Show OTP input page
POST   /two-factor-challenge      - Verify OTP
```

---

### 19. Security Dashboard (`/security`)
**Controller**: `SecurityController`
**Priority**: 🟡 High

#### Features
- Security analytics dashboard
- Audit log viewer
- Suspicious activity detection
- High-risk activity monitoring
- Log export functionality

#### Unique Capabilities
✨ **Real-Time Analytics**: Security metrics and charts
✨ **Suspicious Activity Detection**: Automatic flagging of anomalies
✨ **Audit Log Export**: Export security logs for compliance
✨ **Activity Heatmaps**: Visual representation of system usage

#### Routes
```
GET    /security/dashboard               - Security overview
GET    /security/audit-logs              - Audit log viewer
GET    /security/export-logs             - Export logs
GET    /security/api/analytics           - Analytics data (JSON)
GET    /security/api/suspicious-activity - Suspicious events (JSON)
GET    /security/api/high-risk-activity  - High-risk events (JSON)
```

---

## 📧 Marketing & Communication

### 20. Marketing WhatsApp (`/marketing_whatsapp`)
**Controller**: `MarketingWhatsAppController`
**Priority**: 🟡 High

#### Features
- Bulk WhatsApp messaging
- Customer segmentation
- Message preview
- Template management
- Send to filtered customers

#### Unique Capabilities
✨ **Bulk Messaging**: Send to multiple customers at once
✨ **Preview Before Send**: Preview message with customer data
✨ **Segmentation**: Filter by policy type, status, renewal date

#### Routes
```
GET    /marketing_whatsapp         - Campaign management page
POST   /marketing_whatsapp/send    - Send bulk messages
POST   /marketing_whatsapp/preview - Preview message
```

---

## 📊 Reporting & Analytics

### 21. Reports (`/reports`)
**Controller**: `ReportController`
**Priority**: 🟡 High

#### Features
- Dynamic report generation
- Custom column selection
- Date range filtering
- Excel export
- Save column preferences

#### Unique Capabilities
✨ **Dynamic Columns**: Select which columns to include in report
✨ **Save Preferences**: Save column selections per report type
✨ **Multi-Entity Reports**: Reports across customers, policies, claims
✨ **Date Range Analysis**: Flexible date range filtering

#### Routes
```
GET    /reports                        - Report generation page
POST   /reports                        - Generate report
GET    /reports/export                 - Export to Excel
POST   /reports/selected/columns       - Save column preferences
GET    /reports/load/columns/{name}    - Load saved preferences
```

---

## 👤 Customer Portal Modules

### 22. Customer Portal (`/customer/*`)
**Controller**: `CustomerAuthController`
**Guard**: Customer
**Priority**: 🔴 Critical

#### Features
- Customer login/registration
- View own policies
- Submit claims
- View quotations
- Profile management
- 2FA support (optional)

#### Unique Capabilities
✨ **Separate Authentication**: Independent customer guard
✨ **Policy Viewing**: Customers see only their policies
✨ **Claim Submission**: Submit claims directly
✨ **Document Upload**: Upload claim documents
✨ **Renewal Alerts**: View upcoming renewals

#### Key Routes
```
# Authentication
GET/POST /customer/login           - Customer login
GET/POST /customer/register        - Customer registration
POST     /customer/logout          - Customer logout
GET/POST /customer/forgot-password - Password reset

# Dashboard & Profile
GET    /customer/dashboard          - Customer dashboard
GET    /customer/profile            - View profile
POST   /customer/profile/update     - Update profile
POST   /customer/change-password    - Change password

# Policies
GET    /customer/policies           - View own policies
GET    /customer/policies/{id}      - Policy details

# Claims
GET    /customer/claims             - View own claims
GET    /customer/claims/create      - Submit claim
POST   /customer/claims/store       - Save claim
GET    /customer/claims/{id}        - Claim details

# Quotations
GET    /customer/quotations         - View quotations
GET    /customer/quotations/{id}    - Quotation details
```

---

## 🔧 Utility Modules

### 23. Profile Management (`/profile`)
**Controller**: `HomeController`
**Priority**: 🟡 High

#### Features
- View own profile
- Update profile information
- Change password
- Activity history

#### Routes
```
GET    /profile        - View profile
POST   /profile/update - Update profile
POST   /profile/change-password - Change password
```

---

### 24. Common Operations (`/common`)
**Controller**: `CommonController`
**Priority**: 🟢 Medium

#### Features
- Shared delete functionality
- Common AJAX operations

#### Routes
```
POST   /common/delete_common - Generic delete endpoint
```

---

### 25. Health & Monitoring (`/health`)
**Controller**: `HealthController`
**Priority**: 🟢 Medium

#### Features
- Application health checks
- Database connectivity
- Performance metrics
- Resource monitoring
- Log viewing

#### Routes
```
GET    /health                     - Basic health check
GET    /health/detailed            - Detailed health info
GET    /health/liveness            - Liveness probe
GET    /health/readiness           - Readiness probe
GET    /monitoring/metrics         - Application metrics
GET    /monitoring/performance     - Performance stats
GET    /monitoring/resources       - Resource usage
GET    /monitoring/logs            - Log viewer
```

---

## 📋 Module Summary Table

| # | Module | Priority | Export | WhatsApp | 2FA | Unique Feature |
|---|--------|----------|--------|----------|-----|----------------|
| 1 | Customers | 🔴 Critical | ✅ | ✅ | ❌ | Family Grouping |
| 2 | Policies | 🔴 Critical | ✅ | ✅ | ❌ | Renewal System |
| 3 | Claims | 🔴 Critical | ✅ | ✅ | ❌ | Multi-Stage Workflow |
| 4 | Quotations | 🔴 Critical | ✅ | ✅ | ❌ | Multi-Company Quotes |
| 5 | Family Groups | 🟡 High | ✅ | ❌ | ❌ | Member Management |
| 6 | Insurance Companies | 🟡 High | ✅ | ❌ | ❌ | Company Master |
| 7 | Brokers | 🟡 High | ✅ | ❌ | ❌ | Commission Tracking |
| 8 | RMs | 🟡 High | ✅ | ❌ | ❌ | Assignment Tracking |
| 9 | Reference Users | 🟡 High | ✅ | ❌ | ❌ | Referral Tracking |
| 10 | Policy Types | 🟢 Medium | ✅ | ❌ | ❌ | Type Configuration |
| 11 | Premium Types | 🟢 Medium | ✅ | ❌ | ❌ | Coverage Details |
| 12 | Addon Covers | 🟢 Medium | ✅ | ❌ | ❌ | Pricing Rules |
| 13 | Fuel Types | 🟢 Medium | ✅ | ❌ | ❌ | Premium Multiplier |
| 14 | Branches | 🟢 Medium | ✅ | ❌ | ❌ | Location Management |
| 15 | Users | 🔴 Critical | ✅ | ❌ | ✅ | Role-Based Access |
| 16 | Roles | 🔴 Critical | ❌ | ❌ | ❌ | Permission Management |
| 17 | App Settings | 🔴 Critical | ❌ | ❌ | ❌ | Encrypted Config |
| 18 | 2FA | 🔴 Critical | ❌ | ❌ | ✅ | Multi-Guard Support |
| 19 | Security | 🟡 High | ✅ | ❌ | ❌ | Analytics Dashboard |
| 20 | Marketing WhatsApp | 🟡 High | ❌ | ✅ | ❌ | Bulk Messaging |
| 21 | Reports | 🟡 High | ✅ | ❌ | ❌ | Dynamic Columns |
| 22 | Customer Portal | 🔴 Critical | ❌ | ❌ | ✅ | Separate Guard |
| 23 | Profile | 🟡 High | ❌ | ❌ | ❌ | User Preferences |
| 24 | Common | 🟢 Medium | ❌ | ❌ | ❌ | Shared Utilities |
| 25 | Health | 🟢 Medium | ❌ | ❌ | ❌ | Monitoring |

---

## 🚀 Module Integration Flow

### Customer Lifecycle
```
1. Customer Created → Onboarding WhatsApp Sent
2. Quotation Generated → PDF Sent via WhatsApp
3. Policy Purchased → Policy Document via WhatsApp
4. Renewal Reminder → WhatsApp at 30/15/7/1 days before expiry
5. Claim Submitted → Document checklist via WhatsApp
6. Claim Approved → Notification via Email + WhatsApp
```

### Policy Lifecycle
```
1. Policy Created → Customer Notified
2. 30 Days Before Expiry → 1st Renewal Reminder
3. 15 Days Before Expiry → 2nd Renewal Reminder
4. 7 Days Before Expiry → 3rd Renewal Reminder
5. 1 Day Before Expiry → Final Reminder
6. Policy Expired → Status Updated, Report Generated
7. Policy Renewed → New Policy Created, History Maintained
```

### Claim Lifecycle
```
1. Claim Submitted → Document Checklist WhatsApp Sent
2. Documents Received → Status Updated
3. Surveyor Visit → Stage Added
4. Assessment Complete → Stage Added
5. Claim Approved → Notification Sent
6. Payment Processed → Status: Paid
```

---

## 📝 Module Development Guidelines

### Adding New Module Checklist

1. **Controller**
   - Extend base Controller
   - Use ExportableTrait for export functionality
   - Implement CRUD operations
   - Add status management method

2. **Model**
   - Define fillable fields
   - Add relationships
   - Implement scopes (active, status-based)
   - Add accessors/mutators if needed

3. **Routes**
   - Add resource routes or manual routes
   - Include export route if applicable
   - Add status toggle route
   - Group with middleware

4. **Views**
   - Create index.blade.php (list)
   - Create create.blade.php (form)
   - Create edit.blade.php (form)
   - Create show.blade.php (details)

5. **Export Configuration**
   - Override `getExportRelations()`
   - Override `getSearchableFields()`
   - Override `getExportConfig()` with headings and mapping

6. **Documentation**
   - Add to this MODULES.md file
   - Update route documentation
   - Add API documentation if applicable

---

**End of Modules Reference**

*For detailed implementation guides, refer to other documentation files in `/claudedocs/`*
