<?php

namespace App\Traits;

use App\Services\ExcelExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

trait ExportableTrait
{
    /**
     * Export data using the reusable export system
     */
    public function export(Request $request)
    {
        $exportService = app(ExcelExportService::class);
        $modelClass = $this->getExportModelClass();
        $config = $this->getExportConfig($request);

        // Apply filters if provided
        if ($request->hasAny(['search', 'status', 'start_date', 'end_date'])) {
            return $this->exportFiltered($request, $exportService, $modelClass, $config);
        }

        // Use preset config if available
        $presetType = $this->getExportPresetType();
        if ($presetType) {
            $presetConfig = $exportService->getPresetConfig($presetType);
            $config = array_merge($presetConfig, $config);

            return $exportService->export($modelClass, $config);
        }

        return $exportService->quickExport($modelClass, [], $config);
    }

    /**
     * Export with date range filter
     */
    public function exportDateRange(Request $request, string $startDate, string $endDate)
    {
        $exportService = app(ExcelExportService::class);
        $modelClass = $this->getExportModelClass();
        $dateField = $this->getDateFilterField();
        $config = $this->getExportConfig($request);

        return $exportService->exportDateRange($modelClass, $dateField, $startDate, $endDate, $config);
    }

    protected function exportFiltered(Request $request, ExcelExportService $exportService, string $modelClass, array $config)
    {
        $filters = [];

        if ($request->filled('search')) {
            $searchFields = $this->getSearchableFields();
            $query = app($modelClass)->newQuery();

            foreach ($searchFields as $field) {
                $query->orWhere($field, 'like', '%'.$request->search.'%');
            }

            return $exportService->export($query, $config);
        }

        if ($request->filled('status')) {
            $filters['status'] = $request->status;
        }

        if ($request->filled('start_date') && $request->filled('end_date')) {
            return $exportService->exportDateRange(
                $modelClass,
                $this->getDateFilterField(),
                $request->start_date,
                $request->end_date,
                $config
            );
        }

        if (! empty($filters)) {
            return $exportService->exportFiltered($modelClass, $filters, $config);
        }

        return $exportService->export($modelClass, $config);
    }

    protected function getExportModelClass(): string
    {
        $controllerName = class_basename($this);
        $modelName = str_replace('Controller', '', $controllerName);

        if (Str::endsWith($modelName, 's')) {
            $modelName = Str::singular($modelName);
        }

        $modelClass = "App\\Models\\{$modelName}";

        if (! class_exists($modelClass)) {
            throw new \Exception("Model class {$modelClass} not found. Override getExportModelClass()");
        }

        return $modelClass;
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => $this->getExportFilename(),
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => $this->getExportOrderBy(),
        ];
    }

    protected function getExportPresetType(): ?string
    {
        $controllerName = class_basename($this);

        return match ($controllerName) {
            'CustomerController' => 'customers',
            default => null
        };
    }

    protected function getExportFilename(): string
    {
        $controllerName = class_basename($this);
        $name = str_replace('Controller', '', $controllerName);

        return Str::snake(Str::plural($name));
    }

    protected function getExportRelations(): array
    {
        return [];
    }

    protected function getExportOrderBy(): array
    {
        return ['column' => 'created_at', 'direction' => 'desc'];
    }

    protected function getSearchableFields(): array
    {
        return ['name'];
    }

    protected function getDateFilterField(): string
    {
        return 'created_at';
    }
}
