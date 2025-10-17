<?php

namespace App\Contracts\Services;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

/**
 * Role Service Interface
 *
 * Defines business logic operations for Role management.
 * Handles role operations, permission assignments, and user role management.
 */
interface RoleServiceInterface
{
    /**
     * Get paginated list of roles with filters
     */
    public function getRoles(Request $request, int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new role
     */
    public function createRole(array $data): Role;

    /**
     * Update an existing role
     */
    public function updateRole(Role $role, array $data): bool;

    /**
     * Delete a role
     */
    public function deleteRole(Role $role): bool;

    /**
     * Get all roles for assignment
     */
    public function getAllRoles(): Collection;

    /**
     * Get roles by user
     */
    public function getRolesByUser(int $userId): Collection;

    /**
     * Search roles by name
     */
    public function searchRoles(string $searchTerm, int $limit = 20): Collection;

    /**
     * Get role statistics
     */
    public function getRoleStatistics(): array;

    /**
     * Assign permissions to role
     */
    public function assignPermissionsToRole(int $roleId, array $permissionIds): bool;

    /**
     * Remove permissions from role
     */
    public function removePermissionsFromRole(int $roleId, array $permissionIds): bool;

    /**
     * Assign role to user
     */
    public function assignRoleToUser(int $userId, int $roleId): bool;

    /**
     * Remove role from user
     */
    public function removeRoleFromUser(int $userId, int $roleId): bool;

    /**
     * Get role with permissions
     */
    public function getRoleWithPermissions(int $roleId): ?Role;

    /**
     * Get users count by role
     */
    public function getUsersCountByRole(int $roleId): int;
}
