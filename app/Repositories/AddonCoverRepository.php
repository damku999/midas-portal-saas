<?php

namespace App\Repositories;

use App\Contracts\Repositories\AddonCoverRepositoryInterface;
use App\Models\AddonCover;

/**
 * Addon Cover Repository
 *
 * Handles AddonCover entity data access operations.
 * Inherits common CRUD operations from AbstractBaseRepository.
 */
class AddonCoverRepository extends AbstractBaseRepository implements AddonCoverRepositoryInterface
{
    /**
     * The model class name
     *
     * @var class-string<AddonCover>
     */
    protected string $modelClass = AddonCover::class;

    /**
     * Searchable fields for the getPaginated method
     * AddonCover search includes name and description
     *
     * @var array<string>
     */
    protected array $searchableFields = ['name', 'description'];

    // All CRUD operations are now inherited from AbstractBaseRepository
    // Add addon-cover-specific methods here if needed in the future
}
