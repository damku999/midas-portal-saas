<?php

namespace App\Contracts\Services;

use App\Models\RelationshipManager;

interface RelationshipManagerServiceInterface
{
    /**
     * Create a new relationship manager
     *
     * @throws \Throwable
     */
    public function createRelationshipManager(array $data): RelationshipManager;

    /**
     * Update an existing relationship manager
     *
     * @throws \Throwable
     */
    public function updateRelationshipManager(RelationshipManager $relationshipManager, array $data): bool;

    /**
     * Delete a relationship manager
     *
     * @throws \Throwable
     */
    public function deleteRelationshipManager(RelationshipManager $relationshipManager): bool;

    /**
     * Update relationship manager status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $relationshipManagerId, int $status): bool;
}
