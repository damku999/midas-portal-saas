<?php

namespace App\Services;

use App\Models\ReferenceUser;

/**
 * Reference User Service
 *
 * Handles ReferenceUser business logic operations.
 * Inherits transaction management from BaseService.
 */
class ReferenceUserService extends BaseService
{
    /**
     * Create a new reference user
     *
     * @throws \Throwable
     */
    public function createReferenceUser(array $data): ReferenceUser
    {
        return $this->createInTransaction(
            fn () => ReferenceUser::query()->create($data)
        );
    }

    /**
     * Update an existing reference user
     *
     * @throws \Throwable
     */
    public function updateReferenceUser(ReferenceUser $referenceUser, array $data): bool
    {
        return $this->updateInTransaction(
            fn () => $referenceUser->update($data)
        );
    }

    /**
     * Delete a reference user
     *
     * @throws \Throwable
     */
    public function deleteReferenceUser(ReferenceUser $referenceUser): bool
    {
        return $this->deleteInTransaction(
            fn () => $referenceUser->delete()
        );
    }

    /**
     * Update reference user status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $referenceUserId, int $status): bool
    {
        return $this->executeInTransaction(
            fn () => ReferenceUser::whereId($referenceUserId)->update(['status' => $status])
        );
    }
}
