<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\BranchRepositoryInterface;
use App\Models\Branch;
use App\Traits\ExportableTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Branch Controller
 *
 * Handles Branch CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class BranchController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(/**
     * Branch Repository instance
     */
        private BranchRepositoryInterface $branchRepository)
    {
        $this->setupPermissionMiddleware('branch');
    }

    /**
     * List Branches
     */
    public function index(Request $request): View
    {
        $lengthAwarePaginator = $this->branchRepository->getBranchesWithFilters($request, 10);

        return view('branches.index', ['branches' => $lengthAwarePaginator, 'request' => $request->all()]);
    }

    /**
     * Create Branch
     */
    public function create(): View
    {
        return view('branches.add');
    }

    /**
     * Store Branch
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name',
        ]);

        $this->branchRepository->create([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'status' => 1,
            'created_by' => auth()->id(),
        ]);

        return $this->redirectWithSuccess('branches.index', $this->getSuccessMessage('Branch', 'created'));
    }

    /**
     * Edit Branch
     */
    public function edit(Branch $branch): View
    {
        return view('branches.edit', ['branch' => $branch]);
    }

    /**
     * Update Branch
     */
    public function update(Request $request, Branch $branch): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:branches,name,'.$branch->id,
        ]);

        $branch->update([
            'name' => $request->name,
            'email' => $request->email,
            'mobile_number' => $request->mobile_number,
            'updated_by' => auth()->id(),
        ]);

        return $this->redirectWithSuccess('branches.index', $this->getSuccessMessage('Branch', 'updated'));
    }

    /**
     * Update Branch Status
     *
     * @param  int  $status
     */
    public function updateStatus(int $branch_id, $status): RedirectResponse
    {
        $branch = $this->branchRepository->findById($branch_id);

        if (! $branch instanceof Model) {
            return $this->redirectWithError('Branch not found.');
        }

        $this->branchRepository->update($branch, [
            'status' => $status,
            'updated_by' => auth()->id(),
        ]);

        $message = $status == 1 ? 'Branch activated successfully.' : 'Branch deactivated successfully.';

        return $this->redirectWithSuccess('branches.index', $message);
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
            'filename' => 'branches',
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
