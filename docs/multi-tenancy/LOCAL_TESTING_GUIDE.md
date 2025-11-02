# Local Testing Guide - Multi-Tenancy

**Project**: Midas Portal Multi-Tenant SaaS
**Purpose**: Complete local testing before production deployment
**Date**: 2025-11-02

---

## Prerequisites Checklist

- [ ] XAMPP installed and running
- [ ] PHP 8.2+ installed
- [ ] Composer installed
- [ ] MySQL running
- [ ] Local domain configured (midastech.in.local or similar)

---

## Step 1: Environment Setup

### 1.1 Check Current .env Configuration
Your `.env` should have:

```env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://midastech.in.local:8000

# Central Database
DB_CONNECTION=central
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=central
DB_USERNAME=root
DB_PASSWORD=

# For local testing
TENANCY_DATABASE_PREFIX=tenant_
CENTRAL_DOMAINS=midastech.in.local,localhost,127.0.0.1
```

### 1.2 Update .env if needed
If your `.env` needs updating, run:
```bash
php artisan config:clear
php artisan cache:clear
```

---

## Step 2: Database Setup

### 2.1 Create Central Database
```bash
# If central database doesn't exist
mysql -u root
```

```sql
CREATE DATABASE IF NOT EXISTS central CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
SHOW DATABASES LIKE 'central';
EXIT;
```

### 2.2 Run Central Migrations
```bash
php artisan migrate --database=central --path=database/migrations/central
```

Expected output:
```
Migration table created successfully.
Migrating: 2025_11_02_000001_create_plans_table
Migrated:  2025_11_02_000001_create_plans_table
Migrating: 2025_11_02_000002_create_subscriptions_table
Migrated:  2025_11_02_000002_create_subscriptions_table
Migrating: 2025_11_02_000003_create_tenant_users_table
Migrated:  2025_11_02_000003_create_tenant_users_table
Migrating: 2025_11_02_000004_create_audit_logs_table
Migrated:  2025_11_02_000004_create_audit_logs_table
```

### 2.3 Seed Central Data
```bash
php artisan db:seed --class=CentralAdminSeeder
```

Expected output:
```
Seeding: CentralAdminSeeder
Seeded:  CentralAdminSeeder
```

### 2.4 Verify Central Data
```bash
mysql -u root central
```

```sql
-- Check super admin created
SELECT id, name, email, is_super_admin FROM tenant_users;

-- Check plans created
SELECT id, name, slug, price FROM plans;

-- Should see:
-- 1 super admin (admin@midastech.in)
-- 3 plans (Starter, Professional, Enterprise)
EXIT;
```

---

## Step 3: Start Local Server

### 3.1 Start PHP Development Server
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

Server should start at: http://127.0.0.1:8000

### 3.2 Configure Local Hosts File (for subdomain testing)

**Windows**: Edit `C:\Windows\System32\drivers\etc\hosts` as Administrator
**Mac/Linux**: Edit `/etc/hosts` with sudo

Add these lines:
```
127.0.0.1 midastech.in.local
127.0.0.1 tenant1.midastech.in.local
127.0.0.1 tenant2.midastech.in.local
```

---

## Step 4: Test Central Admin Panel

### 4.1 Access Central Admin Login
Open browser: http://127.0.0.1:8000/admin/login

Or with hosts file: http://midastech.in.local:8000/admin/login

### 4.2 Login as Super Admin
```
Email: admin@midastech.in
Password: password
```

**✅ SUCCESS**: You should see the central admin dashboard with:
- Total tenants: 0
- Active tenants: 0
- Trial tenants: 0
- MRR: ₹0

### 4.3 Explore Dashboard
- Click "Tenants" in sidebar
- Should see empty tenant list
- "Create New Tenant" button should be visible

---

## Step 5: Create First Test Tenant

### 5.1 Create Tenant
1. Click "Create New Tenant" button
2. Fill in the form:

```
Company Information:
- Company Name: Test Company 1
- Subdomain: tenant1
- Email: test@tenant1.com
- Phone: 9876543210

Subscription:
- Select Plan: Professional
- Trial Enabled: Yes
- Trial Days: 14

Administrator:
- First Name: Admin
- Last Name: User
- Email: admin@tenant1.com
- Password: (leave empty for auto-generate OR set: password123)
- Send Welcome Email: No (for testing)
```

3. Click "Create Tenant"

### 5.2 Expected Results

**✅ SUCCESS Indicators**:
- Redirect to tenant details page
- Green success message: "Tenant created successfully!"
- New database created: `tenant_xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx`
- Tenant domain: `tenant1.midastech.in.local`

**Check Database**:
```bash
mysql -u root
```

```sql
SHOW DATABASES LIKE 'tenant_%';
-- Should see new tenant database

USE tenant_xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx;
SHOW TABLES;
-- Should see all tenant tables

-- Check admin user created
SELECT id, name, email FROM users;

-- Check default data seeded
SELECT COUNT(*) FROM lead_statuses;  -- Should be 7
SELECT COUNT(*) FROM lead_sources;   -- Should be 9
SELECT COUNT(*) FROM notification_types; -- Should be 8
EXIT;
```

---

## Step 6: Test Tenant Access

### 6.1 Access Tenant Subdomain
Open browser: http://tenant1.midastech.in.local:8000

Or if hosts file not configured: http://127.0.0.1:8000
(The system will detect tenant from subdomain in hosts file setup)

### 6.2 Login to Tenant Admin
```
Email: admin@tenant1.com
Password: password123 (or check tenant data in central admin)
```

**✅ SUCCESS**: You should see the tenant's admin dashboard

### 6.3 Verify Tenant Data
Navigate through tenant admin:
- **Leads** → Lead Statuses: Should see 7 statuses
- **Leads** → Lead Sources: Should see 9 sources
- **Settings** → Notification Types: Should see 8 types
- **Customers**: Empty (start fresh)
- **Users**: Should see admin user

---

## Step 7: Test Data Isolation

### 7.1 Create Data in Tenant 1
1. Login to tenant1 admin
2. Create a test customer:
   - Name: John Doe
   - Email: john@test.com
   - Phone: 9876543210
3. Create a test user:
   - Name: Test User
   - Email: testuser@tenant1.com

### 7.2 Create Second Tenant
1. Logout from tenant1
2. Login to central admin (http://127.0.0.1:8000/admin/login)
3. Create another tenant:
   ```
   Company Name: Test Company 2
   Subdomain: tenant2
   Email: test@tenant2.com
   Admin Email: admin@tenant2.com
   ```

### 7.3 Verify Isolation
1. Login to tenant2 (http://tenant2.midastech.in.local:8000)
2. Check Customers list → Should be EMPTY
3. Check Users list → Should only see tenant2 admin

**✅ SUCCESS**: Data from tenant1 is NOT visible in tenant2 = Perfect isolation!

### 7.4 Database Verification
```bash
mysql -u root
```

```sql
-- Check tenant1 database
USE tenant_<tenant1_uuid>;
SELECT COUNT(*) FROM customers;  -- Should be 1

-- Check tenant2 database
USE tenant_<tenant2_uuid>;
SELECT COUNT(*) FROM customers;  -- Should be 0

-- ISOLATED! ✅
EXIT;
```

---

## Step 8: Test Subscription & Billing

### 8.1 Access Subscription Page
1. Login to tenant1 admin
2. Navigate to: http://tenant1.midastech.in.local:8000/subscription

**✅ SUCCESS**: You should see:
- Current Plan: Professional Plan
- Status: Trial (ends in 14 days)
- Next Billing Date
- Usage metrics (Users, Customers, Storage)

### 8.2 Check Usage Tracking
Create some data in tenant1:
- Add 2-3 more users
- Add 5 customers
- Add some policies

Refresh subscription page:
- Users count should update
- Customers count should update
- Storage usage should show database size

### 8.3 Test Plan Limits (Optional)
1. Login to central admin
2. Edit tenant1 subscription
3. Change to "Starter" plan (max 5 users, 100 customers)
4. Try creating 6th user in tenant1
5. **Expected**: Should show error "You have reached the maximum number of users"

### 8.4 View Plans Page
1. In tenant1 admin: /subscription/plans
2. Should see all 3 plans with comparison
3. "Upgrade" buttons should be visible for higher plans

---

## Step 9: Test Central Admin Features

### 9.1 Dashboard Metrics
Login to central admin and verify:
- Total Tenants: 2
- Active Tenants: 2
- Trial Tenants: 2
- MRR: ₹11,998 (₹5,999 × 2 tenants on Professional plan)

### 9.2 Tenant Management
Test these actions:

**View Tenant**:
- Click on tenant1 from list
- Should show company details, subscription info, activity logs

**Edit Tenant**:
- Change company name
- Update phone number
- Save and verify changes

**Suspend Tenant**:
- Click "Suspend" button
- Provide reason: "Testing suspension"
- Confirm suspension

**Test Suspension**:
- Try accessing tenant1 subdomain
- Should see "Account Suspended" error page
- **✅ Perfect!**

**Activate Tenant**:
- Go back to central admin
- Click "Activate" button
- Tenant should be accessible again

---

## Step 10: Test Error Handling

### 10.1 Invalid Subdomain
Try accessing: http://nonexistent.midastech.in.local:8000

**Expected**: "Tenant Not Found" error page

### 10.2 Suspended Tenant
1. Suspend a tenant from central admin
2. Try accessing that tenant's subdomain
**Expected**: "Account Suspended" error page

### 10.3 Trial Expired
1. In central admin, edit subscription
2. Set trial_ends_at to yesterday
3. Try accessing tenant subdomain
**Expected**: "Trial Expired" message with upgrade prompt

---

## Step 11: Test Customer Portal (Bonus)

### 11.1 Create Customer in Tenant
1. Login to tenant1 admin
2. Create a customer with email and phone
3. System should send onboarding message

### 11.2 Access Customer Portal
Navigate to: http://tenant1.midastech.in.local:8000/customer/login

Try logging in with customer credentials

**✅ SUCCESS**: Customer portal is also tenant-aware!

---

## Step 12: Performance Testing

### 12.1 Check Query Performance
In tenant admin, open browser dev tools → Network tab

Navigate through pages and check:
- Page load times should be < 500ms
- Database queries should be fast
- No N+1 query issues

### 12.2 Check Cache
```bash
php artisan tinker
```

```php
// Check if usage is cached
$tenant = \App\Models\Central\Tenant::first();
$service = app(\App\Services\UsageTrackingService::class);

// First call (should query DB)
$usage1 = $service->getTenantUsage($tenant);

// Second call (should use cache)
$usage2 = $service->getTenantUsage($tenant);

// Should be identical and second call faster
exit;
```

---

## Troubleshooting Common Issues

### Issue 1: "Tenant Not Found" on localhost
**Solution**: Use 127.0.0.1:8000 instead of localhost:8000

### Issue 2: Central database not found
**Solution**:
```bash
php create-central-db.php
php artisan migrate --database=central --path=database/migrations/central
```

### Issue 3: Subdomain not working
**Solution**:
- Check hosts file has entries
- Use 127.0.0.1 instead of localhost
- Restart browser after hosts file change

### Issue 4: Permission denied creating tenant database
**Solution**:
```bash
mysql -u root
GRANT CREATE ON *.* TO 'root'@'localhost';
FLUSH PRIVILEGES;
```

### Issue 5: Session not persisting
**Solution**:
```bash
php artisan cache:clear
php artisan config:clear
php artisan session:table  # If using database sessions
php artisan migrate
```

---

## Testing Checklist

### Basic Functionality
- [ ] Central admin login works
- [ ] Central dashboard shows correct metrics
- [ ] Can create new tenant
- [ ] Tenant database created automatically
- [ ] Tenant gets default data (statuses, sources, etc.)
- [ ] Can access tenant subdomain
- [ ] Tenant admin login works

### Data Isolation
- [ ] Data in tenant1 not visible in tenant2
- [ ] Users can't cross-login between tenants
- [ ] Separate databases confirmed
- [ ] File uploads (if tested) are isolated

### Subscription & Billing
- [ ] Subscription page displays correctly
- [ ] Usage metrics show real numbers
- [ ] Can view all plans
- [ ] Plan limits are enforced
- [ ] Trial countdown works
- [ ] MRR calculated correctly in central admin

### Central Admin Features
- [ ] Can view tenant details
- [ ] Can edit tenant information
- [ ] Can suspend/activate tenants
- [ ] Audit logs are recorded
- [ ] Search/filter tenants works

### Error Handling
- [ ] Invalid subdomain shows error page
- [ ] Suspended tenant shows error page
- [ ] Expired trial shows upgrade prompt
- [ ] Form validation works

---

## Next Steps After Local Testing

### If Everything Passes ✅:
1. Create a backup of local database
2. Document any issues found
3. Proceed with production deployment
4. Follow DEPLOYMENT_GUIDE.md

### If Issues Found ❌:
1. Document the issue
2. Check error logs: storage/logs/laravel.log
3. Fix and retest
4. Commit fixes
5. Repeat testing

---

## Quick Test Script

Run this complete test automatically:

```bash
# Create test script
cat > test-multitenancy.sh << 'EOF'
#!/bin/bash

echo "🧪 Starting Multi-Tenancy Local Tests..."

# 1. Check database
echo "✓ Checking central database..."
mysql -u root -e "USE central; SELECT COUNT(*) FROM plans;" || exit 1

# 2. Check server
echo "✓ Starting server..."
php artisan serve --host=0.0.0.0 --port=8000 &
SERVER_PID=$!
sleep 3

# 3. Test central admin
echo "✓ Testing central admin endpoint..."
curl -s http://127.0.0.1:8000/admin/login | grep -q "Central Admin" && echo "  ✅ Central admin accessible"

# 4. Stop server
kill $SERVER_PID

echo "🎉 Basic tests passed! Continue with manual testing..."
EOF

chmod +x test-multitenancy.sh
./test-multitenancy.sh
```

---

## Support

If you encounter issues during testing:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check web server logs
3. Check MySQL error log
4. Review error messages carefully
5. Refer to troubleshooting section above

---

**Happy Testing! 🧪**

*Remember*: Thorough local testing now = Smooth production deployment later!
