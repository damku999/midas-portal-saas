<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use App\Contracts\Repositories\RoleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

/**
 * Roles Controller
 *
 * Handles Role CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class RolesController extends AbstractBaseCrudController
{
    public function __construct(
        /**
         * Role Repository instance
         */
        private readonly RoleRepositoryInterface $roleRepository,
        /**
         * Permission Repository instance
         */
        private readonly PermissionRepositoryInterface $permissionRepository
    ) {
        $this->setupCustomPermissionMiddleware([
            ['permission' => 'role-list|role-create|role-edit|role-delete', 'only' => ['index']],
            ['permission' => 'role-create', 'only' => ['create', 'store']],
            ['permission' => 'role-edit', 'only' => ['edit', 'update']],
            ['permission' => 'role-delete', 'only' => ['destroy']],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $lengthAwarePaginator = $this->roleRepository->getRolesWithFilters($request, 10);

        return view('roles.index', [
            'roles' => $lengthAwarePaginator,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $permissionsByGuard = $this->permissionRepository->getPermissionsByGuard();

        return view('roles.add', ['permissions' => $permissionsByGuard]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required',
                'guard_name' => 'required',
            ]);

            DB::beginTransaction();
            $this->roleRepository->create($request->all());
            DB::commit();

            return $this->redirectWithSuccess('roles.index',
                $this->getSuccessMessage('Role', 'created'));
        } catch (\Throwable $throwable) {
            DB::rollback();

            return $this->redirectWithError(
                $this->getErrorMessage('Role', 'create').': '.$throwable->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(int $id)
    {
        $role = $this->roleRepository->getRoleWithPermissions($id);

        if (! $role instanceof Role) {
            return $this->redirectWithError('Role not found.');
        }

        return view('roles.show', ['role' => $role]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(int $id)
    {
        $role = $this->roleRepository->getRoleWithPermissions($id);

        if (! $role instanceof Role) {
            return $this->redirectWithError('Role not found.');
        }

        $permissions = $this->permissionRepository->getPermissionsByGuard($role->guard_name);

        return view('roles.edit', ['role' => $role, 'permissions' => $permissions]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @return Response
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        try {
            $request->validate([
                'name' => 'required',
                'guard_name' => 'required',
            ]);

            DB::beginTransaction();
            $role = $this->roleRepository->findById($id);

            if (! $role instanceof Model) {
                DB::rollback();

                return $this->redirectWithError('Role not found.');
            }

            // Update role data
            $roleData = [
                'name' => $request->name,
                'guard_name' => $request->guard_name,
            ];
            $this->roleRepository->update($role, $roleData);

            // Sync Permissions - using the model's native method
            $permissions = $request->permissions ?? [];
            $role->syncPermissions($permissions);
            DB::commit();

            return $this->redirectWithSuccess('roles.index',
                $this->getSuccessMessage('Role', 'updated'));
        } catch (\Throwable $throwable) {
            DB::rollback();

            return $this->redirectWithError(
                $this->getErrorMessage('Role', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @return Response
     */
    public function destroy(int $id): RedirectResponse
    {
        try {
            DB::beginTransaction();
            $role = $this->roleRepository->findById($id);

            if (! $role instanceof Model) {
                DB::rollback();

                return $this->redirectWithError('Role not found.');
            }

            $this->roleRepository->delete($role);
            DB::commit();

            return $this->redirectWithSuccess('roles.index',
                $this->getSuccessMessage('Role', 'deleted'));
        } catch (\Throwable $throwable) {
            DB::rollback();

            return $this->redirectWithError(
                $this->getErrorMessage('Role', 'delete').': '.$throwable->getMessage());
        }
    }
}
