# User & Role Management

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Role-Based Access Control (RBAC) system using **Spatie Laravel Permission** package for multi-tenant insurance SaaS platform with comprehensive user management, role assignment, and permission control.

### Key Features

- **Spatie Permission Integration**: Roles & permissions management via `spatie/laravel-permission`
- **Multi-Guard Support**: Separate guards for web (staff) and customer portals
- **Role Hierarchy**: Admin, TA/TP (Tenant Admin/Tenant Partner) with granular permissions
- **User Management**: CRUD operations with role assignment, status management, soft delete
- **Permission Groups**: Organized by module (customer, policy, claim, lead, user, role, etc.)
- **Protected Records**: Prevent deletion of system-critical users/roles
- **Two-Factor Authentication**: Optional 2FA with trusted device management
- **Security Settings**: Per-user security configurations
- **Audit Trail**: Comprehensive activity logging via Spatie Activity Log

## User Model

**File**: `app/Models/User.php`

### Attributes

**Identity**
- `first_name` (string) - First name
- `last_name` (string, nullable) - Last name
- `email` (string, unique) - Email address for authentication
- `mobile_number` (string, nullable) - Mobile contact number
- `password` (string, hashed) - Bcrypt hashed password

**Role & Status**
- `role_id` (integer) - Legacy role ID (1=Admin, 2=TA/TP) - use Spatie roles instead
- `status` (integer) - Active (1) / Inactive (0)

**Verification & Security**
- `email_verified_at` (datetime, nullable) - Email verification timestamp
- `is_protected` (boolean) - Prevent accidental deletion
- `protected_reason` (string, nullable) - Why user is protected

**Audit**
- `created_at`, `updated_at`, `deleted_at` - Soft delete timestamps
- `created_by`, `updated_by`, `deleted_by` - User audit trail

### Traits Used

```php
use Auditable;                   // Comprehensive audit logging
use HasApiTokens;                // Laravel Sanctum token authentication
use HasRoles;                    // Spatie Permission roles
use HasSecuritySettings;         // Security configurations
use HasTwoFactorAuth;            // 2FA support
use LogsActivity;                // Spatie Activity Log
use Notifiable;                  // Laravel notifications
use ProtectedRecord;             // Prevent deletion of protected records
use SoftDeletes;                 // Soft delete support
use TableRecordObserver;         // Auto-fill created_by/updated_by
```

### Relationships

```php
// User has many roles (Spatie Permission)
public function roles(): BelongsToMany
{
    return $this->belongsToMany(Role::class, 'model_has_roles');
}

// User has many permissions (direct, bypass roles)
public function permissions(): BelongsToMany
{
    return $this->belongsToMany(Permission::class, 'model_has_permissions');
}

// User has one security setting
public function securitySettings(): HasOne
{
    return $this->hasOne(SecuritySetting::class);
}

// User has one two-factor auth
public function twoFactorAuth(): HasOne
{
    return $this->hasOne(TwoFactorAuth::class);
}

// User has many trusted devices
public function trustedDevices(): HasMany
{
    return $this->hasMany(TrustedDevice::class);
}

// User has many audit logs
public function auditLogs(): HasMany
{
    return $this->hasMany(AuditLog::class);
}
```

### Helper Methods

**Full Name**:
```php
protected function getFullNameAttribute(): string
{
    return "{$this->first_name} {$this->last_name}";
}

// Usage: $user->full_name → "John Smith"
```

**Password Verification**:
```php
public function checkPassword(string $password): bool
{
    return Hash::check($password, $this->password);
}
```

## Spatie Permission System

### Core Concepts

**Roles**: Groups of permissions (e.g., "Admin", "Tenant Admin", "Relationship Manager")
**Permissions**: Specific actions (e.g., "customer-create", "policy-edit", "claim-delete")
**Guards**: Authentication contexts (`web` for staff, `customer` for customers)

### Permission Naming Convention

Format: `{module}-{action}`

Examples:
- `customer-list`, `customer-create`, `customer-edit`, `customer-delete`
- `policy-list`, `policy-create`, `policy-edit`, `policy-delete`
- `claim-list`, `claim-create`, `claim-edit`, `claim-delete`
- `user-list`, `user-create`, `user-edit`, `user-delete`
- `role-list`, `role-create`, `role-edit`, `role-delete`

### Database Tables (Spatie)

**roles** - Role definitions
**permissions** - Permission definitions
**model_has_roles** - User ↔ Role pivot
**model_has_permissions** - User ↔ Permission pivot (direct assignment)
**role_has_permissions** - Role ↔ Permission pivot

## RoleService

**File**: `app/Services/RoleService.php`

### Core Methods

**Create Role with Permissions**:
```php
public function createRole(array $data): Role
{
    return $this->createInTransaction(function() use ($data) {
        // Create role
        $role = $this->roleRepository->create([
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? 'web',
        ]);

        // Assign permissions
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    });
}
```

**Update Role**:
```php
public function updateRole(Role $role, array $data): bool
{
    return $this->updateInTransaction(function() use ($role, $data) {
        // Update role name/guard
        $role = $this->roleRepository->update($role, [
            'name' => $data['name'],
            'guard_name' => $data['guard_name'] ?? $role->guard_name,
        ]);

        // Sync permissions
        if (isset($data['permissions']) && is_array($data['permissions'])) {
            $role->syncPermissions($data['permissions']);
        }

        return $role;
    });
}
```

**Delete Role**:
```php
public function deleteRole(Role $role): bool
{
    return $this->deleteInTransaction(function() use ($role) {
        // Remove role from all users
        $role->users()->detach();

        // Remove all permissions from role
        $role->permissions()->detach();

        // Delete role
        return $this->roleRepository->delete($role);
    });
}
```

**Assign/Remove Permissions**:
```php
public function assignPermissionsToRole(int $roleId, array $permissionIds): bool
{
    $role = $this->roleRepository->findById($roleId);
    $role->givePermissionTo($permissionIds);
    return true;
}

public function removePermissionsFromRole(int $roleId, array $permissionIds): bool
{
    $role = $this->roleRepository->findById($roleId);
    $role->revokePermissionTo($permissionIds);
    return true;
}
```

**Assign/Remove Roles to User**:
```php
public function assignRoleToUser(int $userId, int $roleId): bool
{
    $user = $this->userRepository->findById($userId);
    $role = $this->roleRepository->findById($roleId);

    $user->assignRole($role);
    return true;
}

public function removeRoleFromUser(int $userId, int $roleId): bool
{
    $user = $this->userRepository->findById($userId);
    $role = $this->roleRepository->findById($roleId);

    $user->removeRole($role);
    return true;
}
```

## UserService

**File**: `app/Services/UserService.php`

### Key Methods

**Create User**:
```php
public function createUser(array $data): User
{
    return $this->createInTransaction(function() use ($data) {
        $user = User::create([
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'] ?? null,
            'email' => $data['email'],
            'mobile_number' => $data['mobile_number'] ?? null,
            'password' => Hash::make($data['password']),
            'status' => $data['status'] ?? 1,
        ]);

        // Assign role if provided
        if (isset($data['role_id'])) {
            $role = Role::findById($data['role_id']);
            $user->assignRole($role);
        }

        return $user;
    });
}
```

**Assign Roles**:
```php
public function assignRoles(User $user, array $roleIds): void
{
    // Remove existing roles
    $user->roles()->detach();

    // Assign new roles
    foreach ($roleIds as $roleId) {
        $role = Role::findById($roleId);
        $user->assignRole($role);
    }
}
```

**Change Password**:
```php
public function changePassword(User $user, string $newPassword): bool
{
    return $user->update([
        'password' => Hash::make($newPassword),
    ]);
}
```

## UserController

**File**: `app/Http/Controllers/UserController.php`

### Routes

| Method | Route | Action | Permission |
|--------|-------|--------|------------|
| GET | `/users` | `index()` | user-list |
| GET | `/users/create` | `create()` | user-create |
| POST | `/users` | `store()` | user-create |
| GET | `/users/{id}/edit` | `edit()` | user-edit |
| PUT | `/users/{id}` | `update()` | user-edit |
| PUT | `/users/{id}/status` | `updateStatus()` | user-edit |
| DELETE | `/users/{id}` | `delete()` | user-delete |

### Permission Middleware Setup

```php
public function __construct(private UserServiceInterface $userService)
{
    // Auto-setup permission middleware for all actions
    $this->setupPermissionMiddleware('user');
}

// Expands to:
// index: user-list|user-create|user-edit|user-delete
// create, store: user-create
// edit, update: user-edit
// delete: user-delete
```

## Permission Checking

### In Controllers (Middleware)

**Auto-Setup**:
```php
protected function setupPermissionMiddleware(string $module)
{
    $this->middleware("permission:{$module}-list|{$module}-create|{$module}-edit|{$module}-delete")
        ->only(['index']);
    $this->middleware("permission:{$module}-create")->only(['create', 'store']);
    $this->middleware("permission:{$module}-edit")->only(['edit', 'update']);
    $this->middleware("permission:{$module}-delete")->only(['delete']);
}
```

**Custom Setup**:
```php
protected function setupCustomPermissionMiddleware(array $rules)
{
    foreach ($rules as $rule) {
        $this->middleware("permission:{$rule['permission']}")
            ->only($rule['only']);
    }
}

// Usage:
$this->setupCustomPermissionMiddleware([
    ['permission' => 'claim-list|claim-create', 'only' => ['index']],
    ['permission' => 'claim-create', 'only' => ['create', 'store']],
]);
```

### In Blade Templates

**Check Permission**:
```blade
@can('customer-create')
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        Add Customer
    </a>
@endcan

@cannot('customer-delete')
    <span class="text-muted">Delete not allowed</span>
@endcannot
```

**Check Role**:
```blade
@role('Admin')
    <li class="nav-item">
        <a href="{{ route('users.index') }}">User Management</a>
    </li>
@endrole

@hasanyrole('Admin|Tenant Admin')
    <li class="nav-item">
        <a href="{{ route('settings.index') }}">Settings</a>
    </li>
@endhasanyrole
```

### In PHP Code

**Check Permission**:
```php
if ($user->can('customer-edit')) {
    // User has permission
}

if ($user->hasPermissionTo('claim-delete')) {
    // User has permission
}

// Check multiple permissions (OR logic)
if ($user->hasAnyPermission(['customer-edit', 'customer-delete'])) {
    // User has at least one permission
}

// Check multiple permissions (AND logic)
if ($user->hasAllPermissions(['customer-edit', 'customer-delete'])) {
    // User has all permissions
}
```

**Check Role**:
```php
if ($user->hasRole('Admin')) {
    // User is Admin
}

if ($user->hasAnyRole(['Admin', 'Tenant Admin'])) {
    // User has at least one role
}

if ($user->hasAllRoles(['Admin', 'Manager'])) {
    // User has all roles
}
```

**Get Permissions/Roles**:
```php
$permissions = $user->permissions; // Direct permissions
$roles = $user->roles; // Assigned roles
$allPermissions = $user->getAllPermissions(); // Direct + from roles

foreach ($user->roles as $role) {
    echo $role->name; // "Admin", "Tenant Admin"
}
```

## Database Schema

### users Table

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(255) NOT NULL,
    last_name VARCHAR(255) NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    mobile_number VARCHAR(20) NULL,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role_id INTEGER DEFAULT 1, -- Legacy, use Spatie roles
    status INTEGER DEFAULT 1,
    is_protected BOOLEAN DEFAULT FALSE,
    protected_reason TEXT NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    deleted_by BIGINT UNSIGNED NULL,

    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_role_id (role_id)
);
```

### Spatie Permission Tables

**roles**:
```sql
CREATE TABLE roles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY roles_name_guard_name_unique (name, guard_name)
);
```

**permissions**:
```sql
CREATE TABLE permissions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    guard_name VARCHAR(255) NOT NULL DEFAULT 'web',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    UNIQUE KEY permissions_name_guard_name_unique (name, guard_name)
);
```

**model_has_roles** (User ↔ Role):
```sql
CREATE TABLE model_has_roles (
    role_id BIGINT UNSIGNED NOT NULL,
    model_type VARCHAR(255) NOT NULL,
    model_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (role_id, model_id, model_type),
    INDEX model_has_roles_model_id_model_type_index (model_id, model_type),
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

**role_has_permissions** (Role ↔ Permission):
```sql
CREATE TABLE role_has_permissions (
    permission_id BIGINT UNSIGNED NOT NULL,
    role_id BIGINT UNSIGNED NOT NULL,

    PRIMARY KEY (permission_id, role_id),
    FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE,
    FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE
);
```

## Usage Examples

### Example 1: Create User with Role

```php
use App\Services\UserService;
use Spatie\Permission\Models\Role;

$userService = app(UserService::class);

// Create user
$data = [
    'first_name' => 'John',
    'last_name' => 'Smith',
    'email' => 'john.smith@example.com',
    'mobile_number' => '9876543210',
    'password' => 'SecureP@ss123',
    'status' => 1,
];

$user = $userService->createUser($data);

// Assign "Tenant Admin" role
$role = Role::findByName('Tenant Admin', 'web');
$user->assignRole($role);

// Or assign by role ID
$userService->assignRoles($user, [2]); // Role ID 2
```

### Example 2: Create Role with Permissions

```php
use App\Services\RoleService;
use Spatie\Permission\Models\Permission;

$roleService = app(RoleService::class);

// Create "Relationship Manager" role
$data = [
    'name' => 'Relationship Manager',
    'guard_name' => 'web',
    'permissions' => [
        'customer-list',
        'customer-create',
        'customer-edit',
        'lead-list',
        'lead-create',
        'lead-edit',
        'lead-convert',
        'quotation-list',
        'quotation-create',
    ],
];

$role = $roleService->createRole($data);

echo "Role created: {$role->name}\n";
echo "Permissions: " . $role->permissions()->count() . "\n"; // 9
```

### Example 3: Check Permissions

```php
$user = User::find(1);

// Single permission check
if ($user->can('customer-create')) {
    // Allow customer creation
}

// Multiple permissions (OR logic)
if ($user->hasAnyPermission(['customer-edit', 'customer-delete'])) {
    // Show edit/delete buttons
}

// Multiple permissions (AND logic)
if ($user->hasAllPermissions(['claim-create', 'claim-edit'])) {
    // Allow comprehensive claim management
}

// Check via role
if ($user->hasRole('Admin')) {
    // Admin-only functionality
}
```

### Example 4: Manage Role Permissions

```php
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

$role = Role::findByName('Tenant Admin', 'web');

// Add permissions
$role->givePermissionTo('policy-create');
$role->givePermissionTo(['policy-edit', 'policy-delete']);

// Remove permissions
$role->revokePermissionTo('customer-delete');

// Sync permissions (replace all)
$role->syncPermissions([
    'customer-list',
    'customer-create',
    'customer-edit',
    'policy-list',
    'policy-create',
    'claim-list',
]);

// Get all permissions
$permissions = $role->permissions; // Collection
foreach ($permissions as $permission) {
    echo "{$permission->name}\n";
}
```

### Example 5: Direct User Permissions (Bypass Roles)

```php
$user = User::find(10);

// Give direct permission (bypasses roles)
$user->givePermissionTo('quotation-approve'); // Special permission

// Remove direct permission
$user->revokePermissionTo('quotation-approve');

// Check all permissions (direct + from roles)
$allPermissions = $user->getAllPermissions();
// Returns: Direct permissions + all permissions from assigned roles

// Check only direct permissions
$directPermissions = $user->permissions; // Without role permissions
```

### Example 6: Permission Middleware in Routes

```php
// routes/web.php

// Single permission
Route::get('/customers', [CustomerController::class, 'index'])
    ->middleware('permission:customer-list');

// Multiple permissions (OR logic)
Route::get('/claims', [ClaimController::class, 'index'])
    ->middleware('permission:claim-list|claim-create|claim-edit');

// Role-based access
Route::get('/settings', [SettingController::class, 'index'])
    ->middleware('role:Admin');

// Multiple roles (OR logic)
Route::get('/reports', [ReportController::class, 'index'])
    ->middleware('role:Admin|Tenant Admin');

// Group with permission
Route::middleware(['auth', 'permission:user-list'])->group(function() {
    Route::get('/users', [UserController::class, 'index']);
    Route::get('/users/{id}', [UserController::class, 'show']);
});
```

### Example 7: Blade Template Authorization

```blade
{{-- Check permission --}}
@can('customer-create')
    <a href="{{ route('customers.create') }}" class="btn btn-primary">
        <i class="fas fa-plus"></i> Add Customer
    </a>
@endcan

{{-- Check role --}}
@role('Admin')
    <li class="nav-item">
        <a href="{{ route('users.index') }}">
            <i class="fas fa-users"></i> User Management
        </a>
    </li>
@endrole

{{-- Check any role --}}
@hasanyrole('Admin|Tenant Admin')
    <li class="nav-item">
        <a href="{{ route('settings.index') }}">
            <i class="fas fa-cog"></i> Settings
        </a>
    </li>
@endhasanyrole

{{-- Check permission for specific user --}}
@if(auth()->user()->can('claim-delete'))
    <form action="{{ route('claims.delete', $claim) }}" method="POST">
        @csrf
        @method('DELETE')
        <button type="submit" class="btn btn-danger">Delete</button>
    </form>
@endif

{{-- Multiple permission check (OR) --}}
@canany(['customer-edit', 'customer-delete'])
    <div class="action-buttons">
        @can('customer-edit')
            <a href="{{ route('customers.edit', $customer) }}">Edit</a>
        @endcan
        @can('customer-delete')
            <form action="{{ route('customers.delete', $customer) }}" method="POST">
                @csrf
                @method('DELETE')
                <button>Delete</button>
            </form>
        @endcan
    </div>
@endcanany
```

### Example 8: Protected Records

```php
// Prevent deletion of critical users
$admin = User::find(1);
$admin->update([
    'is_protected' => true,
    'protected_reason' => 'System administrator - cannot be deleted',
]);

// In UserService or controller
if ($user->is_protected) {
    throw new \Exception("Cannot delete protected user: {$user->protected_reason}");
}

// Protected records are checked via ProtectedRecord trait
// Automatically prevents deletion in delete() method
```

## Permission List

### Customer Module
- `customer-list` - View customer list
- `customer-create` - Create new customer
- `customer-edit` - Edit existing customer
- `customer-delete` - Delete customer

### Lead Module
- `lead-list` - View lead list
- `lead-create` - Create new lead
- `lead-edit` - Edit existing lead
- `lead-delete` - Delete lead
- `lead-convert` - Convert lead to customer

### Policy Module
- `policy-list` - View policy list
- `policy-create` - Create new policy
- `policy-edit` - Edit existing policy
- `policy-delete` - Delete policy
- `policy-renew` - Renew policy

### Quotation Module
- `quotation-list` - View quotation list
- `quotation-create` - Create new quotation
- `quotation-edit` - Edit existing quotation
- `quotation-delete` - Delete quotation
- `quotation-send` - Send quotation via WhatsApp/Email

### Claim Module
- `claim-list` - View claim list
- `claim-create` - Create new claim
- `claim-edit` - Edit existing claim
- `claim-delete` - Delete claim

### User Module
- `user-list` - View user list
- `user-create` - Create new user
- `user-edit` - Edit existing user
- `user-delete` - Delete user

### Role Module
- `role-list` - View role list
- `role-create` - Create new role
- `role-edit` - Edit existing role
- `role-delete` - Delete role

## Related Documentation

- **[TWO_FACTOR_AUTHENTICATION.md](../features/TWO_FACTOR_AUTHENTICATION.md)** - 2FA implementation
- **[SECURITY_SETTINGS.md](../features/SECURITY_SETTINGS.md)** - Per-user security config
- **[AUDIT_LOG.md](../features/AUDIT_LOG.md)** - Activity logging system
- **[MULTI_TENANCY.md](../core/MULTI_TENANCY.md)** - Tenant-scoped user management

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
