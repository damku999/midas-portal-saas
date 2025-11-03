# Troubleshooting & Fixes

Common issues, solutions, and critical fixes for Midas Portal multi-tenant system.

---

## Critical Fixes Applied

### 1. Domain Routing Fix (Nov 2, 2025) 🔥
**Issue**: TenantCouldNotBeIdentifiedOnDomainException on central domain
**Solution**: Changed to domain-based route registration
**File**: app/Providers/RouteServiceProvider.php

### 2. Tenant Creation Schema Fix (Nov 3, 2025)
**Issue**: Unknown column 'name' - users table has first_name/last_name
**Solution**: Updated TenantController user creation
**File**: app/Http/Controllers/Central/TenantController.php

### 3. Cache Tagging Fix (Nov 2, 2025)
**Issue**: File cache doesn't support tagging
**Solution**: Changed CACHE_DRIVER=database
**File**: .env

### 4. Double Modal Fix (Nov 3, 2025)
**Issue**: Delete button opening two popups
**Solution**: Skip data-confirm if onclick exists
**File**: resources/views/central/layout.blade.php

---

## Common Issues

### Routing Issues

**404 on Central Domain**:
- Check middleware configuration
- Clear route cache: php artisan route:clear
- Verify central.only middleware exists

**Tenant Routes on Central Domain**:
- Ensure tenant middleware applied
- Check route loading order in RouteServiceProvider

### Tenant Creation

**Database Not Created**:
- Check TenancyServiceProvider events
- Verify MySQL CREATE DATABASE permission
- Manually run: php artisan tenants:migrate --tenants={id}

**Migrations Not Running**:
- Verify migrations in database/migrations/tenant/
- Check for errors in migration files

**Seeders Failing**:
- Check schema matches (status vs is_active, first_name vs name)
- Manually run: php artisan tenants:seed --tenants={id}

### Authentication

**Wrong Login Redirect**:
- Check Authenticate middleware domain detection
- Verify CENTRAL_DOMAINS in config/tenancy.php

**Cross-Guard Login**:
- Check session cookie names are unique
- Verify guards use different providers

### Database

**Cross-Tenant Data**:
- Ensure universal middleware applied
- Never use DB::connection('mysql') in tenant context
- Check tenancy()->tenant is not null

**Outdated Schema**:
- Run: php artisan tenants:migrate --tenants={id}
- Or recreate tenant for fresh schema

### Cache

**Cache Leaking**:
- Use database or redis cache (not file)
- Clear: php artisan cache:clear

**Stale Routes**:
- Clear ALL: php artisan optimize:clear
- Rebuild: php artisan route:cache

---

## Quick Commands

```bash
# Check tenants
php artisan tinker → \App\Models\Central\Tenant::all()

# Check route list
php artisan route:list | grep central

# Check tenant tables
SHOW DATABASES LIKE 'tenant_%';

# Clear everything
php artisan optimize:clear
```

---

**Last Updated**: 2025-11-03
