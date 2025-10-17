<?php

namespace App\Contracts\Services;

use App\Models\FuelType;

interface FuelTypeServiceInterface
{
    /**
     * Create a new fuel type
     *
     * @throws \Throwable
     */
    public function createFuelType(array $data): FuelType;

    /**
     * Update an existing fuel type
     *
     * @throws \Throwable
     */
    public function updateFuelType(FuelType $fuelType, array $data): bool;

    /**
     * Delete a fuel type
     *
     * @throws \Throwable
     */
    public function deleteFuelType(FuelType $fuelType): bool;

    /**
     * Update fuel type status
     *
     * @throws \Throwable
     */
    public function updateStatus(int $fuelTypeId, int $status): bool;
}
