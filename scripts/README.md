# Development Scripts

All development scripts are now managed through Composer for cross-platform compatibility.

## Available Commands

### Testing

**Run Notification Tests**
```bash
composer test:notifications
```
Runs comprehensive notification system tests with coverage reporting.

### Setup & Configuration

**Setup Notification System**
```bash
composer setup:notifications
```
Configures notification system: runs migrations, seeds permissions, clears caches.

### Code Quality

**Quick Fix**
```bash
composer fix
```
Auto-fixes code style with Laravel Pint and clears caches.

**Quick Cache Clear**
```bash
composer fix:quick
```
Optimizes autoloader and clears all application caches.

**Code Analysis**
```bash
composer analyze
```
Runs Pint (dry-run), PHPStan analysis, and security audit.

**Full Analysis**
```bash
composer analyze:full
```
Complete analysis including outdated package check (longer running).

**Simple Check**
```bash
composer check
```
Optimizes autoloader, clears caches, runs security audit.

### Automated Refactoring (Rector)

**Preview Refactoring** (Dry Run)
```bash
composer refactor
```
Shows what code changes Rector would make without applying them.

**Apply Refactoring**
```bash
composer refactor:apply
```
Applies automated code improvements and Laravel best practices.

**Full Upgrade Workflow**
```bash
composer upgrade
```
Complete workflow: clear cache → apply refactoring → fix code style.

**Clear Rector Cache**
```bash
composer refactor:clear
```
Clears Rector's cache (useful after configuration changes).

**List Available Rules**
```bash
composer refactor:sets
```
Lists all available Rector refactoring rules.

### Quality Tools Installation

**Install Quality Tools**
```bash
composer quality:install
```
Installs Laravel Pint, PHPStan, and Larastan for code quality analysis.

---

## Recommended Workflow

### Daily Development
```bash
composer check
```

### Before Each Commit
```bash
composer fix
git add .
git commit -m "Your message"
```

### Weekly Maintenance
```bash
composer refactor        # Check for refactoring opportunities
composer analyze:full    # Full code analysis
```

### Before Major Commits
```bash
composer refactor        # Preview refactoring
composer refactor:apply  # Apply if appropriate
composer fix             # Fix code style
php artisan test         # Run tests
```

### Before Deployment
```bash
composer analyze
composer test:notifications
```

### Monthly Code Improvements
```bash
composer upgrade         # Full refactoring + style fix
composer analyze:full    # Check code quality
php artisan test         # Verify all tests pass
```

---

## Manual Artisan Commands

For direct Laravel command execution:

```bash
# Clear all caches
php artisan optimize:clear

# Run migrations
php artisan migrate

# Run specific seeder
php artisan db:seed --class=UnifiedPermissionsSeeder

# Run tests
php artisan test

# Code style fixing
php artisan pint
# OR
./vendor/bin/pint

# Static analysis
./vendor/bin/phpstan analyse
```

---

## Configuration Files

- `rector.php` - Rector refactoring rules ✨ NEW
- `phpstan.neon` - PHPStan configuration
- `pint.json` - Laravel Pint rules
- `composer.json` - All script definitions

---

## Troubleshooting

### "Command not found" errors
Ensure you're in the project root directory and have run `composer install`.

### "Class not found" errors
```bash
composer dump-autoload -o
```

### Vendor binaries not accessible
```bash
composer install
```

---

## Additional Resources

- **Rector Guide**: `claudedocs/RECTOR_GUIDE.md` - Complete refactoring guide ✨ NEW
- Full project documentation: `claudedocs/PROJECT_DOCUMENTATION.md`
- Test documentation: `claudedocs/TESTING_GUIDE.md`
- Architecture guide: `claudedocs/SYSTEM_ARCHITECTURE.md`
