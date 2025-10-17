<?php

namespace App\Contracts\Repositories;

use App\Models\RelationshipManager;

/**
 * Relationship Manager Repository Interface
 *
 * Extends base repository functionality for RelationshipManager-specific operations.
 * Common CRUD operations are inherited from BaseRepositoryInterface.
 */
interface RelationshipManagerRepositoryInterface extends BaseRepositoryInterface
{
    // All common methods inherited from BaseRepositoryInterface
    // Add relationship manager-specific methods here if needed in the future
}
