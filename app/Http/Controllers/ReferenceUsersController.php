<?php

namespace App\Http\Controllers;

use App\Models\ReferenceUser;
use App\Services\ReferenceUserService;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

/**
 * Reference Users Controller
 *
 * Handles ReferenceUser CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class ReferenceUsersController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(
        private ReferenceUserService $referenceUserService
    ) {
        $this->setupPermissionMiddleware('reference-user');
    }

    /**
     * List ReferenceUsers
     *
     * @return View
     */
    public function index(Request $request)
    {
        $query = ReferenceUser::query();

        if ($request->filled('search')) {
            $searchTerm = '%'.trim($request->search).'%';
            $query->where('name', 'LIKE', $searchTerm)
                ->orWhere('email', 'LIKE', $searchTerm)
                ->orWhere('mobile_number', 'LIKE', $searchTerm);
        }

        $reference_users = $query->paginate(pagination_per_page());

        return view('reference_users.index', ['reference_users' => $reference_users]);
    }

    /**
     * Create ReferenceUser
     *
     * @return View
     */
    public function create()
    {
        return view('reference_users.add');
    }

    /**
     * Store ReferenceUser
     *
     * @return RedirectResponse
     */
    public function store(Request $request)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
            'mobile_number' => 'nullable|string|max:15',
        ];

        $validated = $request->validate($validationRules);

        try {
            $this->referenceUserService->createReferenceUser($validated);

            return $this->redirectWithSuccess('reference_users.index', $this->getSuccessMessage('Reference User', 'created'));
        } catch (Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Reference User', 'create').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Status Of ReferenceUser
     */
    public function updateStatus(int $reference_user_id, int $status): RedirectResponse
    {
        $validationRules = [
            'reference_user_id' => 'required|exists:reference_users,id',
            'status' => 'required|in:0,1',
        ];

        $validator = Validator::make([
            'reference_user_id' => $reference_user_id,
            'status' => $status,
        ], $validationRules);

        if ($validator->fails()) {
            return $this->redirectWithError($validator->errors()->first());
        }

        try {
            $this->referenceUserService->updateStatus($reference_user_id, $status);

            return $this->redirectWithSuccess('reference_users.index', $this->getSuccessMessage('Reference User Status', 'updated'));
        } catch (Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Reference User Status', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Edit ReferenceUser
     *
     * @return View
     */
    public function edit(ReferenceUser $referenceUser)
    {
        return view('reference_users.edit', ['reference_user' => $referenceUser]);
    }

    /**
     * Update ReferenceUser
     *
     * @return RedirectResponse
     */
    public function update(Request $request, ReferenceUser $referenceUser)
    {
        $validationRules = [
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:100',
            'mobile_number' => 'nullable|string|max:15',
        ];

        $validated = $request->validate($validationRules);

        try {
            $this->referenceUserService->updateReferenceUser($referenceUser, $validated);

            return $this->redirectWithSuccess('reference_users.index', $this->getSuccessMessage('Reference User', 'updated'));
        } catch (Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Reference User', 'update').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete ReferenceUser
     */
    public function delete(ReferenceUser $referenceUser): RedirectResponse
    {
        try {
            $this->referenceUserService->deleteReferenceUser($referenceUser);

            return $this->redirectWithSuccess('reference_users.index', $this->getSuccessMessage('Reference User', 'deleted'));
        } catch (Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Reference User', 'delete').': '.$throwable->getMessage());
        }
    }

    /**
     * Import ReferenceUsers
     *
     * @return View
     */
    public function importReferenceUsers()
    {
        return view('reference_users.import');
    }

    protected function getExportRelations(): array
    {
        return [];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'email', 'mobile_number'];
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => 'reference_users',
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'headings' => ['ID', 'Name', 'Email', 'Mobile Number', 'Status', 'Created Date'],
            'mapping' => fn ($model): array => [
                $model->id,
                $model->name,
                $model->email ?? 'N/A',
                $model->mobile_number ?? 'N/A',
                $model->status ? 'Active' : 'Inactive',
                $model->created_at->format('Y-m-d H:i:s'),
            ],
            'with_mapping' => true,
        ];
    }
}
