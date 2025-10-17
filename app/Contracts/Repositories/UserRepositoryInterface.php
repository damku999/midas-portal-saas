<?php

namespace App\Contracts\Repositories;

use App\Models\User;

/**
 * User Repository Interface
 *
 * Extends base repository functionality for User-specific operations.
 * Common CRUD operations are inherited from BaseRepositoryInterface.
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find user with roles loaded.
     */
    public function findWithRoles(int $id): ?User;

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $hashedPassword): bool;
}
