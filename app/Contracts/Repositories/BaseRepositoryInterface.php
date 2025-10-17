<?php

namespace App\Contracts\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

/**
 * Base Repository Interface
 *
 * Provides common CRUD operations for all repository implementations.
 * This interface eliminates code duplication across entity-specific repositories.
 *
 * @template T of Model
 */
interface BaseRepositoryInterface
{
    /**
     * Get paginated results with optional search and filtering
     */
    public function getPaginated(Request $request, int $perPage = 10): LengthAwarePaginator;

    /**
     * Create a new entity
     */
    public function create(array $data): Model;

    /**
     * Update an existing entity
     */
    public function update(Model $entity, array $data): Model;

    /**
     * Delete an entity
     */
    public function delete(Model $entity): bool;

    /**
     * Find entity by ID
     */
    public function findById(int $id): ?Model;

    /**
     * Update entity status
     */
    public function updateStatus(int $id, int $status): bool;

    /**
     * Get all active entities
     */
    public function getActive(): Collection;

    /**
     * Get all entities for export (no pagination)
     */
    public function getAllForExport(): Collection;
}
