<?php

namespace App\Contracts\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

/**
 * Permission Service Interface
 *
 * Defines business logic operations for Permission management.
 * Handles permission operations, role assignments, and access control.
 */
interface PermissionServiceInterface
{
    /**
     * Get paginated list of permissions with filters
     */
    public function getPermissions(Request $request, int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new permission
     */
    public function createPermission(array $data): Permission;

    /**
     * Update an existing permission
     */
    public function updatePermission(Permission $permission, array $data): bool;

    /**
     * Delete a permission
     */
    public function deletePermission(Permission $permission): bool;

    /**
     * Get all permissions for role assignment
     */
    public function getAllPermissions(): Collection;

    /**
     * Get permissions by role
     */
    public function getPermissionsByRole(int $roleId): Collection;

    /**
     * Search permissions by name
     */
    public function searchPermissions(string $searchTerm, int $limit = 20): Collection;

    /**
     * Get permission statistics
     */
    public function getPermissionStatistics(): array;

    /**
     * Sync permissions for a role
     */
    public function syncRolePermissions(int $roleId, array $permissionIds): bool;

    /**
     * Get all permissions grouped by module
     */
    public function getPermissionsGroupedByModule(): array;
}
