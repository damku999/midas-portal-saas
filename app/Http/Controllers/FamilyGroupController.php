<?php

namespace App\Http\Controllers;

use App\Contracts\Repositories\CustomerRepositoryInterface;
use App\Contracts\Services\FamilyGroupServiceInterface;
use App\Models\FamilyGroup;
use App\Models\FamilyMember;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FamilyGroupController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(
        /**
         * Family Group Service instance
         */
        private FamilyGroupServiceInterface $familyGroupService,
        /**
         * Customer Repository instance
         */
        private CustomerRepositoryInterface $customerRepository
    ) {
        $this->setupPermissionMiddleware('family-group');
    }

    /**
     * Display a listing of family groups.
     */
    public function index(Request $request)
    {
        $familyGroups = $this->familyGroupService->getFamilyGroups($request);

        return view('admin.family_groups.index', ['familyGroups' => $familyGroups]);
    }

    /**
     * Show the form for creating a new family group.
     */
    public function create()
    {
        // Clean up any orphaned family member records first
        $this->familyGroupService->cleanupOrphanedRecords();

        $availableCustomers = $this->familyGroupService->getAvailableCustomers();

        return view('admin.family_groups.create', ['availableCustomers' => $availableCustomers]);
    }

    /**
     * Store a newly created family group.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:family_groups,name,NULL,id,deleted_at,NULL',
            'family_head_id' => 'required|exists:customers,id',
            'member_ids' => 'sometimes|array',
            'member_ids.*' => 'exists:customers,id',
            'relationships' => 'sometimes|array',
            'relationships.*' => 'nullable|string|max:50',
            'status' => 'boolean',
            'family_head_password' => 'nullable|string|min:8|confirmed',
            'force_password_change' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->redirectWithValidationErrors($validator);
        }

        try {
            $familyGroup = $this->familyGroupService->createFamilyGroup($request->all());

            return $this->redirectWithSuccess('family_groups.index', 'Family group created successfully. Login credentials sent to family head only. Family head will manage family member access.');

        } catch (\Exception $exception) {
            return $this->redirectWithError('Error creating family group: '.$exception->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified family group.
     */
    public function show(FamilyGroup $familyGroup)
    {
        $familyGroup = $this->familyGroupService->getFamilyGroupWithMembers($familyGroup->id);

        return view('admin.family_groups.show', ['familyGroup' => $familyGroup]);
    }

    /**
     * Show the form for editing the specified family group.
     */
    public function edit(FamilyGroup $familyGroup)
    {
        // Clean up any orphaned family member records first
        $this->familyGroupService->cleanupOrphanedRecords();

        $familyGroup = $this->familyGroupService->getFamilyGroupWithMembers($familyGroup->id);

        $availableCustomers = $this->familyGroupService->getAvailableCustomers($familyGroup->id);

        return view('admin.family_groups.edit', ['familyGroup' => $familyGroup, 'availableCustomers' => $availableCustomers]);
    }

    /**
     * Update the specified family group.
     */
    public function update(Request $request, FamilyGroup $familyGroup)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:family_groups,name,'.$familyGroup->id.',id,deleted_at,NULL',
            'family_head_id' => 'required|exists:customers,id',
            'member_ids' => 'sometimes|array',
            'member_ids.*' => 'exists:customers,id',
            'relationships' => 'sometimes|array',
            'relationships.*' => 'nullable|string|max:50',
            'status' => 'boolean',
            'family_head_password' => 'nullable|string|min:8|confirmed',
            'force_password_change' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->redirectWithValidationErrors($validator);
        }

        try {
            $result = $this->familyGroupService->updateFamilyGroup($familyGroup, $request->all());

            if ($result) {
                $message = 'Family group updated successfully.';
                if ($request->filled('family_head_password')) {
                    $message .= ' Family head password has been updated.';
                }

                return $this->redirectWithSuccess('family_groups.index', $message);
            }

            return $this->redirectWithError('Failed to update family group.')
                ->withInput();

        } catch (\Exception $exception) {
            return $this->redirectWithError('Error updating family group: '.$exception->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified family group.
     */
    public function destroy(FamilyGroup $familyGroup): RedirectResponse
    {
        try {
            $familyName = $familyGroup->name;

            $result = $this->familyGroupService->deleteFamilyGroup($familyGroup);

            if ($result) {
                // Return JSON response for AJAX requests
                if (request()->expectsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => sprintf("Family group '%s' deleted successfully.", $familyName),
                    ]);
                }

                return $this->redirectWithSuccess('family_groups.index', sprintf("Family group '%s' deleted successfully.", $familyName));
            }
            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to delete family group.',
                ], 500);
            }

            return $this->redirectWithError('Failed to delete family group.');

        } catch (\Exception $exception) {
            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error deleting family group: '.$exception->getMessage(),
                ], 500);
            }

            return $this->redirectWithError('Error deleting family group: '.$exception->getMessage());
        }
    }

    /**
     * Update family group status.
     */
    public function updateStatus(int $familyGroupId, $status): RedirectResponse
    {
        try {
            $result = $this->familyGroupService->updateFamilyGroupStatus($familyGroupId, $status);

            if ($result) {
                $message = $status ? 'Family group activated successfully.' : 'Family group deactivated successfully.';

                return $this->redirectWithSuccess(null, $message);
            }

            return $this->redirectWithError('Failed to update family group status.');

        } catch (\Exception $exception) {
            return $this->redirectWithError('Error updating family group status: '.$exception->getMessage());
        }
    }

    /**
     * Export family groups.
     */
    public function export()
    {
        try {
            $familyGroups = $this->familyGroupService->getAllFamilyGroupsForExport();

            // Simple CSV export
            $filename = 'family_groups_'.date('Y_m_d_H_i_s').'.csv';
            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => sprintf('attachment; filename="%s"', $filename),
            ];

            $callback = static function () use ($familyGroups): void {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['ID', 'Family Name', 'Family Head', 'Members Count', 'Status', 'Created Date']);

                foreach ($familyGroups as $familyGroup) {
                    fputcsv($file, [
                        $familyGroup->id,
                        $familyGroup->name,
                        $familyGroup->familyHead->name ?? 'N/A',
                        $familyGroup->familyMembers->count(),
                        $familyGroup->status ? 'Active' : 'Inactive',
                        $familyGroup->created_at->format('Y-m-d H:i:s'),
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } catch (\Exception $exception) {
            return $this->redirectWithError('Error exporting family groups: '.$exception->getMessage());
        }
    }

    /**
     * Remove a specific family member from their family group.
     */
    public function removeMember(FamilyMember $familyMember): RedirectResponse
    {
        try {
            $familyGroupName = $familyMember->familyGroup->name ?? 'Unknown';
            $customerName = $familyMember->customer->name ?? 'Unknown';

            $result = $this->familyGroupService->removeFamilyMemberByObject($familyMember);

            if ($result) {
                // Return JSON response for AJAX requests
                if (request()->expectsJson()) {
                    return response()->json([
                        'status' => 'success',
                        'message' => sprintf("'%s' has been removed from '%s' successfully.", $customerName, $familyGroupName),
                    ]);
                }

                return $this->redirectWithSuccess(null, sprintf("'%s' has been removed from '%s' successfully.", $customerName, $familyGroupName));
            }
            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Failed to remove family member.',
                ], 500);
            }

            return $this->redirectWithError('Failed to remove family member.');

        } catch (\Exception $exception) {
            // Return JSON response for AJAX requests
            if (request()->expectsJson()) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error removing family member: '.$exception->getMessage(),
                ], 500);
            }

            return $this->redirectWithError('Error removing family member: '.$exception->getMessage());
        }
    }

    protected function getExportRelations(): array
    {
        return ['familyHead', 'familyMembers'];
    }

    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => 'family_groups',
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'headings' => ['ID', 'Family Name', 'Family Head', 'Members Count', 'Status', 'Created Date'],
            'mapping' => fn ($model): array => [
                $model->id,
                $model->name,
                $model->familyHead->name ?? 'N/A',
                $model->familyMembers->count(),
                $model->status ? 'Active' : 'Inactive',
                $model->created_at->format('Y-m-d H:i:s'),
            ],
            'with_mapping' => true,
        ];
    }
}
