# Multi-Tenancy Rollback Guide

**Purpose**: Instructions for rolling back each phase if issues occur
**Branch**: `feature/multi-tenancy`

⚠️ **IMPORTANT**: Always backup your database before attempting any rollback!

---

## Emergency Rollback (All Phases)

If you need to completely roll back all multi-tenancy changes:

```bash
# 1. Checkout main branch
git checkout main

# 2. Restore database from backup
mysql -u root -p u430606517_parth2 < backup_pre_migration_YYYYMMDD_HHMMSS.sql

# 3. Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# 4. Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx
```

**Estimated Time**: 15-30 minutes

---

## Phase 1: Package Installation Rollback

### If Issues During Installation

**Rollback Steps**:
```bash
# 1. Remove tenancy package
composer remove stancl/tenancy

# 2. Revert configuration files
git checkout config/tenancy.php
git checkout config/database.php

# 3. Revert .env changes
git checkout .env

# 4. Clear composer cache
composer clear-cache
composer dump-autoload

# 5. Reset to before phase 1
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: ❌ None (no data changes yet)

**Estimated Time**: 10 minutes

---

## Phase 2: Central Management Rollback

### If Issues After Central System Created

**Rollback Steps**:
```bash
# 1. Drop central database if created
mysql -u root -p -e "DROP DATABASE IF EXISTS central;"

# 2. Remove central migrations
rm -rf database/migrations/central/

# 3. Remove central models
rm -rf app/Models/Central/

# 4. Remove central controllers
rm -rf app/Http/Controllers/Central/

# 5. Remove central routes
rm routes/central.php

# 6. Remove central views
rm -rf resources/views/central/

# 7. Revert RouteServiceProvider
git checkout app/Providers/RouteServiceProvider.php

# 8. Reset git commits
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: ⚠️ Low (only central metadata, no operational data)

**Estimated Time**: 15 minutes

---

## Phase 3: Database Refactoring Rollback

### If Issues After Model Updates

**Rollback Steps**:
```bash
# 1. Revert all model changes
git checkout app/Models/

# 2. Move migrations back to original location
mv database/migrations/tenant/* database/migrations/
rm -rf database/migrations/tenant/

# 3. Revert filesystems config
git checkout config/filesystems.php

# 4. Clear config cache
php artisan config:clear

# 5. Reset git commits
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: ❌ None (no data changes, only code structure)

**Estimated Time**: 20 minutes

---

## Phase 4: Subdomain Routing Rollback

### If Issues After Subdomain Setup

**Rollback Steps**:
```bash
# 1. Revert routes/web.php
git checkout routes/web.php

# 2. Revert tenancy config
git checkout config/tenancy.php

# 3. Remove TenancyServiceProvider
rm app/Providers/TenancyServiceProvider.php
# Remove from config/app.php providers array

# 4. Remove middleware
rm app/Http/Middleware/HandleInvalidTenant.php

# 5. Remove error views
rm resources/views/errors/tenant-*.blade.php

# 6. Remove hosts file entries
# Edit /etc/hosts or C:\Windows\System32\drivers\etc\hosts
# Remove all *.midasportal.local entries

# 7. Reset .env
git checkout .env

# 8. Clear all caches
php artisan route:clear
php artisan config:clear
php artisan view:clear

# 9. Reset git commits
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: ❌ None

**Estimated Time**: 25 minutes

---

## Phase 5: Authentication Updates Rollback

### If Issues After Auth Updates

**Rollback Steps**:
```bash
# 1. Revert auth controllers
git checkout app/Http/Controllers/Auth/

# 2. Revert login controller
git checkout app/Http/Controllers/Auth/LoginController.php

# 3. Remove central auth
rm app/Http/Controllers/Central/AuthController.php
rm app/Http/Middleware/CentralAuth.php
rm resources/views/central/auth/

# 4. Remove impersonation controller
rm app/Http/Controllers/Central/ImpersonationController.php

# 5. Revert config/auth.php
git checkout config/auth.php

# 6. Revert Spatie permissions config
git checkout config/permission.php

# 7. Clear auth caches
php artisan auth:clear-resets
php artisan cache:clear

# 8. Reset git commits
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: ⚠️ Low (session data may be lost)

**Estimated Time**: 20 minutes

---

## Phase 6: Data Migration Rollback

⚠️ **CRITICAL PHASE** - High risk of data loss!

### If Issues During/After Migration

**Rollback Steps**:
```bash
# 1. STOP ALL APPLICATION TRAFFIC IMMEDIATELY
# Put site in maintenance mode
php artisan down

# 2. Restore original database from backup
mysql -u root -p u430606517_parth2 < backup_pre_migration_YYYYMMDD_HHMMSS.sql

# 3. Verify backup restored correctly
mysql -u root -p u430606517_parth2 -e "SELECT COUNT(*) FROM users;"
mysql -u root -p u430606517_parth2 -e "SELECT COUNT(*) FROM customers;"
mysql -u root -p u430606517_parth2 -e "SELECT COUNT(*) FROM leads;"

# 4. Drop tenant databases if created
mysql -u root -p -e "DROP DATABASE IF EXISTS tenant_1;"
mysql -u root -p -e "DROP DATABASE IF EXISTS tenant_2;"
# Repeat for all created tenant databases

# 5. Drop central database
mysql -u root -p -e "DROP DATABASE IF EXISTS central;"

# 6. Remove migration command
rm app/Console/Commands/MigrateToTenancy.php

# 7. Remove tenant seeders
rm -rf database/seeders/Tenant/

# 8. Clear all caches
php artisan cache:clear
php artisan config:clear

# 9. Test application
php artisan serve
# Manually verify key functionality

# 10. Bring site back online
php artisan up

# 11. Reset git commits
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: 🔴 **HIGH** if backup not available!

**Estimated Time**: 30-60 minutes

**Prevention**:
- Always verify backup before starting Phase 6
- Test migration on database copy first
- Keep multiple backup copies

---

## Phase 7: Billing System Rollback

### If Issues After Billing Setup

**Rollback Steps**:
```bash
# 1. Remove plan seeder
rm database/seeders/Central/PlanSeeder.php

# 2. Remove usage tracking service
rm app/Services/UsageTrackingService.php

# 3. Remove limit middleware
rm app/Http/Middleware/EnforcePlanLimits.php

# 4. Remove payment service
rm app/Services/PaymentGatewayService.php

# 5. Remove billing controllers
rm app/Http/Controllers/Tenant/BillingController.php
rm app/Http/Controllers/Central/SubscriptionController.php
rm app/Http/Controllers/Webhooks/StripeWebhookController.php

# 6. Remove billing views
rm -rf resources/views/tenant/billing/

# 7. Revert routes with limit middleware
git checkout routes/web.php

# 8. Remove payment packages
composer remove stripe/stripe-php
composer remove razorpay/razorpay

# 9. Clean plans/subscriptions from central DB
mysql -u root -p central -e "TRUNCATE TABLE subscriptions;"
mysql -u root -p central -e "TRUNCATE TABLE plans;"

# 10. Reset git commits
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: ⚠️ Medium (billing data will be lost)

**Estimated Time**: 25 minutes

---

## Phase 8: Testing Rollback

### If Tests Reveal Major Issues

**Rollback Steps**:
```bash
# 1. Simply remove test files (no risk)
rm -rf tests/Unit/Tenancy/
rm -rf tests/Feature/Tenancy/

# 2. Fix any issues found
# Address issues one by one based on test results

# 3. Re-run tests after fixes
php artisan test
```

**Data Loss Risk**: ❌ None

**Estimated Time**: Variable (depends on issues found)

---

## Phase 9: Deployment Config Rollback

### If Deployment Issues

**Rollback Steps**:
```bash
# 1. Remove deployment files
rm -rf deployment/

# 2. Revert server config changes
# Manually restore original Nginx/Apache config
sudo cp /etc/nginx/sites-available/midas-portal.conf.backup /etc/nginx/sites-available/midas-portal.conf
sudo systemctl reload nginx

# 3. Remove SSL certificates if added
sudo certbot delete --cert-name midasportal.com

# 4. Revert MySQL config changes
sudo cp /etc/mysql/mysql.conf.d/mysqld.cnf.backup /etc/mysql/mysql.conf.d/mysqld.cnf
sudo systemctl restart mysql

# 5. Reset git commits
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: ❌ None

**Estimated Time**: 30 minutes

---

## Phase 10: Documentation Rollback

### If Documentation Issues

**Rollback Steps**:
```bash
# Simply remove docs (no risk)
rm -rf docs/multi-tenancy/ARCHITECTURE.md
rm -rf docs/multi-tenancy/ADMIN_GUIDE.md
rm -rf docs/multi-tenancy/DEPLOYMENT.md

# Revert README
git checkout README.md

# Reset git commits
git reset --hard HEAD~[number_of_commits]
```

**Data Loss Risk**: ❌ None

**Estimated Time**: 5 minutes

---

## Partial Rollback Strategies

### Rollback to Specific Phase

If you want to rollback to a specific working phase:

```bash
# 1. Find the commit where phase was completed
git log --oneline --graph

# 2. Reset to that commit
git reset --hard <commit-hash>

# 3. Follow rollback steps for all phases after that point
# (in reverse order)
```

### Rollback Specific Files Only

```bash
# Rollback specific file
git checkout <commit-hash> -- path/to/file

# Rollback specific directory
git checkout <commit-hash> -- path/to/directory/
```

---

## Post-Rollback Checklist

After any rollback, verify:

- [ ] Application loads without errors
- [ ] Users can login
- [ ] Database queries work
- [ ] File uploads work
- [ ] All routes accessible
- [ ] No fatal errors in logs
- [ ] Services running (Nginx, PHP-FPM, MySQL)
- [ ] All caches cleared
- [ ] Composer dependencies correct
- [ ] .env file correct

```bash
# Quick verification commands
php artisan config:cache
php artisan route:list
php artisan migrate:status
tail -f storage/logs/laravel.log
```

---

## Prevention Best Practices

### Before Each Phase

1. **Create a backup**:
   ```bash
   mysqldump -u root -p u430606517_parth2 > backup_phase_X_$(date +%Y%m%d_%H%M%S).sql
   ```

2. **Create a git tag**:
   ```bash
   git tag -a phase-X-start -m "Starting Phase X"
   git push origin phase-X-start
   ```

3. **Document current state**:
   - Note all working URLs
   - Document current user counts
   - Record database sizes

### During Each Phase

1. **Commit frequently**:
   ```bash
   git add .
   git commit -m "Task X.Y: Clear description"
   ```

2. **Test after each task**:
   ```bash
   php artisan test
   php artisan serve
   # Manual testing
   ```

3. **Keep notes in PROGRESS_TRACKER.md**

---

## Emergency Contacts

**Technical Issues**:
- Development Team: [contact info]
- Database Admin: [contact info]
- Server Admin: [contact info]

**Backup Locations**:
- Local: `/path/to/backups/`
- Remote: `s3://bucket/backups/`
- Offsite: `[location]`

---

## Recovery Time Objectives (RTO)

| Scenario | Target RTO | Actual Backup Location |
|----------|-----------|------------------------|
| Phase 1-2 rollback | 15 minutes | Git only |
| Phase 3-5 rollback | 30 minutes | Git + configs |
| Phase 6 rollback | 1-2 hours | Database backup required |
| Phase 7-10 rollback | 15-30 minutes | Git only |
| Complete rollback | 2-4 hours | Full database backup |

---

**Last Updated**: 2025-11-02
**Document Version**: 1.0
