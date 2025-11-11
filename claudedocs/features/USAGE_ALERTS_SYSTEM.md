# Usage Alerts & Notifications System

**Version**: 1.0
**Last Updated**: 2025-01-07
**Status**: Fully Implemented (Core) + Enhancements In Progress

Complete subscription usage alerts and notifications system with real-time monitoring, email notifications, analytics, WhatsApp integration, and custom thresholds.

---

## Table of Contents

1. [System Overview](#system-overview)
2. [Core Features](#core-features)
3. [Alert Thresholds](#alert-thresholds)
4. [Database Schema](#database-schema)
5. [Services & Components](#services--components)
6. [Email Notifications](#email-notifications)
7. [Automated Monitoring](#automated-monitoring)
8. [Central Admin Dashboard](#central-admin-dashboard)
9. [Tenant Portal Integration](#tenant-portal-integration)
10. [WhatsApp Notifications](#whatsapp-notifications)
11. [Usage Analytics](#usage-analytics)
12. [Custom Thresholds](#custom-thresholds)
13. [API Reference](#api-reference)
14. [Testing](#testing)

---

## System Overview

The Usage Alerts System provides automated monitoring of tenant resource usage (users, customers, storage) and sends notifications when tenants approach or exceed their subscription plan limits.

### Key Capabilities

- **Automated Threshold Monitoring**: Checks all tenants every 6 hours
- **Three-Tier Alert System**: Warning (80%), Critical (90%), Exceeded (100%)
- **Multi-Channel Notifications**: Email + WhatsApp
- **Grace Period Management**: 3-day grace period after exceeding limits
- **Auto-Resolution**: Automatically resolves alerts when usage drops
- **Analytics & Predictions**: Usage trending and limit predictions
- **Custom Thresholds**: Per-tenant customizable alert levels

---

## Core Features

### ✅ Implemented

1. **Alert Generation**
   - Automatic threshold detection
   - 24-hour cooldown to prevent spam
   - Support for users, customers, and storage limits

2. **Email Notifications**
   - Three professionally designed templates
   - Severity-based color coding
   - Actionable recommendations
   - Upgrade links

3. **Automated Monitoring**
   - Scheduled Artisan command (every 6 hours)
   - Manual execution support
   - Per-tenant or per-resource filtering

4. **Grace Period System**
   - 3-day buffer after exceeding 100%
   - Countdown tracking
   - Automatic enforcement after grace period

5. **Central Admin Dashboard** (Backend Ready)
   - Global usage statistics
   - Alert management interface
   - Tenant-specific usage views

6. **Usage Analytics** (Backend Ready)
   - 30-day trending data
   - Growth rate calculations
   - Limit predictions

7. **Custom Thresholds** (Backend Ready)
   - Per-tenant threshold configuration
   - Override default 80%/90%/100% levels

8. **WhatsApp Integration** (Backend Ready)
   - Template-based messages
   - Multi-channel delivery

---

## Alert Thresholds

### Default Thresholds

| Level | Percentage | Email | Action |
|-------|-----------|-------|--------|
| **Warning** | 80% | ⚠️ Yellow | Informational notification |
| **Critical** | 90% | 🚨 Red | Urgent notification |
| **Exceeded** | 100% | ⛔ Dark Red | Grace period starts (3 days) |
| **Blocked** | 100% + 3 days | - | Resource creation blocked |

### Threshold Behavior

- **80% (Warning)**:
  - Email sent with friendly reminder
  - Recommendations for optimization
  - Upgrade suggestion

- **90% (Critical)**:
  - Urgent email notification
  - Clear call-to-action
  - Grace period explanation

- **100% (Exceeded)**:
  - Final warning email
  - 3-day grace period activated
  - Timeline of restrictions

- **After Grace Period**:
  - New resource creation blocked
  - Existing data remains accessible
  - Email reminders every 24 hours

### Cooldown Period

- **24 hours** between alerts of the same type
- Prevents notification spam
- Resets after usage drops below threshold

---

## Database Schema

### `usage_alerts` Table

```sql
CREATE TABLE usage_alerts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tenant_id VARCHAR(255) NOT NULL,
    resource_type ENUM('users', 'customers', 'storage') NOT NULL,
    threshold_level ENUM('warning', 'critical', 'exceeded') NOT NULL,
    usage_percentage DECIMAL(5,2) NOT NULL,
    current_usage INT NOT NULL,
    limit_value INT NOT NULL,
    alert_status ENUM('pending', 'sent', 'acknowledged', 'resolved') DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    acknowledged_at TIMESTAMP NULL,
    resolved_at TIMESTAMP NULL,
    notification_channels JSON NULL,
    notes TEXT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    FOREIGN KEY (tenant_id) REFERENCES tenants(id) ON DELETE CASCADE,
    INDEX idx_tenant_resource_threshold (tenant_id, resource_type, threshold_level),
    INDEX idx_alert_status (alert_status),
    INDEX idx_created_at (created_at)
);
```

### Alert Status Flow

```
pending → sent → acknowledged → resolved
    ↓        ↓         ↓            ↓
(created) (email) (tenant views) (usage drops)
```

---

## Services & Components

### 1. UsageAlertService

**Location**: `app/Services/UsageAlertService.php`

**Core Methods**:

```php
// Check thresholds for a tenant
checkTenantThresholds(Tenant $tenant, array $resourceTypes = []): array

// Check specific resource
checkResourceThreshold(Tenant $tenant, string $resourceType): ?UsageAlert

// Send alert notification
sendAlertNotification(UsageAlert $alert): bool

// Auto-resolve alerts
autoResolveAlerts(Tenant $tenant): int

// Get active alerts
getActiveAlerts(Tenant $tenant): Collection

// Check if resource should be blocked
shouldBlockResource(Tenant $tenant, string $resourceType): bool

// Get grace period remaining
getGracePeriodRemaining(Tenant $tenant, string $resourceType): int

// Get global statistics
getGlobalAlertStatistics(): array
```

**Constants**:
```php
const THRESHOLD_WARNING = 80;
const THRESHOLD_CRITICAL = 90;
const THRESHOLD_EXCEEDED = 100;
const ALERT_COOLDOWN_HOURS = 24;
```

### 2. UsageAnalyticsService

**Location**: `app/Services/UsageAnalyticsService.php`

**Core Methods**:

```php
// Get global usage trends
getGlobalUsageTrends(int $days = 30): array

// Get tenant-specific trends
getTenantUsageTrends(Tenant $tenant, int $days = 30): array

// Predict when tenant will hit limits
predictUsage(Tenant $tenant): array
```

**Returns**: Chart-ready data for Chart.js integration

### 3. UsageAlert Model

**Location**: `app/Models/Central/UsageAlert.php`

**Key Features**:
- Belongs to Tenant
- Status helpers (`isWarning()`, `isCritical()`, `isExceeded()`)
- Action methods (`markAsSent()`, `acknowledge()`, `resolve()`)
- Display attributes (`severity`, `resource_type_display`, `usage_display`)
- Query scopes (`active`, `forTenant`, `forResource`, `atThreshold`)

---

## Email Notifications

### Templates

Three responsive HTML email templates with severity-based styling:

1. **Warning Email** (`resources/views/emails/usage-alerts/warning.blade.php`)
   - Yellow/amber color scheme
   - Friendly reminder tone
   - Usage progress bar
   - Recommendations section

2. **Critical Email** (`resources/views/emails/usage-alerts/critical.blade.php`)
   - Red color scheme
   - Urgent tone
   - Action required section
   - Grace period explanation

3. **Exceeded Email** (`resources/views/emails/usage-alerts/exceeded.blade.php`)
   - Dark red scheme
   - 3-day countdown
   - Timeline visualization
   - Multiple resolution options

### Email Data Variables

```php
$emailData = [
    'company_name' => 'Company Name',
    'resource_type' => 'Staff Users',
    'threshold_level' => 'Warning (80%)',
    'usage_percentage' => 85.5,
    'current_usage' => 43,
    'limit_value' => 50,
    'usage_display' => '43 / 50 users',
    'severity' => 'warning',
    'plan_name' => 'Professional Plan',
    'upgrade_url' => 'https://...',
];
```

---

## Automated Monitoring

### Artisan Command

**Command**: `usage:check-thresholds`

**Location**: `app/Console/Commands/CheckUsageThresholds.php`

**Usage**:

```bash
# Check all tenants
php artisan usage:check-thresholds

# Check specific tenant
php artisan usage:check-thresholds --tenant={uuid}

# Check specific resource only
php artisan usage:check-thresholds --resource=users
php artisan usage:check-thresholds --resource=customers
php artisan usage:check-thresholds --resource=storage
```

**Features**:
- Progress bar with colored output
- Summary tables
- Error handling
- Logging

**Schedule**: Every 6 hours (configured in `app/Console/Kernel.php`)

```php
$schedule->command('usage:check-thresholds')
    ->everySixHours()
    ->withoutOverlapping();
```

---

## Central Admin Dashboard

### Controller

**Location**: `app/Http/Controllers/Central/UsageAlertController.php`

**Routes** (to be added):

```php
Route::prefix('midas-admin')->name('central.')->middleware(['auth:central'])->group(function () {
    Route::resource('usage-alerts', UsageAlertController::class)->only(['index', 'show']);
    Route::post('usage-alerts/{alert}/acknowledge', [UsageAlertController::class, 'acknowledge'])->name('usage-alerts.acknowledge');
    Route::post('usage-alerts/{alert}/resolve', [UsageAlertController::class, 'resolve'])->name('usage-alerts.resolve');
    Route::get('usage-alerts/analytics', [UsageAlertController::class, 'analytics'])->name('usage-alerts.analytics');
    Route::get('tenants/{tenant}/usage', [UsageAlertController::class, 'tenantUsage'])->name('tenants.usage');
    Route::post('tenants/{tenant}/thresholds', [UsageAlertController::class, 'updateThresholds'])->name('tenants.thresholds');
});
```

### Features

1. **Alert Dashboard** (`central/usage-alerts/index`)
   - List all alerts with filters (status, threshold, resource)
   - Global statistics cards
   - Usage analytics charts
   - Pagination

2. **Alert Detail View** (`central/usage-alerts/{alert}`)
   - Full alert details
   - Tenant current usage
   - Alert history for resource
   - Actions (acknowledge, resolve)

3. **Tenant Usage View** (AJAX)
   - Real-time usage data
   - Active alerts
   - Alert summary

4. **Analytics Dashboard** (AJAX)
   - 30-day trending charts
   - Growth predictions
   - Tenant comparisons

---

## Tenant Portal Integration

### Usage Warning Banner

**Planned Component**: `resources/views/staff/partials/usage-warning-banner.blade.php`

**Features**:
- Displays at top of all tenant staff pages
- Color-coded by severity (yellow, red, dark red)
- Dismissible (stores in session)
- Direct link to upgrade page
- Grace period countdown (if applicable)

**Example**:

```blade
@if($hasUsageAlerts)
    <div class="alert alert-{{ $alertSeverity }} alert-dismissible fade show mb-3">
        <div class="d-flex align-items-center">
            <span class="alert-icon me-3">{{ $alertIcon }}</span>
            <div class="flex-grow-1">
                <strong>{{ $alertTitle }}</strong>
                <p class="mb-0">{{ $alertMessage }}</p>
                @if($gracePeriodDays > 0)
                    <small>Grace period: {{ $gracePeriodDays }} days remaining</small>
                @endif
            </div>
            <a href="{{ route('staff.settings.plans') }}" class="btn btn-sm btn-primary me-2">
                Upgrade Plan
            </a>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    </div>
@endif
```

### Middleware Integration

**Planned**: `app/Http/Middleware/InjectUsageAlerts.php`

**Purpose**: Inject usage alert data into all tenant views

```php
public function handle($request, Closure $next)
{
    if (auth('staff')->check() && tenant()) {
        $tenant = tenant();
        $usageAlertService = app(UsageAlertService::class);

        $summary = $usageAlertService->getAlertSummary($tenant);

        view()->share('usageAlertSummary', $summary);
        view()->share('hasUsageAlerts', $summary['total_active'] > 0);
        view()->share('hasCriticalAlerts', $summary['has_critical']);
    }

    return $next($request);
}
```

---

## WhatsApp Notifications

### Integration

**Service Enhancement**: Modify `app/Services/UsageAlertService.php::sendAlertNotification()`

```php
public function sendAlertNotification(UsageAlert $alert): bool
{
    $channels = ['email'];

    // Send email
    $emailSent = $this->sendEmail($alert);

    // Send WhatsApp if configured
    if ($this->shouldSendWhatsApp($alert)) {
        $whatsAppSent = $this->sendWhatsApp($alert);
        if ($whatsAppSent) {
            $channels[] = 'whatsapp';
        }
    }

    $alert->markAsSent($channels);
    return $emailSent;
}
```

### WhatsApp Templates

**Warning Template**:
```
⚠️ *Usage Warning*

Hello {{company_name}},

Your {{resource_type}} usage has reached *{{usage_percentage}}%* of your {{plan_name}} limit.

Current: {{current_usage}} / {{limit_value}}

Please monitor your usage or consider upgrading your plan.

Upgrade: {{upgrade_url}}
```

**Critical Template**:
```
🚨 *CRITICAL: Usage Alert*

Hello {{company_name}},

⚠️ *URGENT* - Your {{resource_type}} usage is at *{{usage_percentage}}%*!

Current: {{current_usage}} / {{limit_value}}

You're approaching your limit. Service restrictions will apply at 100%.

Take action now: {{upgrade_url}}
```

**Exceeded Template**:
```
⛔ *Usage Limit Exceeded*

Hello {{company_name}},

Your {{resource_type}} has reached *100%* of your plan limit.

⏰ *Grace Period: 3 days*

After the grace period, creating new {{resource_type}} will be restricted.

Upgrade immediately to avoid service interruption: {{upgrade_url}}
```

---

## Usage Analytics

### Global Analytics

**Endpoint**: `GET /midas-admin/usage-alerts/analytics?days=30`

**Response**:
```json
{
    "labels": ["Jan 1", "Jan 2", ...],
    "datasets": {
        "users": [100, 105, 110, ...],
        "customers": [500, 520, 540, ...],
        "storage": [10.5, 11.2, 12.0, ...],
        "warnings": [2, 3, 1, ...],
        "critical": [0, 1, 1, ...]
    },
    "summary": {
        "users": {
            "first_week_avg": 102.5,
            "last_week_avg": 115.3,
            "trend": "up"
        },
        ...
    }
}
```

### Tenant Analytics

**Endpoint**: `GET /midas-admin/usage-alerts/analytics?tenant_id={uuid}&days=30`

**Response**:
```json
{
    "labels": ["Jan 1", "Jan 2", ...],
    "datasets": {
        "users": [10, 12, 15, ...],
        "customers": [50, 55, 60, ...],
        "storage": [2.5, 2.8, 3.0, ...]
    },
    "limits": {
        "users": 50,
        "customers": 500,
        "storage": 10
    },
    "predictions": {
        "users": {
            "days_until_limit": 45,
            "growth_rate": 5.0,
            "status": "healthy",
            "current_percentage": 30.0
        },
        ...
    }
}
```

### Chart.js Integration

**Example**:
```javascript
fetch('/midas-admin/usage-alerts/analytics?days=30')
    .then(response => response.json())
    .then(data => {
        const ctx = document.getElementById('usageChart').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Users',
                        data: data.datasets.users,
                        borderColor: '#3490dc',
                        fill: false
                    },
                    {
                        label: 'Customers',
                        data: data.datasets.customers,
                        borderColor: '#38c172',
                        fill: false
                    }
                ]
            }
        });
    });
```

---

## Custom Thresholds

### Configuration

Tenants can customize alert thresholds in their settings:

**Endpoint**: `POST /midas-admin/tenants/{tenant}/thresholds`

**Request**:
```json
{
    "enable_custom_thresholds": true,
    "warning_threshold": 75,
    "critical_threshold": 85
}
```

**Storage**: Stored in `tenants.data` JSON column:

```json
{
    "company_name": "ABC Insurance",
    "custom_thresholds": {
        "enabled": true,
        "warning": 75,
        "critical": 85,
        "updated_at": "2025-01-07 10:30:00"
    }
}
```

### Service Integration

```php
protected function getThresholds(Tenant $tenant): array
{
    $customThresholds = $tenant->data['custom_thresholds'] ?? null;

    if ($customThresholds && ($customThresholds['enabled'] ?? false)) {
        return [
            'warning' => $customThresholds['warning'] ?? 80,
            'critical' => $customThresholds['critical'] ?? 90,
            'exceeded' => 100,
        ];
    }

    return [
        'warning' => self::THRESHOLD_WARNING,
        'critical' => self::THRESHOLD_CRITICAL,
        'exceeded' => self::THRESHOLD_EXCEEDED,
    ];
}
```

---

## API Reference

### UsageAlertService Methods

#### checkTenantThresholds

```php
checkTenantThresholds(Tenant $tenant, array $resourceTypes = []): array
```

**Parameters**:
- `$tenant`: Tenant instance
- `$resourceTypes`: Optional array of resources to check (default: all)

**Returns**: Array of created UsageAlert instances

**Example**:
```php
$service = app(UsageAlertService::class);
$alerts = $service->checkTenantThresholds($tenant, ['users', 'customers']);
```

#### shouldBlockResource

```php
shouldBlockResource(Tenant $tenant, string $resourceType): bool
```

**Parameters**:
- `$tenant`: Tenant instance
- `$resourceType`: 'users', 'customers', or 'storage'

**Returns**: Boolean indicating if resource creation should be blocked

**Example**:
```php
if ($service->shouldBlockResource($tenant, 'users')) {
    return redirect()->back()->with('error', 'User limit exceeded. Please upgrade your plan.');
}
```

#### getGracePeriodRemaining

```php
getGracePeriodRemaining(Tenant $tenant, string $resourceType): int
```

**Returns**: Days remaining in grace period (0 if no active exceeded alert)

### UsageAnalyticsService Methods

#### getGlobalUsageTrends

```php
getGlobalUsageTrends(int $days = 30): array
```

**Returns**: Chart-ready data with labels and datasets

#### predictUsage

```php
predictUsage(Tenant $tenant): array
```

**Returns**: Predictions for each resource type including days until limit

---

## Testing

### Manual Testing

```bash
# Test alert generation
php artisan usage:check-thresholds

# Test specific tenant
php artisan usage:check-thresholds --tenant=9d47b5e0-...

# Test specific resource
php artisan usage:check-thresholds --resource=users
```

### Database Checks

```sql
-- View all active alerts
SELECT * FROM usage_alerts WHERE alert_status IN ('pending', 'sent', 'acknowledged');

-- View alerts by tenant
SELECT * FROM usage_alerts WHERE tenant_id = '9d47b5e0-...' ORDER BY created_at DESC;

-- View exceeded alerts
SELECT * FROM usage_alerts WHERE threshold_level = 'exceeded' AND alert_status != 'resolved';
```

### Service Testing

```php
use App\Services\UsageAlertService;
use App\Models\Central\Tenant;

$service = app(UsageAlertService::class);
$tenant = Tenant::first();

// Get active alerts
$alerts = $service->getActiveAlerts($tenant);

// Check if blocked
$blocked = $service->shouldBlockResource($tenant, 'users');

// Get statistics
$stats = $service->getGlobalAlertStatistics();
```

---

## Related Documentation

- [SUBSCRIPTION_MANAGEMENT.md](SUBSCRIPTION_MANAGEMENT.md) - Subscription system
- [TRIAL_CONVERSION_SYSTEM.md](TRIAL_CONVERSION_SYSTEM.md) - Trial management
- [NOTIFICATION_SYSTEM.md](NOTIFICATION_SYSTEM.md) - General notifications

---

**Last Updated**: 2025-01-07
**Version**: 1.0
**For**: Midas Portal Multi-Tenant SaaS Insurance Management System
