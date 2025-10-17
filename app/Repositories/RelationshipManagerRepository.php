<?php

namespace App\Repositories;

use App\Contracts\Repositories\RelationshipManagerRepositoryInterface;
use App\Models\RelationshipManager;

/**
 * Relationship Manager Repository
 *
 * Handles RelationshipManager entity data access operations.
 * Inherits common CRUD operations from AbstractBaseRepository.
 */
class RelationshipManagerRepository extends AbstractBaseRepository implements RelationshipManagerRepositoryInterface
{
    /**
     * The model class name
     *
     * @var class-string<RelationshipManager>
     */
    protected string $modelClass = RelationshipManager::class;

    /**
     * Searchable fields for the getPaginated method
     * Relationship manager-specific search includes name, email, and mobile_number
     *
     * @var array<string>
     */
    protected array $searchableFields = ['name', 'email', 'mobile_number'];

    // All CRUD operations are now inherited from AbstractBaseRepository
    // Add relationship manager-specific methods here if needed in the future
}
