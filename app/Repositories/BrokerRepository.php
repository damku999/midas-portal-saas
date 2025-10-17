<?php

namespace App\Repositories;

use App\Contracts\Repositories\BrokerRepositoryInterface;
use App\Models\Broker;

/**
 * Broker Repository
 *
 * Handles Broker entity data access operations.
 * Inherits common CRUD operations from AbstractBaseRepository.
 */
class BrokerRepository extends AbstractBaseRepository implements BrokerRepositoryInterface
{
    /**
     * The model class name
     *
     * @var class-string<Broker>
     */
    protected string $modelClass = Broker::class;

    /**
     * Searchable fields for the getPaginated method
     * Broker-specific search includes name, email, and mobile_number
     *
     * @var array<string>
     */
    protected array $searchableFields = ['name', 'email', 'mobile_number'];

    // All CRUD operations are now inherited from AbstractBaseRepository
    // Add broker-specific methods here if needed in the future
}
