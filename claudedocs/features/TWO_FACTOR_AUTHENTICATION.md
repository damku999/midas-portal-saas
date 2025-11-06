# Two-Factor Authentication (2FA) System

**Version**: 1.0
**Last Updated**: 2025-11-06
**Status**: Production

## Table of Contents

1. [Overview](#overview)
2. [System Architecture](#system-architecture)
3. [Database Schema](#database-schema)
4. [Service Layer](#service-layer)
5. [2FA Setup Flow](#2fa-setup-flow)
6. [2FA Login Flow](#2fa-login-flow)
7. [Recovery Codes](#recovery-codes)
8. [Device Trust Management](#device-trust-management)
9. [Rate Limiting & Security](#rate-limiting--security)
10. [API Endpoints](#api-endpoints)
11. [UI Components](#ui-components)
12. [Configuration](#configuration)
13. [Testing](#testing)
14. [Troubleshooting](#troubleshooting)
15. [Related Documentation](#related-documentation)

---

## Overview

Midas Portal implements a comprehensive Time-based One-Time Password (TOTP) two-factor authentication system with device trust management, recovery codes, and rate limiting.

### Key Features

- **TOTP-Based Authentication**: RFC 6238 compliant TOTP implementation
- **Dual Implementation**: Separate 2FA systems for Staff Portal (User) and Customer Portal (Customer)
- **QR Code Generation**: Easy authenticator app setup with QR codes
- **Recovery Codes**: 8 single-use recovery codes for backup access
- **Device Trust**: Optional device trust for 30 days to skip 2FA
- **Rate Limiting**: Prevents brute force attacks (5 failed attempts = 15 min lockout)
- **Attempt Logging**: Comprehensive audit trail of all 2FA attempts
- **Encrypted Storage**: Secrets and recovery codes encrypted at rest
- **Multi-Guard Support**: Works seamlessly with web and customer authentication guards

### Architecture Principles

1. **Separation of Concerns**: Separate services for staff (`TwoFactorAuthService`) and customer (`CustomerTwoFactorAuthService`) portals
2. **Zero Conflict Design**: Independent 2FA tables per guard to prevent interference
3. **Security First**: Encrypted secrets, rate limiting, comprehensive logging
4. **User Experience**: Device trust, recovery codes, clear error messages

---

## System Architecture

### Component Overview

```
┌─────────────────────────────────────────────────────────────────┐
│                    2FA System Architecture                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                   │
│  ┌──────────────┐         ┌──────────────┐                      │
│  │ Staff Portal │         │ Customer     │                      │
│  │ (User/Guard) │         │ Portal       │                      │
│  └──────┬───────┘         └──────┬───────┘                      │
│         │                        │                               │
│         ▼                        ▼                               │
│  ┌──────────────────────────────────────────┐                   │
│  │   TwoFactorAuthController (Shared)       │                   │
│  │   - Guard detection                       │                   │
│  │   - Service routing                       │                   │
│  │   - Request handling                      │                   │
│  └──────┬──────────────────────┬────────────┘                   │
│         │                      │                                 │
│         ▼                      ▼                                 │
│  ┌──────────────┐      ┌──────────────────────┐                │
│  │ TwoFactorAuth│      │ CustomerTwoFactorAuth│                │
│  │ Service      │      │ Service               │                │
│  │              │      │                       │                │
│  │ - Staff 2FA  │      │ - Customer 2FA        │                │
│  │ - User model │      │ - Customer model      │                │
│  └──────┬───────┘      └──────┬───────────────┘                │
│         │                     │                                  │
│         ▼                     ▼                                  │
│  ┌──────────────────────────────────────────┐                   │
│  │            Shared Models                  │                   │
│  │ ┌────────────────┐  ┌────────────────┐  │                   │
│  │ │TwoFactorAuth   │  │TrustedDevice   │  │                   │
│  │ │(polymorphic)   │  │(polymorphic)   │  │                   │
│  │ └────────────────┘  └────────────────┘  │                   │
│  │ ┌────────────────┐  ┌────────────────┐  │                   │
│  │ │TwoFactorAttempt│  │SecuritySetting │  │                   │
│  │ │(polymorphic)   │  │                │  │                   │
│  │ └────────────────┘  └────────────────┘  │                   │
│  └──────────────────────────────────────────┘                   │
│                                                                   │
└─────────────────────────────────────────────────────────────────┘
```

### Guard-Based Service Selection

```php
// TwoFactorAuthController automatically selects correct service
private function getTwoFactorService()
{
    // During 2FA challenge, check session guard first
    if (session()->has('2fa_guard')) {
        $guard = session('2fa_guard', 'web');
        if ($guard === 'customer') {
            return $this->customerTwoFactorAuthService;
        }
    }

    // For authenticated requests, check current guard
    if (Auth::guard('customer')->check()) {
        return $this->customerTwoFactorAuthService;
    }

    return $this->twoFactorAuthService; // Staff portal (default)
}
```

### Polymorphic Relationships

All 2FA tables use polymorphic relationships to support multiple authenticatable types:

```php
// Supports both User (staff) and Customer models
authenticatable_type: 'App\Models\User' or 'App\Models\Customer'
authenticatable_id: ID of the user or customer
```

---

## Database Schema

### two_factor_auth Table

**Purpose**: Stores 2FA configuration for users/customers

**Location**: `database/migrations/tenant/2025_10_08_000039_create_two_factor_auth_table.php`

```sql
CREATE TABLE two_factor_auth (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Polymorphic relationship (User or Customer)
    authenticatable_type VARCHAR(125) NOT NULL,
    authenticatable_id BIGINT UNSIGNED NOT NULL,
    INDEX idx_authenticatable (authenticatable_type, authenticatable_id),

    -- TOTP Configuration
    secret TEXT NULL,                    -- Encrypted Base32 secret for TOTP generation
    recovery_codes TEXT NULL,            -- Encrypted JSON array of 8 recovery codes

    -- Status & Lifecycle
    enabled_at TIMESTAMP NULL,           -- When 2FA setup was initiated
    confirmed_at TIMESTAMP NULL,         -- When 2FA was confirmed with valid code
    is_active BOOLEAN DEFAULT 0,         -- TRUE after confirmation

    -- Backup Options (future use)
    backup_method VARCHAR(125) NULL,     -- SMS, Email (not currently used)
    backup_destination VARCHAR(125) NULL,

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Key Fields**:

- `secret`: Base32-encoded TOTP secret (encrypted with Laravel Crypt)
- `recovery_codes`: Array of 8 recovery codes (each encrypted individually)
- `is_active`: Only TRUE after user confirms with valid TOTP code
- `confirmed_at`: NULL = setup pending, NOT NULL = 2FA active

**States**:

```php
// Not started
is_active = false, secret = null, confirmed_at = null

// Setup pending (QR code shown, awaiting confirmation)
is_active = false, secret = "...", confirmed_at = null

// Fully enabled (active and confirmed)
is_active = true, secret = "...", confirmed_at = "2025-11-06 10:30:00"

// Disabled
is_active = false, secret = null, confirmed_at = null, recovery_codes = null
```

### trusted_devices Table

**Purpose**: Stores trusted device information to skip 2FA for known devices

**Location**: `database/migrations/tenant/2025_10_08_000041_create_trusted_devices_table.php`

```sql
CREATE TABLE trusted_devices (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Polymorphic relationship (User or Customer)
    authenticatable_type VARCHAR(125) NOT NULL,
    authenticatable_id BIGINT UNSIGNED NOT NULL,
    INDEX idx_authenticatable (authenticatable_type, authenticatable_id),

    -- Device Identification
    device_id VARCHAR(125) NOT NULL,      -- SHA-256 hash of (user_agent + IP)
    device_name VARCHAR(125) NOT NULL,    -- "Windows PC", "iPhone", "Chrome on Mac"

    -- Device Information
    device_type VARCHAR(125) NULL,        -- desktop, mobile, tablet
    browser VARCHAR(125) NULL,            -- Chrome, Firefox, Safari, Edge
    platform VARCHAR(125) NULL,           -- Windows, macOS, iOS, Android, Linux
    ip_address VARCHAR(125) NOT NULL,
    user_agent VARCHAR(125) NOT NULL,

    -- Trust Management
    last_used_at DATETIME NULL,           -- Last time device was used for login
    trusted_at TIMESTAMP NOT NULL,        -- When device was first trusted
    expires_at TIMESTAMP NULL,            -- When trust expires (30 days default)
    is_active BOOLEAN DEFAULT 1,          -- Can be revoked by user

    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL
);
```

**Device Fingerprinting**:

```php
$deviceId = hash('sha256', $userAgent . $ipAddress);
```

**Trust Duration**: Default 30 days (configurable via `config('security.device_trust_duration')`)

### two_factor_attempts Table

**Purpose**: Audit log of all 2FA verification attempts for rate limiting and security monitoring

**Location**: `database/migrations/tenant/2025_10_08_000040_create_two_factor_attempts_table.php`

```sql
CREATE TABLE two_factor_attempts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,

    -- Polymorphic relationship
    authenticatable_type VARCHAR(125) NOT NULL,
    authenticatable_id BIGINT UNSIGNED NOT NULL,
    INDEX idx_authenticatable (authenticatable_type, authenticatable_id),

    -- Attempt Details
    code_type VARCHAR(125) NOT NULL,      -- 'totp' or 'recovery'
    ip_address VARCHAR(125) NOT NULL,
    user_agent VARCHAR(125) NOT NULL,

    -- Outcome
    successful BOOLEAN DEFAULT 0,
    failure_reason VARCHAR(255) NULL,     -- "Invalid code", "Rate limited", etc.

    attempted_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_attempted_at (attempted_at)
);
```

**Usage**:
- Rate limiting: 5 failed attempts in 15 minutes = locked out
- Security monitoring: Track suspicious patterns
- Audit compliance: Complete 2FA attempt history

---

## Service Layer

### TwoFactorAuthService (Staff Portal)

**Location**: `app/Services/TwoFactorAuthService.php`

**Purpose**: Handles 2FA operations for staff users (User model, web guard)

**Key Methods**:

#### Setup Operations

```php
/**
 * Start 2FA setup - generates secret and recovery codes
 *
 * @param User $user
 * @return array ['secret', 'qr_code_url', 'recovery_codes', 'qr_code_svg']
 */
public function enableTwoFactor($user): array

/**
 * Confirm 2FA setup with TOTP verification code
 *
 * @param User $user
 * @param string $code - 6-digit TOTP code from authenticator app
 * @param Request $request
 * @return bool
 * @throws \Exception - "Invalid verification code" or "Too many failed attempts"
 */
public function confirmTwoFactor($user, string $code, Request $request): bool

/**
 * Disable 2FA completely (requires password)
 *
 * @param User $user
 * @param string|null $currentPassword
 * @return bool
 */
public function disableTwoFactor($user, ?string $currentPassword = null): bool
```

#### Login Operations

```php
/**
 * Verify 2FA code during login
 *
 * @param User $user
 * @param string $code - 6-digit TOTP or 8-char recovery code
 * @param string $codeType - 'totp' or 'recovery'
 * @param Request $request
 * @return bool
 * @throws \Exception - "Invalid code" or "Too many failed attempts"
 */
public function verifyTwoFactorLogin($user, string $code, string $codeType, Request $request): bool
```

#### Device Trust Operations

```php
/**
 * Trust current device for 30 days
 *
 * @param User $user
 * @param Request $request
 * @param string|null $deviceName - Optional custom name
 * @return array ['device', 'was_already_trusted']
 */
public function trustDevice($user, Request $request, ?string $deviceName = null): array

/**
 * Revoke device trust
 *
 * @param User $user
 * @param int $deviceId
 * @return bool
 */
public function revokeDeviceTrust($user, int $deviceId): bool

/**
 * Check if current device is trusted
 *
 * @param User $user
 * @param Request $request
 * @return bool
 */
public function isDeviceTrusted($user, Request $request): bool
```

#### Recovery Code Operations

```php
/**
 * Generate new recovery codes (invalidates old ones)
 *
 * @param User $user
 * @return array - 8 new recovery codes
 * @throws \Exception - "2FA not enabled"
 */
public function generateNewRecoveryCodes($user): array
```

#### Status Operations

```php
/**
 * Get 2FA status for user
 *
 * @param User $user
 * @return array
 */
public function getTwoFactorStatus($user): array
// Returns:
// [
//     'enabled' => true/false,
//     'pending_confirmation' => true/false,
//     'recovery_codes_count' => 8,
//     'trusted_devices_count' => 2,
//     'recent_attempts' => 0,
//     'is_rate_limited' => false
// ]

/**
 * Get all trusted devices for user
 *
 * @param User $user
 * @return array
 */
public function getTrustedDevices($user): array
```

### CustomerTwoFactorAuthService (Customer Portal)

**Location**: `app/Services/CustomerTwoFactorAuthService.php`

**Purpose**: Handles 2FA operations for customers (Customer model, customer guard)

**Key Differences from Staff Service**:

1. **No Base Service Extension**: Standalone service without transaction helpers
2. **Customer-Specific Methods**: Uses `hasCustomerTwoFactorEnabled()` vs `hasTwoFactorEnabled()`
3. **Different QR Code Size**: 400x400px vs 200x200px for better mobile scanning
4. **Skip Password Check**: `disableTwoFactor($customer, $password, $skipPasswordCheck = false)`
   - Allows family head to disable 2FA for family members without their password
5. **Trust Duration Parameter**: `trustDevice($customer, $request, int $trustDurationDays = 30)`

**Method Signatures** (similar to staff service):

```php
public function enableTwoFactor(Customer $customer): array
public function confirmTwoFactor(Customer $customer, string $code, Request $request): bool
public function disableTwoFactor(Customer $customer, ?string $currentPassword = null, bool $skipPasswordCheck = false): bool
public function verifyCode(Customer $customer, string $code, ?Request $request = null): bool
public function verifyTwoFactorLogin(Customer $customer, string $code, string $codeType, Request $request): bool
public function generateNewRecoveryCodes(Customer $customer): array
public function getStatus(Customer $customer): array
public function getTrustedDevices(Customer $customer): array
public function trustDevice(Customer $customer, Request $request, int $trustDurationDays = 30): array
public function revokeDeviceTrust(Customer $customer, string $deviceId): bool
public function isDeviceTrusted(Customer $customer, Request $request): bool
```

### HasTwoFactorAuth Trait

**Location**: `app/Traits/HasTwoFactorAuth.php`

**Used By**: User model, TenantUser model

**Key Methods**:

```php
// Relationships
public function twoFactorAuth(): MorphOne
public function trustedDevices(): MorphMany
public function twoFactorAttempts(): MorphMany

// Status Checks
public function hasTwoFactorEnabled(): bool
public function hasTwoFactorPending(): bool

// Setup Operations
public function enableTwoFactor(?string $secret = null): TwoFactorAuth
public function confirmTwoFactor(string $code): bool
public function disableTwoFactor(): void

// Verification
public function verifyTwoFactorCode(string $code): bool
public function verifyRecoveryCode(string $code): bool

// Device Trust
public function isDeviceTrusted(Request $request): bool
public function trustDevice(Request $request, ?string $deviceName = null): TrustedDevice
public function getActiveTrustedDevices()
public function revokeDeviceTrust(int $deviceId): bool

// TOTP Implementation
protected function generateTwoFactorSecret(): string
protected function validateTOTP(string $secret, string $code, int $window = 1): bool
protected function generateTOTP(string $secret, int $timeSlice): string
protected function base32Decode(string $secret): string

// QR Code URL
public function getTwoFactorQrCodeUrl(): ?string
// Returns: "otpauth://totp/Midas Tech:User Name (user@example.com)?secret=SECRET&issuer=Midas Tech"

// Attempt Logging & Rate Limiting
public function logTwoFactorAttempt(string $codeType, bool $successful, Request $request, ?string $failureReason = null): void
public function getRecentFailedTwoFactorAttempts(int $minutes = 15): int
public function isTwoFactorRateLimited(): bool
```

### HasCustomerTwoFactorAuth Trait

**Location**: `app/Traits/Customer/HasCustomerTwoFactorAuth.php`

**Used By**: Customer model

**Similar to HasTwoFactorAuth but with customer-specific method names**:

```php
public function customerTwoFactorAuth(): MorphOne
public function customerTrustedDevices(): MorphMany
public function hasCustomerTwoFactorEnabled(): bool
public function hasCustomerTwoFactorPending(): bool
public function enableCustomerTwoFactor(?string $secret = null): CustomerTwoFactorAuth
public function confirmCustomerTwoFactor(string $code): bool
public function disableCustomerTwoFactor(): bool
public function verifyCustomerTwoFactorCode(string $code): bool
public function verifyCustomerRecoveryCode(string $code): bool
public function getCustomerTwoFactorQrCodeUrl(): ?string
public function isCustomerDeviceTrusted(Request $request): bool
public function trustCustomerDevice(Request $request, int $trustDurationDays = 30): TrustedDevice
public function getActiveCustomerTrustedDevices()
public function revokeCustomerDeviceTrust(string $deviceId): bool
public function cleanupExpiredCustomerDevices(): int
```

---

## 2FA Setup Flow

### Staff Portal Setup

**Step 1: User Initiates Setup**

```
User clicks "Enable 2FA" on profile page
    ↓
POST /profile/two-factor/enable
    ↓
TwoFactorAuthController@enable
    ↓
TwoFactorAuthService@enableTwoFactor($user)
```

**Step 2: Generate Secret & Recovery Codes**

```php
// In TwoFactorAuthService
public function enableTwoFactor($user): array
{
    return $this->createInTransaction(function () use ($user): array {
        // 1. Check if already enabled
        if ($user->hasTwoFactorEnabled()) {
            throw new \Exception('2FA already enabled.');
        }

        // 2. Enable 2FA (creates secret and recovery codes)
        $twoFactor = $user->enableTwoFactor();
        // This calls HasTwoFactorAuth@enableTwoFactor():
        //   - Generates Base32 secret (32 chars)
        //   - Creates 8 recovery codes
        //   - Sets enabled_at = now(), is_active = false

        // 3. Generate QR code URL
        $qrCodeUrl = $user->getTwoFactorQrCodeUrl();
        // Returns: "otpauth://totp/Midas Tech:John Doe (john@example.com)?secret=SECRET&issuer=Midas Tech"

        return [
            'secret' => $twoFactor->secret,
            'qr_code_url' => $qrCodeUrl,
            'recovery_codes' => $twoFactor->recovery_codes, // 8 codes
            'qr_code_svg' => $this->generateQrCodeSvg($qrCodeUrl) // 200x200 SVG
        ];
    });
}
```

**Step 3: Display QR Code & Recovery Codes**

```javascript
// Frontend AJAX call
fetch('/profile/two-factor/enable', { method: 'POST' })
    .then(response => response.json())
    .then(data => {
        // Display QR code SVG
        document.getElementById('qr-code').innerHTML = data.data.qr_code_svg;

        // Display recovery codes (IMPORTANT: Save these!)
        displayRecoveryCodes(data.data.recovery_codes);
        // ['A1B2C3D4', 'E5F6G7H8', 'I9J0K1L2', ...]

        // Show confirmation step
        showConfirmationStep();
    });
```

**Step 4: User Scans QR Code**

User scans QR code with authenticator app:
- Google Authenticator
- Authy
- Microsoft Authenticator
- 1Password
- Bitwarden

**Step 5: User Confirms with TOTP Code**

```
User enters 6-digit code from authenticator app
    ↓
POST /profile/two-factor/confirm
    {
        "code": "123456"
    }
    ↓
TwoFactorAuthController@confirm
    ↓
TwoFactorAuthService@confirmTwoFactor($user, $code, $request)
```

**Step 6: Verify & Activate**

```php
// In TwoFactorAuthService
public function confirmTwoFactor($user, string $code, Request $request): bool
{
    return $this->updateInTransaction(static function () use ($user, $code, $request): bool {
        // 1. Rate limiting check
        if ($user->isTwoFactorRateLimited()) {
            $user->logTwoFactorAttempt('totp', false, $request, 'Rate limited');
            throw new \Exception('Too many failed attempts. Please try again later.');
        }

        // 2. Verify the code (TOTP validation with ±1 time window)
        if (!$user->confirmTwoFactor($code)) {
            $user->logTwoFactorAttempt('totp', false, $request, 'Invalid code');
            throw new \Exception('Invalid verification code. Please try again.');
        }
        // This calls HasTwoFactorAuth@confirmTwoFactor():
        //   - Validates TOTP code
        //   - Sets confirmed_at = now(), is_active = true

        // 3. Log successful confirmation
        $user->logTwoFactorAttempt('totp', true, $request);

        return true;
    });
}
```

**Setup Complete!** 2FA is now active for the user.

### Customer Portal Setup

**Identical flow** but using CustomerTwoFactorAuthService and customer-specific routes:

```
POST /customer/two-factor/enable
    ↓
TwoFactorAuthController@enable (detects customer guard)
    ↓
CustomerTwoFactorAuthService@enableTwoFactor($customer)
    ↓
POST /customer/two-factor/confirm
    ↓
CustomerTwoFactorAuthService@confirmTwoFactor($customer, $code, $request)
```

**Key Difference**: QR code is 400x400px for better mobile scanning

---

## 2FA Login Flow

### Standard Login with 2FA

**Step 1: User Enters Credentials**

```
POST /login (staff) or POST /customer/login
    {
        "email": "user@example.com",
        "password": "password",
        "remember": true
    }
    ↓
LoginController@login (staff) or CustomerAuthController@login
```

**Step 2: Check if 2FA is Enabled**

```php
// In LoginController (staff) or CustomerAuthController (customer)
public function login(Request $request)
{
    // 1. Validate credentials
    $credentials = $request->only('email', 'password');

    // 2. Attempt authentication WITHOUT logging in
    $user = User::where('email', $credentials['email'])->first();

    if (!$user || !Hash::check($credentials['password'], $user->password)) {
        return back()->withErrors(['email' => 'Invalid credentials']);
    }

    // 3. Check if user has 2FA enabled
    if ($user->hasTwoFactorEnabled()) {
        // Check if device is trusted
        if ($user->isDeviceTrusted($request)) {
            // Skip 2FA challenge, log in directly
            Auth::guard('web')->login($user, $request->remember);
            return redirect()->intended(route('home'));
        }

        // 4. Store user info in session (NOT logged in yet!)
        session([
            '2fa_user_id' => $user->id,
            '2fa_guard' => 'web',
            '2fa_remember' => $request->remember ?? false
        ]);

        // 5. Redirect to 2FA challenge page
        return redirect()->route('two-factor.challenge');
    }

    // 6. No 2FA, log in normally
    Auth::guard('web')->login($user, $request->remember);
    return redirect()->intended(route('home'));
}
```

**Step 3: Show 2FA Challenge Page**

```
GET /two-factor-challenge
    ↓
TwoFactorAuthController@showVerification
```

```php
public function showVerification(Request $request): View
{
    // 1. Check session for pending 2FA
    $userId = session('2fa_user_id');
    $guard = session('2fa_guard', 'web');

    if (!$userId) {
        // Session expired, redirect to login
        $loginRoute = $guard === 'customer' ? 'customer.login' : 'login';
        return redirect()->route($loginRoute)
            ->with('error', '2FA session expired. Please login again.');
    }

    // 2. Get user from session
    $user = $guard === 'customer'
        ? Customer::find($userId)
        : User::find($userId);

    if (!$user) {
        session()->forget(['2fa_user_id', '2fa_guard', '2fa_remember']);
        return redirect()->route($loginRoute)
            ->with('error', 'User not found. Please login again.');
    }

    // 3. Show appropriate view
    if ($guard === 'customer') {
        return view('customer.auth.two-factor-challenge');
    }

    return view('auth.two-factor-challenge');
}
```

**Step 4: User Enters TOTP Code or Recovery Code**

```html
<!-- auth/two-factor-challenge.blade.php -->
<form method="POST" action="{{ route('two-factor.verify') }}">
    @csrf

    <label>Enter 6-digit code from your authenticator app:</label>
    <input type="text" name="code" maxlength="6" pattern="[0-9]{6}" required>
    <input type="hidden" name="code_type" value="totp">

    <!-- OR -->

    <label>Lost your device? Enter a recovery code:</label>
    <input type="text" name="code" maxlength="8">
    <input type="hidden" name="code_type" value="recovery">

    <!-- Optional: Trust this device -->
    <label>
        <input type="checkbox" name="trust_device" value="1">
        Trust this device for 30 days
    </label>

    <button type="submit">Verify</button>
</form>
```

**Step 5: Verify Code**

```
POST /two-factor-challenge
    {
        "code": "123456",
        "code_type": "totp",
        "trust_device": "1"
    }
    ↓
TwoFactorAuthController@verify
```

```php
public function verify(Request $request): RedirectResponse
{
    try {
        // 1. Get user from session
        $userId = session('2fa_user_id');
        $guard = session('2fa_guard', 'web');

        if (!$userId) {
            throw new \Exception('Session expired. Please login again.');
        }

        $user = $guard === 'customer'
            ? Customer::find($userId)
            : User::find($userId);

        // 2. Verify 2FA code
        $service = $this->getTwoFactorService();
        $service->verifyTwoFactorLogin(
            $user,
            $request->code,
            $request->code_type, // 'totp' or 'recovery'
            $request
        );

        // 3. Clear 2FA session data
        $rememberMe = session('2fa_remember', false);
        session()->forget(['2fa_user_id', '2fa_guard', '2fa_remember']);

        // 4. Complete login with the correct guard
        Auth::guard($guard)->login($user, $rememberMe);

        // 5. Trust device if requested
        if ($request->has('trust_device') && $request->trust_device) {
            $service->trustDevice($user, $request);
        }

        // 6. Redirect to dashboard
        $redirectTo = $guard === 'customer'
            ? route('customer.dashboard')
            : route('home');

        return redirect()->intended($redirectTo);

    } catch (\Exception $e) {
        return back()->withErrors(['code' => $e->getMessage()])->withInput();
    }
}
```

**Step 6: Verification Logic**

```php
// In TwoFactorAuthService@verifyTwoFactorLogin
public function verifyTwoFactorLogin($user, string $code, string $codeType, Request $request): bool
{
    return $this->executeInTransaction(static function () use ($user, $code, $codeType, $request): bool {
        // 1. Rate limiting check (5 failed attempts in 15 minutes)
        if ($user->isTwoFactorRateLimited()) {
            $user->logTwoFactorAttempt($codeType, false, $request, 'Rate limited');
            throw new \Exception('Too many failed attempts. Try again in 15 minutes.');
        }

        // 2. Verify based on code type
        $isValid = match ($codeType) {
            'totp' => $user->verifyTwoFactorCode($code), // TOTP validation
            'recovery' => $user->verifyRecoveryCode($code), // Recovery code check
            default => throw new \Exception('Invalid code type.'),
        };

        // 3. Log the attempt
        $user->logTwoFactorAttempt(
            $codeType,
            $isValid,
            $request,
            $isValid ? null : 'Invalid code'
        );

        if (!$isValid) {
            if ($codeType === 'recovery') {
                throw new \Exception('Invalid recovery code. This code may have been used.');
            }
            throw new \Exception('Invalid verification code.');
        }

        return true;
    });
}
```

### TOTP Validation Details

**TOTP Algorithm** (RFC 6238):

```php
// In HasTwoFactorAuth trait
protected function validateTOTP(string $secret, string $code, int $window = 1): bool
{
    $timeSlice = floor(time() / 30); // 30-second window

    // Check current time slice and adjacent slices for clock drift
    for ($i = -$window; $i <= $window; $i++) {
        $calculatedCode = $this->generateTOTP($secret, $timeSlice + $i);
        if (hash_equals($calculatedCode, $code)) {
            return true;
        }
    }

    return false;
}

protected function generateTOTP(string $secret, int $timeSlice): string
{
    // 1. Convert base32 secret to binary
    $secretBinary = $this->base32Decode($secret);

    // 2. Convert time slice to 8-byte big-endian
    $time = pack('N*', 0) . pack('N*', $timeSlice);

    // 3. Generate HMAC-SHA1
    $hash = hash_hmac('sha1', $time, $secretBinary, true);

    // 4. Dynamic truncation
    $offset = ord($hash[19]) & 0xF;
    $code = (
        ((ord($hash[$offset + 0]) & 0x7F) << 24) |
        ((ord($hash[$offset + 1]) & 0xFF) << 16) |
        ((ord($hash[$offset + 2]) & 0xFF) << 8) |
        (ord($hash[$offset + 3]) & 0xFF)
    ) % 1000000;

    return str_pad($code, 6, '0', STR_PAD_LEFT);
}
```

**Time Window**: ±1 time slice (90 seconds total: 30s before, 30s current, 30s after) to account for clock drift

---

## Recovery Codes

### Generation

```php
// In TwoFactorAuth model
public function generateRecoveryCodes(): array
{
    $codes = [];
    for ($i = 0; $i < 8; $i++) {
        $codes[] = strtoupper(bin2hex(random_bytes(4)));
        // Generates 8-character hex codes: "A1B2C3D4"
    }

    $this->recovery_codes = $codes; // Encrypted before storage
    $this->save();

    return $codes;
}
```

**Format**: 8 codes, each 8 characters (uppercase hex)

**Example**:
```
A1B2C3D4
E5F6G7H8
I9J0K1L2
M3N4O5P6
Q7R8S9T0
U1V2W3X4
Y5Z6A7B8
C9D0E1F2
```

### Usage

Recovery codes are **single-use**:

```php
// In TwoFactorAuth model
public function useRecoveryCode(string $code): bool
{
    $codes = $this->recovery_codes;
    $upperCode = strtoupper($code);

    if (!$codes || !in_array($upperCode, $codes)) {
        return false;
    }

    // Remove the used code
    $codes = array_filter($codes, fn($c) => $c !== $upperCode);
    $this->recovery_codes = array_values($codes); // Re-index array
    $this->save();

    return true;
}
```

**After use**: Code is permanently removed from database

### Regeneration

Users can generate new recovery codes at any time:

```php
// POST /profile/two-factor/recovery-codes
// Requires current password for security

$newCodes = $service->generateNewRecoveryCodes($user);
// Returns 8 new codes, invalidates all old codes
```

**Warning**: Always save new recovery codes in a safe place!

---

## Device Trust Management

### Trust Mechanism

When a user logs in with 2FA, they can optionally "trust this device" to skip 2FA for 30 days.

### Device Fingerprinting

```php
// In TrustedDevice model
public static function generateDeviceId(string $userAgent, string $ipAddress, ?string $additionalData = null): string
{
    $fingerprint = $userAgent . $ipAddress . ($additionalData ?? '');
    return hash('sha256', $fingerprint);
}
```

**Components**:
- User-Agent string (browser, OS, device info)
- IP address
- Optional additional data (not currently used)

**Result**: SHA-256 hash = unique device identifier

### Device Information Parsing

```php
// In TrustedDevice@parseUserAgent
protected static function parseUserAgent(string $userAgent): array
{
    // Detect device type: desktop, mobile, tablet
    // Detect browser: Chrome, Firefox, Safari, Edge
    // Detect platform: Windows, macOS, iOS, Android, Linux

    return [
        'device_type' => 'desktop',     // or 'mobile', 'tablet'
        'browser' => 'Chrome',          // or 'Firefox', 'Safari', 'Edge'
        'platform' => 'Windows',        // or 'macOS', 'iOS', 'Android', 'Linux'
        'device_name' => 'Windows PC'   // Human-readable name
    ];
}
```

**Example Device Names**:
- "Windows PC"
- "iPhone"
- "iPad"
- "Android Device"
- "Mac"

### Creating Trusted Device

```php
// In TrustedDevice@createFromRequest
public static function createFromRequest(
    $authenticatable,
    Request $request,
    ?string $deviceName = null
): self {
    $userAgent = $request->userAgent() ?? '';
    $ipAddress = $request->ip();
    $deviceId = self::generateDeviceId($userAgent, $ipAddress);

    // Check if device already exists
    $existingDevice = self::where('authenticatable_type', $authenticatable::class)
        ->where('authenticatable_id', $authenticatable->id)
        ->where('device_id', $deviceId)
        ->first();

    if ($existingDevice) {
        // Reactivate or update existing device
        $existingDevice->update([
            'last_used_at' => now(),
            'trusted_at' => now(),
            'expires_at' => now()->addDays(30),
            'is_active' => true
        ]);
        return $existingDevice;
    }

    // Parse device info
    $deviceInfo = self::parseUserAgent($userAgent);

    // Create new device
    return self::create([
        'authenticatable_type' => $authenticatable::class,
        'authenticatable_id' => $authenticatable->id,
        'device_id' => $deviceId,
        'device_name' => $deviceName ?? $deviceInfo['device_name'],
        'device_type' => $deviceInfo['device_type'],
        'browser' => $deviceInfo['browser'],
        'platform' => $deviceInfo['platform'],
        'ip_address' => $ipAddress,
        'user_agent' => $userAgent,
        'last_used_at' => now(),
        'trusted_at' => now(),
        'expires_at' => now()->addDays(30),
        'is_active' => true
    ]);
}
```

### Checking Device Trust

```php
// In HasTwoFactorAuth trait
public function isDeviceTrusted(Request $request): bool
{
    $deviceId = TrustedDevice::generateDeviceId(
        $request->userAgent() ?? '',
        $request->ip()
    );

    return $this->trustedDevices()
        ->where('device_id', $deviceId)
        ->valid() // Active and not expired
        ->exists();
}

// TrustedDevice@scopeValid
protected function scopeValid($query)
{
    return $query->where('is_active', true)
        ->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });
}
```

### Revoking Device Trust

```php
// Manual revocation by user
POST /profile/two-factor/trusted-devices/{deviceId}
    ↓
TwoFactorAuthController@revokeDevice
    ↓
TwoFactorAuthService@revokeDeviceTrust($user, $deviceId)

// In TwoFactorAuthService
public function revokeDeviceTrust($user, int $deviceId): bool
{
    return $this->updateInTransaction(static function () use ($user, $deviceId) {
        $success = $user->revokeDeviceTrust($deviceId);
        // Sets is_active = false

        if ($success) {
            Log::info('Device trust revoked', [
                'user_type' => $user::class,
                'user_id' => $user->id,
                'device_id' => $deviceId,
            ]);
        }

        return $success;
    });
}
```

### Device List UI

```php
// GET /profile/two-factor/status
$status = $twoFactorAuthService->getTwoFactorStatus($user);
$trustedDevices = $twoFactorAuthService->getTrustedDevices($user);

// Returns:
[
    [
        'id' => 1,
        'device_name' => 'Windows PC',
        'display_name' => 'Windows PC (Chrome) - Windows',
        'device_type' => 'desktop',
        'browser' => 'Chrome',
        'platform' => 'Windows',
        'ip_address' => '192.168.1.100',
        'last_used_at' => '2025-11-06 14:30:00',
        'trusted_at' => '2025-10-07 10:00:00',
        'expires_at' => '2025-11-06 10:00:00'
    ],
    [
        'id' => 2,
        'device_name' => 'iPhone',
        'display_name' => 'iPhone (Safari) - iOS',
        'device_type' => 'mobile',
        'browser' => 'Safari',
        'platform' => 'iOS',
        'ip_address' => '192.168.1.101',
        'last_used_at' => '2025-11-05 08:15:00',
        'trusted_at' => '2025-10-20 12:00:00',
        'expires_at' => '2025-11-19 12:00:00'
    }
]
```

---

## Rate Limiting & Security

### Rate Limiting Rules

```php
// In HasTwoFactorAuth trait
public function getRecentFailedTwoFactorAttempts(int $minutes = 15): int
{
    return $this->twoFactorAttempts()
        ->where('attempted_at', '>=', now()->subMinutes($minutes))
        ->where('successful', false)
        ->count();
}

public function isTwoFactorRateLimited(): bool
{
    return $this->getRecentFailedTwoFactorAttempts() >= 5;
}
```

**Limit**: 5 failed attempts in 15 minutes

**Lockout Duration**: 15 minutes (automatic reset after 15 minutes with no failed attempts)

### Attempt Logging

Every 2FA verification attempt is logged:

```php
// In HasTwoFactorAuth trait
public function logTwoFactorAttempt(
    string $codeType,
    bool $successful,
    Request $request,
    ?string $failureReason = null
): void {
    $this->twoFactorAttempts()->create([
        'code_type' => $codeType,         // 'totp' or 'recovery'
        'ip_address' => $request->ip(),
        'user_agent' => $request->userAgent() ?? '',
        'successful' => $successful,
        'failure_reason' => $failureReason, // "Invalid code", "Rate limited", etc.
        'attempted_at' => now()
    ]);
}
```

**Logged Events**:
- Successful TOTP verification
- Failed TOTP verification
- Successful recovery code usage
- Failed recovery code usage
- Rate limit blocks
- Setup confirmation attempts

### Security Features

**1. Encrypted Storage**

```php
// In TwoFactorAuth model
protected function secret(): Attribute
{
    return Attribute::make(
        get: fn ($value) => $value ? Crypt::decryptString($value) : null,
        set: fn ($value) => $value ? Crypt::encryptString($value) : null
    );
}

protected function recoveryCodes(): Attribute
{
    return Attribute::make(
        get: static function ($value): ?array {
            $codes = json_decode($value, true);
            return array_map(fn ($code) => Crypt::decryptString($code), $codes);
        },
        set: static function ($value) {
            $encryptedCodes = array_map(fn ($code) => Crypt::encryptString($code), $value);
            return json_encode($encryptedCodes);
        }
    );
}
```

- Secrets encrypted with `Laravel Crypt` (AES-256-CBC)
- Recovery codes individually encrypted
- Keys stored in `config/app.php` → `APP_KEY`

**2. Hidden Fields**

```php
// In TwoFactorAuth model
protected $hidden = [
    'secret',
    'recovery_codes',
];
```

Prevents accidental exposure in JSON responses

**3. Hash-Safe Comparisons**

```php
// In HasTwoFactorAuth trait
if (hash_equals($calculatedCode, $code)) {
    return true;
}
```

Prevents timing attacks

**4. Cleanup Job**

```php
// In TwoFactorAuthService
public function cleanupExpiredData(): int
{
    return $this->executeInTransaction(static function (): int {
        // Deactivate expired devices
        TrustedDevice::where('expires_at', '<', now())
            ->update(['is_active' => false]);

        // Delete old 2FA attempts (keep last 30 days)
        TwoFactorAttempt::where('attempted_at', '<', now()->subDays(30))
            ->delete();

        return $expiredDevices + $oldAttempts;
    });
}
```

**Recommended Schedule**: Daily via cron

```php
// In app/Console/Kernel.php
$schedule->call(function () {
    app(TwoFactorAuthService::class)->cleanupExpiredData();
})->daily();
```

---

## API Endpoints

### Staff Portal Routes

**Location**: `routes/web.php:577-590`

**Base**: `/profile/two-factor`

**Middleware**: `auth` (web guard)

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/` | `index` | Show 2FA settings page |
| GET | `/status` | `status` | Get 2FA status (AJAX) |
| POST | `/enable` | `enable` | Start 2FA setup |
| POST | `/confirm` | `confirm` | Confirm 2FA setup with code |
| POST | `/disable` | `disable` | Disable 2FA |
| POST | `/recovery-codes` | `generateRecoveryCodes` | Generate new recovery codes |
| POST | `/trust-device` | `trustDevice` | Trust current device |
| DELETE | `/devices/{device}` | `revokeDevice` | Revoke device trust |

**2FA Challenge Routes** (no auth middleware):

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/two-factor-challenge` | `showVerification` | Show 2FA challenge page |
| POST | `/two-factor-challenge` | `verify` | Verify 2FA code |

### Customer Portal Routes

**Location**: `routes/customer.php:80-149`

**Base**: `/customer/two-factor`

**Middleware**: `auth:customer` (customer guard)

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/` | `index` | Show 2FA settings page (customer view) |
| GET | `/status` | `status` | Get 2FA status (AJAX) |
| POST | `/enable` | `enable` | Start 2FA setup |
| POST | `/confirm` | `confirm` | Confirm 2FA setup with code |
| POST | `/disable` | `disable` | Disable 2FA |
| POST | `/recovery-codes` | `generateRecoveryCodes` | Generate new recovery codes |
| POST | `/trust-device` | `trustDevice` | Trust current device |
| DELETE | `/trusted-devices/{deviceId}` | `revokeDevice` | Revoke device trust |

**2FA Challenge Routes** (no auth middleware):

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| GET | `/customer/two-factor-challenge` | `showVerification` | Show 2FA challenge page (customer view) |
| POST | `/customer/two-factor-challenge` | `verify` | Verify 2FA code |

**Family Management** (customer portal only):

| Method | Endpoint | Controller Method | Description |
|--------|----------|-------------------|-------------|
| POST | `/family-member/{member}/disable-2fa` | `CustomerAuthController@disableFamilyMember2FA` | Family head can disable 2FA for family member |

### API Request/Response Examples

#### Enable 2FA

**Request**:
```http
POST /profile/two-factor/enable HTTP/1.1
Content-Type: application/json
X-CSRF-TOKEN: <token>
```

**Response**:
```json
{
    "success": true,
    "message": "Two-factor authentication setup started. Please scan the QR code with your authenticator app.",
    "data": {
        "qr_code_svg": "<svg>...</svg>",
        "recovery_codes": [
            "A1B2C3D4",
            "E5F6G7H8",
            "I9J0K1L2",
            "M3N4O5P6",
            "Q7R8S9T0",
            "U1V2W3X4",
            "Y5Z6A7B8",
            "C9D0E1F2"
        ],
        "setup_url": "otpauth://totp/Midas Tech:John Doe (john@example.com)?secret=ABCDEFGHIJKLMNOPQRSTUVWXYZ234567&issuer=Midas Tech"
    }
}
```

#### Confirm 2FA

**Request**:
```http
POST /profile/two-factor/confirm HTTP/1.1
Content-Type: application/json
X-CSRF-TOKEN: <token>

{
    "code": "123456"
}
```

**Response**:
```json
{
    "success": true,
    "message": "Two-factor authentication has been successfully enabled for your account."
}
```

#### Get 2FA Status

**Request**:
```http
GET /profile/two-factor/status HTTP/1.1
```

**Response**:
```json
{
    "success": true,
    "data": {
        "status": {
            "enabled": true,
            "pending_confirmation": false,
            "recovery_codes_count": 6,
            "trusted_devices_count": 2,
            "recent_attempts": 0,
            "is_rate_limited": false
        },
        "trusted_devices": [
            {
                "id": 1,
                "device_name": "Windows PC",
                "display_name": "Windows PC (Chrome) - Windows",
                "device_type": "desktop",
                "browser": "Chrome",
                "platform": "Windows",
                "ip_address": "192.168.1.100",
                "last_used_at": "2025-11-06 14:30:00",
                "trusted_at": "2025-10-07 10:00:00",
                "expires_at": "2025-11-06 10:00:00"
            }
        ],
        "current_device_trusted": true
    }
}
```

#### Verify 2FA During Login

**Request**:
```http
POST /two-factor-challenge HTTP/1.1
Content-Type: application/x-www-form-urlencoded
X-CSRF-TOKEN: <token>

code=123456&code_type=totp&trust_device=1
```

**Response**: Redirect to dashboard

**Error Response**:
```json
{
    "errors": {
        "code": "Invalid verification code."
    }
}
```

---

## UI Components

### Staff Portal Views

**Location**: `resources/views/profile/two-factor.blade.php`

**Features**:
- Enable/disable 2FA toggle
- QR code display during setup
- Recovery codes display (with print option)
- Trusted devices list with revoke buttons
- Current device trust status

### Customer Portal Views

**Location**: `resources/views/customer/two-factor.blade.php`

**Features** (same as staff portal):
- Enable/disable 2FA toggle
- QR code display (400x400px for better mobile scanning)
- Recovery codes display
- Trusted devices list
- Current device trust status

### 2FA Challenge Views

**Staff Portal**: `resources/views/auth/two-factor-challenge.blade.php`

**Customer Portal**: `resources/views/customer/auth/two-factor-challenge.blade.php`

**Features**:
- TOTP code input (6 digits)
- Recovery code input option
- "Trust this device" checkbox
- "Lost your device?" help text
- Session timeout handling

---

## Configuration

### Environment Variables

```env
# Application Key (used for encryption)
APP_KEY=base64:...

# Device Trust Duration (days)
DEVICE_TRUST_DURATION=30
```

### Config Files

**config/security.php** (if exists):

```php
return [
    'device_trust_duration' => env('DEVICE_TRUST_DURATION', 30),
];
```

### TOTP Settings

**Issuer Name**: `Midas Tech`

**Time Step**: 30 seconds

**Digits**: 6

**Algorithm**: SHA-1 (HMAC)

**Time Window**: ±1 step (90 seconds total)

### Recovery Code Settings

**Count**: 8 codes

**Length**: 8 characters

**Format**: Uppercase hex (A-F, 0-9)

**Single Use**: Yes

---

## Testing

### Manual Testing Steps

#### Setup Test

1. Log in to staff portal
2. Navigate to Profile → Two-Factor Authentication
3. Click "Enable 2FA"
4. Scan QR code with Google Authenticator
5. Save recovery codes
6. Enter 6-digit code from app
7. Click "Confirm"
8. Verify status shows "Enabled"

#### Login Test

1. Log out
2. Log in with email and password
3. Verify redirect to 2FA challenge page
4. Enter 6-digit code from authenticator app
5. Check "Trust this device"
6. Click "Verify"
7. Verify successful login and redirect to dashboard

#### Recovery Code Test

1. Log out
2. Log in with email and password
3. On 2FA challenge page, select "Use recovery code"
4. Enter one of your saved recovery codes
5. Click "Verify"
6. Verify successful login
7. Check 2FA status: recovery codes count should decrease by 1

#### Device Trust Test

1. After trusting device in previous test
2. Log out
3. Log in with email and password
4. Verify NO 2FA challenge (direct login)
5. Navigate to 2FA settings
6. Verify trusted device appears in list
7. Revoke device trust
8. Log out and log in again
9. Verify 2FA challenge appears again

### Automated Tests

**Unit Tests**: `tests/Unit/TwoFactorAuthTest.php`

```php
public function test_can_enable_two_factor()
public function test_can_confirm_two_factor_with_valid_code()
public function test_cannot_confirm_two_factor_with_invalid_code()
public function test_can_disable_two_factor()
public function test_recovery_codes_are_generated()
public function test_recovery_code_can_be_used_once()
public function test_rate_limiting_blocks_after_5_failed_attempts()
public function test_device_trust_skips_2fa_challenge()
public function test_qr_code_url_is_generated()
```

**Feature Tests**: `tests/Feature/TwoFactorAuthenticationTest.php`

```php
public function test_2fa_setup_flow()
public function test_2fa_login_flow()
public function test_2fa_challenge_with_trusted_device()
public function test_customer_2fa_is_independent_from_staff_2fa()
```

---

## Troubleshooting

### "Invalid verification code" Error

**Causes**:
1. **Clock drift**: Device time not synced
2. **Wrong secret**: QR code scanned incorrectly
3. **App issue**: Authenticator app not working

**Solutions**:
1. Check device time is set to "Automatic" (NTP)
2. Re-scan QR code (disable and re-enable 2FA)
3. Try different authenticator app
4. Use recovery code as backup

### "Too many failed attempts" Error

**Cause**: 5 failed verification attempts in 15 minutes

**Solution**: Wait 15 minutes, then try again

**Override** (for testing only):

```php
// In TwoFactorAttempt model, delete recent failed attempts
TwoFactorAttempt::where('authenticatable_id', $userId)
    ->where('successful', false)
    ->where('attempted_at', '>=', now()->subMinutes(15))
    ->delete();
```

### "2FA session expired" Error

**Cause**: Session timeout between login and 2FA challenge

**Solution**: Log in again from the start

**Session Duration**: Controlled by `config/session.php` → `lifetime` (default: 120 minutes)

### Recovery Codes Not Working

**Causes**:
1. **Already used**: Recovery codes are single-use
2. **Wrong code**: Typo or incorrect code
3. **Case sensitive**: Must be uppercase

**Solutions**:
1. Try another recovery code
2. Double-check code from saved list
3. Ensure uppercase (codes are stored uppercase)
4. Contact admin to disable 2FA manually

### QR Code Not Scanning

**Causes**:
1. **Low resolution**: Mobile device screen too small
2. **Browser zoom**: Page zoomed in/out
3. **Poor lighting**: Camera can't read QR code

**Solutions**:
1. Use larger QR code (customer portal: 400x400px)
2. Reset browser zoom to 100%
3. Improve lighting
4. Manually enter secret key (click "Can't scan? Enter manually")

### Device Not Trusted After Checking Box

**Causes**:
1. **IP address changed**: VPN or dynamic IP
2. **Browser changed**: Different browser = different device
3. **Cookies cleared**: Device fingerprint lost

**Solutions**:
1. Use stable IP address
2. Use same browser
3. Don't clear cookies
4. Re-trust device after login

---

## Related Documentation

- **[DEVICE_TRACKING.md](DEVICE_TRACKING.md)** - Comprehensive device tracking and fingerprinting
- **[AUDIT_LOGGING.md](AUDIT_LOGGING.md)** - Audit trail and compliance logging
- **[MIDDLEWARE_REFERENCE.md](../core/MIDDLEWARE_REFERENCE.md)** - Middleware execution order and `VerifyTwoFactorSession` middleware
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - Complete database schema reference
- **[SERVICE_LAYER.md](../core/SERVICE_LAYER.md)** - All service classes including `TwoFactorAuthService`
- **[API_REFERENCE.md](../API_REFERENCE.md)** - Complete API endpoint documentation
- **[MULTI_PORTAL_ARCHITECTURE.md](../core/MULTI_PORTAL_ARCHITECTURE.md)** - Multi-guard authentication system

---

**Document End** | Version 1.0 | 2025-11-06
