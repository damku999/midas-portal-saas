# Customer Portal Comprehensive Guide

**Version**: 1.0
**Last Updated**: 2025-10-06
**Author**: Technical Documentation Team

---

## Table of Contents

1. [Overview](#overview)
2. [Authentication & Security](#authentication--security)
3. [Dashboard Features](#dashboard-features)
4. [Core Functionality](#core-functionality)
5. [Family Management](#family-management)
6. [User Flows](#user-flows)
7. [Permissions & Access Control](#permissions--access-control)
8. [Security Features](#security-features)
9. [App Settings Usage](#app-settings-usage)
10. [Future Enhancements](#future-enhancements)
11. [Mobile Responsiveness](#mobile-responsiveness)
12. [Technical Architecture](#technical-architecture)

---

## Overview

The Customer Portal is a dedicated web application that allows insurance customers to:
- View and manage their insurance policies
- Access quotations and download policy documents
- Track claims status
- Manage family group insurance
- Secure account with Two-Factor Authentication
- Change passwords and manage profile

### Portal Access
- **URL Pattern**: `/customer/*`
- **Login Route**: `/customer/login`
- **Separate Authentication**: Independent from admin portal using `customer` guard
- **Session Timeout**: 60 minutes (configurable via App Settings)

---

## Authentication & Security

### 1. Customer Login System

#### Login Features
- **Email and Password Authentication**
- **Remember Me Functionality**: Session persistence option
- **Rate Limiting**: 10 login attempts per minute
- **Account Status Validation**: Only active customers can login
- **Audit Logging**: All login attempts (successful and failed) are logged

#### Login Flow
```
1. Customer enters email and password
2. System validates credentials against active customers
3. If 2FA enabled → Redirect to 2FA challenge
4. If 2FA disabled → Direct to dashboard
5. Session created with timeout tracking
```

#### Failed Login Handling
- Tracks failed attempts per email address
- Lockout after 5 failed attempts for 15 minutes
- Audit logs capture:
  - IP address
  - User agent
  - Timestamp
  - Failure reason

### 2. Password Management

#### Password Reset Flow
1. **Request Reset**
   - Customer enters email
   - Rate limit: 5 requests per minute
   - Generates secure reset token
   - Sends email with reset link

2. **Reset Password**
   - Customer clicks link with token
   - Token validation (24-hour expiry)
   - New password entry (min 8 characters)
   - Password confirmation required
   - Token cleared after successful reset

3. **Change Password (Authenticated)**
   - Requires current password verification
   - New password must be 8+ characters
   - Confirmation required
   - `password_changed_at` timestamp updated
   - Audit log created

#### Force Password Change
- System can flag accounts with `must_change_password`
- Customers redirected to password change page
- Cannot access other features until password changed
- Used for security incidents or initial setup

### 3. Email Verification

#### Verification System
- **Token-Based Verification**: Secure unique tokens generated
- **Email Sent**: Verification link sent to customer email
- **Token Storage**: `email_verification_token` field
- **Status Tracking**: `email_verified_at` timestamp

#### Verification Flow
```
1. Admin creates customer account
2. System generates verification token
3. Email sent with verification link
4. Customer clicks link → Token validated
5. Email marked as verified
6. Customer gains full access
```

#### Resend Verification
- Available from customer dashboard
- Rate limited: 10 requests per minute
- New token generated and sent
- Old token invalidated

### 4. Two-Factor Authentication (2FA)

#### 2FA Features
- **TOTP-Based**: Time-based One-Time Password using QR codes
- **Authenticator App Support**: Google Authenticator, Microsoft Authenticator, Authy
- **Recovery Codes**: 8 backup codes for account recovery
- **Trusted Devices**: Remember devices for 30 days
- **Device Management**: View and revoke trusted devices

#### 2FA Setup Process
```
1. Customer navigates to Two-Factor page
2. Clicks "Enable Two-Factor Authentication"
3. QR code generated and displayed
4. Customer scans with authenticator app
5. Enters 6-digit verification code
6. Recovery codes displayed (must save)
7. 2FA confirmed and activated
```

#### 2FA Login Challenge
```
1. Customer logs in with email/password
2. If 2FA enabled → Redirected to 2FA challenge
3. Customer enters 6-digit code OR recovery code
4. Option to trust device for 30 days
5. Upon success → Dashboard access granted
```

#### Recovery Code Management
- **Generation**: 8 unique codes created during 2FA setup
- **One-Time Use**: Each code can only be used once
- **Regeneration**: Customer can generate new codes (requires password)
- **Storage Warning**: Customers advised to save in secure location

#### Trusted Device Features
- **30-Day Trust Period**: Skip 2FA for trusted devices
- **Device Fingerprinting**: Browser, OS, IP tracking
- **Custom Device Names**: Optional naming for identification
- **Device List**: View all trusted devices
- **Revoke Access**: Remove trust from any device

### 5. Session Management

#### Session Timeout System
- **Default Timeout**: 60 minutes of inactivity
- **Activity Tracking**: `customer_last_activity` session variable
- **Auto-Logout**: Forced logout on timeout
- **AJAX Handling**: JSON response for AJAX requests
- **Audit Logging**: Timeout events logged

#### Timeout Exclusions
Critical operations skip timeout check:
- Password changes
- Password reset
- Logout operations
- Email verification

#### Session Security
- **Session Regeneration**: On login, logout, 2FA verification
- **Token Regeneration**: CSRF token refreshed
- **Session Invalidation**: Complete session flush on logout
- **Concurrent Session Handling**: No limit (configurable)

### 6. Security Audit Logging

#### Audit Events Tracked
- Login/Logout (successful and failed)
- Password changes
- Password reset requests
- Email verification
- 2FA enable/disable/verify
- Trusted device management
- Policy access/download
- Quotation access/download
- Claim viewing
- SQL injection attempts
- Security violations

#### Audit Log Data
```php
[
    'customer_id' => 123,
    'action' => 'login',
    'description' => 'Customer logged in successfully',
    'ip_address' => '192.168.1.1',
    'user_agent' => 'Mozilla/5.0...',
    'session_id' => 'abc123...',
    'success' => true,
    'metadata' => [
        'login_method' => 'email_password',
        'remember_me' => true
    ]
]
```

---

## Dashboard Features

### Main Dashboard Components

#### 1. Expiring Policies Alert Section
**Visibility**: Only shown if policies expiring within 30 days exist

**Features**:
- Highlighted warning banner (yellow/orange theme)
- Table of expiring policies showing:
  - Policy number and registration
  - Policy holder name (with "You" badge if own policy)
  - Insurance company
  - Expiry date
  - Days remaining (color-coded: red <7 days, yellow 7-30 days)
  - Quick action button to view details
- Direct link to all policies page
- Visual urgency indicators (clock icons, countdown)

#### 2. Active Policies Section
**Visibility**: Shown when active policies exist

**Features**:
- Family insurance portfolio overview (for family heads)
- Individual policies list (for regular members)
- Badge showing active policy count
- Comprehensive table with:
  - Policy number and vehicle registration
  - Policy holder (with relationship indicator)
  - Insurance company
  - Policy type (badge)
  - Premium type (badge)
  - Premium amount (formatted currency)
  - Validity period (start to end date)
  - Status indicator (Active/Expiring Soon/Expired)
  - Quick view action button
- "Manage All" link to policies page
- Responsive card design with icons

#### 3. Recent Quotations Section
**Visibility**: Shown when quotations exist (limit 5 recent)

**Features**:
- List of most recent quotation requests
- Table showing:
  - Quote reference number
  - Requestor name (with "You" badge)
  - Vehicle details (number, make/model)
  - Quotation status (Draft/Sent/Accepted/Rejected)
  - Creation date
  - View and Download actions
- "View All Quotes" link
- Status color coding

#### 4. Empty State Messages
When no data exists:
- **No Policies**: Friendly message with icon, suggestion to get quote
- **No Quotations**: Informational message with dashboard return link
- **New Customer**: Welcome message with guidance

### Dashboard Access Control

#### Family Head Access
- Views ALL family member policies
- Views ALL family quotations
- Summary counts for entire family
- Family member management shortcuts

#### Regular Family Member Access
- Views ONLY own policies
- Views ONLY own quotations
- Summary counts for self only
- Limited family visibility

---

## Core Functionality

### 1. Insurance Policies Management

#### Policy Viewing Features

**Policy List Page** (`/customer/policies`)
- **Tab-Based Interface**:
  - Active Policies Tab (green theme)
  - Expired Policies Tab (red theme)
  - Badge counters on each tab

**Active Policies Display**:
- Policy number and registration
- Policy holder identification
- Insurance company details
- Policy type and premium type
- Premium amount (with GST)
- Coverage period (start and end dates)
- Status indicators:
  - Active (green badge)
  - Expiring Soon (yellow badge, <30 days)
- Quick action: View Details button

**Expired Policies Display**:
- Similar information as active
- Expiry date prominently displayed
- "Expired X days ago" indicator
- Status: Expired (red badge)
- Historical record preservation

#### Policy Detail Page

**Comprehensive Information**:
- Full policy document metadata
- Insurance company details
- Policy type and coverage information
- Premium breakdown
- Start and expiry dates
- Days until expiry (if active)
- Warning messages if expiring soon
- Download policy document button

**Security Features**:
- Access verification (family group check)
- Audit logging on view
- Audit logging on download
- Path traversal protection on downloads
- File type validation (PDF only)

#### Policy Download Security

**Multi-Layer Security**:
1. **Access Control**:
   - Family head: Download all family policies
   - Regular member: Download only own policies
   - Validation of family group membership

2. **Path Sanitization**:
   - Remove path traversal attempts (`../`, `..\\`)
   - Validate allowed characters
   - Real path resolution
   - Directory containment check

3. **File Validation**:
   - Verify file exists
   - Check file type (PDF only)
   - Validate file extension

4. **Audit Trail**:
   - Log all download attempts
   - Log security violations
   - Track file access patterns

### 2. Quotations Management

#### Quotation List Page (`/customer/quotations`)

**Display Features**:
- Comprehensive quotation table
- Sortable/filterable columns
- Information shown:
  - Quote reference (auto-generated)
  - Policy holder name
  - Vehicle details (if vehicle insurance)
  - Total IDV (Insured Declared Value)
  - Best quote (lowest premium)
  - Quotation status
  - Creation date
  - Action buttons

**Quotation Statuses**:
- **Draft**: Initial state, not sent
- **Generated**: Quotes generated
- **Sent**: Sent to customer
- **Accepted**: Customer accepted quote
- **Rejected**: Customer declined

**Access Permissions**:
- Family heads see all family quotations
- Regular members see only own quotations
- Read-only access (no editing/deletion)

#### Quotation Detail Page

**Detailed Information**:
- Quote reference and metadata
- Customer information
- Vehicle/asset details
- Insurance company comparisons
- Premium breakdowns per company
- IDV calculations
- Add-on covers included
- Total costs with GST
- Status and timestamps

**Actions Available**:
- View comprehensive details
- Download PDF quotation
- No modification capabilities (admin-only)

#### Quotation PDF Download

**PDF Features**:
- Professional formatted document
- Company logos and branding
- Comparison table of all quotes
- Detailed premium breakdowns
- Terms and conditions
- Valid for specified period
- Company contact information

**Security**:
- Family group access validation
- Audit logging on download
- Error handling for missing data

### 3. Claims Management (Read-Only)

#### Claims List Page (`/customer/view-claims`)

**Display Features**:
- Paginated claims table (15 per page)
- Filter/search capabilities
- Information columns:
  - Claim number
  - Insurance type (Health/Vehicle)
  - Customer name (with family indicator)
  - Policy number
  - Vehicle registration
  - Current stage/status
  - Incident date
  - Claim creation date
  - View details action

**Claims Statistics Dashboard**:
- Total claims count
- Health claims count
- Vehicle claims count
- Color-coded stat cards

**Access Control**:
- Family heads see all family claims
- Regular members see only own claims
- Read-only access (no creation/editing)

#### Claim Detail Page

**Comprehensive View**:
- Claim number and metadata
- Customer information
- Policy details
- Insurance company
- Incident information
- Current stage
- Stage history (chronological)
- Document checklist:
  - Document name
  - Required status
  - Submission status
  - Submission date
- Liability details (if applicable):
  - Claim type
  - Claim amount
  - Salvage amount
  - Deductions
  - Amount to be paid
  - Amount received
  - Notes

**Stage Tracking**:
- Chronological stage progression
- Stage names and timestamps
- Visual timeline representation
- Current stage highlighted

**Document Tracking**:
- Required documents list
- Submission status indicators
- Date submitted
- Pending documents highlighted

---

## Family Management

### Family Group Concept

**Purpose**: Allow families to manage insurance collectively under one umbrella

**Structure**:
- One **Family Head** (primary decision-maker)
- Multiple **Family Members** (spouse, children, dependents)
- Shared family group ID
- Hierarchical permissions

### Family Head Privileges

#### Policy Management
- View ALL family member policies
- Download ANY family policy document
- Receive expiry notifications for all policies
- Access comprehensive family portfolio

#### Quotation Management
- View ALL family quotations
- Request quotes for any family member
- Compare family insurance costs
- Download family quotations

#### Member Management
- View all family member profiles
- Change passwords for family members (without old password)
- Manage 2FA settings for members
- Access family member details

#### Claims Visibility
- View ALL family claims
- Track claim status for any member
- Access claim documents
- Monitor claim progression

### Regular Family Member Access

#### Own Data Only
- View ONLY personal policies
- View ONLY personal quotations
- View ONLY personal claims
- Limited family visibility

#### Family Awareness
- See family group name
- View member count
- Know family head identity
- Cannot manage others

### Family Member Profile Management

#### View Family Member Profile
**Route**: `/customer/family-member/{member}/profile`

**Features**:
- Read-only profile information
- Personal details
- Contact information
- Insurance summary
- Cannot edit profile (admin-only)

**Security**:
- Verify same family group
- Prevent self-viewing via this route
- Audit logging

#### Change Family Member Password
**Route**: `/customer/family-member/{member}/change-password`

**Features** (Family Head Only):
- Change ANY family member's password
- No old password required
- Minimum 8 characters
- Password confirmation
- Resets `must_change_password` flag
- Dual audit logging:
  - Member's audit log: "Password changed by family head"
  - Family head's audit log: "Changed password for member X"

**Security**:
- Verify family head status
- Prevent self-password change (use regular flow)
- Same family group validation
- Comprehensive audit trail

#### Disable Family Member 2FA
**Route**: `/customer/family-member/{member}/disable-2fa` (POST)

**Features** (Family Head Only):
- Disable 2FA for any family member
- Skip password verification
- Revoke all trusted devices for member
- Emergency access recovery

**Security**:
- Family head verification
- Same family group check
- Cannot disable own 2FA via this route
- Comprehensive logging
- JSON API endpoint

---

## User Flows

### 1. New Customer Onboarding Flow

```
1. Admin creates customer account
   └─> System generates verification token
   └─> WhatsApp/Email sent with credentials

2. Customer receives login credentials
   └─> Clicks verification link

3. First Login
   └─> Email verified automatically (via token)
   └─> Must change password (if flagged)
   └─> Lands on dashboard

4. Initial Setup (Optional)
   └─> Enable Two-Factor Authentication
   └─> Complete profile information
   └─> Explore features
```

### 2. Policy Viewing Flow

```
1. Customer Dashboard
   └─> Sees active policies summary

2. Click "View All Policies" or "View Details"
   └─> Redirected to policies list page

3. Policies List Page
   └─> Tab selection: Active or Expired
   └─> Browse policies with filters

4. Click specific policy
   └─> Detailed policy information
   └─> Download policy document option
   └─> Audit log created
```

### 3. Quotation Request Flow (Admin-Initiated)

```
1. Customer contacts admin for quote
   └─> Admin creates quotation in system

2. Admin generates company quotes
   └─> System calculates premiums
   └─> Quotation marked as "Generated"

3. Admin sends quotation via WhatsApp
   └─> Customer receives PDF
   └─> Status changes to "Sent"

4. Customer Portal Access
   └─> Customer logs in
   └─> Views quotation in "Recent Quotations"
   └─> Downloads PDF for comparison
   └─> Makes decision

5. Customer responds to admin
   └─> Admin updates status (Accepted/Rejected)
   └─> If Accepted → Policy creation
```

### 4. Claim Tracking Flow

```
1. Incident Occurs
   └─> Customer contacts admin

2. Admin Creates Claim
   └─> Claim number generated
   └─> Documents list created
   └─> Initial stage set

3. Customer Portal Notification
   └─> Customer logs in
   └─> Views claim in "My Claims"
   └─> Sees current stage and documents required

4. Document Submission (Offline)
   └─> Customer submits documents to admin
   └─> Admin marks documents as received
   └─> Customer sees updated status

5. Stage Progression
   └─> Admin updates claim stages
   └─> Customer tracks progress
   └─> Customer sees stage history

6. Claim Settlement
   └─> Final stage reached
   └─> Liability details visible
   └─> Settlement amount shown
   └─> Claim marked complete
```

### 5. Password Reset Flow

```
1. Customer Forgot Password
   └─> Goes to login page
   └─> Clicks "Forgot Password?"

2. Password Reset Request
   └─> Enters email address
   └─> System generates reset token
   └─> Email sent with reset link

3. Customer Clicks Email Link
   └─> Token validated (24-hour expiry)
   └─> Reset password form displayed

4. New Password Entry
   └─> Enters new password (8+ chars)
   └─> Confirms new password
   └─> Submits form

5. Password Updated
   └─> Token cleared
   └─> Password changed
   └─> Audit log created
   └─> Redirect to login with success message
```

### 6. Two-Factor Authentication Setup Flow

```
1. Customer Profile
   └─> Clicks "Two-Factor Authentication"
   └─> Redirected to 2FA management page

2. Enable 2FA
   └─> Clicks "Enable Two-Factor Authentication"
   └─> System generates QR code and recovery codes
   └─> Modal displays setup instructions

3. Scan QR Code
   └─> Customer opens authenticator app
   └─> Scans QR code
   └─> App generates 6-digit codes

4. Verify Setup
   └─> Customer enters 6-digit code
   └─> System validates code
   └─> 2FA confirmed and activated

5. Save Recovery Codes
   └─> Customer views 8 recovery codes
   └─> Copies or saves codes securely
   └─> Setup complete

6. Next Login (with 2FA)
   └─> Customer logs in with email/password
   └─> Redirected to 2FA challenge
   └─> Enters 6-digit code or recovery code
   └─> Optionally trusts device for 30 days
   └─> Access granted
```

---

## Permissions & Access Control

### Access Matrix

| Feature | Family Head | Regular Member | Guest |
|---------|-------------|----------------|-------|
| View Own Profile | ✅ | ✅ | ❌ |
| Edit Own Profile | ❌ (Admin) | ❌ (Admin) | ❌ |
| Change Own Password | ✅ | ✅ | ❌ |
| View Own Policies | ✅ | ✅ | ❌ |
| View Family Policies | ✅ | ❌ | ❌ |
| Download Own Policies | ✅ | ✅ | ❌ |
| Download Family Policies | ✅ | ❌ | ❌ |
| View Own Quotations | ✅ | ✅ | ❌ |
| View Family Quotations | ✅ | ❌ | ❌ |
| Download Quotations | ✅ | ✅ | ❌ |
| View Own Claims | ✅ | ✅ | ❌ |
| View Family Claims | ✅ | ❌ | ❌ |
| View Family Member Profiles | ✅ | ❌ | ❌ |
| Change Family Member Password | ✅ | ❌ | ❌ |
| Disable Family Member 2FA | ✅ | ❌ | ❌ |
| Enable Own 2FA | ✅ | ✅ | ❌ |
| Manage Trusted Devices | ✅ | ✅ | ❌ |
| Create/Edit Policies | ❌ (Admin) | ❌ (Admin) | ❌ |
| Create/Edit Quotations | ❌ (Admin) | ❌ (Admin) | ❌ |
| Create/Edit Claims | ❌ (Admin) | ❌ (Admin) | ❌ |

### Route Protection

#### Middleware Stack
1. **customer.auth**: Validates customer authentication
2. **customer.timeout**: Enforces session timeout
3. **customer.family**: Ensures family group membership (where applicable)
4. **throttle**: Rate limiting protection

#### Rate Limiting Configuration
- Login attempts: **10 per minute**
- Password reset: **5 per minute**
- Email verification: **3 per minute**
- General routes: **200 per minute**
- Downloads: **10 per minute**
- 2FA operations: **6-120 per minute** (varies by operation)

#### Protected Routes
All customer routes require authentication except:
- `/customer/login` (GET, POST)
- `/customer/password/reset` (GET, POST)
- `/customer/email/verify/{token}` (GET)

---

## Security Features

### 1. Authentication Security

#### Password Requirements
- Minimum 8 characters
- Must contain at least one letter and one number (recommended)
- No maximum length limit
- Hashed using bcrypt algorithm
- Password history not tracked (can be added)

#### Account Lockout
- **Trigger**: 5 failed login attempts
- **Duration**: 15 minutes
- **Reset**: Automatic after lockout period
- **Bypass**: None (admin cannot override)
- **Logging**: All lockout events audited

#### Session Security
- **CSRF Protection**: Token validation on all forms
- **Session Fixation Prevention**: Session regeneration on login
- **Secure Cookies**: HttpOnly and Secure flags
- **SameSite Cookies**: Strict policy
- **Session Timeout**: 60 minutes configurable

### 2. Data Protection

#### SQL Injection Prevention
- **Eloquent ORM**: Parameterized queries
- **Input Validation**: Type checking and sanitization
- **Family Group ID Validation**: Numeric validation with exception handling
- **Error Handling**: SQL exceptions caught and logged
- **Audit Trail**: Injection attempts logged as security violations

#### Path Traversal Protection
- **File Download Security**:
  - Remove `../` and `..\\` patterns
  - Validate characters (alphanumeric, dash, underscore, slash, dot only)
  - Real path resolution
  - Directory containment verification
  - Allowed directory: `storage/app/public/`

#### XSS Prevention
- **Blade Template Escaping**: Automatic `{{ }}` escaping
- **Raw Output Control**: Minimal use of `{!! !!}`
- **Input Sanitization**: Server-side validation
- **Content Security Policy**: Headers configured

#### File Upload Security (Admin-Only)
- File type validation (MIME type checking)
- File size limits
- Virus scanning (recommended)
- Secure storage location
- Access control on downloads

### 3. API Security (No Public API)

**Current State**: No REST API endpoints for customers
- All interactions via web interface
- No API tokens issued
- No OAuth/API key system
- Admin API exists but separate

**Future Consideration**: Customer API could be added with:
- Laravel Sanctum tokens
- Rate limiting per token
- Scope-based permissions
- API versioning

### 4. Audit & Monitoring

#### Comprehensive Audit Logging
All security-relevant events logged with:
- **Customer ID**: Who performed action
- **Action**: What was done
- **Description**: Human-readable description
- **IP Address**: Origin of request
- **User Agent**: Browser and device info
- **Session ID**: Session identifier
- **Success Status**: True/false
- **Failure Reason**: If failed, why
- **Metadata**: Additional context (JSON)
- **Timestamp**: When it occurred

#### Logged Events
- Authentication (login, logout, failures)
- Password operations (change, reset)
- Email verification
- 2FA operations (enable, disable, verify, challenge)
- Device management (trust, revoke)
- Policy access and downloads
- Quotation access and downloads
- Claim viewing
- Security violations (SQL injection attempts, path traversal, unauthorized access)

#### Log Retention
- Stored in `customer_audit_logs` table
- No automatic purging (admin-configurable)
- Indexed for performance
- Searchable and filterable

---

## App Settings Usage

### Customer Portal Settings

The customer portal integrates with the App Settings system for configuration:

#### 1. Session Management
- **Setting Key**: `customer_session_timeout`
- **Purpose**: Control session timeout duration
- **Default**: 60 minutes
- **Impact**: Automatic logout after inactivity period

#### 2. Email Configuration
- **SMTP Settings**: Use global app email settings
- **From Address**: Configured in mail settings
- **Templates**: Password reset, verification emails

#### 3. WhatsApp Integration
- **OnBoarding Messages**: Customer account creation notifications
- **Policy Documents**: Send policy PDFs via WhatsApp
- **Quotations**: Send quotation PDFs via WhatsApp
- **Claim Updates**: Notify customers of claim progress

#### 4. Security Settings
- **2FA Enforcement**: Can be made mandatory via settings
- **Password Policy**: Minimum length, complexity rules
- **Session Security**: Cookie settings, timeout values

#### 5. Date/Time Formatting
- **Indian Format**: DD/MM/YYYY display format
- **Timezone**: Configured via app settings
- **Locale**: Date formatting based on Indian standards

#### 6. File Storage
- **Upload Path**: Configurable via settings
- **Max File Size**: Policy document size limits
- **Allowed Types**: PDF restriction for downloads

---

## Future Enhancements

### Planned Features (Not Yet Implemented)

#### 1. Customer Self-Service
- **Update Profile**: Allow customers to edit contact details
- **Document Upload**: Customers upload claim documents directly
- **Claim Submission**: Customers initiate claims online
- **Policy Renewal**: Online renewal requests
- **Payment Integration**: Online premium payments

#### 2. Communication Features
- **In-App Messaging**: Chat with admin/support
- **Notifications Center**: Centralized notification management
- **SMS Alerts**: Critical updates via SMS
- **Email Preferences**: Customize notification preferences

#### 3. Enhanced Policy Management
- **Policy Comparison**: Compare different policies side-by-side
- **Coverage Calculator**: Estimate required coverage
- **Add-on Selection**: Choose add-on covers during renewal
- **Policy Recommendations**: AI-based coverage suggestions

#### 4. Document Management
- **Document Vault**: Secure storage for personal documents
- **Document Expiry Alerts**: Passport, license expiry reminders
- **Digital Signatures**: Sign documents electronically
- **Document Sharing**: Share with family members

#### 5. Claims Enhancement
- **Claim Initiation**: Start claim process from portal
- **Document Upload**: Attach claim-related documents
- **Claim Chat**: Communicate with claims processor
- **Status Notifications**: Real-time claim status updates

#### 6. Family Portal Features
- **Family Calendar**: Important dates (renewals, anniversaries)
- **Shared Documents**: Family document repository
- **Budget Tracking**: Insurance expense tracking
- **Coverage Dashboard**: Visualize family coverage

#### 7. Analytics & Reports
- **Premium History**: Track premium payments over time
- **Coverage Reports**: Comprehensive coverage analysis
- **Claim Statistics**: Personal claim history analysis
- **Expense Reports**: Download insurance expense reports

#### 8. Mobile App
- **Native Apps**: iOS and Android applications
- **Push Notifications**: Mobile push alerts
- **Biometric Login**: Fingerprint/Face ID authentication
- **Offline Mode**: View policies offline

#### 9. API Access
- **RESTful API**: For third-party integrations
- **API Keys**: Secure API authentication
- **Webhooks**: Event-driven notifications
- **Developer Portal**: API documentation

#### 10. Advanced Security
- **Biometric 2FA**: Fingerprint/face authentication
- **Hardware Tokens**: YubiKey support
- **Security Questions**: Additional authentication factor
- **Activity Monitoring**: Suspicious activity detection

---

## Mobile Responsiveness

### Current Responsive Design

#### Breakpoints Used
- **Mobile**: <768px
- **Tablet**: 768px - 1024px
- **Desktop**: >1024px

#### Responsive Features

**Dashboard**:
- ✅ Stacked cards on mobile
- ✅ Responsive tables (horizontal scroll)
- ✅ Touch-friendly buttons
- ✅ Collapsible navigation

**Policies List**:
- ✅ Card-based layout on mobile
- ✅ Simplified information display
- ✅ Touch-optimized actions
- ✅ Swipeable tabs

**Profile Page**:
- ✅ Vertical stacking on mobile
- ✅ Full-width action buttons
- ✅ Readable fonts and spacing

**2FA Setup**:
- ✅ QR code scaling
- ✅ Large input fields
- ✅ Copy button accessibility

### Mobile Usability Testing Results

#### Tested Devices
- ✅ iPhone (Safari)
- ✅ Android (Chrome)
- ✅ iPad (Safari)
- ✅ Android Tablet (Chrome)

#### Known Issues
- ⚠️ Large tables may require horizontal scroll
- ⚠️ PDF downloads open in browser (not native viewer)
- ⚠️ QR code scanning requires separate app

### Mobile Enhancement Recommendations

1. **Progressive Web App (PWA)**
   - Add service worker
   - Enable offline mode
   - Install to home screen

2. **Touch Gestures**
   - Swipe to delete trusted devices
   - Pull to refresh data
   - Pinch to zoom on documents

3. **Mobile-First Tables**
   - Card view instead of tables
   - Expandable rows
   - Filter/sort in drawer

4. **Native Features**
   - Camera for document upload
   - Biometric authentication
   - Push notifications

---

## Technical Architecture

### Authentication Stack

#### Guards & Providers
```php
// config/auth.php
'guards' => [
    'customer' => [
        'driver' => 'session',
        'provider' => 'customers',
    ],
],

'providers' => [
    'customers' => [
        'driver' => 'eloquent',
        'model' => App\Models\Customer::class,
    ],
],
```

#### Middleware Stack
1. **CustomerAuth**: Authenticates customer, checks status, enforces password changes
2. **CustomerSessionTimeout**: Tracks activity, enforces timeout, handles expiry
3. **Throttle**: Rate limiting per route
4. **VerifyCsrfToken**: CSRF protection

### Database Schema

#### Customers Table
```
customers
├── id (PK)
├── name
├── email (unique, indexed)
├── mobile_number
├── password (bcrypt)
├── status (boolean)
├── family_group_id (FK, nullable)
├── date_of_birth
├── wedding_anniversary_date
├── engagement_anniversary_date
├── email_verified_at
├── email_verification_token
├── password_reset_token
├── password_reset_expires_at
├── must_change_password (boolean)
├── password_changed_at
├── two_factor_secret (encrypted)
├── two_factor_recovery_codes (encrypted)
├── two_factor_confirmed_at
├── created_at
├── updated_at
├── deleted_at (soft deletes)
```

#### Customer Audit Logs Table
```
customer_audit_logs
├── id (PK)
├── customer_id (FK, nullable)
├── action (indexed)
├── description
├── ip_address
├── user_agent
├── session_id
├── success (boolean)
├── failure_reason
├── metadata (JSON)
├── created_at
```

#### Trusted Devices Table
```
customer_trusted_devices
├── id (PK)
├── customer_id (FK)
├── device_name
├── device_type (mobile/tablet/desktop)
├── device_fingerprint (unique)
├── browser
├── platform
├── ip_address
├── last_used_at
├── expires_at
├── created_at
├── updated_at
```

### Key Models & Traits

#### Customer Model
```php
App\Models\Customer extends Authenticatable
├── Uses: HasCustomerTwoFactorAuth (Trait)
├── Uses: Auditable (Trait)
├── Uses: HasApiTokens (Trait - for future)
├── Uses: Notifiable (Trait)
├── Uses: SoftDeletes (Trait)
└── Relationships:
    ├── familyGroup() -> BelongsTo FamilyGroup
    ├── familyMembers() -> HasMany FamilyMember
    ├── insurance() -> HasMany CustomerInsurance
    ├── quotations() -> HasMany Quotation
    └── claims() -> HasMany Claim
```

### View Structure

#### Layouts
- `layouts/customer.blade.php`: Main authenticated customer layout
- `layouts/customer-auth.blade.php`: Login/register layout
- `customer/partials/`: Reusable components (header, footer, sidebar)

#### Views Hierarchy
```
resources/views/customer/
├── auth/
│   ├── login.blade.php
│   ├── password-reset.blade.php
│   ├── reset-password.blade.php
│   ├── verify-email.blade.php
│   └── change-password.blade.php
├── dashboard.blade.php
├── profile.blade.php
├── policies.blade.php
├── policy-detail.blade.php
├── quotations.blade.php
├── quotation-detail.blade.php
├── claims.blade.php
├── claim-detail.blade.php
├── two-factor.blade.php
├── family-member-profile.blade.php
└── family-member-password.blade.php
```

### Routes Architecture

#### Route File
- `routes/customer.php`: All customer-specific routes
- Loaded in `RouteServiceProvider`
- Separate from `web.php` (admin routes)

#### Route Naming Convention
All customer routes prefixed with `customer.`:
- `customer.login`
- `customer.dashboard`
- `customer.policies`
- `customer.policies.detail`
- etc.

### Controllers

#### CustomerAuthController
**Responsibilities**:
- Login/logout handling
- Password management (change, reset)
- Email verification
- Dashboard display
- Policy viewing and download
- Quotation viewing and download
- Claims viewing
- Family member management

**Key Methods**:
- `showLoginForm()`: Display login page
- `login()`: Handle login POST
- `logout()`: Handle logout
- `dashboard()`: Show dashboard
- `showPolicies()`: List policies
- `showPolicyDetail()`: Show policy details
- `downloadPolicy()`: Secure policy download
- `showQuotations()`: List quotations
- `downloadQuotation()`: Generate quotation PDF
- `showClaims()`: List claims
- `showProfile()`: Show profile
- `changePassword()`: Change password
- `showFamilyMemberProfile()`: View family member profile
- `updateFamilyMemberPassword()`: Change family member password

#### TwoFactorAuthController
**Responsibilities**:
- 2FA enablement and setup
- QR code generation
- Code verification
- Recovery code generation
- Trusted device management
- 2FA challenge during login

**Note**: Shared with admin but uses customer guard when needed

---

## What Customers CAN Do

### Self-Service Capabilities

✅ **Account Management**
- Log in to dedicated customer portal
- Change own password (requires current password)
- Enable/disable Two-Factor Authentication
- Manage trusted devices (30-day trust)
- Generate recovery codes
- View own profile information
- Verify email address

✅ **Policy Management**
- View all personal insurance policies
- View family policies (if family head)
- Download policy documents (PDF)
- See policy expiry dates and warnings
- Check premium amounts and payment details
- View policy coverage information
- Track policy status (Active/Expired/Expiring Soon)

✅ **Quotation Access**
- View all personal quotations
- View family quotations (if family head)
- Download quotation PDFs
- Compare insurance company quotes
- See quotation status (Draft/Sent/Accepted/Rejected)
- Review premium calculations

✅ **Claims Tracking**
- View personal claims
- View family claims (if family head)
- Track claim stages and progress
- See required documents list
- Check document submission status
- View liability and settlement details
- Monitor claim history

✅ **Family Management (Family Heads Only)**
- View all family member profiles
- Change family member passwords (without old password)
- Disable family member 2FA (for account recovery)
- View family insurance portfolio
- Access family quotations and claims

✅ **Security**
- Set up Two-Factor Authentication
- Manage trusted devices
- View security status
- Track login activity (via audit logs, if exposed)
- Receive timeout warnings

---

## What Customers CANNOT Do

### Restricted Actions (Admin-Only)

❌ **Profile Editing**
- Cannot edit personal information (name, email, mobile)
- Cannot change date of birth or anniversary dates
- Cannot update address or contact details
- Cannot upload documents (PAN, Aadhar, GST)

❌ **Policy Management**
- Cannot create new policies
- Cannot edit existing policies
- Cannot renew policies (must contact admin)
- Cannot add/remove add-on covers
- Cannot change insurance company
- Cannot modify premium amounts
- Cannot update policy dates

❌ **Quotation Management**
- Cannot create quotation requests
- Cannot edit quotations
- Cannot delete quotations
- Cannot generate company quotes
- Cannot modify IDV or premium calculations
- Cannot send quotations to insurance companies
- Cannot change quotation status

❌ **Claim Management**
- Cannot create new claims
- Cannot edit claim details
- Cannot delete claims
- Cannot upload claim documents (must contact admin)
- Cannot update claim stages
- Cannot modify liability details
- Cannot mark documents as submitted

❌ **Family Management**
- Cannot create family groups
- Cannot add/remove family members
- Cannot edit family member profiles
- Cannot delete family members
- Cannot change family head designation

❌ **Administrative Functions**
- Cannot access admin portal
- Cannot manage other customers
- Cannot view analytics or reports
- Cannot configure app settings
- Cannot manage insurance companies
- Cannot manage brokers or reference users

❌ **Payment & Financial**
- Cannot make online payments (not implemented)
- Cannot issue refunds
- Cannot generate invoices
- Cannot view payment history

---

## Security Best Practices for Customers

### Recommendations to Share with Customers

1. **Strong Passwords**
   - Use minimum 8 characters (longer is better)
   - Mix uppercase, lowercase, numbers, symbols
   - Don't reuse passwords from other sites
   - Change passwords regularly

2. **Enable Two-Factor Authentication**
   - Adds extra layer of security
   - Use authenticator apps (Google Authenticator, Authy)
   - Save recovery codes securely
   - Don't share 2FA codes with anyone

3. **Trusted Devices**
   - Only trust personal devices
   - Don't trust public/shared computers
   - Revoke trust if device is lost/stolen
   - Review trusted devices regularly

4. **Session Management**
   - Log out when done (especially on shared devices)
   - Don't leave portal open unattended
   - Be aware of 60-minute timeout
   - Don't share session links

5. **Email Security**
   - Verify email address
   - Don't click suspicious links
   - Check sender address for password resets
   - Report phishing attempts

6. **Privacy**
   - Don't share login credentials
   - Be cautious of social engineering
   - Verify admin communication channels
   - Report suspicious activity

---

## Support & Help Resources

### Customer Support Channels

1. **Contact Admin**
   - For policy changes, quotations, claims
   - For account issues
   - For technical problems

2. **Email Support**
   - Password reset emails
   - Verification emails
   - Policy documents

3. **WhatsApp Notifications**
   - Onboarding messages
   - Policy updates
   - Quotation delivery
   - Claim notifications

### Common Issues & Solutions

| Issue | Solution |
|-------|----------|
| Forgot Password | Use "Forgot Password" link on login page |
| Email Not Verified | Click verification link or request new one |
| Session Expired | Log in again (60-minute timeout) |
| 2FA Code Not Working | Check time sync on device, use recovery code |
| Cannot View Family Policies | Verify you are family head, check family group |
| Policy Download Fails | Check file exists, contact admin if persists |
| Lost Recovery Codes | Generate new codes (requires password) |
| Account Locked | Wait 15 minutes or contact admin |

---

## Appendix

### A. Route Reference

**Full Route List** (as of 2025-10-06):

```
Public Routes:
├── customer.login (GET, POST)
├── customer.password.request (GET)
├── customer.password.email (POST)
├── customer.password.reset (GET, POST)
└── customer.verify-email (GET)

Authenticated Routes:
├── customer.logout (POST)
├── customer.dashboard (GET)
├── customer.profile (GET)
├── customer.change-password (GET, POST)
├── customer.verify-email-notice (GET)
├── customer.verification.send (POST)
├── customer.policies (GET)
├── customer.policies.detail (GET)
├── customer.policies.download (GET)
├── customer.quotations (GET)
├── customer.quotations.detail (GET)
├── customer.quotations.download (GET)
├── customer.claims (GET)
├── customer.claims.detail (GET)
├── customer.two-factor.index (GET)
├── customer.two-factor.enable (POST)
├── customer.two-factor.confirm (POST)
├── customer.two-factor.disable (POST)
├── customer.two-factor.recovery-codes (POST)
├── customer.two-factor.trust-device (POST)
├── customer.two-factor.revoke-device (DELETE)
├── customer.two-factor.status (GET)
├── customer.family-member.profile (GET)
├── customer.family-member.change-password (GET)
├── customer.family-member.password (PUT)
└── customer.family-member.disable-2fa (POST)
```

### B. Configuration Reference

**Environment Variables**:
```
SESSION_LIFETIME=120 (global)
CUSTOMER_SESSION_TIMEOUT=60 (via app settings)
MAIL_MAILER=smtp
MAIL_FROM_ADDRESS=noreply@yourcompany.com
```

**Config Files**:
- `config/auth.php`: Customer guard configuration
- `config/session.php`: Session management
- `config/mail.php`: Email settings

### C. Error Codes

| Code | Meaning | Action |
|------|---------|--------|
| 401 | Unauthorized | Login required |
| 403 | Forbidden | Insufficient permissions |
| 404 | Not Found | Resource doesn't exist |
| 419 | CSRF Token Mismatch | Refresh page |
| 429 | Too Many Requests | Rate limit exceeded, wait |
| 500 | Server Error | Contact admin |

---

## Revision History

| Version | Date | Author | Changes |
|---------|------|--------|---------|
| 1.0 | 2025-10-06 | Technical Team | Initial comprehensive guide |

---

**Document End**

For questions or clarifications, contact the technical documentation team.
