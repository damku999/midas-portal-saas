<?php

namespace App\Services;

use App\Contracts\Repositories\UserRepositoryInterface;
use App\Contracts\Services\UserServiceInterface;
use App\Exceptions\ProtectedRecordException;
use App\Exports\UserExport;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Models\Role;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

/**
 * User Service
 *
 * Handles User business logic including role management and password handling.
 * Inherits transaction management from BaseService.
 */
class UserService extends BaseService implements UserServiceInterface
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {}

    public function getUsers(Request $request): LengthAwarePaginator
    {
        return $this->userRepository->getPaginated($request);
    }

    public function createUser(array $data): User
    {
        return $this->createInTransaction(function () use ($data): Model {
            if (isset($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            }

            $model = $this->userRepository->create($data);

            if (isset($data['roles'])) {
                $model->assignRole($data['roles']);
            }

            return $model;
        });
    }

    public function updateUser(User $user, array $data): User
    {
        return $this->updateInTransaction(function () use ($user, $data): Model {
            if (isset($data['password']) && ! empty($data['password'])) {
                $data['password'] = Hash::make($data['password']);
            } else {
                unset($data['password']);
            }

            $model = $this->userRepository->update($user, $data);

            if (isset($data['roles'])) {
                $model->syncRoles($data['roles']);
            }

            return $model;
        });
    }

    public function deleteUser(User $user): bool
    {
        // Check if user is protected
        if ($user->isProtected()) {
            throw ProtectedRecordException::deletionPrevented($user);
        }

        return $this->deleteInTransaction(
            fn (): bool => $this->userRepository->delete($user)
        );
    }

    public function updateStatus(int $userId, int $status): bool
    {
        // Get the user to check protection
        $user = User::findOrFail($userId);

        // Check if user is protected and being deactivated
        if ($user->isProtected() && $status == 0) {
            throw ProtectedRecordException::statusChangePrevented($user);
        }

        return $this->updateInTransaction(
            fn (): bool => $this->userRepository->updateStatus($userId, $status)
        );
    }

    public function assignRoles(User $user, array $roles): void
    {
        $this->executeInTransaction(static function () use ($user, $roles): void {
            $user->syncRoles($roles);
        });
    }

    public function exportUsers(): BinaryFileResponse
    {
        return Excel::download(new UserExport, 'users.xlsx');
    }

    public function getActiveUsers(): Collection
    {
        return $this->userRepository->getActive();
    }

    public function changePassword(User $user, string $newPassword): bool
    {
        return $this->updateInTransaction(function () use ($user, $newPassword): bool {
            $hashedPassword = Hash::make($newPassword);

            return $this->userRepository->updatePassword($user, $hashedPassword);
        });
    }

    public function getUserWithRoles(int $userId): ?User
    {
        return $this->userRepository->findWithRoles($userId);
    }

    public function getStoreValidationRules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'mobile_number' => 'required|numeric|digits:10',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|numeric|in:0,1',
            'new_password' => 'required|min:8|max:16|regex:/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d\s:])([^\s]){8,16}$/',
            'new_confirm_password' => 'required|same:new_password',
        ];
    }

    public function getUpdateValidationRules(User $user): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,'.$user->id,
            'mobile_number' => 'required|numeric|digits:10',
            'role_id' => 'required|exists:roles,id',
            'status' => 'required|numeric|in:0,1',
        ];
    }

    public function getPasswordValidationRules(): array
    {
        return [
            'new_password' => 'required|min:8|max:16|regex:/^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[^\w\d\s:])([^\s]){8,16}$/',
            'new_confirm_password' => 'required|same:new_password',
        ];
    }

    public function getRoles(): Collection
    {
        return Role::all();
    }
}
