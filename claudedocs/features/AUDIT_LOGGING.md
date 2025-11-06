# Audit Logging & Compliance

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Audit Log Models](#audit-log-models)
4. [Event Categories](#event-categories)
5. [Audit Services](#audit-services)
6. [Security Audit Service](#security-audit-service)
7. [Risk Assessment](#risk-assessment)
8. [Database Schema](#database-schema)
9. [Usage Examples](#usage-examples)
10. [Querying & Reporting](#querying--reporting)
11. [Compliance Features](#compliance-features)
12. [Best Practices](#best-practices)
13. [Performance Optimization](#performance-optimization)
14. [Related Documentation](#related-documentation)

---

## Overview

The Audit Logging system provides comprehensive tracking of all user actions, system events, and security-related activities across the multi-tenant SaaS platform. The system supports compliance requirements (GDPR, SOC 2, HIPAA), security monitoring, and forensic analysis.

### Key Features

- **Multi-Level Audit Trails**:
  - `AuditLog`: Tenant-level comprehensive audit with risk assessment
  - `CustomerAuditLog`: Customer portal activity tracking
  - `Central AuditLog`: Cross-tenant administrative events

- **Security Monitoring**: Real-time threat detection and risk scoring
- **Compliance Support**: GDPR-compliant data retention and access logs
- **Event Categorization**: Structured event taxonomy for analysis
- **Risk Assessment**: Automatic risk scoring and suspicious activity detection
- **Forensic Analysis**: Detailed context capture for investigation
- **Report Generation**: Security reports, compliance reports, trend analysis

### System Components

```
┌─────────────────────────────────────────────────────────┐
│              Audit Logging System                        │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  ┌──────────────────┐  ┌──────────────────┐            │
│  │ AuditLog         │  │ CustomerAuditLog │            │
│  │ (Tenant Staff)   │  │ (Customer Portal)│            │
│  └──────────────────┘  └──────────────────┘            │
│                                                           │
│  ┌──────────────────┐  ┌──────────────────┐            │
│  │ Central AuditLog │  │ Security Events  │            │
│  │ (Admin)          │  │ (Table)          │            │
│  └──────────────────┘  └──────────────────┘            │
│                                                           │
│  ┌─────────────────────────────────────────┐            │
│  │ AuditService & SecurityAuditService     │            │
│  └─────────────────────────────────────────┘            │
└─────────────────────────────────────────────────────────┘
```

---

## System Architecture

### Three-Tier Audit System

#### 1. Tenant Audit Log (Staff Actions)

**Model**: `AuditLog`
**Purpose**: Track all staff user actions within tenant context

**Features**:
- Polymorphic relationships (actor, auditable, target)
- Automatic risk scoring
- Change tracking (old_values, new_values)
- Geographic location tracking
- Session and request correlation
- Suspicious activity flagging

**Use Cases**:
- Staff user activity monitoring
- Data modification tracking
- Security event logging
- Compliance audit trails

#### 2. Customer Audit Log

**Model**: `CustomerAuditLog`
**Purpose**: Track customer portal activities

**Features**:
- Customer-specific action logging
- Policy access tracking
- Resource-based logging
- Success/failure tracking
- Simplified structure for performance

**Use Cases**:
- Customer activity tracking
- Policy access logs
- Document download tracking
- Customer portal security

#### 3. Central Audit Log (Cross-Tenant)

**Model**: `Central\AuditLog`
**Purpose**: Track super-admin and cross-tenant activities

**Features**:
- Central database storage
- Tenant-agnostic logging
- System-level event tracking

**Use Cases**:
- Super admin activity
- Tenant management events
- System configuration changes
- Cross-tenant operations

### Data Flow

```
┌──────────────┐
│ User Action  │
└──────┬───────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Action Intercepted                   │
│ - Controller                         │
│ - Middleware                         │
│ - Service Layer                      │
│ - Event Listener                     │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Context Collection                   │
│ - User/Customer identification       │
│ - IP address & geolocation           │
│ - User agent & device info           │
│ - Request ID & session ID            │
│ - Resource identification            │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Risk Assessment                      │
│ - Calculate risk score               │
│ - Identify risk factors              │
│ - Flag suspicious activity           │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Audit Log Storage                    │
│ - Create audit log record            │
│ - Store in appropriate table         │
│ - Index for fast retrieval           │
└──────┬───────────────────────────────┘
       │
       ├─── High Risk? ──► Trigger Alert
       │
       ├─── Suspicious? ──► Security Review Queue
       │
       └─── Normal ──► Standard Logging
```

---

## Audit Log Models

### AuditLog Model (Tenant Staff)

**File**: `app/Models/AuditLog.php`

#### Core Attributes

**Relationships**:
- `auditable_type` (string): Entity being audited (polymorphic)
- `auditable_id` (int): Entity ID
- `actor_type` (string, nullable): Who performed action (User/Customer)
- `actor_id` (int, nullable): Actor ID
- `target_type` (string, nullable): Secondary entity affected
- `target_id` (int, nullable): Target ID

**Event Information**:
- `event` (string): Event name (e.g., 'user.created', 'policy.updated')
- `event_category` (string): Category (authentication, authorization, data_access, etc.)
- `action` (string, nullable): Human-readable action description
- `properties` (string, nullable): Event-specific properties
- `category` (string, nullable): Additional categorization

**Change Tracking**:
- `old_values` (JSON, nullable): State before change
- `new_values` (JSON, nullable): State after change
- `metadata` (JSON, nullable): Additional context

**Context**:
- `ip_address` (string, nullable): Request IP
- `user_agent` (string, nullable): Browser/device info
- `session_id` (string, nullable): Session identifier
- `request_id` (string, nullable): Request correlation ID
- `occurred_at` (datetime): When event occurred

**Risk Assessment**:
- `severity` (string): low, medium, high, critical
- `risk_score` (int, nullable): 0-100 calculated risk
- `risk_level` (string, nullable): low, medium, high, critical
- `risk_factors` (JSON array, nullable): List of risk indicators
- `is_suspicious` (boolean): Flagged for review

**Location** (if tracked):
- `location_country` (string, nullable)
- `location_city` (string, nullable)
- `location_lat` (decimal, nullable)
- `location_lng` (decimal, nullable)

#### Methods

**Query Scopes**:
```php
AuditLog::highRisk()              // risk_level = 'high' OR 'critical'
    ->suspicious()                 // is_suspicious = true
    ->byEventCategory('authentication')
    ->byRiskScore(70)              // risk_score >= 70
    ->recentActivity(24)           // last 24 hours
    ->get();
```

**Accessors**:
```php
$log->formatted_location;          // "Mumbai, India"
$log->risk_badge_class;            // Bootstrap badge class based on risk
$log->hasRiskFactor('unusual_location'); // Check specific risk factor
```

**Relationships**:
```php
$log->auditable;  // Polymorphic - the entity being audited
$log->actor;      // Polymorphic - who performed the action
```

### CustomerAuditLog Model

**File**: `app/Models/CustomerAuditLog.php`

#### Core Attributes

- `customer_id` (int): Foreign key to customers
- `action` (string): Action performed (login, logout, view_policy, download_document)
- `resource_type` (string, nullable): Type of resource (policy, profile, family_data)
- `resource_id` (int, nullable): Resource ID
- `description` (string, nullable): Human-readable description
- `metadata` (JSON, nullable): Additional context
- `ip_address` (string, nullable): Request IP
- `user_agent` (string, nullable): Browser info
- `session_id` (string, nullable): Session ID
- `success` (boolean): Whether action succeeded
- `failure_reason` (string, nullable): Why action failed

#### Static Helper Methods

**Log General Action**:
```php
CustomerAuditLog::logAction(
    'profile_updated',
    'Customer updated their profile information',
    ['fields_changed' => ['email', 'mobile']]
);
```

**Log Policy Action**:
```php
CustomerAuditLog::logPolicyAction(
    'view',
    $customerInsurance,
    'Customer viewed policy details',
    ['policy_no' => $customerInsurance->policy_no]
);
```

**Log Failure**:
```php
CustomerAuditLog::logFailure(
    'policy_download',
    'Policy document not found',
    ['policy_id' => 123]
);
```

---

## Event Categories

### Standard Event Categories

| Category | Description | Examples |
|----------|-------------|----------|
| **authentication** | Login, logout, session events | login_success, login_failed, logout, session_expired |
| **authorization** | Permission checks, access control | permission_denied, role_changed, access_granted |
| **data_access** | Viewing/reading data | policy_viewed, customer_viewed, report_accessed |
| **data_modification** | Creating, updating, deleting | policy_created, customer_updated, claim_deleted |
| **security** | Security-related events | password_changed, 2fa_enabled, device_blocked |
| **compliance** | Compliance-related actions | data_export, gdpr_request, audit_log_accessed |
| **system** | System events | configuration_changed, backup_created, migration_run |
| **financial** | Payment and billing | payment_processed, subscription_changed, refund_issued |

### Event Naming Convention

Events follow a structured naming pattern: `entity.action` or `category.event`

**Examples**:
- `user.created` - User account created
- `policy.updated` - Insurance policy updated
- `authentication.login_success` - Successful login
- `authorization.permission_denied` - Access denied
- `security.password_changed` - Password changed
- `compliance.data_exported` - Data export requested

---

## Audit Services

### AuditService

**File**: `app/Services/AuditService.php`

Provides high-level audit log querying, analysis, and reporting functionality.

#### Core Methods

**Activity Retrieval**:

```php
// Get recent activity (last 24 hours, limit 50)
$activity = $auditService->getRecentActivity(24, 50);

// Get suspicious activity (last 7 days)
$suspicious = $auditService->getSuspiciousActivity(7);

// Get high-risk activity (last 30 days)
$highRisk = $auditService->getHighRiskActivity(30);

// Get activity by specific user
$userActivity = $auditService->getActivityByUser(
    $userId,
    User::class,
    30 // days
);

// Get activity for specific entity
$entityActivity = $auditService->getActivityByEntity(
    $policyId,
    CustomerInsurance::class,
    30
);
```

**Security Metrics**:

```php
$metrics = $auditService->getSecurityMetrics(30);

// Returns:
[
    'total_events' => 15234,
    'suspicious_events' => 45,
    'high_risk_events' => 12,
    'failed_logins' => 234,
    'unique_ips' => 1523,
    'events_by_category' => [
        'authentication' => 5432,
        'data_access' => 8901,
        'data_modification' => 789,
        // ...
    ],
    'risk_distribution' => [
        'low' => 14000,
        'medium' => 1000,
        'high' => 200,
        'critical' => 34
    ],
    'hourly_activity' => [...],
    'top_risk_factors' => [
        'unusual_location' => 45,
        'multiple_failed_logins' => 23,
        // ...
    ]
]
```

**Search & Filter**:

```php
$logs = $auditService->searchLogs([
    'event' => 'login_success',
    'event_category' => 'authentication',
    'risk_level' => 'high',
    'is_suspicious' => true,
    'ip_address' => '192.168.1.1',
    'actor_id' => 123,
    'actor_type' => User::class,
    'date_from' => '2025-01-01',
    'date_to' => '2025-01-31',
    'search' => 'policy'  // Searches event, IP, user agent, metadata
], $perPage = 25);
```

**Report Generation**:

```php
$report = $auditService->generateSecurityReport(30);

// Returns comprehensive report:
[
    'period' => [
        'days' => 30,
        'start_date' => '2025-01-07',
        'end_date' => '2025-02-06'
    ],
    'summary' => [...],              // Metrics
    'suspicious_activity' => [...],  // Top 20 suspicious events
    'high_risk_activity' => [...],   // Top 20 high-risk events
    'recommendations' => [           // Auto-generated recommendations
        [
            'type' => 'warning',
            'title' => 'High Suspicious Activity',
            'description' => 'More than 10% of events are flagged...',
            'priority' => 'high'
        ]
    ],
    'generated_at' => '2025-02-06T10:30:00Z'
]
```

**Export**:

```php
// Export to CSV
$csv = $auditService->exportLogs($filters, 'csv');

// Export to JSON
$json = $auditService->exportLogs($filters, 'json');
```

---

## Security Audit Service

### SecurityAuditService

**File**: `app/Services/SecurityAuditService.php`

Specialized service for security event logging and monitoring.

#### Event Logging Methods

**Authentication Events**:

```php
$securityAuditService->logAuthenticationEvent(
    'login_success',
    $userId,
    [
        'method' => '2fa',
        'device_trusted' => true,
        'location' => 'Mumbai, India'
    ]
);

// Event severities auto-assigned:
// - login_success: low
// - login_failed: medium
// - multiple_failed_logins: high
// - account_locked: high
```

**Authorization Events**:

```php
$securityAuditService->logAuthorizationEvent(
    'permission_denied',
    $userId,
    [
        'required_permission' => 'customer-delete',
        'attempted_action' => 'DELETE /customers/123',
        'user_roles' => ['staff', 'viewer']
    ]
);
```

**Data Access Events**:

```php
$securityAuditService->logDataAccessEvent(
    'customer_insurances',  // resource type
    'view',                 // action
    $policyId,              // resource ID
    $userId                 // actor
);

// Automatically stores only for sensitive resources:
// - customers, customer_insurances, claims
// - users, financial_data, payment_information
```

**Security Violations**:

```php
$securityAuditService->logSecurityViolation(
    'csrf_token_mismatch',
    [
        'expected_token' => 'abc...',
        'received_token' => 'xyz...',
        'form_action' => 'POST /policies'
    ]
);

// Automatically triggers pattern detection:
// - 5+ violations from same user in 1 hour → alert
// - 10+ violations from same IP in 1 hour → alert
```

**File Operations**:

```php
$securityAuditService->logFileOperation(
    'upload',
    'policy_document.pdf',
    $userId,
    [
        'file_size' => 2048576,
        'mime_type' => 'application/pdf',
        'storage_path' => 'tenant_123/documents/...'
    ]
);

// Logged operations: upload, download, delete
```

#### Security Reports

```php
$report = $securityAuditService->generateSecurityReport(
    Carbon::now()->subDays(30),
    Carbon::now()
);

// Returns:
[
    'period' => [...],
    'summary' => [
        'total_events' => 5432,
        'by_severity' => [
            'low' => 4000,
            'medium' => 1200,
            'high' => 200,
            'critical' => 32
        ],
        'by_event_type' => [...],
        'unique_users' => 234,
        'unique_ips' => 456
    ],
    'top_events' => [...],
    'failed_logins' => 234,
    'csrf_violations' => 12,
    'authorization_failures' => 45,
    'suspicious_activities' => 23,
    'trends' => [
        'hourly_distribution' => [...],
        'daily_patterns' => [...],
        'problematic_ips' => [...]
    ],
    'recommendations' => [...]
]
```

#### Pattern Detection & Alerts

**Automatic Monitoring**:

- **User-based**: 5+ high-severity violations in 1 hour → alert
- **IP-based**: 10+ high-severity violations in 1 hour → alert
- **Critical alerts**: Logged and optionally emailed to security team

**Alert Types**:
- `alert_multiple_violations_user` - Repeated user violations
- `alert_multiple_violations_ip` - Repeated IP violations
- Custom security alerts based on patterns

---

## Risk Assessment

### Risk Scoring System

AuditLog implements automatic risk scoring (0-100) based on multiple factors:

**Risk Factors** (examples):
- `unusual_location` - Access from unexpected geographic location
- `multiple_failed_logins` - Several failed login attempts
- `unusual_time` - Access during unusual hours
- `new_device` - First-time device usage
- `privilege_escalation` - Permission/role changes
- `sensitive_data_access` - Access to sensitive resources
- `bulk_operations` - Large volume of operations
- `api_abuse` - High API request rate

### Risk Levels

| Risk Score | Risk Level | Auto-Actions | Monitoring |
|------------|------------|--------------|------------|
| 0-25 | low | None | Standard |
| 26-50 | medium | Log warning | Increased |
| 51-75 | high | Flag for review | Active monitoring |
| 76-100 | critical | Alert security team | Immediate review |

### Suspicious Activity Detection

Events are flagged as suspicious based on:
- Risk score > 50
- Presence of high-severity risk factors
- Pattern matching against known attack signatures
- Anomaly detection (deviations from normal behavior)

---

## Database Schema

### audit_logs Table (Tenant)

**Note**: Schema shows comprehensive version from code. Actual migration may differ.

```sql
CREATE TABLE audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Polymorphic Relationships
    auditable_type VARCHAR(125) NOT NULL,
    auditable_id BIGINT UNSIGNED NOT NULL,
    actor_type VARCHAR(125) NULL,
    actor_id BIGINT UNSIGNED NULL,
    target_type VARCHAR(125) NULL,
    target_id BIGINT UNSIGNED NULL,

    -- Event Information
    event VARCHAR(255) NOT NULL,
    event_category VARCHAR(125) NOT NULL,
    action VARCHAR(255) NULL,
    properties TEXT NULL,
    category VARCHAR(125) NULL,

    -- Change Tracking
    old_values JSON NULL,
    new_values JSON NULL,
    metadata JSON NULL,

    -- Context
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    session_id VARCHAR(255) NULL,
    request_id VARCHAR(255) NULL,
    occurred_at TIMESTAMP NOT NULL,

    -- Risk Assessment
    severity VARCHAR(50) DEFAULT 'low',
    risk_score INT NULL,
    risk_level VARCHAR(50) NULL,
    risk_factors JSON NULL,
    is_suspicious BOOLEAN DEFAULT 0,

    -- Location (optional)
    location_country VARCHAR(125) NULL,
    location_city VARCHAR(125) NULL,
    location_lat DECIMAL(10,8) NULL,
    location_lng DECIMAL(11,8) NULL,

    -- Tenant Context (from actual schema)
    tenant_user_id BIGINT NULL,
    tenant_id VARCHAR(255) NULL,
    subject_type VARCHAR(255) NULL,  -- Similar to auditable_type
    subject_id VARCHAR(255) NULL,     -- Similar to auditable_id
    description TEXT NULL,
    details JSON NULL,                -- Similar to metadata

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Indexes
    INDEX idx_actor (actor_type, actor_id),
    INDEX idx_auditable (auditable_type, auditable_id),
    INDEX idx_event_category (event_category),
    INDEX idx_risk_level (risk_level),
    INDEX idx_is_suspicious (is_suspicious),
    INDEX idx_occurred_at (occurred_at),
    INDEX audit_logs_action_index (action),
    INDEX audit_logs_created_at_index (created_at),
    INDEX audit_logs_subject_type_subject_id_index (subject_type, subject_id),
    INDEX audit_logs_tenant_id_index (tenant_id),
    INDEX audit_logs_tenant_user_id_index (tenant_user_id)
);
```

### customer_audit_logs Table

```sql
CREATE TABLE customer_audit_logs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,

    -- Action Information
    action VARCHAR(255) NOT NULL,
    resource_type VARCHAR(125) NULL,
    resource_id BIGINT UNSIGNED NULL,
    description TEXT NULL,
    metadata JSON NULL,

    -- Context
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    session_id VARCHAR(255) NULL,

    -- Success Tracking
    success BOOLEAN DEFAULT 1,
    failure_reason TEXT NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Indexes
    INDEX idx_customer (customer_id),
    INDEX idx_action (action),
    INDEX idx_resource (resource_type, resource_id),
    INDEX idx_created_at (created_at),

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
```

### security_events Table (Central)

Used by SecurityAuditService for centralized security event tracking.

```sql
CREATE TABLE security_events (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    event_type VARCHAR(255) NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    url TEXT NULL,
    method VARCHAR(10) NULL,
    context JSON NULL,
    severity VARCHAR(50) DEFAULT 'medium',

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_event_type (event_type),
    INDEX idx_user_id (user_id),
    INDEX idx_severity (severity),
    INDEX idx_created_at (created_at)
);
```

---

## Usage Examples

### Example 1: Log User Action with Change Tracking

```php
use App\Models\AuditLog;
use App\Models\Customer;
use App\Models\User;

// When updating a customer record
$customer = Customer::find(123);
$oldValues = $customer->toArray();

$customer->update([
    'email' => 'newemail@example.com',
    'mobile_number' => '9876543210'
]);

$newValues = $customer->fresh()->toArray();

AuditLog::create([
    'auditable_type' => Customer::class,
    'auditable_id' => $customer->id,
    'actor_type' => User::class,
    'actor_id' => auth()->id(),
    'event' => 'customer.updated',
    'event_category' => 'data_modification',
    'action' => 'update',
    'old_values' => [
        'email' => $oldValues['email'],
        'mobile_number' => $oldValues['mobile_number']
    ],
    'new_values' => [
        'email' => $newValues['email'],
        'mobile_number' => $newValues['mobile_number']
    ],
    'metadata' => [
        'customer_name' => $customer->name,
        'fields_changed' => ['email', 'mobile_number']
    ],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'session_id' => session()->getId(),
    'occurred_at' => now(),
    'severity' => 'low',
    'risk_score' => 15,
    'risk_level' => 'low'
]);
```

### Example 2: Customer Portal Activity Logging

```php
use App\Models\CustomerAuditLog;

// In CustomerInsuranceController
public function show(CustomerInsurance $customerInsurance)
{
    // Log the policy view
    CustomerAuditLog::logPolicyAction(
        'view',
        $customerInsurance,
        'Customer viewed policy details'
    );

    return view('customer.policies.show', compact('customerInsurance'));
}

// Document download
public function downloadDocument(CustomerInsurance $customerInsurance, $documentId)
{
    try {
        $document = $customerInsurance->documents()->findOrFail($documentId);

        CustomerAuditLog::logPolicyAction(
            'download_document',
            $customerInsurance,
            "Downloaded document: {$document->name}",
            [
                'document_id' => $document->id,
                'document_name' => $document->name,
                'file_size' => $document->file_size
            ]
        );

        return response()->download($document->path);
    } catch (\Exception $e) {
        CustomerAuditLog::logFailure(
            'download_document',
            'Document not found or access denied',
            ['document_id' => $documentId]
        );

        return back()->with('error', 'Document not found');
    }
}
```

### Example 3: Security Event Logging

```php
use App\Services\SecurityAuditService;

// In authentication flow
public function login(Request $request)
{
    $securityAudit = app(SecurityAuditService::class);

    $credentials = $request->only('email', 'password');

    if (Auth::attempt($credentials)) {
        $securityAudit->logAuthenticationEvent(
            'login_success',
            auth()->id(),
            [
                'method' => 'password',
                'remember_me' => $request->has('remember')
            ]
        );

        return redirect()->intended('dashboard');
    }

    $securityAudit->logAuthenticationEvent(
        'login_failed',
        null,
        [
            'email' => $credentials['email'],
            'reason' => 'invalid_credentials'
        ]
    );

    return back()->withErrors(['email' => 'Invalid credentials']);
}
```

### Example 4: Authorization Failure Logging

```php
// In middleware or authorization check
public function handle($request, Closure $next, $permission)
{
    if (!auth()->user()->hasPermission($permission)) {
        app(SecurityAuditService::class)->logAuthorizationEvent(
            'permission_denied',
            auth()->id(),
            [
                'required_permission' => $permission,
                'user_permissions' => auth()->user()->permissions->pluck('name'),
                'attempted_url' => $request->fullUrl(),
                'method' => $request->method()
            ]
        );

        abort(403, 'Unauthorized action.');
    }

    return $next($request);
}
```

### Example 5: Query Recent Suspicious Activity

```php
use App\Services\AuditService;

$auditService = app(AuditService::class);

// Get suspicious activity from last 7 days
$suspicious = $auditService->getSuspiciousActivity(7);

foreach ($suspicious as $log) {
    echo "Event: {$log->event}\n";
    echo "Actor: {$log->actor_type}#{$log->actor_id}\n";
    echo "Risk: {$log->risk_level} ({$log->risk_score})\n";
    echo "Factors: " . implode(', ', $log->risk_factors ?? []) . "\n";
    echo "Location: {$log->formatted_location}\n";
    echo "---\n";
}
```

### Example 6: Generate Security Report

```php
use App\Services\AuditService;

$auditService = app(AuditService::class);
$report = $auditService->generateSecurityReport(30);

// Email to security team
Mail::to('security@company.com')->send(
    new SecurityReportMail($report)
);

// Or display in dashboard
return view('admin.security.report', compact('report'));
```

### Example 7: Export Audit Logs for Compliance

```php
// Export last 90 days for compliance audit
$logs = app(AuditService::class)->exportLogs([
    'date_from' => now()->subDays(90),
    'event_category' => 'data_access',
    'actor_type' => User::class
], 'csv');

// Save to file
Storage::put('compliance/audit_export_' . now()->format('Y-m-d') . '.csv', $logs);

// Or download
return response($logs)
    ->header('Content-Type', 'text/csv')
    ->header('Content-Disposition', 'attachment; filename="audit_logs.csv"');
```

### Example 8: Real-Time Security Monitoring

```php
// Schedule command to run every 5 minutes
// app/Console/Commands/MonitorSecurityEvents.php

public function handle()
{
    $auditService = app(AuditService::class);

    // Check last 5 minutes for high-risk events
    $highRisk = AuditLog::highRisk()
        ->recentActivity(0.08) // ~5 minutes in hours
        ->get();

    if ($highRisk->count() > 10) {
        // Alert: Unusual spike in high-risk events
        Notification::send(
            User::admins()->get(),
            new HighRiskActivitySpike($highRisk->count())
        );
    }

    // Check for suspicious patterns
    $suspicious = $auditService->getSuspiciousActivity(0.08);

    foreach ($suspicious as $log) {
        if ($log->hasRiskFactor('privilege_escalation')) {
            // Immediate alert for privilege escalation
            Notification::send(
                User::superAdmins()->get(),
                new PrivilegeEscalationAlert($log)
            );
        }
    }
}
```

---

## Querying & Reporting

### Advanced Queries

**Find All Actions by Specific User**:
```php
$userLogs = AuditLog::where('actor_type', User::class)
    ->where('actor_id', $userId)
    ->with(['auditable'])
    ->orderBy('occurred_at', 'desc')
    ->paginate(50);
```

**Track Changes to Specific Entity**:
```php
$policyHistory = AuditLog::where('auditable_type', CustomerInsurance::class)
    ->where('auditable_id', $policyId)
    ->whereNotNull('old_values')
    ->orderBy('occurred_at', 'asc')
    ->get();
```

**Find Failed Login Attempts**:
```php
$failedLogins = AuditLog::where('event', 'login_failed')
    ->where('occurred_at', '>=', now()->subDay())
    ->groupBy('ip_address')
    ->selectRaw('ip_address, count(*) as attempts')
    ->having('attempts', '>=', 5)
    ->get();
```

**Compliance Query - Data Access Logs**:
```php
$dataAccessLogs = AuditLog::byEventCategory('data_access')
    ->where('auditable_type', Customer::class)
    ->where('auditable_id', $customerId)
    ->whereBetween('occurred_at', [$startDate, $endDate])
    ->with(['actor'])
    ->get();
```

### Dashboard Metrics

```php
// Last 24 hours overview
$metrics = [
    'total_events' => AuditLog::recentActivity(24)->count(),
    'failed_logins' => AuditLog::where('event', 'login_failed')
        ->recentActivity(24)->count(),
    'high_risk' => AuditLog::highRisk()->recentActivity(24)->count(),
    'suspicious' => AuditLog::suspicious()->recentActivity(24)->count(),
    'by_category' => AuditLog::recentActivity(24)
        ->groupBy('event_category')
        ->selectRaw('event_category, count(*) as count')
        ->pluck('count', 'event_category'),
    'by_hour' => AuditLog::recentActivity(24)
        ->selectRaw('HOUR(occurred_at) as hour, count(*) as count')
        ->groupBy('hour')
        ->pluck('count', 'hour')
];
```

---

## Compliance Features

### GDPR Compliance

**Data Subject Access Request (DSAR)**:
```php
// Retrieve all audit logs for a customer
public function getCustomerAuditData(Customer $customer)
{
    return [
        'customer_portal_activity' => CustomerAuditLog::where('customer_id', $customer->id)->get(),
        'staff_actions_on_customer' => AuditLog::where('auditable_type', Customer::class)
            ->where('auditable_id', $customer->id)
            ->get(),
        'related_policy_activity' => AuditLog::where('auditable_type', CustomerInsurance::class)
            ->whereIn('auditable_id', $customer->customerInsurances->pluck('id'))
            ->get()
    ];
}
```

**Right to Erasure**:
```php
// Anonymize audit logs when customer data is deleted
public function anonymizeCustomerAuditLogs(Customer $customer)
{
    CustomerAuditLog::where('customer_id', $customer->id)->update([
        'ip_address' => null,
        'user_agent' => null,
        'session_id' => null,
        'metadata' => null
    ]);

    AuditLog::where('auditable_type', Customer::class)
        ->where('auditable_id', $customer->id)
        ->update([
            'ip_address' => null,
            'user_agent' => null,
            'metadata->customer_email' => 'deleted@anonymized.com'
        ]);
}
```

### Data Retention

```php
// Scheduled command to clean old audit logs
// Keep 2 years for compliance, then delete
public function cleanOldAuditLogs()
{
    $retentionDate = now()->subYears(2);

    $deleted = AuditLog::where('occurred_at', '<', $retentionDate)->delete();
    $this->info("Deleted {$deleted} audit logs older than 2 years");

    $deletedCustomer = CustomerAuditLog::where('created_at', '<', $retentionDate)->delete();
    $this->info("Deleted {$deletedCustomer} customer audit logs older than 2 years");
}
```

### SOC 2 Compliance

**Access Control Audit Trail**:
```php
// Track all permission and role changes
AuditLog::create([
    'auditable_type' => User::class,
    'auditable_id' => $user->id,
    'actor_type' => User::class,
    'actor_id' => auth()->id(),
    'event' => 'permissions.changed',
    'event_category' => 'authorization',
    'old_values' => ['permissions' => $oldPermissions],
    'new_values' => ['permissions' => $newPermissions],
    'severity' => 'medium',
    'occurred_at' => now()
]);
```

---

## Best Practices

### 1. Consistent Event Naming

**Use structured naming**:
```php
// Good
'customer.created'
'policy.updated'
'authentication.login_success'

// Bad
'customer_was_created'
'UpdatedPolicy'
'LOGIN'
```

### 2. Capture Sufficient Context

**Include relevant metadata**:
```php
// Good
AuditLog::create([
    'event' => 'policy.status_changed',
    'metadata' => [
        'policy_no' => $policy->policy_no,
        'old_status' => 'pending',
        'new_status' => 'active',
        'reason' => 'Payment received',
        'changed_by_role' => auth()->user()->roles->pluck('name')
    ]
]);

// Bad
AuditLog::create([
    'event' => 'policy.updated'
]);
```

### 3. Implement Audit Logging Consistently

**Use service layer or traits**:
```php
// Create a HasAuditLog trait
trait HasAuditLog
{
    protected static function bootHasAuditLog()
    {
        static::created(function ($model) {
            $model->logAudit('created');
        });

        static::updated(function ($model) {
            $model->logAudit('updated');
        });

        static::deleted(function ($model) {
            $model->logAudit('deleted');
        });
    }

    public function logAudit(string $action, array $metadata = [])
    {
        AuditLog::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->id,
            'actor_type' => auth()->check() ? get_class(auth()->user()) : null,
            'actor_id' => auth()->id(),
            'event' => strtolower(class_basename($this)) . '.' . $action,
            'event_category' => 'data_modification',
            'old_values' => $this->getOriginal(),
            'new_values' => $this->getAttributes(),
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'occurred_at' => now()
        ]);
    }
}
```

### 4. Regular Monitoring & Alerting

**Set up automated monitoring**:
```php
// Schedule:command('audit:monitor')->everyFiveMinutes()

public function handle()
{
    $alerts = [];

    // Check for unusual activity
    $recentHighRisk = AuditLog::highRisk()->recentActivity(0.08)->count();
    if ($recentHighRisk > 10) {
        $alerts[] = "High risk activity spike: {$recentHighRisk} events in 5 min";
    }

    // Check failed login patterns
    $failedLogins = AuditLog::where('event', 'login_failed')
        ->recentActivity(1)
        ->count();
    if ($failedLogins > 50) {
        $alerts[] = "Potential brute force attack: {$failedLogins} failed logins in 1 hour";
    }

    if (!empty($alerts)) {
        Notification::route('slack', config('logging.slack_webhook'))
            ->notify(new SecurityAlert($alerts));
    }
}
```

### 5. Performance Optimization

**Use database indexes**:
- Already indexed: actor, auditable, event_category, risk_level, occurred_at
- Add custom indexes for frequent queries

**Partition old data**:
```php
// Consider archiving old logs to separate table
public function archiveOldLogs()
{
    $archiveDate = now()->subMonths(6);

    DB::transaction(function () use ($archiveDate) {
        $oldLogs = AuditLog::where('occurred_at', '<', $archiveDate)->get();

        foreach ($oldLogs->chunk(1000) as $chunk) {
            DB::table('audit_logs_archive')->insert($chunk->toArray());
        }

        AuditLog::where('occurred_at', '<', $archiveDate)->delete();
    });
}
```

### 6. Testing Audit Logs

**Write tests for audit logging**:
```php
public function test_customer_creation_is_audited()
{
    $user = User::factory()->create();
    $this->actingAs($user);

    $customer = Customer::factory()->create();

    $this->assertDatabaseHas('audit_logs', [
        'auditable_type' => Customer::class,
        'auditable_id' => $customer->id,
        'actor_type' => User::class,
        'actor_id' => $user->id,
        'event' => 'customer.created',
        'event_category' => 'data_modification'
    ]);
}
```

---

## Performance Optimization

### 1. Eager Loading Relationships

```php
// Avoid N+1 queries
$logs = AuditLog::with(['auditable', 'actor'])
    ->recentActivity(24)
    ->get();
```

### 2. Index Usage

All critical query patterns are indexed:
- `(actor_type, actor_id)` - Actor lookup
- `(auditable_type, auditable_id)` - Entity history
- `event_category` - Category filtering
- `risk_level` - Risk-based queries
- `occurred_at` - Time-based queries

### 3. Async Logging (Future Enhancement)

For high-volume systems, consider queuing audit log creation:

```php
// Dispatch to queue
dispatch(new CreateAuditLogJob($auditData));

// Or use events
event(new AuditableActionPerformed($auditData));
```

### 4. Database Partitioning

For large-scale deployments:

```sql
-- Partition by month
ALTER TABLE audit_logs
PARTITION BY RANGE (YEAR(occurred_at) * 100 + MONTH(occurred_at)) (
    PARTITION p202501 VALUES LESS THAN (202502),
    PARTITION p202502 VALUES LESS THAN (202503),
    -- ...
);
```

---

## Related Documentation

### Core Features
- **[DEVICE_TRACKING.md](DEVICE_TRACKING.md)** - Device security event integration
- **[TWO_FACTOR_AUTHENTICATION.md](TWO_FACTOR_AUTHENTICATION.md)** - 2FA audit events
- **[SUBSCRIPTION_MANAGEMENT.md](SUBSCRIPTION_MANAGEMENT.md)** - Billing event auditing

### Architecture
- **[SERVICE_LAYER.md](../core/SERVICE_LAYER.md)** - AuditService and SecurityAuditService
- **[MIDDLEWARE_REFERENCE.md](../core/MIDDLEWARE_REFERENCE.md)** - Security middleware integration
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - Complete audit table schemas

### Compliance
- **[SECURITY_SETTINGS.md](SECURITY_SETTINGS.md)** - Security configuration
- **[DATA_PRIVACY.md](DATA_PRIVACY.md)** - GDPR compliance and data handling

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
**System Version**: Multi-Tenancy SaaS v2.0
**Maintained By**: Development Team
