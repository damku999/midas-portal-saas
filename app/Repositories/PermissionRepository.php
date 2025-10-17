<?php

namespace App\Repositories;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\Permission\Models\Permission;

/**
 * Permission Repository
 *
 * Handles Permission data access operations using Spatie Permission.
 * Inherits common CRUD operations from AbstractBaseRepository.
 */
class PermissionRepository extends AbstractBaseRepository implements PermissionRepositoryInterface
{
    /**
     * The model class name
     */
    protected string $modelClass = Permission::class;

    /**
     * Searchable fields for the getPaginated method
     */
    protected array $searchableFields = [
        'name', 'guard_name',
    ];

    /**
     * Get paginated list of permissions with filtering and search
     */
    public function getPermissionsWithFilters(Request $request, int $perPage = 15): LengthAwarePaginator
    {
        $query = Permission::withCount('roles');

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

        // Module filter (based on permission name prefix)
        if ($request->filled('module')) {
            $query->where('name', 'like', $request->module.'.%');
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Get permission with roles loaded
     */
    public function getPermissionWithRoles(int $permissionId): ?Permission
    {
        return Permission::with('roles')->find($permissionId);
    }

    /**
     * Get permissions with roles count
     */
    public function getPermissionsWithRolesCount(): Collection
    {
        return Permission::withCount('roles')
            ->orderBy('name')
            ->get();
    }

    /**
     * Search permissions by name
     */
    public function searchPermissions(string $searchTerm, int $limit = 20): Collection
    {
        if (strlen($searchTerm) < 2) {
            return collect();
        }

        return Permission::where('name', 'like', "%{$searchTerm}%")
            ->limit($limit)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get permissions for specific guard
     */
    public function getPermissionsByGuard(string $guardName = 'web'): Collection
    {
        return Permission::where('guard_name', $guardName)
            ->orderBy('name')
            ->get();
    }

    /**
     * Get permissions by module/prefix
     */
    public function getPermissionsByModule(string $module): Collection
    {
        return Permission::where('name', 'like', $module.'.%')
            ->orderBy('name')
            ->get();
    }

    /**
     * Get permission statistics
     */
    public function getPermissionStatistics(): array
    {
        $moduleStats = Permission::selectRaw("
            SUBSTRING_INDEX(name, '.', 1) as module,
            COUNT(*) as count
        ")
            ->groupBy('module')
            ->pluck('count', 'module')
            ->toArray();

        return [
            'total_permissions' => Permission::count(),
            'web_guard_permissions' => Permission::where('guard_name', 'web')->count(),
            'api_guard_permissions' => Permission::where('guard_name', 'api')->count(),
            'permissions_with_roles' => Permission::has('roles')->count(),
            'permissions_without_roles' => Permission::doesntHave('roles')->count(),
            'total_permission_role_assignments' => \DB::table('role_has_permissions')->count(),
            'permissions_by_module' => $moduleStats,
            'this_month_permissions' => Permission::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->count(),
        ];
    }

    /**
     * Get permissions assigned to specific role
     */
    public function getPermissionsByRole(int $roleId): Collection
    {
        return Permission::whereHas('roles', function ($query) use ($roleId) {
            $query->where('id', $roleId);
        })->orderBy('name')->get();
    }

    /**
     * Get permissions not assigned to specific role
     */
    public function getUnassignedPermissions(int $roleId, string $guardName = 'web'): Collection
    {
        return Permission::where('guard_name', $guardName)
            ->whereDoesntHave('roles', function ($query) use ($roleId) {
                $query->where('id', $roleId);
            })
            ->orderBy('name')
            ->get();
    }
}
