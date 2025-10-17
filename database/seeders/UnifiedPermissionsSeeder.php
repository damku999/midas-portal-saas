<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UnifiedPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Comprehensive permissions seeder for all modules in the application
     */
    public function run(): void
    {
        // Clear existing permissions to avoid duplicates
        Permission::where('guard_name', 'web')->delete();

        // Clear permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // User Management (4)
            'user-list',
            'user-create',
            'user-edit',
            'user-delete',

            // Role Management (4)
            'role-list',
            'role-create',
            'role-edit',
            'role-delete',

            // Permission Management (4)
            'permission-list',
            'permission-create',
            'permission-edit',
            'permission-delete',

            // Customer Management (4)
            'customer-list',
            'customer-create',
            'customer-edit',
            'customer-delete',

            // Customer Insurance (4)
            'customer-insurance-list',
            'customer-insurance-create',
            'customer-insurance-edit',
            'customer-insurance-delete',

            // Branch Management (4)
            'branch-list',
            'branch-create',
            'branch-edit',
            'branch-delete',

            // Broker Management (4)
            'broker-list',
            'broker-create',
            'broker-edit',
            'broker-delete',

            // Reference Users (4)
            'reference-user-list',
            'reference-user-create',
            'reference-user-edit',
            'reference-user-delete',

            // Relationship Managers (4)
            'relationship_manager-list',
            'relationship_manager-create',
            'relationship_manager-edit',
            'relationship_manager-delete',

            // Insurance Companies (4)
            'insurance_company-list',
            'insurance_company-create',
            'insurance_company-edit',
            'insurance_company-delete',

            // Premium Types (4)
            'premium-type-list',
            'premium-type-create',
            'premium-type-edit',
            'premium-type-delete',

            // Policy Types (4)
            'policy-type-list',
            'policy-type-create',
            'policy-type-edit',
            'policy-type-delete',

            // Fuel Types (4)
            'fuel-type-list',
            'fuel-type-create',
            'fuel-type-edit',
            'fuel-type-delete',

            // Addon Covers (4)
            'addon-cover-list',
            'addon-cover-create',
            'addon-cover-edit',
            'addon-cover-delete',

            // Claims Management (4)
            'claim-list',
            'claim-create',
            'claim-edit',
            'claim-delete',

            // Quotations (7)
            'quotation-list',
            'quotation-create',
            'quotation-edit',
            'quotation-delete',
            'quotation-generate',
            'quotation-send-whatsapp',
            'quotation-download-pdf',

            // Reports (1)
            'report-list',

            // App Settings (4)
            'app-setting-list',
            'app-setting-create',
            'app-setting-edit',
            'app-setting-delete',

            // Notification Templates (4)
            'notification-template-list',
            'notification-template-create',
            'notification-template-edit',
            'notification-template-delete',

            // Notification Types (4)
            'notification-type-list',
            'notification-type-create',
            'notification-type-edit',
            'notification-type-delete',

            // Notification Logs (4)
            'notification-log-list',
            'notification-log-view',
            'notification-log-resend',
            'notification-log-analytics',

            // Customer Devices (Push Notifications) (4)
            'customer-device-list',
            'customer-device-view',
            'customer-device-deactivate',
            'customer-device-cleanup',
        ];

        // Create all permissions
        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // Assign all permissions to admin role (ID 1)
        $this->assignPermissionsToAdminRole($permissions);

        $this->command->info('Created '.count($permissions).' permissions successfully.');
    }

    /**
     * Assign all permissions to admin role
     */
    private function assignPermissionsToAdminRole(array $permissions): void
    {
        $adminRole = Role::find(1);

        if ($adminRole) {
            $permissionObjects = Permission::whereIn('name', $permissions)->get();
            $adminRole->syncPermissions($permissionObjects);
            $this->command->info('Assigned all permissions to admin role.');
        } else {
            $this->command->warn('Admin role (ID: 1) not found. Permissions created but not assigned.');
        }
    }
}
