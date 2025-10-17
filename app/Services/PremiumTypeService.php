<?php

namespace App\Services;

use App\Models\PremiumType;

/**
 * Premium Type Service
 *
 * Handles PremiumType business logic operations.
 * Inherits transaction management from BaseService.
 */
class PremiumTypeService extends BaseService
{
    /**
     * Create a new premium type
     *
     * @throws \Throwable
     */
    public function createPremiumType(array $data): PremiumType
    {
        $this->validateVehicleAndLifeInsurance($data);

        return $this->createInTransaction(
            fn () => PremiumType::query()->create($data)
        );
    }

    /**
     * Update an existing premium type
     *
     * @throws \Throwable
     */
    public function updatePremiumType(PremiumType $premiumType, array $data): bool
    {
        $this->validateVehicleAndLifeInsurance($data);

        return $this->updateInTransaction(
            fn () => $premiumType->update($data)
        );
    }

    /**
     * Delete a premium type
     *
     * @throws \Throwable
     */
    public function deletePremiumType(PremiumType $premiumType): bool
    {
        return $this->deleteInTransaction(
            fn () => $premiumType->delete()
        );
    }

    /**
     * Update premium type status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $premiumTypeId, int $status): bool
    {
        return $this->executeInTransaction(
            fn () => PremiumType::whereId($premiumTypeId)->update(['status' => $status])
        );
    }

    /**
     * Validate that both vehicle and life insurance cannot be true
     *
     * @throws \InvalidArgumentException
     */
    private function validateVehicleAndLifeInsurance(array $data): void
    {
        $isVehicle = isset($data['is_vehicle']) && (bool) $data['is_vehicle'];
        $isLifeInsurance = isset($data['is_life_insurance_policies']) && (bool) $data['is_life_insurance_policies'];

        if ($isVehicle && $isLifeInsurance) {
            throw new \InvalidArgumentException('Both "Is it for Vehicle?" and "Is Life Insurance Policies?" cannot be true at the same time.');
        }
    }
}
