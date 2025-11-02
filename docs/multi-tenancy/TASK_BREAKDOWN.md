# Multi-Tenancy Implementation - Detailed Task Breakdown

**Branch**: `feature/multi-tenancy`
**Total Estimated Time**: 37-51 hours

> ✅ = Completed | 🔄 = In Progress | ⏳ = Pending | ❌ = Blocked

---

## Phase 1: Package Installation & Setup (2-3 hours)

### Task 1.1: Create Git Branch ⏳
**Duration**: 5 minutes
**Dependencies**: None

- [ ] Checkout feature branch: `git checkout -b feature/multi-tenancy`
- [ ] Verify branch created: `git branch`
- [ ] Push to remote: `git push -u origin feature/multi-tenancy`

### Task 1.2: Install Tenancy Package ⏳
**Duration**: 15 minutes
**Dependencies**: 1.1

```bash
composer require stancl/tenancy
```

- [ ] Run composer require command
- [ ] Verify package installed in composer.json
- [ ] Check for any package conflicts
- [ ] Run `composer dump-autoload`
- [ ] Commit: "Install stancl/tenancy package"

### Task 1.3: Publish Configuration ⏳
**Duration**: 10 minutes
**Dependencies**: 1.2

```bash
php artisan tenancy:install
php artisan vendor:publish --provider="Stancl\Tenancy\TenancyServiceProvider"
```

- [ ] Run `php artisan tenancy:install`
- [ ] Publish configuration: `php artisan vendor:publish`
- [ ] Review generated files in config/tenancy.php
- [ ] Check migrations published
- [ ] Commit: "Publish tenancy configuration files"

### Task 1.4: Configure Tenancy Settings ⏳
**Duration**: 30 minutes
**Dependencies**: 1.3
**File**: `config/tenancy.php`

- [ ] Set `database.based_on` to `'per-tenant'`
- [ ] Configure `features` array (enable needed features)
- [ ] Set `identification.domain.enabled` to `true`
- [ ] Configure `bootstrappers` array
- [ ] Set `database.central_connection` to `'central'`
- [ ] Set `database.template_tenant_connection` to `'tenant'`
- [ ] Configure `filesystem.suffix_base` for tenant storage
- [ ] Test configuration: `php artisan config:cache`
- [ ] Commit: "Configure tenancy settings"

### Task 1.5: Update Database Configuration ⏳
**Duration**: 30 minutes
**Dependencies**: 1.4
**File**: `config/database.php`

Add central connection:
```php
'central' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => 'central',
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    // ... other settings
],
```

Add tenant connection template:
```php
'tenant' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    'port' => env('DB_PORT', '3306'),
    'database' => null, // Will be set dynamically
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    // ... other settings
],
```

- [ ] Add 'central' database connection
- [ ] Add 'tenant' database connection template
- [ ] Update default connection references if needed
- [ ] Test connections: `php artisan db:show`
- [ ] Commit: "Add central and tenant database connections"

### Task 1.6: Update Environment Variables ⏳
**Duration**: 15 minutes
**Dependencies**: 1.5
**File**: `.env`

- [ ] Add `TENANCY_DATABASE=central`
- [ ] Add `TENANT_DATABASE_PREFIX=tenant_`
- [ ] Add `TENANT_SUBDOMAIN_ENABLED=true`
- [ ] Add `APP_DOMAIN=midasportal.com`
- [ ] Add `CENTRAL_DOMAIN=admin.midasportal.com`
- [ ] Update `.env.example` with new variables
- [ ] Commit: "Add tenancy environment variables"

**🎯 Phase 1 Complete**: Tenancy package installed and configured

---

## Phase 2: Central Management System (4-6 hours)

### Task 2.1: Create Central Database Migrations ⏳
**Duration**: 45 minutes
**Dependencies**: Phase 1

#### 2.1a: Create Tenants Table
**File**: `database/migrations/central/2025_11_02_000001_create_tenants_table.php`

```bash
mkdir -p database/migrations/central
php artisan make:migration create_tenants_table
```

Table Structure:
```php
Schema::create('tenants', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('subdomain')->unique();
    $table->string('database_name')->unique();
    $table->foreignId('plan_id')->nullable()->constrained();
    $table->enum('status', ['active', 'suspended', 'trial', 'cancelled'])->default('trial');
    $table->json('metadata')->nullable();
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamps();
    $table->softDeletes();

    $table->index(['subdomain', 'status']);
});
```

- [ ] Create migration directory: `database/migrations/central/`
- [ ] Create tenants table migration
- [ ] Add all required columns
- [ ] Add indexes
- [ ] Test migration locally
- [ ] Commit: "Create tenants table migration"

#### 2.1b: Create Plans Table
**File**: `database/migrations/central/2025_11_02_000002_create_plans_table.php`

```php
Schema::create('plans', function (Blueprint $table) {
    $table->id();
    $table->string('name'); // Starter, Professional, Enterprise
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->decimal('price', 10, 2);
    $table->enum('interval', ['monthly', 'yearly']);
    $table->json('features'); // Array of feature names
    $table->json('limits'); // user_limit, customer_limit, lead_limit, etc.
    $table->boolean('is_active')->default(true);
    $table->integer('sort_order')->default(0);
    $table->timestamps();
});
```

- [ ] Create plans table migration
- [ ] Define all columns
- [ ] Create PlanSeeder
- [ ] Commit: "Create plans table and seeder"

#### 2.1c: Create Subscriptions Table
**File**: `database/migrations/central/2025_11_02_000003_create_subscriptions_table.php`

```php
Schema::create('subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
    $table->foreignId('plan_id')->constrained();
    $table->enum('status', ['active', 'cancelled', 'expired', 'past_due'])->default('active');
    $table->timestamp('trial_ends_at')->nullable();
    $table->timestamp('starts_at');
    $table->timestamp('ends_at')->nullable();
    $table->timestamp('cancelled_at')->nullable();
    $table->string('payment_gateway')->nullable(); // stripe, razorpay
    $table->string('gateway_subscription_id')->nullable();
    $table->timestamps();

    $table->index(['tenant_id', 'status']);
});
```

- [ ] Create subscriptions table migration
- [ ] Add foreign keys
- [ ] Add indexes
- [ ] Commit: "Create subscriptions table migration"

#### 2.1d: Create Tenant Users Table (Super Admins)
**File**: `database/migrations/central/2025_11_02_000004_create_tenant_users_table.php`

```php
Schema::create('tenant_users', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('email')->unique();
    $table->string('password');
    $table->boolean('is_super_admin')->default(false);
    $table->rememberToken();
    $table->timestamps();
});
```

- [ ] Create tenant_users table migration
- [ ] Commit: "Create tenant_users table for super admins"

### Task 2.2: Create Central Models ⏳
**Duration**: 60 minutes
**Dependencies**: 2.1

#### 2.2a: Tenant Model
**File**: `app/Models/Central/Tenant.php`

```php
<?php

namespace App\Models\Central;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase;

    protected $connection = 'central';

    protected $fillable = [
        'name',
        'subdomain',
        'database_name',
        'plan_id',
        'status',
        'metadata',
        'trial_ends_at',
    ];

    protected $casts = [
        'metadata' => 'array',
        'trial_ends_at' => 'datetime',
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isOnTrial(): bool
    {
        return $this->status === 'trial' &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }
}
```

- [ ] Create Central directory: `app/Models/Central/`
- [ ] Create Tenant model extending Stancl base
- [ ] Add relationships
- [ ] Add helper methods
- [ ] Commit: "Create Tenant model"

#### 2.2b: Plan Model
**File**: `app/Models/Central/Plan.php`

- [ ] Create Plan model
- [ ] Add relationships
- [ ] Add features/limits accessors
- [ ] Commit: "Create Plan model"

#### 2.2c: Subscription Model
**File**: `app/Models/Central/Subscription.php`

- [ ] Create Subscription model
- [ ] Add status management methods
- [ ] Add trial/billing logic
- [ ] Commit: "Create Subscription model"

#### 2.2d: TenantUser Model
**File**: `app/Models/Central/TenantUser.php`

- [ ] Create TenantUser model
- [ ] Implement authentication
- [ ] Commit: "Create TenantUser model"

### Task 2.3: Create Central Controllers ⏳
**Duration**: 90 minutes
**Dependencies**: 2.2

#### 2.3a: Central Dashboard Controller
**File**: `app/Http/Controllers/Central/DashboardController.php`

```php
<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use App\Models\Central\Subscription;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_tenants' => Tenant::count(),
            'active_tenants' => Tenant::where('status', 'active')->count(),
            'trial_tenants' => Tenant::where('status', 'trial')->count(),
            'suspended_tenants' => Tenant::where('status', 'suspended')->count(),
            'total_revenue' => Subscription::where('status', 'active')->sum('plan.price'),
        ];

        $recent_tenants = Tenant::with('plan', 'subscription')
            ->latest()
            ->limit(10)
            ->get();

        return view('central.dashboard', compact('stats', 'recent_tenants'));
    }
}
```

- [ ] Create Central controllers directory
- [ ] Create DashboardController
- [ ] Add statistics methods
- [ ] Commit: "Create central dashboard controller"

#### 2.3b: Tenant Management Controller
**File**: `app/Http/Controllers/Central/TenantController.php`

Methods needed:
- `index()` - List all tenants
- `create()` - Show create form
- `store()` - Provision new tenant
- `edit($id)` - Show edit form
- `update($id)` - Update tenant
- `destroy($id)` - Delete tenant
- `suspend($id)` - Suspend tenant
- `activate($id)` - Activate tenant
- `provision($id)` - Manual provisioning

- [ ] Create TenantController
- [ ] Implement all CRUD methods
- [ ] Add provisioning logic
- [ ] Add validation
- [ ] Commit: "Create tenant management controller"

#### 2.3c: Plan Controller
**File**: `app/Http/Controllers/Central/PlanController.php`

- [ ] Create PlanController
- [ ] Implement CRUD operations
- [ ] Commit: "Create plan management controller"

### Task 2.4: Create Central Routes ⏳
**Duration**: 30 minutes
**Dependencies**: 2.3
**File**: `routes/central.php` (NEW FILE)

```php
<?php

use App\Http\Controllers\Central\DashboardController;
use App\Http\Controllers\Central\TenantController;
use App\Http\Controllers\Central\PlanController;
use Illuminate\Support\Facades\Route;

Route::domain(config('tenancy.central_domain'))->middleware(['web', 'central.auth'])->group(function () {

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('central.dashboard');

    // Tenant Management
    Route::resource('tenants', TenantController::class)->names([
        'index' => 'central.tenants.index',
        'create' => 'central.tenants.create',
        'store' => 'central.tenants.store',
        'show' => 'central.tenants.show',
        'edit' => 'central.tenants.edit',
        'update' => 'central.tenants.update',
        'destroy' => 'central.tenants.destroy',
    ]);

    Route::post('tenants/{tenant}/provision', [TenantController::class, 'provision'])->name('central.tenants.provision');
    Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('central.tenants.suspend');
    Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('central.tenants.activate');

    // Plan Management
    Route::resource('plans', PlanController::class)->names([
        'index' => 'central.plans.index',
        'create' => 'central.plans.create',
        'store' => 'central.plans.store',
        'edit' => 'central.plans.edit',
        'update' => 'central.plans.update',
        'destroy' => 'central.plans.destroy',
    ]);
});
```

- [ ] Create routes/central.php file
- [ ] Define all central routes
- [ ] Add middleware groups
- [ ] Register in RouteServiceProvider
- [ ] Commit: "Create central domain routes"

### Task 2.5: Update RouteServiceProvider ⏳
**Duration**: 15 minutes
**Dependencies**: 2.4
**File**: `app/Providers/RouteServiceProvider.php`

```php
public function boot(): void
{
    $this->routes(function () {
        // Central domain routes
        Route::middleware('web')
            ->group(base_path('routes/central.php'));

        // Tenant routes (will be updated in Phase 4)
        Route::middleware('web')
            ->group(base_path('routes/web.php'));

        Route::middleware('api')
            ->prefix('api')
            ->group(base_path('routes/api.php'));
    });
}
```

- [ ] Update boot() method
- [ ] Load central.php routes
- [ ] Test route loading: `php artisan route:list`
- [ ] Commit: "Register central routes in RouteServiceProvider"

### Task 2.6: Create Central Middleware ⏳
**Duration**: 30 minutes
**Dependencies**: 2.5
**File**: `app/Http/Middleware/EnsureCentralDomain.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCentralDomain
{
    public function handle(Request $request, Closure $next)
    {
        $centralDomain = config('tenancy.central_domain');
        $currentDomain = $request->getHost();

        if ($currentDomain !== $centralDomain) {
            abort(403, 'Access denied. This resource is only accessible from the central domain.');
        }

        return $next($request);
    }
}
```

- [ ] Create EnsureCentralDomain middleware
- [ ] Add domain validation logic
- [ ] Register in Kernel.php
- [ ] Commit: "Create central domain middleware"

### Task 2.7: Create Central Views ⏳
**Duration**: 120 minutes
**Dependencies**: 2.6

#### 2.7a: Central Layout
**File**: `resources/views/central/layouts/app.blade.php`

- [ ] Create central views directory
- [ ] Create base layout
- [ ] Add navigation
- [ ] Add sidebar
- [ ] Commit: "Create central admin layout"

#### 2.7b: Dashboard View
**File**: `resources/views/central/dashboard.blade.php`

- [ ] Create dashboard view
- [ ] Add statistics cards
- [ ] Add recent tenants table
- [ ] Add charts (optional)
- [ ] Commit: "Create central dashboard view"

#### 2.7c: Tenant Management Views
**Files**:
- `resources/views/central/tenants/index.blade.php`
- `resources/views/central/tenants/create.blade.php`
- `resources/views/central/tenants/edit.blade.php`
- `resources/views/central/tenants/show.blade.php`

- [ ] Create tenant list view
- [ ] Create tenant creation form
- [ ] Create tenant edit form
- [ ] Create tenant details view
- [ ] Add actions (suspend, activate, delete)
- [ ] Commit: "Create tenant management views"

#### 2.7d: Plan Management Views
**Files**:
- `resources/views/central/plans/index.blade.php`
- `resources/views/central/plans/create.blade.php`
- `resources/views/central/plans/edit.blade.php`

- [ ] Create plan list view
- [ ] Create plan form views
- [ ] Commit: "Create plan management views"

**🎯 Phase 2 Complete**: Central management system built

---

## Phase 3: Database Architecture Refactoring (6-8 hours)

### Task 3.1: Create Migration Directory Structure ⏳
**Duration**: 15 minutes
**Dependencies**: Phase 2

```bash
mkdir -p database/migrations/central
mkdir -p database/migrations/tenant
```

- [ ] Create central migrations directory
- [ ] Create tenant migrations directory
- [ ] Move existing migrations to tenant/
- [ ] Move central migrations (from Phase 2) to central/
- [ ] Commit: "Organize migrations into central/tenant directories"

### Task 3.2: Move Existing Migrations ⏳
**Duration**: 30 minutes
**Dependencies**: 3.1

- [ ] Move all existing migrations to `database/migrations/tenant/`
- [ ] Keep only central migrations in `database/migrations/central/`
- [ ] Update migration paths in config if needed
- [ ] Test migration discovery
- [ ] Commit: "Move existing migrations to tenant directory"

### Task 3.3: Update All Models for Tenancy ⏳
**Duration**: 4-5 hours
**Dependencies**: 3.2

> **Note**: Each model needs to be reviewed and updated. Most will automatically use tenant connection via middleware, but some may need explicit configuration.

#### Models to Update (45 models):

**User Management** (2 models):
- [ ] `app/Models/User.php` - Keep tenant-scoped
- [ ] `app/Models/Customer.php` - Add tenant awareness

**Insurance Core** (7 models):
- [ ] `app/Models/InsuranceCompany.php`
- [ ] `app/Models/PolicyType.php`
- [ ] `app/Models/PremiumType.php`
- [ ] `app/Models/FuelType.php`
- [ ] `app/Models/AddonCover.php`
- [ ] `app/Models/CustomerInsurance.php`
- [ ] `app/Models/CustomerType.php`

**Leads Management** (5 models):
- [ ] `app/Models/Lead.php`
- [ ] `app/Models/LeadActivity.php`
- [ ] `app/Models/LeadDocument.php`
- [ ] `app/Models/LeadSource.php`
- [ ] `app/Models/LeadStatus.php`

**WhatsApp Lead Features** (4 models):
- [ ] `app/Models/LeadWhatsAppMessage.php`
- [ ] `app/Models/LeadWhatsAppCampaign.php`
- [ ] `app/Models/LeadWhatsAppCampaignLead.php`
- [ ] `app/Models/LeadWhatsAppTemplate.php`

**Quotations** (3 models):
- [ ] `app/Models/Quotation.php`
- [ ] `app/Models/QuotationCompany.php`
- [ ] `app/Models/QuotationStatus.php`

**Claims** (4 models):
- [ ] `app/Models/Claim.php`
- [ ] `app/Models/ClaimDocument.php`
- [ ] `app/Models/ClaimStage.php`
- [ ] `app/Models/ClaimLiabilityDetail.php`

**Notifications** (4 models):
- [ ] `app/Models/NotificationType.php`
- [ ] `app/Models/NotificationTemplate.php`
- [ ] `app/Models/NotificationLog.php`
- [ ] `app/Models/NotificationDeliveryTracking.php`

**Support Entities** (7 models):
- [ ] `app/Models/Branch.php`
- [ ] `app/Models/Broker.php`
- [ ] `app/Models/ReferenceUser.php`
- [ ] `app/Models/RelationshipManager.php`
- [ ] `app/Models/CommissionType.php`
- [ ] `app/Models/FamilyGroup.php`
- [ ] `app/Models/FamilyMember.php`

**Audit & Security** (6 models):
- [ ] `app/Models/AuditLog.php`
- [ ] `app/Models/CustomerAuditLog.php`
- [ ] `app/Models/AppSetting.php`
- [ ] `app/Models/DeviceTracking.php`
- [ ] `app/Models/CustomerDevice.php`
- [ ] `app/Models/SecuritySetting.php`

**Auth & Security** (3 models):
- [ ] `app/Models/TwoFactorAuth.php`
- [ ] `app/Models/TwoFactorAttempt.php`
- [ ] `app/Models/TrustedDevice.php`

**Other** (1 model):
- [ ] `app/Models/Report.php`

**Batch Commits**:
- [ ] Commit: "Update user and customer models for tenancy"
- [ ] Commit: "Update insurance models for tenancy"
- [ ] Commit: "Update lead models for tenancy"
- [ ] Commit: "Update quotation models for tenancy"
- [ ] Commit: "Update claim models for tenancy"
- [ ] Commit: "Update notification models for tenancy"
- [ ] Commit: "Update support entity models for tenancy"
- [ ] Commit: "Update audit and security models for tenancy"

### Task 3.4: Configure File Storage ⏳
**Duration**: 45 minutes
**Dependencies**: 3.3
**File**: `config/filesystems.php`

Add tenant-specific storage configuration:

```php
'disks' => [
    // ... existing disks ...

    'tenant' => [
        'driver' => 'local',
        'root' => storage_path('app/tenants/' . tenant('id')),
        'url' => env('APP_URL').'/storage/tenants/' . tenant('id'),
        'visibility' => 'private',
        'throw' => false,
    ],

    'tenant_public' => [
        'driver' => 'local',
        'root' => storage_path('app/public/tenants/' . tenant('id')),
        'url' => env('APP_URL').'/storage/tenants/' . tenant('id'),
        'visibility' => 'public',
        'throw' => false,
    ],
],
```

- [ ] Add tenant disk configuration
- [ ] Add tenant_public disk configuration
- [ ] Create storage helper functions
- [ ] Update file upload controllers
- [ ] Test file uploads with tenant context
- [ ] Commit: "Configure tenant-specific file storage"

**🎯 Phase 3 Complete**: All models tenant-aware

---

## Phase 4: Subdomain Routing & Tenant Identification (4-5 hours)

### Task 4.1: Update Tenancy Configuration ⏳
**Duration**: 30 minutes
**Dependencies**: Phase 3
**File**: `config/tenancy.php`

```php
'identification' => [
    'domain' => [
        'enabled' => true,
    ],
],

'bootstrappers' => [
    Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
    Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
],
```

- [ ] Enable domain identification
- [ ] Configure bootstrappers
- [ ] Set feature flags
- [ ] Test configuration
- [ ] Commit: "Configure tenant subdomain identification"

### Task 4.2: Update Web Routes with Tenancy Middleware ⏳
**Duration**: 60 minutes
**Dependencies**: 4.1
**File**: `routes/web.php`

Wrap all tenant routes:

```php
Route::middleware(['web', InitializeTenancyByDomain::class])->group(function () {
    // All existing application routes
    Auth::routes(['register' => false]);
    Route::get('/home', [HomeController::class, 'index'])->name('home');
    // ... all other routes ...
});
```

- [ ] Wrap tenant routes with InitializeTenancyByDomain middleware
- [ ] Ensure authentication routes are wrapped
- [ ] Test route resolution
- [ ] Verify tenant context loaded
- [ ] Commit: "Add tenancy middleware to web routes"

### Task 4.3: Create Tenant Bootstrap Service Provider ⏳
**Duration**: 45 minutes
**Dependencies**: 4.2
**File**: `app/Providers/TenancyServiceProvider.php`

```php
<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Stancl\Tenancy\Tenant;

class TenancyServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Initialize tenant context
        $this->bootTenantContext();

        // Boot tenant-specific config
        $this->bootTenantConfig();
    }

    protected function bootTenantContext(): void
    {
        // Set tenant-specific cache prefix
        // Set tenant-specific session domain
        // Load tenant-specific config
    }

    protected function bootTenantConfig(): void
    {
        if (tenancy()->initialized) {
            // Load tenant settings from app_settings table
            // Override config values with tenant-specific ones
        }
    }
}
```

- [ ] Create TenancyServiceProvider
- [ ] Add tenant bootstrapping logic
- [ ] Register in config/app.php
- [ ] Test provider loading
- [ ] Commit: "Create TenancyServiceProvider"

### Task 4.4: Handle Invalid/Suspended Tenants ⏳
**Duration**: 45 minutes
**Dependencies**: 4.3
**File**: `app/Http/Middleware/HandleInvalidTenant.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HandleInvalidTenant
{
    public function handle(Request $request, Closure $next)
    {
        if (!tenancy()->initialized) {
            return response()->view('errors.tenant-not-found', [], 404);
        }

        $tenant = tenant();

        if ($tenant->isSuspended()) {
            return response()->view('errors.tenant-suspended', [
                'tenant' => $tenant
            ], 403);
        }

        if ($tenant->status === 'cancelled') {
            return response()->view('errors.tenant-cancelled', [
                'tenant' => $tenant
            ], 403);
        }

        return $next($request);
    }
}
```

- [ ] Create HandleInvalidTenant middleware
- [ ] Add status checking logic
- [ ] Create error view templates
- [ ] Register middleware
- [ ] Test with invalid subdomains
- [ ] Commit: "Add invalid tenant handler middleware"

### Task 4.5: Create Tenant Error Views ⏳
**Duration**: 30 minutes
**Dependencies**: 4.4

**Files**:
- `resources/views/errors/tenant-not-found.blade.php`
- `resources/views/errors/tenant-suspended.blade.php`
- `resources/views/errors/tenant-cancelled.blade.php`

- [ ] Create tenant-not-found view
- [ ] Create tenant-suspended view
- [ ] Create tenant-cancelled view
- [ ] Style error pages
- [ ] Commit: "Create tenant error views"

### Task 4.6: Local Testing Setup ⏳
**Duration**: 45 minutes
**Dependencies**: 4.5

#### Update hosts file:
**Windows**: `C:\Windows\System32\drivers\etc\hosts`
**Linux/Mac**: `/etc/hosts`

```
127.0.0.1 midasportal.local
127.0.0.1 admin.midasportal.local
127.0.0.1 tenant1.midasportal.local
127.0.0.1 tenant2.midasportal.local
```

#### Update .env for local testing:
```env
APP_URL=http://midasportal.local:8000
APP_DOMAIN=midasportal.local
CENTRAL_DOMAIN=admin.midasportal.local
```

- [ ] Update local hosts file
- [ ] Update .env for local subdomains
- [ ] Test central domain access
- [ ] Test tenant subdomain access
- [ ] Document local testing setup
- [ ] Commit: "Add local testing configuration"

**🎯 Phase 4 Complete**: Subdomain routing functional

---

## Phase 5: Authentication & Authorization Updates (3-4 hours)

### Task 5.1: Update Login Controller ⏳
**Duration**: 45 minutes
**Dependencies**: Phase 4
**File**: `app/Http/Controllers/Auth/LoginController.php`

```php
protected function authenticated(Request $request, $user)
{
    // Add tenant_id to session
    session(['tenant_id' => tenant('id')]);

    // Log successful login with tenant context
    activity()
        ->causedBy($user)
        ->log('User logged in from subdomain: ' . tenant('subdomain'));

    return redirect()->intended($this->redirectPath());
}
```

- [ ] Add tenant context to login
- [ ] Validate user belongs to current tenant
- [ ] Add tenant_id to session
- [ ] Update redirect logic
- [ ] Test login flow
- [ ] Commit: "Update login with tenant validation"

### Task 5.2: Update Password Reset Flow ⏳
**Duration**: 30 minutes
**Dependencies**: 5.1

**Files**:
- `app/Http/Controllers/Auth/ForgotPasswordController.php`
- `app/Http/Controllers/Auth/ResetPasswordController.php`

- [ ] Scope password reset tokens to tenant
- [ ] Update email templates with tenant branding
- [ ] Test password reset flow
- [ ] Commit: "Scope password reset to tenant"

### Task 5.3: Update Spatie Permissions ⏳
**Duration**: 45 minutes
**Dependencies**: 5.2

**Files**:
- `database/seeders/Tenant/RolesAndPermissionsSeeder.php`
- `config/permission.php`

```php
// Make sure permissions are tenant-scoped
'teams' => true,
'team_foreign_key' => 'tenant_id',
```

- [ ] Update permission config for tenancy
- [ ] Create default roles seeder for tenants
- [ ] Test permission checking
- [ ] Verify role isolation between tenants
- [ ] Commit: "Make Spatie permissions tenant-aware"

### Task 5.4: Create Super Admin Authentication ⏳
**Duration**: 90 minutes
**Dependencies**: 5.3

#### Files to Create:
- `app/Http/Controllers/Central/AuthController.php`
- `resources/views/central/auth/login.blade.php`
- `app/Http/Middleware/CentralAuth.php`

```php
// CentralAuth middleware
public function handle(Request $request, Closure $next)
{
    if (!Auth::guard('central')->check()) {
        return redirect()->route('central.login');
    }

    if (!Auth::guard('central')->user()->is_super_admin) {
        abort(403, 'Unauthorized access to central admin.');
    }

    return $next($request);
}
```

- [ ] Create central auth controller
- [ ] Create central login view
- [ ] Add central auth guard in config/auth.php
- [ ] Create CentralAuth middleware
- [ ] Test super admin login
- [ ] Commit: "Create super admin authentication system"

### Task 5.5: Add Tenant User Impersonation ⏳
**Duration**: 60 minutes
**Dependencies**: 5.4

**File**: `app/Http/Controllers/Central/ImpersonationController.php`

```php
public function impersonate(Tenant $tenant, $userId)
{
    // Security check
    if (!auth()->guard('central')->user()->is_super_admin) {
        abort(403);
    }

    // Switch to tenant context
    tenancy()->initialize($tenant);

    // Find user in tenant database
    $user = User::findOrFail($userId);

    // Store impersonation data
    session([
        'impersonating' => true,
        'impersonator_id' => auth()->guard('central')->id(),
        'tenant_id' => $tenant->id,
    ]);

    // Log impersonation
    activity()
        ->causedBy(auth()->guard('central')->user())
        ->performedOn($user)
        ->log('Super admin started impersonating user');

    // Login as tenant user
    auth()->guard('web')->login($user);

    return redirect()->to("http://{$tenant->subdomain}." . config('app.domain') . "/home");
}

public function stopImpersonation()
{
    if (session('impersonating')) {
        activity()->log('Super admin stopped impersonating user');

        session()->forget(['impersonating', 'impersonator_id', 'tenant_id']);
        auth()->guard('web')->logout();

        return redirect()->route('central.dashboard');
    }

    abort(403);
}
```

- [ ] Create ImpersonationController
- [ ] Add security checks
- [ ] Implement impersonation logic
- [ ] Add "Exit Impersonation" button in layout
- [ ] Log all impersonation events
- [ ] Test impersonation flow
- [ ] Commit: "Add tenant user impersonation for support"

**🎯 Phase 5 Complete**: Authentication tenant-aware

---

## Phase 6: Data Migration (3-4 hours)

⚠️ **CRITICAL**: Full database backup required before starting this phase!

### Task 6.1: Create Database Backup ⏳
**Duration**: 30 minutes
**Dependencies**: Phase 5

```bash
# Backup current database
mysqldump -u root -p u430606517_parth2 > backup_pre_migration_$(date +%Y%m%d_%H%M%S).sql

# Verify backup
mysql -u root -p -e "SOURCE backup_pre_migration_*.sql;" test_restore_db
```

- [ ] Create complete database dump
- [ ] Compress backup file
- [ ] Store backup in safe location
- [ ] Verify backup integrity
- [ ] Document backup location
- [ ] Commit backup script: "Add database backup script"

### Task 6.2: Create Migration Command ⏳
**Duration**: 90 minutes
**Dependencies**: 6.1

**File**: `app/Console/Commands/MigrateToTenancy.php`

```bash
php artisan make:command MigrateToTenancy
```

```php
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Central\Tenant;
use Illuminate\Support\Facades\DB;

class MigrateToTenancy extends Command
{
    protected $signature = 'tenancy:migrate-data {--tenant-name=} {--subdomain=}';
    protected $description = 'Migrate existing data to first tenant';

    public function handle()
    {
        $this->info('Starting data migration to tenant structure...');

        // Step 1: Create first tenant
        $tenant = $this->createFirstTenant();

        // Step 2: Create tenant database
        $this->createTenantDatabase($tenant);

        // Step 3: Run tenant migrations
        $this->runTenantMigrations($tenant);

        // Step 4: Migrate data
        $this->migrateData($tenant);

        // Step 5: Verify migration
        $this->verifyMigration($tenant);

        $this->info('Data migration completed successfully!');
    }

    protected function createFirstTenant()
    {
        // Create tenant record in central database
    }

    protected function createTenantDatabase(Tenant $tenant)
    {
        // CREATE DATABASE tenant_1
    }

    protected function runTenantMigrations(Tenant $tenant)
    {
        // Run all tenant migrations
    }

    protected function migrateData(Tenant $tenant)
    {
        // Copy data from old database to tenant database
        // Handle all 60+ tables
    }

    protected function verifyMigration(Tenant $tenant)
    {
        // Verify record counts match
        // Check relationships
    }
}
```

- [ ] Create MigrateToTenancy command
- [ ] Add tenant creation logic
- [ ] Add database creation logic
- [ ] Add data migration logic
- [ ] Add rollback capability
- [ ] Add data verification
- [ ] Test on database copy
- [ ] Commit: "Create tenant migration command"

### Task 6.3: Run Data Migration ⏳
**Duration**: 30 minutes
**Dependencies**: 6.2

```bash
php artisan tenancy:migrate-data --tenant-name="Midas Insurance" --subdomain=midas
```

- [ ] Review migration command output
- [ ] Check for errors
- [ ] Verify tenant created in central DB
- [ ] Verify tenant database created
- [ ] Check data migrated
- [ ] Test application with first tenant
- [ ] Commit: "Migrate existing data to first tenant"

### Task 6.4: Create Default Tenant Seeders ⏳
**Duration**: 60 minutes
**Dependencies**: 6.3

**Files**:
- `database/seeders/Tenant/RolesAndPermissionsSeeder.php`
- `database/seeders/Tenant/DefaultStatusesSeeder.php`
- `database/seeders/Tenant/DefaultSettingsSeeder.php`
- `database/seeders/Tenant/TenantDatabaseSeeder.php`

```php
// TenantDatabaseSeeder.php
public function run()
{
    $this->call([
        RolesAndPermissionsSeeder::class,
        DefaultStatusesSeeder::class,
        DefaultSettingsSeeder::class,
    ]);
}
```

- [ ] Create RolesAndPermissionsSeeder
- [ ] Create DefaultStatusesSeeder (lead statuses, quotation statuses)
- [ ] Create DefaultSettingsSeeder (app settings)
- [ ] Create master TenantDatabaseSeeder
- [ ] Test seeders on new tenant
- [ ] Commit: "Create default tenant seeders"

### Task 6.5: Verify Data Migration ⏳
**Duration**: 30 minutes
**Dependencies**: 6.4

**Verification Checklist**:
- [ ] Compare record counts (old DB vs tenant DB)
- [ ] Verify users can login to new subdomain
- [ ] Test customer data accessible
- [ ] Test lead data accessible
- [ ] Test quotations accessible
- [ ] Test claims accessible
- [ ] Verify relationships intact (foreign keys)
- [ ] Check file uploads working
- [ ] Test notifications working
- [ ] Document any issues
- [ ] Commit: "Data migration verification report"

**🎯 Phase 6 Complete**: Data successfully migrated to tenant structure

---

## Phase 7: Billing & Subscription System (6-8 hours)

### Task 7.1: Create Plan Seeder ⏳
**Duration**: 30 minutes
**Dependencies**: Phase 6

**File**: `database/seeders/Central/PlanSeeder.php`

```php
DB::table('plans')->insert([
    [
        'name' => 'Starter',
        'slug' => 'starter',
        'description' => 'Perfect for small insurance brokers',
        'price' => 999.00,
        'interval' => 'monthly',
        'features' => json_encode([
            '5 users',
            '1,000 customers',
            '500 leads per month',
            'Basic support',
            'Email notifications',
        ]),
        'limits' => json_encode([
            'users' => 5,
            'customers' => 1000,
            'leads_per_month' => 500,
            'storage_gb' => 5,
        ]),
        'is_active' => true,
        'sort_order' => 1,
    ],
    [
        'name' => 'Professional',
        'slug' => 'professional',
        'description' => 'For growing insurance businesses',
        'price' => 2999.00,
        'interval' => 'monthly',
        'features' => json_encode([
            '20 users',
            '10,000 customers',
            '5,000 leads per month',
            'Priority support',
            'Email + WhatsApp notifications',
            'Advanced reporting',
        ]),
        'limits' => json_encode([
            'users' => 20,
            'customers' => 10000,
            'leads_per_month' => 5000,
            'storage_gb' => 50,
        ]),
        'is_active' => true,
        'sort_order' => 2,
    ],
    [
        'name' => 'Enterprise',
        'slug' => 'enterprise',
        'description' => 'For large insurance organizations',
        'price' => 9999.00,
        'interval' => 'monthly',
        'features' => json_encode([
            'Unlimited users',
            'Unlimited customers',
            'Unlimited leads',
            'Dedicated support',
            'All communication channels',
            'Custom integrations',
            'API access',
        ]),
        'limits' => json_encode([
            'users' => -1, // unlimited
            'customers' => -1,
            'leads_per_month' => -1,
            'storage_gb' => 500,
        ]),
        'is_active' => true,
        'sort_order' => 3,
    ],
]);
```

- [ ] Create PlanSeeder
- [ ] Define Starter plan
- [ ] Define Professional plan
- [ ] Define Enterprise plan
- [ ] Run seeder
- [ ] Commit: "Create pricing plans seeder"

### Task 7.2: Create Usage Tracking Service ⏳
**Duration**: 90 minutes
**Dependencies**: 7.1

**File**: `app/Services/UsageTrackingService.php`

```php
<?php

namespace App\Services;

use App\Models\Central\Tenant;
use App\Models\User;
use App\Models\Customer;
use App\Models\Lead;
use Illuminate\Support\Facades\DB;

class UsageTrackingService
{
    public function getTenantUsage(Tenant $tenant): array
    {
        tenancy()->initialize($tenant);

        return [
            'users' => [
                'current' => User::count(),
                'limit' => $tenant->plan->limits['users'] ?? 0,
            ],
            'customers' => [
                'current' => Customer::count(),
                'limit' => $tenant->plan->limits['customers'] ?? 0,
            ],
            'leads_this_month' => [
                'current' => Lead::whereMonth('created_at', now()->month)->count(),
                'limit' => $tenant->plan->limits['leads_per_month'] ?? 0,
            ],
            'storage' => [
                'current_gb' => $this->getStorageUsage($tenant),
                'limit_gb' => $tenant->plan->limits['storage_gb'] ?? 0,
            ],
        ];
    }

    protected function getStorageUsage(Tenant $tenant): float
    {
        $path = storage_path("app/tenants/{$tenant->id}");

        if (!is_dir($path)) {
            return 0;
        }

        $size = 0;
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($path)) as $file) {
            $size += $file->getSize();
        }

        return round($size / 1024 / 1024 / 1024, 2); // Convert to GB
    }

    public function isLimitExceeded(Tenant $tenant, string $resource): bool
    {
        $usage = $this->getTenantUsage($tenant);

        if (!isset($usage[$resource])) {
            return false;
        }

        $limit = $usage[$resource]['limit'];

        // -1 means unlimited
        if ($limit === -1) {
            return false;
        }

        return $usage[$resource]['current'] >= $limit;
    }
}
```

- [ ] Create UsageTrackingService
- [ ] Add user count tracking
- [ ] Add customer count tracking
- [ ] Add lead count tracking
- [ ] Add storage usage calculation
- [ ] Add limit checking methods
- [ ] Test usage tracking
- [ ] Commit: "Create usage tracking service"

### Task 7.3: Create Limit Enforcement Middleware ⏳
**Duration**: 60 minutes
**Dependencies**: 7.2

**File**: `app/Http/Middleware/EnforcePlanLimits.php`

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Services\UsageTrackingService;

class EnforcePlanLimits
{
    protected $usageService;

    public function __construct(UsageTrackingService $usageService)
    {
        $this->usageService = $usageService;
    }

    public function handle(Request $request, Closure $next, string $resource)
    {
        $tenant = tenant();

        if (!$tenant || !$tenant->plan) {
            return $next($request);
        }

        if ($this->usageService->isLimitExceeded($tenant, $resource)) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Plan limit exceeded',
                    'message' => "You have reached your plan limit for {$resource}. Please upgrade your plan.",
                    'upgrade_url' => route('billing.upgrade'),
                ], 402); // 402 Payment Required
            }

            return redirect()->route('billing.upgrade')
                ->with('error', "You have reached your plan limit for {$resource}. Please upgrade your plan.");
        }

        return $next($request);
    }
}
```

- [ ] Create EnforcePlanLimits middleware
- [ ] Add resource limit checking
- [ ] Add upgrade prompts
- [ ] Register middleware with aliases
- [ ] Apply to relevant routes
- [ ] Test limit enforcement
- [ ] Commit: "Create plan limit enforcement middleware"

### Task 7.4: Update Routes with Limit Middleware ⏳
**Duration**: 30 minutes
**Dependencies**: 7.3

**File**: `routes/web.php`

```php
// Apply limit middleware to resource creation routes
Route::middleware(['enforce_limits:users'])->group(function () {
    Route::post('users/store', [UserController::class, 'store'])->name('users.store');
});

Route::middleware(['enforce_limits:customers'])->group(function () {
    Route::post('customers/store', [CustomerController::class, 'store'])->name('customers.store');
});

Route::middleware(['enforce_limits:leads_this_month'])->group(function () {
    Route::post('leads/store', [LeadController::class, 'store'])->name('leads.store');
});
```

- [ ] Add limit middleware to user creation
- [ ] Add limit middleware to customer creation
- [ ] Add limit middleware to lead creation
- [ ] Test limit blocking
- [ ] Commit: "Apply plan limits to resource creation"

### Task 7.5: Install Payment Gateway SDK ⏳
**Duration**: 30 minutes
**Dependencies**: 7.4

```bash
composer require stripe/stripe-php
# OR
composer require razorpay/razorpay
```

- [ ] Install Stripe SDK
- [ ] Install Razorpay SDK (optional)
- [ ] Add API keys to .env
- [ ] Commit: "Install payment gateway SDKs"

### Task 7.6: Create Payment Service ⏳
**Duration**: 120 minutes
**Dependencies**: 7.5

**File**: `app/Services/PaymentGatewayService.php`

```php
<?php

namespace App\Services;

use App\Models\Central\Tenant;
use App\Models\Central\Subscription;
use Stripe\Stripe;
use Stripe\Customer as StripeCustomer;
use Stripe\Subscription as StripeSubscription;

class PaymentGatewayService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    public function createSubscription(Tenant $tenant, Plan $plan, string $paymentMethodId): Subscription
    {
        // Create Stripe customer
        $stripeCustomer = StripeCustomer::create([
            'email' => $tenant->email,
            'name' => $tenant->name,
            'payment_method' => $paymentMethodId,
            'invoice_settings' => [
                'default_payment_method' => $paymentMethodId,
            ],
        ]);

        // Create Stripe subscription
        $stripeSubscription = StripeSubscription::create([
            'customer' => $stripeCustomer->id,
            'items' => [['price' => $plan->stripe_price_id]],
            'expand' => ['latest_invoice.payment_intent'],
        ]);

        // Create local subscription record
        return Subscription::create([
            'tenant_id' => $tenant->id,
            'plan_id' => $plan->id,
            'status' => 'active',
            'starts_at' => now(),
            'payment_gateway' => 'stripe',
            'gateway_subscription_id' => $stripeSubscription->id,
        ]);
    }

    public function cancelSubscription(Subscription $subscription)
    {
        // Cancel in Stripe
        $stripeSubscription = StripeSubscription::retrieve($subscription->gateway_subscription_id);
        $stripeSubscription->cancel();

        // Update local record
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
            'ends_at' => $stripeSubscription->current_period_end,
        ]);
    }

    public function handleWebhook(array $payload)
    {
        // Handle Stripe webhooks
        // subscription.updated, subscription.deleted, payment.failed, etc.
    }
}
```

- [ ] Create PaymentGatewayService
- [ ] Implement subscription creation
- [ ] Implement subscription cancellation
- [ ] Implement webhook handler
- [ ] Add error handling
- [ ] Test payment flow
- [ ] Commit: "Create payment gateway service"

### Task 7.7: Create Billing Controllers ⏳
**Duration**: 90 minutes
**Dependencies**: 7.6

**Files**:
- `app/Http/Controllers/Tenant/BillingController.php`
- `app/Http/Controllers/Central/SubscriptionController.php`
- `app/Http/Controllers/Webhooks/StripeWebhookController.php`

- [ ] Create BillingController (tenant side)
- [ ] Create SubscriptionController (central admin)
- [ ] Create StripeWebhookController
- [ ] Add all CRUD methods
- [ ] Add payment processing methods
- [ ] Commit: "Create billing controllers"

### Task 7.8: Create Billing Views ⏳
**Duration**: 90 minutes
**Dependencies**: 7.7

**Files**:
- `resources/views/tenant/billing/index.blade.php` (usage dashboard)
- `resources/views/tenant/billing/upgrade.blade.php` (plan selection)
- `resources/views/tenant/billing/payment.blade.php` (payment form)
- `resources/views/tenant/billing/invoices.blade.php` (invoice history)

- [ ] Create billing dashboard view
- [ ] Create plan upgrade view
- [ ] Create payment form view
- [ ] Create invoice listing view
- [ ] Add Stripe.js integration
- [ ] Commit: "Create billing dashboard views"

**🎯 Phase 7 Complete**: Billing system functional

---

## Phase 8: Testing & QA (4-6 hours)

### Task 8.1: Create Unit Tests ⏳
**Duration**: 120 minutes
**Dependencies**: Phase 7

**Files**:
- `tests/Unit/Tenancy/TenantModelTest.php`
- `tests/Unit/Tenancy/TenantProvisioningTest.php`
- `tests/Unit/Tenancy/UsageTrackingTest.php`

```bash
php artisan make:test Tenancy/TenantModelTest --unit
php artisan make:test Tenancy/TenantProvisioningTest --unit
```

Tests to write:
- [ ] Test Tenant model creation
- [ ] Test tenant database creation
- [ ] Test database switching
- [ ] Test usage tracking calculations
- [ ] Test plan limit checking
- [ ] Run all tests: `php artisan test`
- [ ] Commit: "Add unit tests for tenancy"

### Task 8.2: Create Feature Tests ⏳
**Duration**: 120 minutes
**Dependencies**: 8.1

**Files**:
- `tests/Feature/Tenancy/TenantIsolationTest.php`
- `tests/Feature/Tenancy/SubdomainRoutingTest.php`
- `tests/Feature/Tenancy/BillingTest.php`

Tests to write:
- [ ] Test tenant data isolation (no cross-tenant access)
- [ ] Test subdomain routing
- [ ] Test file storage isolation
- [ ] Test authentication per tenant
- [ ] Test billing workflow
- [ ] Run all tests: `php artisan test --testsuite=Feature`
- [ ] Commit: "Add feature tests for multi-tenancy"

### Task 8.3: Security Testing ⏳
**Duration**: 90 minutes
**Dependencies**: 8.2

Security checks:
- [ ] Test cross-tenant data access attempts (should fail)
- [ ] Test SQL injection prevention
- [ ] Test session hijacking prevention
- [ ] Test file access restrictions
- [ ] Test subdomain spoofing prevention
- [ ] Test super admin authentication
- [ ] Document all security findings
- [ ] Fix any security issues found
- [ ] Commit: "Security testing report and fixes"

### Task 8.4: Performance Testing ⏳
**Duration**: 60 minutes
**Dependencies**: 8.3

Performance benchmarks:
- [ ] Test tenant provisioning time (should be < 2 min)
- [ ] Test database query performance with tenant context
- [ ] Test concurrent requests to different tenants
- [ ] Measure memory usage
- [ ] Measure response times
- [ ] Document performance benchmarks
- [ ] Optimize slow queries if needed
- [ ] Commit: "Performance testing results and optimizations"

**🎯 Phase 8 Complete**: All tests passing, security verified

---

## Phase 9: Deployment Configuration (3-4 hours)

### Task 9.1: Create Server Configuration Files ⏳
**Duration**: 60 minutes
**Dependencies**: Phase 8

#### Nginx Configuration
**File**: `deployment/nginx-config.conf`

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name *.midasportal.com midasportal.com;

    root /var/www/midas-portal/public;
    index index.php index.html;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

#### Apache Configuration
**File**: `deployment/apache-config.conf`

```apache
<VirtualHost *:80>
    ServerName midasportal.com
    ServerAlias *.midasportal.com

    DocumentRoot /var/www/midas-portal/public

    <Directory /var/www/midas-portal/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/midas-portal-error.log
    CustomLog ${APACHE_LOG_DIR}/midas-portal-access.log combined
</VirtualHost>
```

- [ ] Create deployment directory
- [ ] Create Nginx configuration
- [ ] Create Apache configuration
- [ ] Add PHP-FPM settings
- [ ] Commit: "Add server configuration files"

### Task 9.2: SSL Certificate Setup ⏳
**Duration**: 45 minutes
**Dependencies**: 9.1

**File**: `deployment/ssl-setup.sh`

```bash
#!/bin/bash

# Install Certbot
sudo apt-get install certbot python3-certbot-nginx -y

# Get wildcard certificate
sudo certbot certonly --manual \
  --preferred-challenges=dns \
  --email=admin@midasportal.com \
  --server https://acme-v02.api.letsencrypt.org/directory \
  --agree-tos \
  -d midasportal.com \
  -d *.midasportal.com

# Setup auto-renewal
sudo crontab -e
# Add: 0 3 * * * certbot renew --quiet
```

- [ ] Create SSL setup script
- [ ] Document DNS TXT record requirements
- [ ] Add certificate renewal cron job
- [ ] Update Nginx/Apache with SSL config
- [ ] Commit: "Add SSL certificate setup documentation"

### Task 9.3: Database Optimization ⏳
**Duration**: 45 minutes
**Dependencies**: 9.2

**File**: `deployment/mysql-optimization.cnf`

```ini
[mysqld]
# Connection settings
max_connections = 200
connect_timeout = 10

# Performance settings
innodb_buffer_pool_size = 2G
innodb_log_file_size = 256M
query_cache_size = 64M
tmp_table_size = 64M
max_heap_table_size = 64M

# Per-tenant optimization
innodb_file_per_table = 1
```

- [ ] Create MySQL optimization config
- [ ] Configure connection pooling
- [ ] Add query cache settings
- [ ] Optimize indexes
- [ ] Document database tuning
- [ ] Commit: "Add database optimization configuration"

### Task 9.4: Create Deployment Script ⏳
**Duration**: 60 minutes
**Dependencies**: 9.3

**File**: `deployment/deploy.sh`

```bash
#!/bin/bash

echo "Starting deployment..."

# Pre-deployment checks
echo "Running pre-deployment checks..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Database migrations
echo "Running central migrations..."
php artisan migrate --database=central --force

echo "Running tenant migrations..."
php artisan tenants:migrate --force

# Clear caches
php artisan cache:clear
php artisan config:clear

# Restart services
sudo systemctl restart php8.2-fpm
sudo systemctl restart nginx

echo "Deployment complete!"
```

- [ ] Create deployment script
- [ ] Add pre-deployment checks
- [ ] Add migration steps
- [ ] Add cache clearing
- [ ] Add service restart
- [ ] Add rollback capability
- [ ] Test script in staging
- [ ] Commit: "Create deployment automation script"

**🎯 Phase 9 Complete**: Production deployment ready

---

## Phase 10: Documentation (2-3 hours)

### Task 10.1: Technical Documentation ⏳
**Duration**: 60 minutes
**Dependencies**: Phase 9

**File**: `docs/multi-tenancy/ARCHITECTURE.md`

Content:
- System architecture diagram
- Database schema (central + tenant)
- Authentication flow
- Tenant provisioning flow
- Subdomain routing mechanism
- File storage structure
- API endpoints

- [ ] Create architecture document
- [ ] Add system diagrams
- [ ] Document database schema
- [ ] Document key workflows
- [ ] Commit: "Add technical architecture documentation"

### Task 10.2: Administrator Guide ⏳
**Duration**: 45 minutes
**Dependencies**: 10.1

**File**: `docs/multi-tenancy/ADMIN_GUIDE.md`

Content:
- How to access central admin
- How to create new tenants
- How to manage subscriptions
- How to handle support requests
- How to impersonate users
- Troubleshooting common issues

- [ ] Create administrator guide
- [ ] Add step-by-step instructions
- [ ] Add screenshots
- [ ] Add troubleshooting section
- [ ] Commit: "Add administrator user guide"

### Task 10.3: Deployment Guide ⏳
**Duration**: 30 minutes
**Dependencies**: 10.2

**File**: `docs/multi-tenancy/DEPLOYMENT.md`

Content:
- Server requirements
- DNS configuration
- SSL setup
- Database setup
- Environment variables
- Deployment checklist

- [ ] Create deployment guide
- [ ] Add server requirements
- [ ] Add configuration steps
- [ ] Add deployment checklist
- [ ] Commit: "Add deployment documentation"

### Task 10.4: Update Main README ⏳
**Duration**: 15 minutes
**Dependencies**: 10.3

**File**: `README.md`

- [ ] Add multi-tenancy overview
- [ ] Update installation instructions
- [ ] Add configuration guide
- [ ] Link to detailed documentation
- [ ] Commit: "Update README for multi-tenancy"

**🎯 Phase 10 Complete**: Documentation complete

---

## Final Steps

### Merge to Main ⏳
**Duration**: 30 minutes

- [ ] Run all tests one final time
- [ ] Review all commits
- [ ] Create pull request
- [ ] Code review
- [ ] Merge to main branch
- [ ] Tag release: `v2.0.0-multi-tenancy`
- [ ] Deploy to production

---

## Progress Tracking Legend

- ✅ = Completed
- 🔄 = In Progress
- ⏳ = Pending
- ❌ = Blocked
- ⚠️ = Issue/Warning

---

**Total Tasks**: 150+
**Estimated Total Time**: 37-51 hours
**Last Updated**: 2025-11-02
