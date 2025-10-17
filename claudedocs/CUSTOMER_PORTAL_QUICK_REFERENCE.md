# Customer Portal Quick Reference Guide

**Quick access guide for common customer portal features and workflows**

---

## Portal Access

- **URL**: `/customer/login`
- **Session Timeout**: 60 minutes
- **Rate Limit**: 10 login attempts per minute

---

## Key Features at a Glance

### Authentication & Security
- ✅ Email/Password login
- ✅ Two-Factor Authentication (TOTP)
- ✅ Trusted devices (30-day trust)
- ✅ Password reset via email
- ✅ Email verification
- ✅ Session timeout protection
- ✅ Comprehensive audit logging

### Dashboard Access
- ✅ Active policies overview
- ✅ Expiring policies alerts (30 days)
- ✅ Recent quotations (last 5)
- ✅ Quick action buttons
- ✅ Family insurance portfolio (family heads)

### Policy Management (Read-Only)
- ✅ View all personal policies
- ✅ View family policies (family heads only)
- ✅ Download policy documents (PDF)
- ✅ Track policy expiry dates
- ✅ See premium amounts
- ✅ Filter: Active vs Expired

### Quotations (Read-Only)
- ✅ View all personal quotes
- ✅ View family quotes (family heads only)
- ✅ Download quotation PDFs
- ✅ Compare company quotes
- ✅ Track quotation status

### Claims (Read-Only)
- ✅ View personal claims
- ✅ View family claims (family heads only)
- ✅ Track claim stages/progress
- ✅ See required documents
- ✅ View settlement details

### Family Management (Family Heads Only)
- ✅ View all family member profiles
- ✅ Change family member passwords
- ✅ Disable family member 2FA
- ✅ Access all family insurance data

---

## User Types & Permissions

| Feature | Family Head | Regular Member |
|---------|-------------|----------------|
| View own data | ✅ | ✅ |
| View family data | ✅ | ❌ |
| Download own documents | ✅ | ✅ |
| Download family documents | ✅ | ❌ |
| Manage family passwords | ✅ | ❌ |
| Enable own 2FA | ✅ | ✅ |
| Edit profiles | ❌ (Admin) | ❌ (Admin) |
| Create policies | ❌ (Admin) | ❌ (Admin) |
| Create claims | ❌ (Admin) | ❌ (Admin) |

---

## Common Workflows

### First Time Login
```
1. Receive email with verification link
2. Click verification link → Email verified
3. Login with credentials
4. Change password (if required)
5. Set up 2FA (recommended)
6. Explore dashboard
```

### View & Download Policy
```
1. Dashboard → "View All Policies"
2. Select "Active" or "Expired" tab
3. Click policy to view details
4. Click "Download" button
5. PDF downloads to device
```

### Enable Two-Factor Authentication
```
1. Profile → "Two-Factor Authentication"
2. Click "Enable 2FA"
3. Scan QR code with authenticator app
4. Enter 6-digit code to confirm
5. Save 8 recovery codes securely
6. 2FA enabled
```

### Reset Forgotten Password
```
1. Login page → "Forgot Password?"
2. Enter email address
3. Check email for reset link
4. Click link → Enter new password
5. Confirm new password
6. Login with new password
```

### Change Family Member Password (Family Head)
```
1. Profile → Family members section
2. Click key icon next to member
3. Enter new password (no old password needed)
4. Confirm new password
5. Password changed
6. Member notified (optional)
```

---

## Security Best Practices

### For Customers
1. ✅ Use strong, unique passwords (8+ characters)
2. ✅ Enable Two-Factor Authentication
3. ✅ Only trust personal devices
4. ✅ Save recovery codes securely
5. ✅ Log out when finished
6. ✅ Don't share credentials
7. ✅ Verify email addresses in communications
8. ✅ Report suspicious activity

### For Admins
1. ✅ Enforce 2FA for all customers
2. ✅ Monitor audit logs regularly
3. ✅ Review trusted devices periodically
4. ✅ Keep session timeout reasonable (60 min)
5. ✅ Disable inactive accounts
6. ✅ Use strong password policies

---

## Rate Limits (Per Minute)

| Operation | Limit |
|-----------|-------|
| Login | 10 |
| Password reset | 5 |
| Email verification | 3 |
| General browsing | 200 |
| Downloads | 10 |
| 2FA operations | 6-120 (varies) |

**Lockout**: 5 failed logins → 15-minute lockout

---

## File Locations

### Controllers
- `app/Http/Controllers/Auth/CustomerAuthController.php` (Main)
- `app/Http/Controllers/TwoFactorAuthController.php` (2FA)

### Middleware
- `app/Http/Middleware/CustomerAuth.php` (Authentication)
- `app/Http/Middleware/CustomerSessionTimeout.php` (Timeout)

### Views
- `resources/views/customer/` (All customer views)
- `resources/views/layouts/customer.blade.php` (Layout)

### Routes
- `routes/customer.php` (All customer routes)

### Models
- `app/Models/Customer.php` (Customer model)
- `app/Models/CustomerAuditLog.php` (Audit logging)

---

## What Customers CAN'T Do

### Editing Restrictions
- ❌ Edit personal profile information
- ❌ Create/edit policies
- ❌ Create/edit quotations
- ❌ Create/edit claims
- ❌ Upload documents
- ❌ Make online payments (not implemented)
- ❌ Access admin portal
- ❌ Manage insurance companies
- ❌ Configure app settings

**All editing must be done by admin**

---

## App Settings Used

| Setting | Purpose | Default |
|---------|---------|---------|
| `customer_session_timeout` | Session timeout duration | 60 minutes |
| Email settings | Password reset, verification | SMTP config |
| WhatsApp settings | Policy documents, notifications | API config |
| Date format | Display format | DD/MM/YYYY (Indian) |

---

## Troubleshooting

### Session Expired
**Issue**: "Your session has expired"
**Solution**: Log in again (60-minute timeout)

### 2FA Code Not Working
**Issue**: Code rejected
**Solution**:
1. Check device time sync
2. Use recovery code
3. Generate new recovery codes

### Cannot View Family Policies
**Issue**: Don't see family member policies
**Solution**: Verify you are designated as family head

### Policy Download Fails
**Issue**: Download button doesn't work
**Solution**:
1. Check if policy document uploaded
2. Contact admin if file missing
3. Clear browser cache

### Account Locked
**Issue**: "Too many login attempts"
**Solution**: Wait 15 minutes or contact admin

### Email Not Verified
**Issue**: Cannot access full features
**Solution**:
1. Click verification link in email
2. Request new verification email
3. Contact admin if issues persist

---

## Contact & Support

### For Customer Issues
- Contact admin directly
- Check email for notifications
- Review WhatsApp messages

### For Technical Issues
- Clear browser cache
- Try different browser
- Check internet connection
- Contact admin for assistance

---

## Quick Statistics

### Database Tables
- `customers` (main customer data)
- `customer_audit_logs` (security logs)
- `customer_trusted_devices` (2FA devices)
- `family_groups` (family management)
- `family_members` (family relationships)

### Total Routes
- **Public**: 5 routes (login, password reset, email verify)
- **Authenticated**: 25+ routes (dashboard, policies, quotations, claims, 2FA)

### Middleware Stack
1. Customer authentication
2. Session timeout check
3. Rate limiting
4. CSRF protection

---

## Feature Availability Matrix

| Feature | Status | Notes |
|---------|--------|-------|
| Login/Logout | ✅ Live | Fully functional |
| Password Reset | ✅ Live | Email-based |
| Email Verification | ✅ Live | Token-based |
| Two-Factor Auth | ✅ Live | TOTP with recovery codes |
| Trusted Devices | ✅ Live | 30-day trust period |
| View Policies | ✅ Live | Read-only |
| Download Policies | ✅ Live | PDF format |
| View Quotations | ✅ Live | Read-only |
| Download Quotations | ✅ Live | PDF generation |
| View Claims | ✅ Live | Read-only |
| Family Management | ✅ Live | Family head only |
| Profile Editing | ❌ Future | Admin-only currently |
| Document Upload | ❌ Future | Not implemented |
| Online Payments | ❌ Future | Not implemented |
| Claim Submission | ❌ Future | Admin-only currently |
| Mobile App | ❌ Future | Web-only currently |

---

## Security Checklist

### Daily Admin Tasks
- [ ] Review audit logs for suspicious activity
- [ ] Check failed login attempts
- [ ] Monitor session timeout patterns
- [ ] Review trusted device list

### Weekly Admin Tasks
- [ ] Analyze security reports
- [ ] Review password reset requests
- [ ] Check email verification status
- [ ] Monitor 2FA adoption rate

### Monthly Admin Tasks
- [ ] Security audit review
- [ ] Update password policies
- [ ] Review family group permissions
- [ ] Clean up old audit logs (optional)

---

## Version Information

**Document Version**: 1.0
**Platform Version**: Laravel 10.x
**PHP Version**: 8.1+
**Database**: MySQL 8.0+
**Created**: 2025-10-06

---

## Related Documentation

- `CUSTOMER_PORTAL_GUIDE.md` - Comprehensive detailed guide
- `APP_SETTINGS_USAGE_AUDIT.md` - App settings integration
- `NOTIFICATION_SETTINGS_IMPLEMENTATION.md` - Notification system
- `SECURITY_IMPLEMENTATION.md` - Security features detail

---

**For detailed information, refer to the comprehensive Customer Portal Guide.**
