<?php

namespace App\Services;

use App\Models\RelationshipManager;

/**
 * Relationship Manager Service
 *
 * Handles RelationshipManager business logic operations.
 * Inherits transaction management from BaseService.
 */
class RelationshipManagerService extends BaseService
{
    /**
     * Create a new relationship manager
     *
     * @throws \Throwable
     */
    public function createRelationshipManager(array $data): RelationshipManager
    {
        return $this->createInTransaction(
            fn () => RelationshipManager::query()->create($data)
        );
    }

    /**
     * Update an existing relationship manager
     *
     * @throws \Throwable
     */
    public function updateRelationshipManager(RelationshipManager $relationshipManager, array $data): bool
    {
        return $this->updateInTransaction(
            fn () => $relationshipManager->update($data)
        );
    }

    /**
     * Delete a relationship manager
     *
     * @throws \Throwable
     */
    public function deleteRelationshipManager(RelationshipManager $relationshipManager): bool
    {
        return $this->deleteInTransaction(
            fn () => $relationshipManager->delete()
        );
    }

    /**
     * Update relationship manager status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $relationshipManagerId, int $status): bool
    {
        return $this->executeInTransaction(
            fn () => RelationshipManager::whereId($relationshipManagerId)->update(['status' => $status])
        );
    }
}
