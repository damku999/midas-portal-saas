# Security Fixes Checklist

Track your progress fixing security vulnerabilities.

## 🚨 CRITICAL - Fix Immediately

- [ ] **#1: Mass Assignment - Report Model** (`app/Models/Report.php:73`)
  - Replace `protected $guarded = []` with explicit `$fillable`
  - Test: Try to mass-assign protected fields

- [ ] **#2: IDOR - Generic Delete** (`app/Http/Controllers/CommonController.php:14-30`)
  - Add model whitelist
  - Add authorization checks
  - Add tenant isolation
  - Test: Try to delete records you don't own

- [ ] **#3: Path Traversal** (`app/Http/Controllers/CommonController.php:58-70`)
  - Add path validation with `realpath()`
  - Whitelist allowed directories
  - Add MIME type checks
  - Test: Try `../../etc/passwd` attack

- [ ] **#4: SQL Injection** (`app/Http/Controllers/LeadWhatsAppController.php:596`)
  - Validate input format
  - Use Laravel JSON where clauses
  - Test: Try SQL injection payloads

- [ ] **#5: Payment Verification Bypass** (`app/Http/Controllers/SubscriptionController.php:175-219`)
  - Add `->where('tenant_id', tenant()->id)` check
  - Verify subscription belongs to tenant
  - Test: Try to verify another tenant's payment

## 🔴 HIGH - Fix This Week

- [ ] **#6: Unrestricted File Upload** (`app/Http/Controllers/LeadDocumentController.php:15-42`)
  - Add MIME type whitelist: `mimes:pdf,doc,docx,jpg,png`
  - Add MIME validation in code (not just extension)
  - Sanitize filenames
  - Store in private disk
  - Test: Try to upload .php, .exe files

- [ ] **#7: File Download Authorization** (`app/Http/Controllers/LeadDocumentController.php:46-61`)
  - Add `auth()->user()->can('view', $lead)` check
  - Add audit logging
  - Test: Try to download another user's files

- [ ] **#8: Hardcoded Bank Details** (`app/Services/PaymentService.php:138-145`)
  - Move to `.env` variables
  - Create `config/payment.php`
  - Update service to use config values

- [ ] **#9: Missing API Rate Limiting** (`routes/api.php:37-80`)
  - Add `middleware('throttle:60,1')` to API routes
  - Use stricter limits for sensitive endpoints
  - Test: Make rapid API requests

- [ ] **#10: Stripe Webhook No Verification** (`app/Http/Controllers/PaymentWebhookController.php:96-148`)
  - Uncomment signature verification code
  - Implement `\Stripe\Webhook::constructEvent()`
  - Test: Send webhook without valid signature

- [ ] **#11: Weak Email Authorization** (`app/Http/Controllers/AppSettingController.php:360-377`)
  - Replace email domain check with role check
  - Use `hasRole('Super Admin')` or `can('delete-app-settings')`
  - Create proper permissions

- [ ] **#12: IDOR Device Deactivation** (`app/Http/Controllers/Api/CustomerDeviceApiController.php:402-439`)
  - Use explicit ownership check in query
  - Don't rely on route model binding
  - Test: Try to deactivate another user's device

- [ ] **#13: Dynamic Model Instantiation** (`app/Http/Controllers/CommonController.php`)
  - Already fixed with #2 (model whitelist)

## 🟡 MEDIUM - Fix This Month

- [ ] **#14: Weak Password Generation** (`app/Models/Customer.php:547-550`)
  - Increase to 12 characters
  - Add special characters
  - Add lowercase letters
  - Ensure one of each type

- [ ] **#15: Search Input Sanitization** (`app/Http/Controllers/CustomerController.php:43-50`)
  - Sanitize search input with regex
  - Use proper query grouping

- [ ] **#16: HTTPS Enforcement**
  - Create `ForceHttps` middleware
  - Register in `Kernel.php`
  - Test: Try HTTP in production

- [ ] **#17: Verbose Error Messages**
  - Set `APP_DEBUG=false` in production
  - Update exception handler
  - Return generic error messages

- [ ] **#18: CORS Validation**
  - Review `config/cors.php`
  - Restrict allowed origins

- [ ] **#19: Session Fixation**
  - Regenerate session on login
  - Regenerate on privilege change

- [ ] **#20: Security Event Logging**
  - Log failed login attempts
  - Log authorization failures
  - Log file access

- [ ] **#21: Content-Security-Policy**
  - Add CSP headers middleware
  - Configure policy in `.env`

- [ ] **#22: Cookie Security**
  - Set `secure`, `httpOnly`, `sameSite` flags
  - Update `config/session.php`

- [ ] **#23: Timing Attack Protection**
  - Use constant-time comparison for passwords
  - Add random delays on failures

- [ ] **#24: Database Audit Logging**
  - Enable query logging for sensitive operations
  - Log all data modifications

## 🟢 LOW - Address in Roadmap

- [ ] **#25-32:** Debug code, validation consistency, security headers, etc.

---

## Testing After Fixes

### Manual Testing:
```bash
# 1. Test IDOR
- Try to access/delete other users' records
- Try to access other tenants' data

# 2. Test File Upload
- Upload .php, .exe, .sh files
- Upload files with double extensions (.jpg.php)
- Upload oversized files

# 3. Test Path Traversal
- Try ../../etc/passwd
- Try absolute paths
- Try URL encoding

# 4. Test Authorization
- Access admin endpoints as regular user
- Try to modify other users' data
- Try payment bypass

# 5. Test API Security
- Make rapid API requests (rate limiting)
- Try SQL injection in search
- Test CSRF on forms
```

### Automated Testing:
```bash
# Security checks
composer require --dev enlightn/security-checker
php artisan security:check

# Static analysis
vendor/bin/phpstan analyse app

# Dependency audit
composer audit

# Run tests
php artisan test
```

### Penetration Testing:
```bash
# OWASP ZAP
zap.sh -quickurl http://your-domain.com -quickprogress

# Nikto
nikto -h http://your-domain.com
```

---

## Sign-off

### Developer:
- [ ] All Critical fixes implemented
- [ ] All High fixes implemented
- [ ] Manual testing completed
- [ ] Automated tests passing
- [ ] Code reviewed by peer

**Name:** _________________
**Date:** _________________

### Security Team:
- [ ] Fixes verified
- [ ] Penetration testing completed
- [ ] Approved for production

**Name:** _________________
**Date:** _________________

---

## Production Deployment Checklist

Before deploying to production:

- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] All Critical vulnerabilities fixed
- [ ] All High vulnerabilities fixed
- [ ] SSL/TLS certificate installed
- [ ] Firewall configured
- [ ] Backup system tested
- [ ] Monitoring enabled
- [ ] Incident response plan documented
- [ ] Security contact information updated

---

**Started:** __________
**Completed:** __________
**Deployed:** __________
