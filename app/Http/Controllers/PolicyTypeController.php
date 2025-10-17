<?php

namespace App\Http\Controllers;

use App\Models\PolicyType;
use App\Services\PolicyTypeService;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Policy Type Controller
 *
 * Handles PolicyType CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class PolicyTypeController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(
        private PolicyTypeService $policyTypeService
    ) {
        $this->setupPermissionMiddleware('policy-type');
    }

    /**
     * List PolicyType
     *
     * @param Nill
     * @return array $policy_type
     *
     * @author Darshan Baraiya
     */
    public function index(Request $request)
    {
        $builder = PolicyType::query()->select('*');
        if (! empty($request->search)) {
            $builder->where('name', 'LIKE', '%'.trim((string) $request->search).'%');
        }

        $policy_type = $builder->paginate(config('app.pagination_default', 15));

        return view('policy_type.index', ['policy_type' => $policy_type]);
    }

    /**
     * Create PolicyType
     *
     * @param Nill
     * @return array $policy_type
     *
     * @author Darshan Baraiya
     */
    public function create()
    {
        return view('policy_type.add');
    }

    /**
     * Store PolicyType
     *
     * @return View PolicyTypes
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
            $this->policyTypeService->createPolicyType($validated);

            return $this->redirectWithSuccess('policy_type.index', $this->getSuccessMessage('Policy Type', 'created'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Policy Type', 'create').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Status Of PolicyType
     *
     * @return List Page With Success
     *
     * @author Darshan Baraiya
     */
    public function updateStatus(int $policy_type_id, int $status): RedirectResponse
    {
        // Validation
        $validator = Validator::make([
            'policy_type_id' => $policy_type_id,
            'status' => $status,
        ], [
            'policy_type_id' => 'required|exists:policy_types,id',
            'status' => 'required|in:0,1',
        ]);

        // If Validations Fails
        if ($validator->fails()) {
            return $this->redirectWithError($validator->errors()->first());
        }

        try {
            $this->policyTypeService->updateStatus($policy_type_id, $status);

            return $this->redirectWithSuccess('policy_type.index', $this->getSuccessMessage('Policy Type Status', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Policy Type Status', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Edit PolicyType
     *
     * @param  int  $policyType
     * @return Collection $policy_type
     *
     * @author Darshan Baraiya
     */
    public function edit(PolicyType $policyType)
    {
        return view('policy_type.edit')->with([
            'policy_type' => $policyType,
        ]);
    }

    /**
     * Update PolicyType
     *
     * @param  Request  $request,  PolicyType $policy_type
     * @return View PolicyTypes
     *
     * @author Darshan Baraiya
     */
    public function update(Request $request, PolicyType $policyType)
    {
        // Validations
        $validation_array = [
            'name' => 'required',
        ];

        $validated = $request->validate($validation_array);

        try {
            $this->policyTypeService->updatePolicyType($policyType, $validated);

            return $this->redirectWithSuccess('policy_type.index', $this->getSuccessMessage('Policy Type', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Policy Type', 'update').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete PolicyType
     *
     * @return Index PolicyTypes
     *
     * @author Darshan Baraiya
     */
    public function delete(PolicyType $policyType): RedirectResponse
    {
        try {
            $this->policyTypeService->deletePolicyType($policyType);

            return $this->redirectWithSuccess('policy_type.index', $this->getSuccessMessage('Policy Type', 'deleted'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Policy Type', 'delete').': '.$throwable->getMessage());
        }
    }

    /**
     * Import PolicyTypes
     *
     * @param null
     * @return View File
     */
    public function importPolicyTypes()
    {
        return view('policy_type.import');
    }

    protected function getExportRelations(): array
    {
        return [];
    }

    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => 'policy_types',
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'headings' => ['ID', 'Name', 'Status', 'Created Date'],
            'mapping' => fn ($model): array => [
                $model->id,
                $model->name,
                $model->status ? 'Active' : 'Inactive',
                $model->created_at->format('Y-m-d H:i:s'),
            ],
            'with_mapping' => true,
        ];
    }
}
