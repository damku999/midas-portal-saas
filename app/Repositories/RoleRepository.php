<?php

namespace App\Repositories;

use App\Contracts\Repositories\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Role;

/**
 * Role Repository
 *
 * Handles Role data access operations using Spatie Permission.
 * Inherits common CRUD operations from AbstractBaseRepository.
 */
class RoleRepository extends AbstractBaseRepository implements RoleRepositoryInterface
{
    /**
     * The model class name
     */
    protected string $modelClass = Role::class;

    /**
     * Searchable fields for the getPaginated method
     */
    protected array $searchableFields = [
        'name', 'guard_name',
    ];

    /**
     * Get paginated list of roles with filtering and search
     */
    public function getRolesWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = Role::withCount('permissions');

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('guard_name', 'like', "%{$search}%");
            });
        }

        // Guard filter
        if ($request->filled('guard')) {
            $query->where('guard_name', $request->guard);
        }

        return $query->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Get role with permissions loaded
     */
    public function getRoleWithPermissions(int $roleId): ?Role
    {
        return Role::with('permissions')->find($roleId);
    }

    /**
     * Get roles with permissions count
     */
    public function getRolesWithPermissionsCount(): Collection
    {
        return Role::withCount('permissions')
            ->orderBy('name')
            ->get();
    }

    /**
     * Search roles by name
     */
    public function searchRoles(string $searchTerm, int $limit = 20): Collection
    {
        if (strlen($searchTerm) < 2) {
            return collect();
        }

        return Role::where('name', 'like', "%{$searchTerm}%")
            ->limit($limit)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get roles for specific guard
     */
    public function getRolesByGuard(string $guardName = 'web'): Collection
    {
        return Role::where('guard_name', $guardName)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get role statistics
     */
    public function getRoleStatistics(): array
    {
        return [
            'total_roles' => Role::count(),
            'web_guard_roles' => Role::where('guard_name', 'web')->count(),
            'api_guard_roles' => Role::where('guard_name', 'api')->count(),
            'roles_with_permissions' => Role::has('permissions')->count(),
            'roles_without_permissions' => Role::doesntHave('permissions')->count(),
            'total_role_permission_assignments' => \DB::table('role_has_permissions')->count(),
            'this_month_roles' => Role::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get roles assigned to specific model
     */
    public function getRolesByModel(string $modelType, int $modelId): Collection
    {
        return Role::whereHas('users', function ($query) use ($modelType, $modelId) {
            $query->where('model_type', $modelType)
                ->where('model_id', $modelId);
        })->get();
    }
}
