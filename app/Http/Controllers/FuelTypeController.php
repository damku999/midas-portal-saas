<?php

namespace App\Http\Controllers;

use App\Models\FuelType;
use App\Services\FuelTypeService;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Fuel Type Controller
 *
 * Handles FuelType CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class FuelTypeController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(
        private FuelTypeService $fuelTypeService
    ) {
        $this->setupPermissionMiddleware('fuel-type');
    }

    /**
     * List FuelType
     *
     * @param Nill
     * @return array $fuel_type
     *
     * @author Darshan Baraiya
     */
    public function index(Request $request)
    {
        $builder = FuelType::query()->select('*');
        if (! empty($request->search)) {
            $builder->where('name', 'LIKE', '%'.trim((string) $request->search).'%');
        }

        $fuel_type = $builder->paginate(pagination_per_page());

        return view('fuel_type.index', ['fuel_type' => $fuel_type, 'request' => $request->all()]);
    }

    /**
     * Create FuelType
     *
     * @param Nill
     * @return array $fuel_type
     *
     * @author Darshan Baraiya
     */
    public function create()
    {
        return view('fuel_type.add');
    }

    /**
     * Store FuelType
     *
     * @return View FuelTypes
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
            $this->fuelTypeService->createFuelType($validated);

            return $this->redirectWithSuccess('fuel_type.index', $this->getSuccessMessage('Fuel Type', 'created'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Fuel Type', 'create').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Status Of FuelType
     *
     * @return List Page With Success
     *
     * @author Darshan Baraiya
     */
    public function updateStatus(int $fuel_type_id, int $status): RedirectResponse
    {
        // Validation
        $validator = Validator::make([
            'fuel_type_id' => $fuel_type_id,
            'status' => $status,
        ], [
            'fuel_type_id' => 'required|exists:fuel_types,id',
            'status' => 'required|in:0,1',
        ]);

        // If Validations Fails
        if ($validator->fails()) {
            return $this->redirectWithError($validator->errors()->first());
        }

        try {
            $this->fuelTypeService->updateStatus($fuel_type_id, $status);

            return $this->redirectWithSuccess('fuel_type.index', $this->getSuccessMessage('Fuel Type Status', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Fuel Type Status', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Edit FuelType
     *
     * @param  int  $fuelType
     * @return Collection $fuel_type
     *
     * @author Darshan Baraiya
     */
    public function edit(FuelType $fuelType)
    {
        return view('fuel_type.edit')->with([
            'fuel_type' => $fuelType,
        ]);
    }

    /**
     * Update FuelType
     *
     * @param  Request  $request,  FuelType $fuel_type
     * @return View FuelTypes
     *
     * @author Darshan Baraiya
     */
    public function update(Request $request, FuelType $fuelType)
    {
        $validation_array = [
            'name' => 'required',
        ];

        $validated = $request->validate($validation_array);

        try {
            $this->fuelTypeService->updateFuelType($fuelType, $validated);

            return $this->redirectWithSuccess('fuel_type.index', $this->getSuccessMessage('Fuel Type', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Fuel Type', 'update').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete FuelType
     *
     * @return Index FuelTypes
     *
     * @author Darshan Baraiya
     */
    public function delete(FuelType $fuelType): RedirectResponse
    {
        try {
            $this->fuelTypeService->deleteFuelType($fuelType);

            return $this->redirectWithSuccess('fuel_type.index', $this->getSuccessMessage('Fuel Type', 'deleted'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError($this->getErrorMessage('Fuel Type', 'delete').': '.$throwable->getMessage());
        }
    }

    /**
     * Import FuelTypes
     *
     * @param null
     * @return View File
     */
    public function importFuelTypes()
    {
        return view('fuel_type.import');
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
            'filename' => 'fuel_types',
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
