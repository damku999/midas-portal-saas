# Local Tenant Access Guide

## How to Access Tenants Locally

### Method 1: Using Hosts File (Recommended)

1. **Edit your hosts file:**
   - Windows: `C:\Windows\System32\drivers\etc\hosts`
   - Open as Administrator

2. **Add tenant domain mapping:**
   ```
   127.0.0.1    test.midastech.in
   127.0.0.1    demo.midastech.in
   127.0.0.1    client1.midastech.in
   ```

3. **Access tenant:**
   - Open browser: `http://test.midastech.in:8000`
   - Login with tenant admin credentials

### Method 2: Modify config/tenancy.php for Local Development

Add your local development domains to `central_domains` array:

```php
'central_domains' => [
    '127.0.0.1',
    'localhost',
    'localhost:8000',
    '127.0.0.1:8000',
],
```

Then access tenant by subdomain parameter (requires additional routing setup).

### Method 3: Direct Database Access

1. **Find tenant database name:**
   - Go to: http://127.0.0.1:8000/midas-admin/tenants
   - Click on tenant to see details
   - Database name format: `tenant_{uuid}`
   - Example: `tenant_5228b291-ac2c-4fbd-9a00-b59ad3e1b2d5`

2. **Check tenant data in phpMyAdmin or MySQL:**
   ```sql
   USE tenant_5228b291-ac2c-4fbd-9a00-b59ad3e1b2d5;
   SELECT * FROM users;
   SELECT * FROM customers;
   ```

## How to Delete a Tenant

### Method 1: Via Central Admin UI (Safe - Includes Confirmation)

1. **Navigate to tenant details:**
   - http://127.0.0.1:8000/midas-admin/tenants
   - Click on the tenant you want to delete

2. **Find "Danger Zone" section** (right sidebar at bottom)

3. **Click "Delete Tenant" button**
   - You'll get a confirmation prompt
   - Confirm deletion

4. **What happens:**
   - Tenant record deleted from central database
   - Tenant's database automatically deleted (via TenancyServiceProvider event)
   - All associated data permanently removed

### Method 2: Via Artisan Command (Quick)

```bash
php artisan tenants:list
php artisan tenants:delete {tenant-id}
```

Example:
```bash
php artisan tenants:delete 5228b291-ac2c-4fbd-9a00-b59ad3e1b2d5
```

### Method 3: Direct Database Deletion (Manual - Not Recommended)

```sql
-- 1. Find tenant ID
SELECT id, data FROM tenants;

-- 2. Delete from central database
DELETE FROM subscriptions WHERE tenant_id = '5228b291-ac2c-4fbd-9a00-b59ad3e1b2d5';
DELETE FROM domains WHERE tenant_id = '5228b291-ac2c-4fbd-9a00-b59ad3e1b2d5';
DELETE FROM tenants WHERE id = '5228b291-ac2c-4fbd-9a00-b59ad3e1b2d5';

-- 3. Manually drop tenant database
DROP DATABASE IF EXISTS tenant_5228b291_ac2c_4fbd_9a00_b59ad3e1b2d5;
```

**Warning:** Manual deletion doesn't trigger event listeners, so database cleanup must be done manually.

## Current Tenant Details

**Tenant Created:**
- ID: `5228b291-ac2c-4fbd-9a00-b59ad3e1b2d5`
- Domain: `test.midastech.in` (or whatever subdomain you used)
- Database: `tenant_5228b291-ac2c-4fbd-9a00-b59ad3e1b2d5`

**Admin Credentials:**
- Check the tenant details page for the admin email
- Password: Whatever you entered during creation (or auto-generated)

## Verification Steps

### 1. Check if tenant database was created:
```bash
php artisan tinker
>>> DB::connection('mysql')->select('SHOW DATABASES LIKE "tenant_%"');
```

### 2. Check tenant's users:
```bash
php artisan tenants:run "DB::table('users')->get()"
```

### 3. List all tenants:
```bash
php artisan tenants:list
```

## Troubleshooting

### Can't access tenant domain:
1. Check hosts file has correct mapping
2. Clear browser cache
3. Restart web server: `php artisan serve --host=0.0.0.0 --port=8000`
4. Check `config/tenancy.php` central_domains

### Database not found:
1. Check if migrations ran: `php artisan tenants:migrate --tenant={id}`
2. Manually create if needed: `php artisan tenants:migrate`

### Login fails:
1. Check user exists in tenant database
2. Reset password: `php artisan tenants:run "User::where('email', 'admin@test.com')->first()->update(['password' => bcrypt('newpassword')])" --tenant={id}`
