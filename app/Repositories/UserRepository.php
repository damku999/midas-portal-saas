<?php

namespace App\Repositories;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Models\User;

/**
 * User Repository
 *
 * Extends base repository functionality for User-specific operations.
 * Common CRUD operations are inherited from AbstractBaseRepository.
 */
class UserRepository extends AbstractBaseRepository implements UserRepositoryInterface
{
    protected string $modelClass = User::class;

    protected array $searchableFields = ['name', 'email'];

    /**
     * Find user with roles loaded.
     */
    public function findWithRoles(int $id): ?User
    {
        return User::with('roles')->find($id);
    }

    /**
     * Update user password.
     */
    public function updatePassword(User $user, string $hashedPassword): bool
    {
        return $user->update(['password' => $hashedPassword]);
    }

    /**
     * Override getAllForExport to include roles relationship.
     */
    public function getAllForExport(): \Illuminate\Database\Eloquent\Collection
    {
        return User::with('roles')->get();
    }
}
