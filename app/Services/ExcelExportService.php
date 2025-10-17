<?php

namespace App\Services;

use App\Exports\GenericExport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;
use Maatwebsite\Excel\Facades\Excel;

class ExcelExportService
{
    /**
     * Export data to Excel
     */
    public function export($data, array $config = [])
    {
        $config = array_merge($this->getDefaultConfig(), $config);
        $collection = $this->resolveDataSource($data, $config);
        $filename = $this->generateFilename($config);
        $genericExport = new GenericExport($collection, $config);

        return Excel::download($genericExport, $filename);
    }

    /**
     * Export with custom mapping and headers
     */
    public function exportWithMapping($data, array $headings, callable $mapping, array $config = [])
    {
        $config = array_merge($config, [
            'headings' => $headings,
            'mapping' => $mapping,
            'with_headings' => true,
            'with_mapping' => true,
        ]);

        return $this->export($data, $config);
    }

    /**
     * Quick export for simple models
     */
    public function quickExport(string $modelClass, array $columns = [], array $config = [])
    {
        $model = app($modelClass);
        $columns = $columns !== [] ? $columns : $this->getDefaultColumns($model);

        $config = array_merge($config, [
            'columns' => $columns,
            'headings' => $this->generateHeadingsFromColumns($columns),
            'with_headings' => true,
        ]);

        return $this->export($modelClass, $config);
    }

    /**
     * Export with relationships
     */
    public function exportWithRelations($modelClass, array $relations, array $config = [])
    {
        $config = array_merge($config, [
            'relations' => $relations,
            'with_relations' => true,
        ]);

        return $this->export($modelClass, $config);
    }

    /**
     * Export filtered data
     */
    public function exportFiltered($modelClass, array $filters, array $config = [])
    {
        $query = app($modelClass)->newQuery();

        foreach ($filters as $field => $value) {
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } elseif (! empty($value)) {
                $query->where($field, 'like', sprintf('%%%s%%', $value));
            }
        }

        return $this->export($query, $config);
    }

    /**
     * Export date range data
     */
    public function exportDateRange($modelClass, string $dateField, $startDate, $endDate, array $config = [])
    {
        $query = app($modelClass)->newQuery()
            ->whereBetween($dateField, [$startDate, $endDate])
            ->orderBy($dateField, 'desc');

        $config = array_merge($config, [
            'filename_suffix' => date('Y_m_d', strtotime((string) $startDate)).'_to_'.date('Y_m_d', strtotime((string) $endDate)),
        ]);

        return $this->export($query, $config);
    }

    private function resolveDataSource($data, array $config)
    {
        if ($data instanceof Collection || $data instanceof SupportCollection) {
            return $data;
        }

        if ($data instanceof Builder) {
            if (! empty($config['relations'])) {
                $data->with($config['relations']);
            }

            if (! empty($config['order_by'])) {
                $data->orderBy($config['order_by']['column'], $config['order_by']['direction'] ?? 'asc');
            } else {
                $data->orderBy('created_at', 'desc');
            }

            if (! empty($config['limit'])) {
                $data->limit($config['limit']);
            }

            return $data->get();
        }

        if (is_string($data) && class_exists($data)) {
            $query = app($data)->newQuery();

            if (! empty($config['relations'])) {
                $query->with($config['relations']);
            }

            if (! empty($config['columns'])) {
                $query->select($config['columns']);
            }

            if (! empty($config['order_by'])) {
                $query->orderBy($config['order_by']['column'], $config['order_by']['direction'] ?? 'asc');
            } else {
                $query->orderBy('created_at', 'desc');
            }

            if (! empty($config['limit'])) {
                $query->limit($config['limit']);
            }

            return $query->get();
        }

        throw new \InvalidArgumentException('Invalid data source provided for export');
    }

    private function generateFilename(array $config): string
    {
        $base = $config['filename'] ?? 'export';
        $suffix = $config['filename_suffix'] ?? date('Y_m_d_H_i_s');
        $extension = $config['format'] ?? 'xlsx';

        return sprintf('%s_%s.%s', $base, $suffix, $extension);
    }

    private function getDefaultConfig(): array
    {
        return [
            'format' => 'xlsx',
            'with_headings' => false,
            'with_mapping' => false,
            'with_relations' => false,
            'strict_null_comparison' => true,
            'auto_size' => true,
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'limit' => null,
            'relations' => [],
            'columns' => [],
            'headings' => [],
            'mapping' => null,
            'filename' => 'export',
            'filename_suffix' => date('Y_m_d_H_i_s'),
        ];
    }

    private function getDefaultColumns(Model $model): array
    {
        $fillable = $model->getFillable();
        $basic = ['id', 'created_at', 'updated_at'];

        return array_merge($fillable, $basic);
    }

    private function generateHeadingsFromColumns(array $columns): array
    {
        return array_map(fn ($column): string => ucwords(str_replace(['_', 'id'], [' ', 'ID'], $column)), $columns);
    }

    /**
     * Get predefined export configs
     */
    public function getPresetConfig(string $modelType): array
    {
        $presets = [
            'customers' => [
                'filename' => 'customers',
                'relations' => ['familyGroup'],
                'headings' => ['ID', 'Name', 'Email', 'Mobile', 'Status', 'Created Date'],
                'mapping' => fn ($customer): array => [
                    $customer->id,
                    $customer->name,
                    $customer->email,
                    $customer->mobile_number,
                    ucfirst((string) $customer->status),
                    $customer->created_at->format('Y-m-d H:i:s'),
                ],
                'with_headings' => true,
                'with_mapping' => true,
            ],
        ];

        return $presets[$modelType] ?? [];
    }
}
