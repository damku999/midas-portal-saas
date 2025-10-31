# SMTP Email Configuration Analysis

## Issue Summary
Email sending fails with SSL/TLS configuration errors when using encrypted SMTP credentials from app_settings table.

## Root Cause Analysis

### 1. Configuration System (Working Correctly ✅)
- **Encryption/Decryption**: AppSetting model properly encrypts/decrypts credentials using Laravel's Crypt facade
- **Runtime Binding**: DynamicConfigServiceProvider successfully loads mail settings from database at boot time
- **Credentials**: Username `d10d175f9a5b32` and password `59a1245bfbc9ef` decrypt correctly

### 2. SMTP Connection Issue (Found 🔍)
**Error**: `stream_socket_client(): SSL operation failed with code 1. OpenSSL Error messages: error:0A00010B:SSL routines::wrong version number`

**Problem**: Laravel's SMTP transport incorrectly applies SSL wrapper (`ssl://`) to port 2525 when encryption is set to `tls`.

**Correct Behavior**:
- Port 2525 with TLS = STARTTLS (no SSL wrapper, upgrade after connection)
- Port 465 with SSL = SSL wrapper required
- Port 587 with TLS = STARTTLS

### 3. Missing Event Class (Fixed ✅)
Created `App\Events\Communication\EmailQueued` event class used by NotifyAdminOfRegistration listener at app/Listeners/Customer/NotifyAdminOfRegistration.php:25

## Database Configuration

```sql
SELECT * FROM app_settings WHERE category = 'mail';
```

| Key | Value | Encrypted |
|-----|-------|-----------|
| mail_smtp_host | sandbox.smtp.mailtrap.io | No |
| mail_smtp_port | 2525 | No |
| mail_smtp_encryption | tls | No |
| mail_smtp_username | (encrypted) | Yes ✅ |
| mail_smtp_password | (encrypted) | Yes ✅ |

## Solutions

### Option 1: Change Encryption Setting (Recommended ⭐)
Update the database to use `null` encryption for port 2525:

```sql
UPDATE app_settings
SET value = NULL
WHERE `key` = 'mail_smtp_encryption';
```

Then STARTTLS will be negotiated correctly without SSL wrapper.

### Option 2: Use Port 587 with TLS
```sql
UPDATE app_settings SET value = '587' WHERE `key` = 'mail_smtp_port';
```

### Option 3: Use Port 465 with SSL
```sql
UPDATE app_settings
SET value = '465'
WHERE `key` = 'mail_smtp_port';

UPDATE app_settings
SET value = 'ssl'
WHERE `key` = 'mail_smtp_encryption';
```

## Test Results

### Failed Configuration
```php
// Config showing the issue
'mail.mailers.smtp.host' => 'sandbox.smtp.mailtrap.io'
'mail.mailers.smtp.port' => 2525
'mail.mailers.smtp.encryption' => 'tls'  // ❌ Creates ssl:// wrapper
```

### Working Configuration
```php
// Manual test with correct STARTTLS
$transport = new EsmtpTransport('sandbox.smtp.mailtrap.io', 2525, false);
$transport->setUsername('d10d175f9a5b32');
$transport->setPassword('59a1245bfbc9ef');
$transport->start(); // ✅ SUCCESS
```

## Files Modified

### Created
- `app/Events/Communication/EmailQueued.php` - Email event with proper constructor signature

### Analysis Performed
- `app/Services/AppSettingService.php` - Encryption system working correctly
- `app/Providers/DynamicConfigServiceProvider.php` - Runtime binding working correctly
- `app/Models/AppSetting.php` - Accessor/mutator for encryption working correctly
- `app/Services/CustomerService.php:505` - Email sending logic identified

## Recommendations

1. **Immediate Fix**: Update `mail_smtp_encryption` to `null` in database
2. **Clear Config Cache**: Run `php artisan config:clear` after database update
3. **Test Email**: Use test script or CustomerService to verify email sending
4. **Update .env.example**: Document the correct port/encryption combinations

## Port/Encryption Matrix

| Port | Encryption | Protocol | Status |
|------|-----------|----------|--------|
| 25 | null | Plain SMTP | ⚠️ Insecure |
| 465 | ssl | SMTPS | ✅ Secure |
| 587 | tls | STARTTLS | ✅ Secure |
| 2525 | null | STARTTLS | ✅ Secure (Mailtrap) |
| 2525 | tls | Wrong | ❌ SSL wrapper error |

## Resolution ✅

**Applied Fix**: Option 1 (Recommended approach)
- Updated `mail_smtp_encryption` to `NULL` in database
- Port 2525 now uses STARTTLS correctly without SSL wrapper
- Config cache cleared successfully

**Test Results**:
1. ✅ SMTP connection successful
2. ✅ Test email sent successfully through Mail facade
3. ✅ Configuration loaded correctly from database
4. ✅ Encryption/decryption working properly

**Final Configuration**:
```
Host: sandbox.smtp.mailtrap.io
Port: 2525
Encryption: null (uses STARTTLS)
Username: (encrypted) ✅
Password: (encrypted) ✅
```

## Next Steps

1. ✅ ~~Apply database fix~~ - COMPLETED
2. ✅ ~~Clear Laravel config cache~~ - COMPLETED
3. ✅ ~~Test email sending~~ - COMPLETED
4. Test customer creation with welcome email
5. Monitor logs for any remaining issues
6. Update .env.example with correct port/encryption combinations
