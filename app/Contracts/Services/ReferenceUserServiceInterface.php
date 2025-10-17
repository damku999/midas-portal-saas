<?php

namespace App\Contracts\Services;

use App\Models\ReferenceUser;

interface ReferenceUserServiceInterface
{
    /**
     * Create a new reference user
     *
     * @throws \Throwable
     */
    public function createReferenceUser(array $data): ReferenceUser;

    /**
     * Update an existing reference user
     *
     * @throws \Throwable
     */
    public function updateReferenceUser(ReferenceUser $referenceUser, array $data): bool;

    /**
     * Delete a reference user
     *
     * @throws \Throwable
     */
    public function deleteReferenceUser(ReferenceUser $referenceUser): bool;

    /**
     * Update reference user status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $referenceUserId, int $status): bool;
}
