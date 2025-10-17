<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

/**
 * Permission Repository Interface
 *
 * Defines methods for Permission data access operations using Spatie Permission.
 * Extends BaseRepositoryInterface for common CRUD operations.
 */
interface PermissionRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get paginated list of permissions with filtering and search
     */
    public function getPermissionsWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get permission with roles loaded
     */
    public function getPermissionWithRoles(int $permissionId): ?Permission;

    /**
     * Get permissions with roles count
     */
    public function getPermissionsWithRolesCount(): Collection;

    /**
     * Search permissions by name
     */
    public function searchPermissions(string $searchTerm, int $limit = 20): Collection;

    /**
     * Get permissions for specific guard
     */
    public function getPermissionsByGuard(string $guardName = 'web'): Collection;

    /**
     * Get permissions by module/prefix
     */
    public function getPermissionsByModule(string $module): Collection;

    /**
     * Get permission statistics
     */
    public function getPermissionStatistics(): array;

    /**
     * Get permissions assigned to specific role
     */
    public function getPermissionsByRole(int $roleId): Collection;

    /**
     * Get permissions not assigned to specific role
     */
    public function getUnassignedPermissions(int $roleId, string $guardName = 'web'): Collection;
}
