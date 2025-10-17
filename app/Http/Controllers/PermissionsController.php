<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

/**
 * Permissions Controller
 *
 * Handles Permission CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class PermissionsController extends AbstractBaseCrudController
{
    public function __construct(/**
     * Permission Repository instance
     */
        private readonly PermissionRepositoryInterface $permissionRepository)
    {
        $this->setupCustomPermissionMiddleware([
            ['permission' => 'permission-list|permission-create|permission-edit|permission-delete', 'only' => ['index']],
            ['permission' => 'permission-create', 'only' => ['create', 'store']],
            ['permission' => 'permission-edit', 'only' => ['edit', 'update']],
            ['permission' => 'permission-delete', 'only' => ['destroy']],
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $lengthAwarePaginator = $this->permissionRepository->getPermissionsWithFilters($request, 10);

        return view('permissions.index', [
            'permissions' => $lengthAwarePaginator,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        return view('permissions.add');
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
            $this->permissionRepository->create($request->all());
            DB::commit();

            return $this->redirectWithSuccess('permissions.index',
                $this->getSuccessMessage('Permission', 'created'));
        } catch (\Throwable $throwable) {
            DB::rollback();

            return $this->redirectWithError(
                $this->getErrorMessage('Permission', 'create').': '.$throwable->getMessage());
        }
    }

    /**
     * Display the specified resource.
     *
     * @return Response
     */
    public function show(int $id)
    {
        $permission = $this->permissionRepository->getPermissionWithRoles($id);

        if (! $permission instanceof Permission) {
            return $this->redirectWithError(
                $this->getErrorMessage('Permission', 'find'));
        }

        return view('permissions.show', ['permission' => $permission]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return Response
     */
    public function edit(int $id)
    {
        $permission = $this->permissionRepository->findById($id);

        if (! $permission instanceof Model) {
            return $this->redirectWithError(
                $this->getErrorMessage('Permission', 'find'));
        }

        return view('permissions.edit', ['permission' => $permission]);
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

            $permission = $this->permissionRepository->findById($id);
            if (! $permission instanceof Model) {
                return $this->redirectWithError(
                    $this->getErrorMessage('Permission', 'find'));
            }

            DB::beginTransaction();
            $this->permissionRepository->update($permission, $request->only(['name', 'guard_name']));
            DB::commit();

            return $this->redirectWithSuccess('permissions.index',
                $this->getSuccessMessage('Permission', 'updated'));
        } catch (\Throwable $throwable) {
            DB::rollback();

            return $this->redirectWithError(
                $this->getErrorMessage('Permission', 'update').': '.$throwable->getMessage());
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
            $permission = $this->permissionRepository->findById($id);
            if (! $permission instanceof Model) {
                return $this->redirectWithError(
                    $this->getErrorMessage('Permission', 'find'));
            }

            DB::beginTransaction();
            $this->permissionRepository->delete($permission);
            DB::commit();

            return $this->redirectWithSuccess('permissions.index',
                $this->getSuccessMessage('Permission', 'deleted'));
        } catch (\Throwable $throwable) {
            DB::rollback();

            return $this->redirectWithError(
                $this->getErrorMessage('Permission', 'delete').': '.$throwable->getMessage());
        }
    }
}
