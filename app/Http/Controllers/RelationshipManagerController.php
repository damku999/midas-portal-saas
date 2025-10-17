<?php

namespace App\Http\Controllers;

use App\Models\RelationshipManager;
use App\Services\RelationshipManagerService;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Relationship Manager Controller
 *
 * Handles RelationshipManager CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class RelationshipManagerController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(
        private RelationshipManagerService $relationshipManagerService
    ) {
        $this->setupPermissionMiddleware('relationship_manager');
    }

    /**
     * List RelationshipManager
     *
     * @param Nill
     * @return array $relationship_manager
     *
     * @author Darshan Baraiya
     */
    public function index(Request $request)
    {
        $builder = RelationshipManager::query()->select('*');
        if (! empty($request->search)) {
            $builder->where('name', 'LIKE', '%'.trim((string) $request->search).'%')->orWhere('email', 'LIKE', '%'.trim((string) $request->search).'%')->orWhere('mobile_number', 'LIKE', '%'.trim((string) $request->search).'%');
        }

        $relationship_managers = $builder->paginate(config('app.pagination_default', 15));

        return view('relationship_managers.index', ['relationship_managers' => $relationship_managers]);
    }

    /**
     * Create RelationshipManager
     *
     * @param Nill
     * @return array $relationship_manager
     *
     * @author Darshan Baraiya
     */
    public function create()
    {
        return view('relationship_managers.add');
    }

    /**
     * Store RelationshipManager
     *
     * @return View RelationshipManagers
     *
     * @author Darshan Baraiya
     */
    public function store(Request $request)
    {
        // Validations
        $validation_array = [
            'name' => 'required',
        ];

        $validated = $request->validate($validation_array);

        try {
            $this->relationshipManagerService->createRelationshipManager($validated);

            return $this->redirectWithSuccess('relationship_managers.index', $this->getSuccessMessage('Relationship Manager', 'created'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Relationship Manager', 'create').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Status Of RelationshipManager
     *
     * @return List Page With Success
     *
     * @author Darshan Baraiya
     */
    public function updateStatus(int $relationship_manager_id, int $status): RedirectResponse
    {
        // Validation
        $validator = Validator::make([
            'relationship_manager_id' => $relationship_manager_id,
            'status' => $status,
        ], [
            'relationship_manager_id' => 'required|exists:relationship_managers,id',
            'status' => 'required|in:0,1',
        ]);

        // If Validations Fails
        if ($validator->fails()) {
            return $this->redirectWithError($validator->errors()->first());
        }

        try {
            $this->relationshipManagerService->updateStatus($relationship_manager_id, $status);

            return $this->redirectWithSuccess('relationship_managers.index', $this->getSuccessMessage('Relationship Manager Status', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Relationship Manager Status', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Edit RelationshipManager
     *
     * @param  int  $relationshipManager
     * @return Collection $relationship_manager
     *
     * @author Darshan Baraiya
     */
    public function edit(RelationshipManager $relationshipManager)
    {
        return view('relationship_managers.edit')->with([
            'relationship_manager' => $relationshipManager,
        ]);
    }

    /**
     * Update RelationshipManager
     *
     * @param  Request  $request,  RelationshipManager $relationship_manager
     * @return View RelationshipManagers
     *
     * @author Darshan Baraiya
     */
    public function update(Request $request, RelationshipManager $relationshipManager)
    {
        // Validations
        $validation_array = [
            'name' => 'required',
        ];

        $validated = $request->validate($validation_array);

        try {
            $this->relationshipManagerService->updateRelationshipManager($relationshipManager, $validated);

            return $this->redirectWithSuccess('relationship_managers.index', $this->getSuccessMessage('Relationship Manager', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Relationship Manager', 'update').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete RelationshipManager
     *
     * @return Index RelationshipManagers
     *
     * @author Darshan Baraiya
     */
    public function delete(RelationshipManager $relationshipManager): RedirectResponse
    {
        try {
            $this->relationshipManagerService->deleteRelationshipManager($relationshipManager);

            return $this->redirectWithSuccess('relationship_managers.index', $this->getSuccessMessage('Relationship Manager', 'deleted'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Relationship Manager', 'delete').': '.$throwable->getMessage());
        }
    }

    /**
     * Import RelationshipManagers
     *
     * @param null
     * @return View File
     */
    public function importRelationshipManagers()
    {
        return view('relationship_managers.import');
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
            'filename' => 'relationship_managers',
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
