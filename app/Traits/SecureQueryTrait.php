<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

trait SecureQueryTrait
{
    /**
     * Secure search with input sanitization and SQL injection prevention
     */
    public function secureSearch(Builder $query, string $searchTerm, array $searchableFields): Builder
    {
        // Sanitize search term
        $searchTerm = $this->sanitizeSearchTerm($searchTerm);

        if (empty($searchTerm)) {
            return $query;
        }

        return $query->where(function ($q) use ($searchTerm, $searchableFields) {
            foreach ($searchableFields as $field) {
                // Validate field name to prevent injection
                if ($this->isValidFieldName($field)) {
                    $q->orWhere($field, 'LIKE', '%'.$searchTerm.'%');
                }
            }
        });
    }

    /**
     * Secure ordering with column validation
     */
    public function secureOrderBy(Builder $query, string $column, string $direction = 'asc'): Builder
    {
        // Validate column name
        if (! $this->isValidFieldName($column)) {
            $this->logSecurityWarning('Invalid order column attempted', ['column' => $column]);

            return $query;
        }

        // Validate direction
        $direction = strtolower($direction);
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        return $query->orderBy($column, $direction);
    }

    /**
     * Secure filtering with parameter validation
     */
    public function secureFilter(Builder $query, array $filters): Builder
    {
        foreach ($filters as $field => $value) {
            if (! $this->isValidFieldName($field)) {
                $this->logSecurityWarning('Invalid filter field attempted', ['field' => $field]);

                continue;
            }

            if (is_null($value) || $value === '') {
                continue;
            }

            // Handle different filter types
            if (is_array($value)) {
                $query->whereIn($field, $value);
            } elseif (strpos($value, '%') !== false) {
                $query->where($field, 'LIKE', $value);
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    /**
     * Secure bulk operations with validation
     */
    public function secureBulkUpdate(array $ids, array $data): int
    {
        // Validate IDs
        $validIds = array_filter($ids, function ($id) {
            return is_numeric($id) && $id > 0;
        });

        if (empty($validIds)) {
            return 0;
        }

        // Sanitize data
        $sanitizedData = $this->sanitizeUpdateData($data);

        if (empty($sanitizedData)) {
            return 0;
        }

        try {
            return $this->whereIn('id', $validIds)->update($sanitizedData);
        } catch (\Exception $e) {
            $this->logSecurityWarning('Bulk update failed', [
                'error' => $e->getMessage(),
                'ids' => $validIds,
                'data' => $sanitizedData,
            ]);

            return 0;
        }
    }

    /**
     * Sanitize search terms to prevent injection
     */
    private function sanitizeSearchTerm(string $searchTerm): string
    {
        // Remove potential SQL injection patterns
        $searchTerm = preg_replace('/[\'"`]/', '', $searchTerm);
        $searchTerm = preg_replace('/\b(union|select|insert|update|delete|drop|create|alter|exec|execute)\b/i', '', $searchTerm);

        // Trim and limit length
        $searchTerm = trim($searchTerm);
        $searchTerm = substr($searchTerm, 0, 255);

        return $searchTerm;
    }

    /**
     * Validate field names to prevent injection
     */
    private function isValidFieldName(string $fieldName): bool
    {
        // Only allow alphanumeric characters, underscores, and dots
        if (! preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*(\.[a-zA-Z_][a-zA-Z0-9_]*)?$/', $fieldName)) {
            return false;
        }

        // Check against allowed fields if defined
        if (property_exists($this, 'allowedFilterFields')) {
            return in_array($fieldName, $this->allowedFilterFields);
        }

        // Check against table columns
        return $this->isValidTableColumn($fieldName);
    }

    /**
     * Check if field exists in table
     */
    private function isValidTableColumn(string $fieldName): bool
    {
        try {
            $tableName = (new $this->modelClass)->getTable();
            $columns = DB::getSchemaBuilder()->getColumnListing($tableName);

            // Handle dot notation for relationships
            if (strpos($fieldName, '.') !== false) {
                $parts = explode('.', $fieldName);

                return count($parts) === 2 && ! empty($parts[0]) && ! empty($parts[1]);
            }

            return in_array($fieldName, $columns);
        } catch (\Exception $e) {
            $this->logSecurityWarning('Column validation failed', [
                'field' => $fieldName,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Sanitize update data
     */
    private function sanitizeUpdateData(array $data): array
    {
        $sanitized = [];

        foreach ($data as $key => $value) {
            if (! $this->isValidFieldName($key)) {
                continue;
            }

            // Sanitize value based on type
            if (is_string($value)) {
                $value = trim($value);
                // Remove potential script tags
                $value = preg_replace('/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/mi', '', $value);
            }

            $sanitized[$key] = $value;
        }

        return $sanitized;
    }

    /**
     * Secure pagination with validation
     */
    public function securePaginate(Builder $query, int $perPage = 15, int $page = 1): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        // Validate pagination parameters
        $perPage = max(1, min($perPage, 100)); // Limit between 1 and 100
        $page = max(1, $page);

        return $query->paginate($perPage, ['*'], 'page', $page);
    }

    /**
     * Execute safe raw queries with parameter binding
     */
    public function safeRawQuery(string $sql, array $bindings = []): \Illuminate\Support\Collection
    {
        // Log raw query execution for security monitoring
        $this->logSecurityInfo('Raw query executed', [
            'sql' => $sql,
            'bindings' => $bindings,
        ]);

        try {
            return collect(DB::select($sql, $bindings));
        } catch (\Exception $e) {
            $this->logSecurityWarning('Raw query failed', [
                'sql' => $sql,
                'bindings' => $bindings,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Log security warnings
     */
    private function logSecurityWarning(string $message, array $context = []): void
    {
        Log::channel('security')->warning($message, array_merge($context, [
            'user_id' => auth()->id(),
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->header('User-Agent'),
            'timestamp' => now()->toISOString(),
            'class' => get_class($this),
        ]));
    }

    /**
     * Log security info
     */
    private function logSecurityInfo(string $message, array $context = []): void
    {
        Log::channel('security')->info($message, array_merge($context, [
            'user_id' => auth()->id(),
            'ip_address' => request()?->ip(),
            'timestamp' => now()->toISOString(),
            'class' => get_class($this),
        ]));
    }
}
