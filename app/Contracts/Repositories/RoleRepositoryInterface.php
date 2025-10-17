<?php

namespace App\Contracts\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

/**
 * Role Repository Interface
 *
 * Defines methods for Role data access operations using Spatie Permission.
 * Extends BaseRepositoryInterface for common CRUD operations.
 */
interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Get paginated list of roles with filtering and search
     */
    public function getRolesWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator;

    /**
     * Get role with permissions loaded
     */
    public function getRoleWithPermissions(int $roleId): ?Role;

    /**
     * Get roles with permissions count
     */
    public function getRolesWithPermissionsCount(): Collection;

    /**
     * Search roles by name
     */
    public function searchRoles(string $searchTerm, int $limit = 20): Collection;

    /**
     * Get roles for specific guard
     */
    public function getRolesByGuard(string $guardName = 'web'): Collection;

    /**
     * Get role statistics
     */
    public function getRoleStatistics(): array;

    /**
     * Get roles assigned to specific model
     */
    public function getRolesByModel(string $modelType, int $modelId): Collection;
}
