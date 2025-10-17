<?php

namespace App\Contracts\Services;

use App\Models\PremiumType;

interface PremiumTypeServiceInterface
{
    /**
     * Create a new premium type
     *
     * @throws \Throwable
     */
    public function createPremiumType(array $data): PremiumType;

    /**
     * Update an existing premium type
     *
     * @throws \Throwable
     */
    public function updatePremiumType(PremiumType $premiumType, array $data): bool;

    /**
     * Delete a premium type
     *
     * @throws \Throwable
     */
    public function deletePremiumType(PremiumType $premiumType): bool;

    /**
     * Update premium type status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $premiumTypeId, int $status): bool;
}
