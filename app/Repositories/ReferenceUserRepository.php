<?php

namespace App\Repositories;

use App\Contracts\Repositories\ReferenceUserRepositoryInterface;
use App\Models\ReferenceUser;

/**
 * Reference User Repository
 *
 * Handles ReferenceUser entity data access operations.
 * Inherits common CRUD operations from AbstractBaseRepository.
 */
class ReferenceUserRepository extends AbstractBaseRepository implements ReferenceUserRepositoryInterface
{
    /**
     * The model class name
     *
     * @var class-string<ReferenceUser>
     */
    protected string $modelClass = ReferenceUser::class;

    /**
     * Searchable fields for the getPaginated method
     * Reference user-specific search includes name, email, and mobile_number
     *
     * @var array<string>
     */
    protected array $searchableFields = ['name', 'email', 'mobile_number'];

    // All CRUD operations are now inherited from AbstractBaseRepository
    // Add reference user-specific methods here if needed in the future
}
