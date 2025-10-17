# Rector Laravel - Automated Code Refactoring Guide

**Version**: 1.0
**Last Updated**: 2025-10-10
**Rector Version**: 2.2.2
**Rector Laravel**: 2.0.7

---

## Table of Contents

1. [What is Rector?](#what-is-rector)
2. [Why Use Rector?](#why-use-rector)
3. [Quick Start](#quick-start)
4. [Available Commands](#available-commands)
5. [Configuration](#configuration)
6. [Refactoring Sets](#refactoring-sets)
7. [Common Use Cases](#common-use-cases)
8. [Best Practices](#best-practices)
9. [Before & After Examples](#before--after-examples)
10. [Troubleshooting](#troubleshooting)

---

## What is Rector?

Rector is an **automated refactoring tool** for PHP that:
- Upgrades your code to newer PHP versions
- Applies Laravel best practices
- Modernizes code patterns
- Removes dead code
- Improves type safety
- Enforces coding standards

**Official Site**: https://github.com/rectorphp/rector
**Laravel Rules**: https://github.com/driftingly/rector-laravel

---

## Why Use Rector?

### Automated Improvements
- **PHP 8.1 Features**: Constructor property promotion, named arguments, enums
- **Laravel Best Practices**: Query builder over magic methods, facade imports
- **Code Quality**: Remove dead code, add type declarations, strict types
- **Consistency**: Apply consistent patterns across entire codebase

### Time Savings
- Refactor hundreds of files in seconds
- Automatic Laravel version upgrades
- Consistent code style enforcement
- Reduce manual code review time

### Safety
- **Dry-run mode**: Preview changes before applying
- **Granular control**: Apply specific rules or sets
- **Reversible**: Version control allows easy rollback

---

## Quick Start

### 1. Preview Changes (Dry Run)
```bash
composer refactor
```
Shows what would change without modifying files.

### 2. Apply Changes
```bash
composer refactor:apply
```
Applies all refactoring changes to your codebase.

### 3. Full Upgrade Workflow
```bash
composer upgrade
```
Clears cache → Applies refactoring → Fixes code style

---

## Available Commands

### Core Commands

**Preview Refactoring** (Dry Run)
```bash
composer refactor
# OR
php vendor/bin/rector process --dry-run
```
Shows proposed changes without modifying files.

**Apply Refactoring**
```bash
composer refactor:apply
# OR
php vendor/bin/rector process
```
Applies all configured refactoring rules.

**Clear Cache**
```bash
composer refactor:clear
# OR
php vendor/bin/rector clear-cache
```
Clears Rector's internal cache (useful after config changes).

**List Available Rules**
```bash
composer refactor:sets
# OR
php vendor/bin/rector list-rules
```
Lists all available refactoring rules.

**Full Upgrade**
```bash
composer upgrade
```
Complete workflow: clear cache → refactor → code style fix.

### Advanced Commands

**Process Specific Directory**
```bash
php vendor/bin/rector process app/Http/Controllers --dry-run
```

**Process Single File**
```bash
php vendor/bin/rector process app/Models/Customer.php
```

**With Debug Info**
```bash
php vendor/bin/rector process --dry-run --debug
```

**Show Diffs**
```bash
php vendor/bin/rector process --dry-run --diff
```

---

## Configuration

### rector.php Configuration File

Located at project root: `rector.php`

#### Paths Configuration
```php
->withPaths([
    __DIR__ . '/app',
    __DIR__ . '/config',
    __DIR__ . '/database',
    __DIR__ . '/routes',
    __DIR__ . '/tests',
])
```

#### Skip Configuration
```php
->withSkip([
    // Skip directories
    __DIR__ . '/vendor',
    __DIR__ . '/storage',
    __DIR__ . '/bootstrap/cache',

    // Skip specific files
    // __DIR__ . '/app/Http/Middleware/SomeFile.php',

    // Skip specific rules
    // ReadOnlyPropertyRector::class,
])
```

#### Import Names Configuration
```php
->withImportNames(
    importNames: true,              // Import class names
    importDocBlockNames: true,      // Import in doc blocks
    importShortClasses: false,      // Don't import single-word classes
    removeUnusedImports: true       // Remove unused imports
)
```

---

## Refactoring Sets

### Laravel-Specific Sets

**Laravel 10.0 Upgrade**
- `LaravelSetList::LARAVEL_100`
- Updates code to Laravel 10 standards

**Code Quality**
- `LaravelSetList::LARAVEL_CODE_QUALITY`
- Laravel best practices and improvements

**Facade Aliases**
- `LaravelSetList::LARAVEL_FACADE_ALIASES_TO_FULL_NAMES`
- Converts facade aliases to full class names
- Example: `Cache::get()` → `\Illuminate\Support\Facades\Cache::get()`

**Array/String Functions**
- `LaravelSetList::LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL`
- Modernizes array/string helper usage

**Eloquent Query Builder**
- `LaravelSetList::LARAVEL_ELOQUENT_MAGIC_METHOD_TO_QUERY_BUILDER`
- Converts magic methods to explicit query builder

### PHP Version Sets

**PHP 8.1 Features**
- `SetList::PHP_81`
- Enums, readonly properties, never return type, etc.

### Code Quality Sets

**Dead Code Removal**
- `SetList::DEAD_CODE`
- Removes unused methods, properties, variables

**Code Quality**
- `SetList::CODE_QUALITY`
- General quality improvements

**Coding Style**
- `SetList::CODING_STYLE`
- Consistent coding patterns

**Type Declarations**
- `SetList::TYPE_DECLARATION`
- Adds type hints and return types

**Privatization**
- `SetList::PRIVATIZATION`
- Makes properties/methods private where possible

**Early Return**
- `SetList::EARLY_RETURN`
- Reduces nesting with early returns

**Strict Booleans**
- `SetList::STRICT_BOOLEANS`
- Enforces strict boolean checks

---

## Common Use Cases

### 1. PHP 8.1 Constructor Property Promotion

**Before**:
```php
class CustomerService
{
    private CustomerRepository $repository;
    private NotificationService $notifier;

    public function __construct(
        CustomerRepository $repository,
        NotificationService $notifier
    ) {
        $this->repository = $repository;
        $this->notifier = $notifier;
    }
}
```

**After**:
```php
class CustomerService
{
    public function __construct(
        private CustomerRepository $repository,
        private NotificationService $notifier
    ) {
    }
}
```

### 2. Add Void Return Types

**Before**:
```php
public function deleteCustomer(int $id)
{
    $this->repository->delete($id);
}
```

**After**:
```php
public function deleteCustomer(int $id): void
{
    $this->repository->delete($id);
}
```

### 3. Laravel Eloquent Query Builder

**Before**:
```php
$users = User::where('active', true)->get();
$user = User::find($id);
```

**After**:
```php
use Illuminate\Database\Eloquent\Builder;

$users = User::query()->where('active', true)->get();
$user = User::query()->find($id);
```

### 4. Static Closures

**Before**:
```php
$collection->filter(function ($item) {
    return $item->active;
});
```

**After**:
```php
$collection->filter(static function ($item) {
    return $item->active;
});
```

### 5. Remove Unused Imports

**Before**:
```php
<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CustomerService
{
    public function getCustomers()
    {
        return Customer::all();
    }
}
```

**After**:
```php
<?php

namespace App\Services;

use App\Models\Customer;

class CustomerService
{
    public function getCustomers()
    {
        return Customer::all();
    }
}
```

### 6. Import Full Class Names

**Before**:
```php
/**
 * @var Collection
 */
private $items;

public function process(): Collection
{
    return collect($this->items);
}
```

**After**:
```php
use Illuminate\Support\Collection;

/**
 * @var Collection
 */
private Collection $items;

public function process(): Collection
{
    return collect($this->items);
}
```

### 7. Early Return Pattern

**Before**:
```php
public function calculateDiscount($customer)
{
    if ($customer->isPremium()) {
        $discount = 20;
    } else {
        $discount = 10;
    }

    return $discount;
}
```

**After**:
```php
public function calculateDiscount($customer): int
{
    if ($customer->isPremium()) {
        return 20;
    }

    return 10;
}
```

---

## Best Practices

### 1. Always Use Dry Run First
```bash
composer refactor  # Preview changes
# Review output carefully
composer refactor:apply  # Apply if satisfied
```

### 2. Version Control
```bash
git status  # Check current state
git add .
git commit -m "Before Rector refactoring"
composer refactor:apply
git diff  # Review changes
git commit -m "After Rector refactoring"
```

### 3. Incremental Refactoring
```bash
# Process specific directories first
php vendor/bin/rector process app/Services --dry-run
php vendor/bin/rector process app/Models --dry-run
php vendor/bin/rector process app/Http/Controllers --dry-run
```

### 4. Test After Refactoring
```bash
composer refactor:apply
composer fix  # Fix code style
php artisan test  # Run tests
```

### 5. Clear Cache After Config Changes
```bash
# Edit rector.php
composer refactor:clear  # Clear cache
composer refactor  # Test changes
```

### 6. Combine with Other Tools
```bash
# Recommended workflow
composer upgrade      # Rector refactoring
composer fix          # Laravel Pint style fixes
composer analyze      # PHPStan analysis
php artisan test      # Run tests
```

---

## Before & After Examples

### Example 1: Customer Controller

**Before**:
```php
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    private $repository;

    public function __construct(CustomerRepository $repository)
    {
        $this->repository = $repository;
    }

    public function index(Request $request)
    {
        $customers = Customer::all();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $this->repository->create($data);

        return redirect()->route('customers.index');
    }
}
```

**After**:
```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Repositories\CustomerRepository;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function __construct(
        private CustomerRepository $repository
    ) {
    }

    public function index(Request $request): View
    {
        $customers = Customer::query()->get();

        return view('customers.index', compact('customers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required',
            'email' => 'required|email',
        ]);

        $this->repository->create($data);

        return redirect()->route('customers.index');
    }
}
```

### Example 2: Service Class

**Before**:
```php
<?php

namespace App\Services;

class NotificationService
{
    private $mailer;
    private $smsGateway;

    public function __construct($mailer, $smsGateway)
    {
        $this->mailer = $mailer;
        $this->smsGateway = $smsGateway;
    }

    public function send($user, $message)
    {
        if ($user->email) {
            $this->mailer->send($user->email, $message);
        }

        if ($user->phone) {
            $this->smsGateway->send($user->phone, $message);
        }
    }
}
```

**After**:
```php
<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\MailerInterface;
use App\Contracts\SmsGatewayInterface;
use App\Models\User;

class NotificationService
{
    public function __construct(
        private MailerInterface $mailer,
        private SmsGatewayInterface $smsGateway
    ) {
    }

    public function send(User $user, string $message): void
    {
        if ($user->email) {
            $this->mailer->send($user->email, $message);
        }

        if ($user->phone) {
            $this->smsGateway->send($user->phone, $message);
        }
    }
}
```

---

## Troubleshooting

### Issue: Changes Not Applied

**Problem**: Rector doesn't apply changes after running `refactor:apply`

**Solutions**:
```bash
# 1. Clear Rector cache
composer refactor:clear

# 2. Check if files are skipped in rector.php
# Review withSkip() configuration

# 3. Run with debug flag
php vendor/bin/rector process --debug
```

### Issue: Too Many Changes

**Problem**: Rector wants to change too many files at once

**Solutions**:
```bash
# Process directories incrementally
php vendor/bin/rector process app/Services --dry-run
php vendor/bin/rector process app/Models --dry-run

# Or skip specific rules in rector.php
->withSkip([
    SomeRuleThatCausesTooManyChanges::class,
])
```

### Issue: Syntax Errors After Refactoring

**Problem**: Code has syntax errors after applying Rector

**Solutions**:
```bash
# 1. Run code style fixer
composer fix

# 2. Check for PHP version compatibility
php -v  # Ensure PHP 8.1+

# 3. Run tests to identify issues
php artisan test
```

### Issue: Performance Problems

**Problem**: Rector takes too long to process

**Solutions**:
```bash
# 1. Clear cache
composer refactor:clear

# 2. Process smaller directories
php vendor/bin/rector process app/Models

# 3. Increase memory limit
php -d memory_limit=2G vendor/bin/rector process
```

### Issue: Rules Not Working

**Problem**: Specific rule doesn't seem to apply

**Solutions**:
```bash
# 1. List available rules
composer refactor:sets

# 2. Check rule is imported
# Ensure use statement in rector.php

# 3. Clear cache and retry
composer refactor:clear
composer refactor
```

---

## Workflow Recommendations

### Daily Development
```bash
# Not typically used daily
# Use when adding new features or refactoring
```

### Before Major Commits
```bash
composer refactor        # Preview changes
# Review output
composer refactor:apply  # Apply if appropriate
composer fix             # Fix code style
php artisan test         # Verify tests pass
git add .
git commit -m "Refactor: Apply Rector improvements"
```

### Monthly Maintenance
```bash
composer refactor:clear  # Clear cache
composer upgrade         # Full upgrade workflow
composer analyze         # Check code quality
php artisan test         # Run full test suite
```

### Before Deployment
```bash
composer refactor        # Check for improvements
composer analyze:full    # Full code analysis
php artisan test         # All tests
```

### Laravel Version Upgrade
```bash
# 1. Update composer.json Laravel version
# 2. Update rector.php to target new version
# 3. Run Rector
composer refactor:clear
composer upgrade
# 4. Fix any breaking changes manually
# 5. Test thoroughly
php artisan test
```

---

## Additional Resources

- **Rector Documentation**: https://getrector.com/documentation
- **Rector Laravel**: https://github.com/driftingly/rector-laravel
- **Laravel Upgrade Guide**: https://laravel.com/docs/10.x/upgrade
- **PHP 8.1 Features**: https://www.php.net/releases/8.1/en.php

---

## Summary

### Key Benefits
✅ **Automated refactoring** - Save hours of manual work
✅ **PHP 8.1 features** - Modern PHP patterns automatically
✅ **Laravel best practices** - Apply framework standards
✅ **Type safety** - Add type declarations automatically
✅ **Dead code removal** - Clean up unused code
✅ **Consistent codebase** - Apply patterns uniformly

### Quick Commands Reference
```bash
# Preview changes
composer refactor

# Apply changes
composer refactor:apply

# Full upgrade
composer upgrade

# Clear cache
composer refactor:clear

# List rules
composer refactor:sets
```

### Recommended Workflow
```bash
git commit -m "Before refactoring"  # Save state
composer refactor                    # Preview
composer refactor:apply              # Apply
composer fix                         # Fix style
php artisan test                     # Verify
git commit -m "After refactoring"    # Commit
```

---

**Last Updated**: 2025-10-10
**Maintained By**: Development Team
**Next Review**: After Laravel version upgrades
