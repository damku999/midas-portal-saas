# Device Tracking & Trust Management

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Device Identification](#device-identification)
4. [Trust Score System](#trust-score-system)
5. [Device Models](#device-models)
6. [Trust Management](#trust-management)
7. [Security Events](#security-events)
8. [Session Fingerprinting](#session-fingerprinting)
9. [Controllers & API](#controllers--api)
10. [Database Schema](#database-schema)
11. [Configuration](#configuration)
12. [Usage Examples](#usage-examples)
13. [Best Practices](#best-practices)
14. [Security Considerations](#security-considerations)
15. [Related Documentation](#related-documentation)

---

## Overview

The Device Tracking system provides comprehensive device identification, trust management, and security monitoring for both staff users and customers. The system tracks device fingerprints, maintains trust scores, monitors login patterns, and enables security features like device blocking and trusted device management.

### Key Features

- **Device Fingerprinting**: Unique device identification using hardware/browser signatures
- **Trust Score Calculation**: Dynamic scoring based on usage patterns and security events
- **Multi-Level Tracking**:
  - `DeviceTracking`: Comprehensive security monitoring with trust scores
  - `TrustedDevice`: Simple trusted device management for 2FA
  - `CustomerDevice`: Mobile app push notification device registration
- **Automatic Security**: Auto-blocking on suspicious activity, trust score management
- **Location & IP History**: Track device usage patterns over time
- **Security Events**: Comprehensive audit trail of device-related security events

### System Components

```
┌─────────────────────────────────────────────────────────┐
│                  Device Tracking System                  │
├─────────────────────────────────────────────────────────┤
│                                                           │
│  ┌──────────────────┐  ┌──────────────────┐            │
│  │ DeviceTracking   │  │ TrustedDevice    │            │
│  │ (Security        │  │ (2FA Trust)      │            │
│  │  Monitoring)     │  │                  │            │
│  └──────────────────┘  └──────────────────┘            │
│                                                           │
│  ┌──────────────────┐  ┌──────────────────┐            │
│  │ CustomerDevice   │  │ Security Events  │            │
│  │ (Push Tokens)    │  │ (Audit Trail)    │            │
│  └──────────────────┘  └──────────────────┘            │
│                                                           │
│  ┌─────────────────────────────────────────┐            │
│  │ Session Fingerprinting (Middleware)     │            │
│  └─────────────────────────────────────────┘            │
└─────────────────────────────────────────────────────────┘
```

---

## System Architecture

### Three-Tier Device Tracking

The system implements three complementary tracking mechanisms:

#### 1. DeviceTracking (Security-Focused)

**Purpose**: Comprehensive security monitoring and risk assessment

**Features**:
- Advanced fingerprinting with hardware/browser data
- Dynamic trust score calculation (0-100)
- Login attempt tracking with auto-blocking
- Location and IP history tracking
- Security event logging
- Automatic threat detection

**Use Cases**:
- Security monitoring and threat detection
- Suspicious activity identification
- Device risk assessment
- Compliance and audit requirements

#### 2. TrustedDevice (2FA Integration)

**Purpose**: Simple trusted device management for Two-Factor Authentication

**Features**:
- Device trust for 2FA bypass
- Expiration-based trust management
- User-managed trust revocation
- Polymorphic (User/Customer support)

**Use Cases**:
- "Remember this device" functionality
- 2FA trusted device management
- User convenience features

#### 3. CustomerDevice (Mobile App Support)

**Purpose**: Mobile app push notification device registration

**Features**:
- Push notification token management
- App version tracking
- Device activity monitoring
- Platform-specific management (iOS/Android/Web)

**Use Cases**:
- Push notification delivery
- Mobile app device management
- App version tracking

### Data Flow

```
┌──────────────┐
│ User Login   │
└──────┬───────┘
       │
       ▼
┌──────────────────────────────────────┐
│ Device Fingerprinting                │
│ - User Agent parsing                 │
│ - Browser/OS detection               │
│ - Hardware info collection           │
│ - Generate device_id hash            │
└──────┬───────────────────────────────┘
       │
       ▼
┌──────────────────────────────────────┐
│ DeviceTracking Check                 │
│ - Find or create device record       │
│ - Update last_seen_at                │
│ - Record login attempt               │
│ - Calculate trust score              │
└──────┬───────────────────────────────┘
       │
       ├─── Trust Score < 20? ──► Block Device
       │
       ├─── Failed Logins >= 5? ──► Block Device
       │
       ├─── Suspicious Activity? ──► Log Security Event
       │
       └──► Allow Login
             │
             ▼
      ┌──────────────────┐
      │ 2FA Required?    │
      └─────┬────────────┘
            │
            ├── YES ──► Check TrustedDevice
            │           │
            │           ├─── Trusted? ──► Skip 2FA
            │           └─── Not Trusted? ──► Require 2FA
            │                                 │
            │                                 └─► "Remember Device" Option
            │
            └── NO ──► Login Success
```

---

## Device Identification

### Device ID Generation

Device IDs are generated using SHA-256 hash of device characteristics:

#### DeviceTracking (Comprehensive)

```php
public static function generateDeviceId(array $fingerprintData): string
{
    $fingerprintString = json_encode($fingerprintData);
    return 'device_' . hash('sha256', $fingerprintString);
}
```

**Fingerprint Data Components**:
- Browser name and version
- Operating system and version
- Platform type (desktop/mobile/tablet)
- Screen resolution
- Hardware info (CPU, memory)
- Canvas fingerprint
- WebGL signature
- User agent string

#### TrustedDevice (Simple)

```php
public static function generateDeviceId(string $userAgent, string $ipAddress, ?string $additionalData = null): string
{
    $fingerprint = $userAgent . $ipAddress . ($additionalData ?? '');
    return hash('sha256', $fingerprint);
}
```

**Components**:
- User agent string
- IP address
- Optional additional data

### User Agent Parsing

Both systems parse user agents to extract device information:

```php
protected static function parseUserAgent(string $userAgent): array
{
    // Device Type Detection
    if (preg_match('/Mobile|Android|iPhone|iPad/', $userAgent)) {
        $deviceType = 'mobile';
        if (str_contains($userAgent, 'iPad')) $deviceType = 'tablet';
    } else {
        $deviceType = 'desktop';
    }

    // Browser Detection
    if (str_contains($userAgent, 'Chrome') && !str_contains($userAgent, 'Edg'))
        $browser = 'Chrome';
    elseif (str_contains($userAgent, 'Firefox'))
        $browser = 'Firefox';
    elseif (str_contains($userAgent, 'Safari') && !str_contains($userAgent, 'Chrome'))
        $browser = 'Safari';
    elseif (str_contains($userAgent, 'Edg'))
        $browser = 'Edge';

    // Platform Detection
    if (str_contains($userAgent, 'Windows')) $platform = 'Windows';
    elseif (str_contains($userAgent, 'Mac')) $platform = 'macOS';
    elseif (str_contains($userAgent, 'Linux')) $platform = 'Linux';
    elseif (str_contains($userAgent, 'Android')) $platform = 'Android';
    elseif (str_contains($userAgent, 'iOS|iPhone|iPad')) $platform = 'iOS';

    return [
        'device_type' => $deviceType,
        'browser' => $browser,
        'platform' => $platform,
        'device_name' => "{$browser} on {$platform}"
    ];
}
```

---

## Trust Score System

### Trust Score Calculation

DeviceTracking implements a sophisticated 0-100 trust score system:

```php
public function calculateTrustScore(): int
{
    $score = 50; // Base score

    // Age Factor: Older devices are more trusted
    $daysSinceFirstSeen = $this->first_seen_at->diffInDays(now());
    $score += min(20, floor($daysSinceFirstSeen / 7)); // +1 per week, max +20

    // Usage Frequency: More logins = more trust
    $score += min(15, $this->login_count); // +1 per login, max +15

    // Failed Login Penalty
    $score -= $this->failed_login_attempts * 3;

    // Trust History Bonus
    if ($this->is_trusted) {
        $score += 10;
    }

    // Recent Activity Bonus
    if ($this->last_seen_at && $this->last_seen_at->isAfter(now()->subWeek())) {
        $score += 5;
    }

    // Security Events Penalty
    $criticalEvents = $this->securityEvents()
        ->where('event_severity', 'critical')
        ->where('is_resolved', false)
        ->count();
    $score -= $criticalEvents * 10;

    return max(0, min(100, $score)); // Clamp to 0-100
}
```

### Trust Score Ranges

| Score Range | Classification | Auto-Actions |
|-------------|----------------|--------------|
| 80-100 | Highly Trusted | None |
| 60-79 | Trusted | None |
| 40-59 | Neutral | Monitor activity |
| 20-39 | Low Trust | Increase monitoring |
| 0-19 | High Risk | **Auto-block device** |

### Automatic Trust Management

```php
public function updateTrustScore(): void
{
    $newScore = $this->calculateTrustScore();
    $this->update(['trust_score' => $newScore]);

    // Auto-block if score is too low
    if ($newScore < 20 && !$this->is_blocked) {
        $this->blockDevice('Trust score too low');
    }
}
```

### Trust Score Modifiers

**Positive Modifiers**:
- Device age: +1 per week (max +20)
- Login count: +1 per login (max +15)
- Active trust status: +10
- Recent activity (last 7 days): +5

**Negative Modifiers**:
- Failed login attempts: -3 per failure
- Critical security events: -10 per unresolved event
- Auto-block trigger: Score < 20

---

## Device Models

### DeviceTracking Model

**File**: `app/Models/DeviceTracking.php`

#### Polymorphic Relationship

Tracks devices for any trackable entity (User, Customer, etc.):

```php
public function trackable(): MorphTo
{
    return $this->morphTo();
}
```

#### Key Methods

**Trust Management**:

```php
// Grant Trust
public function grantTrust(int $durationDays = 30, ?string $reason = null): void;

// Revoke Trust
public function revokeTrust(?string $reason = null): void;

// Check Trust Status
public function isTrusted(): bool;
public function isTrustExpired(): bool;
```

**Device Blocking**:

```php
// Block Device
public function blockDevice(string $reason): void;

// Unblock Device
public function unblockDevice(?string $reason = null): void;

// Check Block Status
public function isBlocked(): bool;
```

**Login Tracking**:

```php
// Record Successful Login
public function recordSuccessfulLogin(string $ip, ?array $location = null): void;

// Record Failed Login
public function recordFailedLogin(string $ip, string $reason): void;

// Auto-block after 5 failed attempts
```

**Activity Monitoring**:

```php
// Get Activity Summary
public function getActivitySummary(int $days = 30): array;
// Returns: total_sessions, total_duration, avg_session_duration,
//          unique_ips, unique_locations, suspicious_sessions
```

#### Query Scopes

```php
// Filter devices
DeviceTracking::trusted()     // Only trusted, non-expired devices
    ->untrusted()              // Only untrusted devices
    ->blocked()                // Only blocked devices
    ->active(30)               // Active in last N days
    ->highRisk(70)             // Trust score below threshold
    ->suspicious()             // Failed logins >= 3 OR trust score < 30
    ->get();
```

#### Attributes

**Core Fields**:
- `device_id` (string, unique): SHA-256 hash identifier
- `device_name` (string, nullable): User-friendly name
- `device_type` (string): desktop/mobile/tablet
- `browser` (string): Browser name
- `browser_version` (string, nullable): Browser version
- `operating_system` (string): OS name
- `os_version` (string, nullable): OS version
- `platform` (string): Platform type
- `user_agent` (string): Full user agent string

**Fingerprinting**:
- `screen_resolution` (JSON): {width, height}
- `hardware_info` (JSON): {cpu_cores, memory}
- `fingerprint_data` (JSON): {canvas, webgl, fonts, plugins}

**Trust Management**:
- `trust_score` (integer, 0-100): Calculated trust score
- `is_trusted` (boolean): Manual/automatic trust flag
- `trusted_at` (datetime, nullable): When trust was granted
- `trust_expires_at` (datetime, nullable): Trust expiration time

**Activity Tracking**:
- `first_seen_at` (datetime): First device usage
- `last_seen_at` (datetime): Most recent activity
- `login_count` (integer): Successful login count
- `failed_login_attempts` (integer): Failed login count
- `last_failed_login_at` (datetime, nullable): Last failed login

**History**:
- `location_history` (JSON array): Last 50 locations with timestamps
- `ip_history` (JSON array): Last 100 IPs with timestamps

**Blocking**:
- `is_blocked` (boolean): Device block status
- `blocked_reason` (string, nullable): Reason for blocking
- `blocked_at` (datetime, nullable): When device was blocked

### TrustedDevice Model

**File**: `app/Models/TrustedDevice.php`

#### Features

Simpler model focused on 2FA trusted device management:

```php
// Polymorphic relationship to User or Customer
public function authenticatable(): MorphTo;

// Check if device is valid and trusted
public function isValid(): bool;

// Update last used timestamp
public function updateLastUsed(): void;

// Revoke trust
public function revoke(): void;

// Extend trust period
public function extendTrust(?int $days = null): void;

// Get display name
public function getDisplayName(): string; // "Device Name (Browser) - Platform"
```

#### Query Scopes

```php
TrustedDevice::active()  // Only active devices
    ->valid()            // Active AND not expired
    ->get();
```

#### Attributes

- `device_id` (string): SHA-256 hash
- `device_name` (string): User-friendly name
- `device_type` (string, nullable): desktop/mobile/tablet
- `browser` (string, nullable): Browser name
- `platform` (string, nullable): Platform
- `ip_address` (string): IP address at trust time
- `user_agent` (string): User agent string
- `last_used_at` (datetime, nullable): Last usage
- `trusted_at` (datetime): When trust was granted
- `expires_at` (datetime, nullable): Trust expiration
- `is_active` (boolean): Active status

### CustomerDevice Model

**File**: `app/Models/CustomerDevice.php`

#### Features

Mobile app device registration for push notifications:

```php
// Relationship
public function customer();

// Mark device as active
public function markActive(): void;

// Deactivate device
public function deactivate(): void;
```

#### Query Scopes

```php
CustomerDevice::active()         // Only active devices
    ->ofType('ios')              // Filter by device type
    ->get();
```

#### Attributes

- `customer_id` (integer): Foreign key to customers
- `device_type` (string): ios/android/web
- `device_token` (string): Push notification token
- `device_name` (string, nullable): Device name
- `device_model` (string, nullable): Device model
- `os_version` (string, nullable): OS version
- `app_version` (string, nullable): App version
- `last_active_at` (datetime, nullable): Last activity
- `is_active` (boolean): Active status

---

## Trust Management

### Granting Trust

#### Via DeviceTracking

```php
$device = DeviceTracking::where('device_id', $deviceId)->first();

// Grant trust for 30 days with optional reason
$device->grantTrust(30, 'User verified via email confirmation');

// Effects:
// - Sets is_trusted = true
// - Sets trusted_at = now()
// - Sets trust_expires_at = now() + 30 days
// - Increases trust_score by +20 (max 100)
// - Logs 'trust_granted' security event
```

#### Via TrustedDevice (2FA)

```php
use App\Models\TrustedDevice;
use Illuminate\Http\Request;

// Create trusted device from request
$device = TrustedDevice::createFromRequest(
    $user,           // User or Customer model
    $request,        // Current request
    'My iPhone 14'   // Optional device name
);

// Or via trait method
$user->trustDevice($request, 'My MacBook Pro');
```

**CreateFromRequest Logic**:
1. Generates device_id from user agent + IP
2. Checks for existing device
3. If exists and inactive: reactivates
4. If exists and active: updates last_used_at
5. If new: creates with trust expiration (config: `security.device_trust_duration`)

### Revoking Trust

#### Via DeviceTracking

```php
$device->revokeTrust('User reported suspicious activity');

// Effects:
// - Sets is_trusted = false
// - Clears trusted_at and trust_expires_at
// - Logs 'trust_revoked' security event
```

#### Via TrustedDevice

```php
$trustedDevice->revoke();

// Effects:
// - Sets is_active = false
// - Device will no longer bypass 2FA
```

#### Via User/Customer (Bulk)

```php
// Revoke specific device
$user->revokeDeviceTrust($deviceId);

// Revoke all devices
$user->revokeAllDeviceTrust();
```

### Extending Trust

```php
// Via TrustedDevice
$trustedDevice->extendTrust(45); // Extend for 45 more days

// Via DeviceTracking
$device->grantTrust(60, 'Extended due to consistent usage pattern');
```

---

## Security Events

### Event Logging

DeviceTracking automatically logs security events:

```php
protected function logSecurityEvent(
    string $type,
    string $severity,
    string $description,
    array $data = []
): void;
```

### Event Types

| Event Type | Severity | Trigger |
|------------|----------|---------|
| `trust_granted` | medium | Manual trust grant |
| `trust_revoked` | medium | Trust revocation |
| `device_blocked` | high | Device blocked |
| `device_unblocked` | medium | Device unblocked |
| `successful_login` | low | Successful login recorded |
| `failed_login` | medium | Failed login attempt |
| `suspicious_activity` | high | Threshold exceeded |
| `trust_expired` | low | Trust period expired |

### Security Event Storage

Events are stored in the `device_security_events` relationship:

```php
$device->securityEvents()
    ->where('event_severity', 'high')
    ->where('is_resolved', false)
    ->get();
```

### Suspicious Activity Detection

```php
// Scope for suspicious devices
DeviceTracking::suspicious()->get();

// Criteria:
// - Failed login attempts >= 3
// - OR Trust score < 30
// - OR Has unresolved high/critical security events
```

---

## Session Fingerprinting

### Middleware Implementation

**File**: `app/Http/Middleware/EnhancedAuthorizationMiddleware.php`

Session fingerprinting validates that the browser/device hasn't changed mid-session:

```php
private function validateSessionFingerprint(Request $request): bool
{
    $currentFingerprint = $this->generateFingerprint($request);
    $sessionFingerprint = session('security_fingerprint');

    if ($sessionFingerprint && $sessionFingerprint !== $currentFingerprint) {
        return false; // Fingerprint mismatch - possible session hijacking
    }

    if (!$sessionFingerprint) {
        session(['security_fingerprint' => $currentFingerprint]);
    }

    return true;
}
```

### Fingerprint Generation

```php
private function generateFingerprint(Request $request): string
{
    return hash('sha256', implode('|', [
        $request->userAgent(),
        $request->header('Accept-Language'),
        $request->header('Accept-Encoding'),
        // Additional browser headers
    ]));
}
```

### Configuration

Controlled via `config/security.php`:

```php
'session_security' => [
    'fingerprint_validation' => env('SESSION_FINGERPRINT', true),
],

'enhanced_features' => [
    'session_fingerprinting' => env('SECURITY_SESSION_FINGERPRINTING', true),
    'user_agent_validation' => env('SECURITY_USER_AGENT_VALIDATION', true),
],
```

### Security Flow

```
┌─────────────┐
│ User Request│
└──────┬──────┘
       │
       ▼
┌──────────────────────────────┐
│ Generate Current Fingerprint │
└──────┬───────────────────────┘
       │
       ▼
┌───────────────────────────────┐
│ Compare with Session Value    │
└──────┬────────────────────────┘
       │
       ├─── Match? ──► Continue Request
       │
       └─── Mismatch? ──► Logout + Redirect
                          Log 'session_fingerprint_mismatch'
```

---

## Controllers & API

### CustomerDeviceController

**File**: `app/Http/Controllers/CustomerDeviceController.php`

Manages customer device viewing and administration.

#### Routes

```php
// Staff Portal - Customer Device Management
Route::middleware(['auth', 'permission:customer-device-list'])->group(function () {
    Route::get('/customer-devices', [CustomerDeviceController::class, 'index'])
        ->name('customer-devices.index');

    Route::get('/customer-devices/{customerDevice}', [CustomerDeviceController::class, 'show'])
        ->name('customer-devices.show');

    Route::post('/customer-devices/{customerDevice}/deactivate',
        [CustomerDeviceController::class, 'deactivate'])
        ->middleware('permission:customer-device-deactivate')
        ->name('customer-devices.deactivate');

    Route::post('/customer-devices/cleanup', [CustomerDeviceController::class, 'cleanupInvalid'])
        ->middleware('permission:customer-device-cleanup')
        ->name('customer-devices.cleanup');
});
```

#### Methods

**Index - List Devices**:

```php
public function index(Request $request)
{
    $builder = CustomerDevice::with('customer')
        ->orderBy('last_active_at', 'desc');

    // Filters: customer_id, device_type, status (active/inactive)
    // Search: device_name, device_token, customer name/mobile

    $devices = $builder->paginate(pagination_per_page());

    // Statistics
    $stats = [
        'total' => CustomerDevice::count(),
        'active' => CustomerDevice::where('is_active', true)->count(),
        'inactive' => CustomerDevice::where('is_active', false)->count(),
        'android' => CustomerDevice::where('device_type', 'android')
            ->where('is_active', true)->count(),
        'ios' => CustomerDevice::where('device_type', 'ios')
            ->where('is_active', true)->count(),
        'web' => CustomerDevice::where('device_type', 'web')
            ->where('is_active', true)->count(),
    ];

    return view('admin.customer_devices.index', compact('devices', 'stats'));
}
```

**Show - Device Details**:

```php
public function show(CustomerDevice $customerDevice)
{
    $customerDevice->load('customer');

    // Get notification logs for this device
    $notifications = DB::table('notification_logs')
        ->where('channel', 'push')
        ->where('recipient', $customerDevice->device_token)
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();

    return view('admin.customer_devices.show', compact('device', 'notifications'));
}
```

**Deactivate - Disable Device**:

```php
public function deactivate(CustomerDevice $customerDevice)
{
    $customerDevice->update(['is_active' => false]);
    return back()->with('success', 'Device deactivated successfully');
}
```

**Cleanup - Remove Inactive Devices**:

```php
public function cleanupInvalid(Request $request)
{
    // Deactivate devices inactive for 90+ days
    $count = CustomerDevice::where('is_active', true)
        ->where('last_active_at', '<', now()->subDays(90))
        ->update(['is_active' => false]);

    return response()->json([
        'success' => true,
        'message' => "Deactivated {$count} inactive devices"
    ]);
}
```

### CustomerDeviceApiController

**File**: `app/Http/Controllers/Api/CustomerDeviceApiController.php`

API endpoints for mobile apps to register and manage devices.

Expected endpoints (based on standard implementation):

```php
// Register new device
POST /api/customer/devices
{
    "device_type": "ios|android|web",
    "device_token": "fcm-token-or-apns-token",
    "device_name": "iPhone 14 Pro",
    "device_model": "iPhone15,2",
    "os_version": "17.0",
    "app_version": "1.2.0"
}

// Update device info
PUT /api/customer/devices/{device}
{
    "app_version": "1.2.1",
    "os_version": "17.0.1"
}

// Mark device as active (heartbeat)
POST /api/customer/devices/{device}/heartbeat

// Deactivate device (logout)
DELETE /api/customer/devices/{device}
```

---

## Database Schema

### device_tracking Table

**Migration**: `database/migrations/tenant/2025_10_08_000038_create_device_tracking_table.php`

```sql
CREATE TABLE device_tracking (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Polymorphic Relationship
    trackable_type VARCHAR(125) NOT NULL,
    trackable_id BIGINT UNSIGNED NOT NULL,

    -- Device Identification
    device_id VARCHAR(125) UNIQUE NOT NULL,
    device_name VARCHAR(125) NULL,
    device_type VARCHAR(125) NOT NULL,

    -- Browser/OS Information
    browser VARCHAR(125) NOT NULL,
    browser_version VARCHAR(125) NULL,
    operating_system VARCHAR(125) NOT NULL,
    os_version VARCHAR(125) NULL,
    platform VARCHAR(125) NOT NULL,

    -- Device Fingerprinting
    screen_resolution JSON NULL,
    hardware_info JSON NULL,
    user_agent VARCHAR(125) NOT NULL,
    fingerprint_data JSON NOT NULL,

    -- Trust Management
    trust_score INT DEFAULT 0,
    is_trusted BOOLEAN DEFAULT 0,
    trusted_at TIMESTAMP NULL,
    trust_expires_at TIMESTAMP NULL,

    -- Activity Tracking
    first_seen_at TIMESTAMP NOT NULL,
    last_seen_at TIMESTAMP NOT NULL,
    login_count INT DEFAULT 0,
    failed_login_attempts INT DEFAULT 0,
    last_failed_login_at TIMESTAMP NULL,

    -- Location & IP History
    location_history JSON NULL,  -- Last 50 locations
    ip_history JSON NULL,        -- Last 100 IPs

    -- Blocking
    is_blocked BOOLEAN DEFAULT 0,
    blocked_reason VARCHAR(125) NULL,
    blocked_at TIMESTAMP NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    -- Indexes
    INDEX device_tracking_trackable_idx (trackable_type, trackable_id),
    INDEX device_tracking_device_trust_idx (device_id, is_trusted),
    INDEX device_tracking_trust_score_idx (trust_score, is_trusted),
    INDEX device_tracking_activity_idx (last_seen_at, is_trusted),
    INDEX device_tracking_blocked_idx (is_blocked, blocked_at)
);
```

### trusted_devices Table

**Migration**: `database/migrations/tenant/2025_10_08_000041_create_trusted_devices_table.php`

```sql
CREATE TABLE trusted_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Polymorphic Relationship
    authenticatable_type VARCHAR(125) NOT NULL,
    authenticatable_id BIGINT UNSIGNED NOT NULL,

    -- Device Identification
    device_id VARCHAR(125) NOT NULL,
    device_name VARCHAR(125) NOT NULL,
    device_type VARCHAR(125) NULL,
    browser VARCHAR(125) NULL,
    platform VARCHAR(125) NULL,

    -- Network Information
    ip_address VARCHAR(125) NOT NULL,
    user_agent VARCHAR(125) NOT NULL,

    -- Trust Management
    last_used_at DATETIME NULL,
    trusted_at TIMESTAMP NOT NULL,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT 1,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX (authenticatable_type, authenticatable_id)
);
```

### customer_devices Table

**Migration**: `database/migrations/tenant/[timestamp]_create_customer_devices_table.php`

```sql
CREATE TABLE customer_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    customer_id BIGINT UNSIGNED NOT NULL,

    -- Device Registration
    device_type VARCHAR(255) NOT NULL,  -- ios, android, web
    device_token VARCHAR(255) NOT NULL, -- FCM/APNS token
    device_name VARCHAR(255) NULL,
    device_model VARCHAR(255) NULL,

    -- App Information
    os_version VARCHAR(255) NULL,
    app_version VARCHAR(255) NULL,

    -- Activity
    last_active_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT 1,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    FOREIGN KEY (customer_id) REFERENCES customers(id) ON DELETE CASCADE
);
```

---

## Configuration

### Security Configuration

**File**: `config/security.php`

```php
return [
    'session_security' => [
        'fingerprint_validation' => env('SESSION_FINGERPRINT', true),
        'strict_ip_check' => env('SESSION_STRICT_IP', false),
    ],

    'enhanced_features' => [
        'session_fingerprinting' => env('SECURITY_SESSION_FINGERPRINTING', true),
        'user_agent_validation' => env('SECURITY_USER_AGENT_VALIDATION', true),
    ],

    // Device Trust Configuration
    'device_trust_duration' => env('DEVICE_TRUST_DURATION', 30), // days
    'device_trust_max_devices' => env('DEVICE_TRUST_MAX_DEVICES', 5),

    // Auto-blocking Thresholds
    'device_max_failed_logins' => 5,
    'device_min_trust_score' => 20,
    'device_suspicious_threshold' => 30,
];
```

### Environment Variables

```ini
# Session Security
SESSION_FINGERPRINT=true
SESSION_STRICT_IP=false
SECURITY_SESSION_FINGERPRINTING=true
SECURITY_USER_AGENT_VALIDATION=true

# Device Trust
DEVICE_TRUST_DURATION=30
DEVICE_TRUST_MAX_DEVICES=5
```

---

## Usage Examples

### Example 1: Track User Login Device

```php
use App\Models\DeviceTracking;
use Illuminate\Http\Request;

// In your authentication controller/service
public function trackLoginDevice(User $user, Request $request)
{
    $fingerprintData = [
        'browser' => $request->header('Sec-CH-UA'),
        'platform' => $request->header('Sec-CH-UA-Platform'),
        'screen_resolution' => ['width' => 1920, 'height' => 1080],
        'canvas' => 'canvas_hash_value',
        'webgl' => 'webgl_hash_value',
    ];

    $deviceId = DeviceTracking::generateDeviceId($fingerprintData);

    // Find or create device tracking record
    $device = DeviceTracking::firstOrCreate(
        ['device_id' => $deviceId],
        [
            'trackable_type' => User::class,
            'trackable_id' => $user->id,
            'device_type' => 'desktop',
            'browser' => 'Chrome',
            'browser_version' => '120.0',
            'operating_system' => 'Windows',
            'os_version' => '11',
            'platform' => 'PC',
            'user_agent' => $request->userAgent(),
            'fingerprint_data' => $fingerprintData,
            'first_seen_at' => now(),
            'last_seen_at' => now(),
        ]
    );

    // Record successful login
    $location = [
        'city' => 'Mumbai',
        'country' => 'India',
        'lat' => 19.0760,
        'lng' => 72.8777,
    ];

    $device->recordSuccessfulLogin($request->ip(), $location);

    // Update trust score
    $device->updateTrustScore();

    return $device;
}
```

### Example 2: Handle Failed Login

```php
public function handleFailedLogin(User $user, Request $request, string $reason)
{
    $deviceId = DeviceTracking::generateDeviceId($fingerprintData);

    $device = DeviceTracking::where('device_id', $deviceId)->first();

    if ($device) {
        $device->recordFailedLogin($request->ip(), $reason);

        // Automatically blocks after 5 failed attempts
        if ($device->is_blocked) {
            // Notify security team or user
            event(new DeviceBlockedEvent($device));
        }
    }
}
```

### Example 3: Trusted Device Management (2FA)

```php
use App\Services\TwoFactorAuthService;

public function setupTrustedDevice(User $user, Request $request)
{
    $twoFactorService = app(TwoFactorAuthService::class);

    // Trust the current device for 2FA bypass
    $result = $twoFactorService->trustDevice(
        $user,
        $request,
        'My Laptop' // Optional custom name
    );

    // Returns:
    // [
    //     'device' => TrustedDevice instance,
    //     'was_already_trusted' => false
    // ]

    return response()->json([
        'message' => 'Device trusted successfully',
        'device' => $result['device'],
        'expires_at' => $result['device']->expires_at,
    ]);
}
```

### Example 4: Check Device Trust Status

```php
// During 2FA challenge
public function checkDeviceTrust(User $user, Request $request)
{
    if ($user->isDeviceTrusted($request)) {
        // Skip 2FA for this device
        return redirect()->intended('dashboard');
    }

    // Show 2FA verification form
    return view('auth.two-factor-challenge');
}
```

### Example 5: Revoke Suspicious Device

```php
public function revokeDevice(User $user, int $deviceId)
{
    $device = DeviceTracking::find($deviceId);

    if ($device && $device->trackable_id === $user->id) {
        // Revoke device trust
        $device->revokeTrust('Revoked by user');

        // Optionally block the device
        $device->blockDevice('Suspicious activity reported by user');

        return back()->with('success', 'Device revoked and blocked');
    }

    return back()->with('error', 'Device not found');
}
```

### Example 6: Query Suspicious Devices

```php
// Get all suspicious devices for security audit
$suspiciousDevices = DeviceTracking::suspicious()
    ->with('trackable')
    ->where('is_blocked', false) // Not yet blocked
    ->get();

foreach ($suspiciousDevices as $device) {
    Log::warning('Suspicious device detected', [
        'device_id' => $device->device_id,
        'user_id' => $device->trackable_id,
        'trust_score' => $device->trust_score,
        'failed_attempts' => $device->failed_login_attempts,
    ]);
}
```

### Example 7: Mobile App Device Registration

```php
// API endpoint for mobile app device registration
public function registerDevice(Request $request)
{
    $validated = $request->validate([
        'device_type' => 'required|in:ios,android,web',
        'device_token' => 'required|string',
        'device_name' => 'nullable|string',
        'device_model' => 'nullable|string',
        'os_version' => 'nullable|string',
        'app_version' => 'nullable|string',
    ]);

    $customer = auth()->user(); // Customer authenticated via API

    // Find or create device
    $device = CustomerDevice::updateOrCreate(
        [
            'customer_id' => $customer->id,
            'device_token' => $validated['device_token'],
        ],
        array_merge($validated, [
            'last_active_at' => now(),
            'is_active' => true,
        ])
    );

    return response()->json([
        'success' => true,
        'device' => $device,
    ]);
}
```

### Example 8: Device Activity Summary

```php
// Get comprehensive activity summary for a device
public function getDeviceActivity(DeviceTracking $device)
{
    $summary = $device->getActivitySummary(30); // Last 30 days

    return [
        'device' => [
            'id' => $device->id,
            'name' => $device->display_name,
            'trust_score' => $device->trust_score,
            'is_trusted' => $device->isTrusted(),
            'is_blocked' => $device->is_blocked,
        ],
        'activity' => $summary,
        'security_events' => $device->securityEvents()
            ->where('occurred_at', '>=', now()->subDays(30))
            ->orderBy('occurred_at', 'desc')
            ->get(),
        'location_summary' => [
            'unique_ips' => count($device->ip_history ?? []),
            'unique_locations' => count($device->location_history ?? []),
            'last_ip' => $device->last_ip,
            'last_location' => $device->last_location,
        ],
    ];
}
```

---

## Best Practices

### 1. Device Tracking Strategy

**Always Track on Login**:
```php
// Good: Centralized tracking
public function authenticated(Request $request, $user)
{
    $this->deviceTrackingService->trackLogin($user, $request);
}

// Bad: Manual tracking scattered across controllers
```

**Update Activity Regularly**:
```php
// Use middleware to update last_seen_at
public function handle(Request $request, Closure $next)
{
    if ($user = auth()->user()) {
        $device = $user->getCurrentDevice($request);
        $device?->updateLastSeen();
    }

    return $next($request);
}
```

### 2. Trust Management

**Configurable Trust Durations**:
```php
// Good: Use configuration
$duration = config('security.device_trust_duration', 30);
$device->grantTrust($duration);

// Bad: Hardcoded values
$device->grantTrust(30);
```

**Limit Trusted Devices**:
```php
// Enforce maximum trusted devices per user
$maxDevices = config('security.device_trust_max_devices', 5);
$activeTrusted = $user->getActiveTrustedDevices();

if ($activeTrusted->count() >= $maxDevices) {
    // Revoke oldest device or prompt user to choose
    $activeTrusted->sortBy('trusted_at')->first()->revoke();
}
```

### 3. Security Event Monitoring

**Automated Monitoring**:
```php
// Schedule regular checks for suspicious activity
Schedule::command('devices:check-suspicious')
    ->hourly()
    ->onSuccess(function () {
        $suspicious = DeviceTracking::suspicious()->count();
        if ($suspicious > 10) {
            // Alert security team
        }
    });
```

**Real-Time Alerts**:
```php
// Use events for critical security issues
Event::listen(DeviceBlocked::class, function ($event) {
    Mail::to($event->device->trackable)
        ->send(new DeviceBlockedNotification($event->device));
});
```

### 4. Performance Optimization

**Eager Loading**:
```php
// Good: Load relationships in one query
$devices = DeviceTracking::with(['trackable', 'securityEvents'])
    ->suspicious()
    ->get();

// Bad: N+1 queries
$devices = DeviceTracking::suspicious()->get();
foreach ($devices as $device) {
    $device->trackable; // N+1!
}
```

**Index Usage**:
```php
// Queries are optimized with existing indexes
DeviceTracking::where('is_trusted', true)  // Uses device_trust_idx
    ->where('trust_score', '>', 70)        // Uses trust_score_idx
    ->get();
```

### 5. Data Privacy

**GDPR Compliance**:
```php
// Implement data retention policies
public function cleanupOldDeviceData()
{
    // Remove device data older than 2 years
    DeviceTracking::where('last_seen_at', '<', now()->subYears(2))
        ->delete();

    // Anonymize IP history older than 6 months
    DeviceTracking::where('last_seen_at', '<', now()->subMonths(6))
        ->update(['ip_history' => null, 'location_history' => null]);
}
```

### 6. Error Handling

**Graceful Degradation**:
```php
// Don't block login if device tracking fails
try {
    $this->deviceTrackingService->trackLogin($user, $request);
} catch (\Exception $e) {
    Log::error('Device tracking failed', [
        'error' => $e->getMessage(),
        'user_id' => $user->id,
    ]);
    // Continue with login
}
```

### 7. Testing Strategy

**Feature Tests**:
```php
public function test_device_trust_management()
{
    $user = User::factory()->create();
    $request = Request::create('/', 'GET');

    // Test trust creation
    $device = $user->trustDevice($request);
    $this->assertTrue($device->isValid());

    // Test trust revocation
    $device->revoke();
    $this->assertFalse($device->isValid());
}
```

---

## Security Considerations

### 1. Device Fingerprinting Limitations

**Not 100% Unique**:
- Browser fingerprints can be spoofed
- Multiple devices may generate similar fingerprints
- Privacy tools can randomize fingerprints

**Mitigation**:
- Combine with IP tracking and behavior analysis
- Use trust scores instead of binary trust/untrust
- Implement multi-factor verification for high-risk actions

### 2. Session Hijacking Prevention

**Session Fingerprinting**:
```php
// Middleware validates fingerprint on every request
// Logs out user if fingerprint changes
if (!$this->validateSessionFingerprint($request)) {
    Auth::logout();
    $this->logSecurityEvent('session_fingerprint_mismatch', $request, $user);
    return redirect()->route('login')
        ->with('error', 'Session security validation failed.');
}
```

**IP Validation** (Optional, Configurable):
```php
'strict_ip_check' => env('SESSION_STRICT_IP', false),
```

### 3. Rate Limiting

**Failed Login Attempts**:
```php
// Automatically blocks after 5 failed attempts
if ($device->failed_login_attempts >= 5) {
    $device->blockDevice('Too many failed login attempts');
}
```

**2FA Verification Attempts**:
```php
// Integrated with TWO_FACTOR_AUTHENTICATION.md rate limiting
if ($user->isTwoFactorRateLimited()) {
    throw new \Exception('Too many failed attempts. Please try again later.');
}
```

### 4. Trust Expiration

**Automatic Expiration**:
```php
// Trust expires after configured duration
$device->isTrustExpired(); // Checks trust_expires_at

// Cleanup command
php artisan two-factor:cleanup
// Deactivates expired devices, deletes old attempts
```

### 5. Device Blocking Strategy

**Automatic Blocking Triggers**:
1. **5+ Failed Logins**: Immediate block
2. **Trust Score < 20**: Automatic block
3. **Critical Security Events**: Manual review + potential block

**Unblocking Process**:
```php
// Requires manual review and approval
$device->unblockDevice('Verified with user via phone call');
```

### 6. Data Retention

**Limit History Size**:
```php
// Automatically maintains only:
// - Last 50 locations
// - Last 100 IP addresses
// - 30 days of security events
```

**Cleanup Strategy**:
```php
// Schedule:command('devices:cleanup')->daily()
DeviceTracking::where('last_seen_at', '<', now()->subMonths(6))
    ->whereNull('is_blocked')
    ->delete();
```

### 7. Audit Trail

**Comprehensive Logging**:
```php
// All device actions are logged with security events
$device->securityEvents()->create([
    'event_type' => 'trust_granted',
    'event_severity' => 'medium',
    'description' => 'Device trust granted by user',
    'event_data' => ['reason' => 'user_initiated'],
    'ip_address' => request()->ip(),
    'user_agent' => request()->userAgent(),
    'occurred_at' => now(),
]);
```

---

## Related Documentation

### Core Features
- **[TWO_FACTOR_AUTHENTICATION.md](TWO_FACTOR_AUTHENTICATION.md)** - 2FA integration with trusted devices
- **[AUDIT_LOGGING.md](AUDIT_LOGGING.md)** - Security event logging and compliance
- **[NOTIFICATION_SYSTEM.md](NOTIFICATION_SYSTEM.md)** - Device-based notifications (push tokens)

### Architecture
- **[MULTI_PORTAL_ARCHITECTURE.md](../core/MULTI_PORTAL_ARCHITECTURE.md)** - Portal-specific authentication
- **[MIDDLEWARE_REFERENCE.md](../core/MIDDLEWARE_REFERENCE.md)** - Session fingerprinting middleware
- **[SERVICE_LAYER.md](../core/SERVICE_LAYER.md)** - TwoFactorAuthService device management

### Security
- **[SECURITY_SETTINGS.md](SECURITY_SETTINGS.md)** - Security configuration and policies
- **Configuration**: `config/security.php` - Device tracking settings

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
**System Version**: Multi-Tenancy SaaS v2.0
**Maintained By**: Development Team
