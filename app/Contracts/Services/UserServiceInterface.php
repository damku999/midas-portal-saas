<?php

namespace App\Contracts\Services;

use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface UserServiceInterface
{
    public function getUsers(Request $request): LengthAwarePaginator;

    public function createUser(array $data): User;

    public function updateUser(User $user, array $data): User;

    public function deleteUser(User $user): bool;

    public function updateStatus(int $userId, int $status): bool;

    public function assignRoles(User $user, array $roles): void;

    public function exportUsers(): \Symfony\Component\HttpFoundation\BinaryFileResponse;

    public function getActiveUsers(): \Illuminate\Database\Eloquent\Collection;

    public function changePassword(User $user, string $newPassword): bool;

    public function getUserWithRoles(int $userId): ?User;

    public function getStoreValidationRules(): array;

    public function getUpdateValidationRules(User $user): array;

    public function getPasswordValidationRules(): array;

    public function getRoles(): \Illuminate\Database\Eloquent\Collection;
}
