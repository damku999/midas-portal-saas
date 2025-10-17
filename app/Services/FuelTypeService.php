<?php

namespace App\Services;

use App\Models\FuelType;

/**
 * Fuel Type Service
 *
 * Handles FuelType business logic operations.
 * Inherits transaction management from BaseService.
 */
class FuelTypeService extends BaseService
{
    /**
     * Create a new fuel type
     *
     * @throws \Throwable
     */
    public function createFuelType(array $data): FuelType
    {
        return $this->createInTransaction(
            fn () => FuelType::query()->create($data)
        );
    }

    /**
     * Update an existing fuel type
     *
     * @throws \Throwable
     */
    public function updateFuelType(FuelType $fuelType, array $data): bool
    {
        return $this->updateInTransaction(
            fn () => $fuelType->update($data)
        );
    }

    /**
     * Delete a fuel type
     *
     * @throws \Throwable
     */
    public function deleteFuelType(FuelType $fuelType): bool
    {
        return $this->deleteInTransaction(
            fn () => $fuelType->delete()
        );
    }

    /**
     * Update fuel type status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $fuelTypeId, int $status): bool
    {
        return $this->executeInTransaction(
            fn () => FuelType::whereId($fuelTypeId)->update(['status' => $status])
        );
    }
}
