<?php

namespace App\Http\Controllers;

use App\Contracts\Services\UserServiceInterface;
use App\Models\User;
use App\Traits\ExportableTrait;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

/**
 * User Controller
 *
 * Handles User CRUD operations.
 * Inherits middleware setup and common utilities from AbstractBaseCrudController.
 */
class UserController extends AbstractBaseCrudController
{
    use ExportableTrait;

    public function __construct(
        private UserServiceInterface $userService
    ) {
        $this->setupPermissionMiddleware('user');
    }

    /**
     * List User
     *
     * @return View
     *
     * @author Darshan Baraiya
     */
    public function index(Request $request)
    {
        $lengthAwarePaginator = $this->userService->getUsers($request);

        return view('users.index', ['users' => $lengthAwarePaginator]);
    }

    /**
     * Create User
     *
     * @return View
     *
     * @author Darshan Baraiya
     */
    public function create()
    {
        $roles = $this->userService->getRoles();

        return view('users.add', ['roles' => $roles]);
    }

    /**
     * Store User
     *
     * @return View Users
     *
     * @author Darshan Baraiya
     */
    public function store(Request $request)
    {
        // Validations using service
        $validationRules = $this->userService->getStoreValidationRules();
        $request->validate($validationRules, [
            'new_password.regex' => 'The new password format is invalid. It must contain at least one number, one special character, one uppercase letter, one lowercase letter, and be between 8 and 16 characters long.',
        ]);

        try {
            // Create user through service
            $user = $this->userService->createUser($request->all());

            // Assign roles through service
            $this->userService->assignRoles($user, [$request->role_id]);

            return $this->redirectWithSuccess('users.index',
                $this->getSuccessMessage('User', 'created'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('User', 'create').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Update Status Of User
     *
     * @return RedirectResponse Page With Success
     *
     * @author Darshan Baraiya
     */
    public function updateStatus(int $user_id, int $status): RedirectResponse
    {
        // Validation
        $validator = Validator::make([
            'user_id' => $user_id,
            'status' => $status,
        ], [
            'user_id' => 'required|exists:users,id',
            'status' => 'required|in:0,1',
        ]);

        // If Validations Fails
        if ($validator->fails()) {
            return $this->redirectWithError($validator->errors()->first());
        }

        try {
            // Update status through service
            $this->userService->updateStatus($user_id, $status);

            return $this->redirectWithSuccess('users.index',
                $this->getSuccessMessage('User status', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('User status', 'update').': '.$throwable->getMessage());
        }
    }

    /**
     * Edit User
     *
     * @return View
     *
     * @author Darshan Baraiya
     */
    public function edit(User $user)
    {
        $roles = $this->userService->getRoles();

        return view('users.edit')->with([
            'roles' => $roles,
            'user' => $user,
        ]);
    }

    /**
     * Update User
     *
     * @return View Users
     *
     * @author Darshan Baraiya
     */
    public function update(Request $request, User $user)
    {
        // Validations using service
        $validationRules = $this->userService->getUpdateValidationRules($user);
        $request->validate($validationRules);

        // Check if new password is not empty in the request
        if (! empty($request->input('new_password'))) {
            $passwordRules = $this->userService->getPasswordValidationRules();
            $customMessages = [
                'new_password.regex' => 'The new password format is invalid. It must contain at least one number, one special character, one uppercase letter, one lowercase letter, and be between 8 and 16 characters long.',
            ];

            // Perform the validation
            $validator = Validator::make($request->all(), $passwordRules, $customMessages);

            // Check if validation fails
            if ($validator->fails()) {
                return $this->redirectWithError($validator->errors()->first())->withInput();
            }
        }

        try {
            // Update user through service
            $this->userService->updateUser($user, $request->all());

            // Assign roles through service
            $this->userService->assignRoles($user, [$request->role_id]);

            // Handle password change if provided
            if (! empty($request->input('new_password'))) {
                $this->userService->changePassword($user, $request->new_password);
            }

            return $this->redirectWithSuccess('users.index',
                $this->getSuccessMessage('User', 'updated'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('User', 'update').': '.$throwable->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete User
     *
     * @return RedirectResponse Users
     *
     * @author Darshan Baraiya
     */
    public function delete(User $user): RedirectResponse
    {
        try {
            // Delete user through service
            $this->userService->deleteUser($user);

            return $this->redirectWithSuccess('users.index',
                $this->getSuccessMessage('User', 'deleted'));
        } catch (\Throwable $throwable) {
            return $this->redirectWithError(
                $this->getErrorMessage('User', 'delete').': '.$throwable->getMessage());
        }
    }

    protected function getExportRelations(): array
    {
        return ['roles'];
    }

    protected function getSearchableFields(): array
    {
        return ['first_name', 'last_name', 'email', 'mobile_number'];
    }

    protected function getExportConfig(Request $request): array
    {
        return [
            'format' => $request->get('format', 'xlsx'),
            'filename' => 'users',
            'with_headings' => true,
            'auto_size' => true,
            'relations' => $this->getExportRelations(),
            'order_by' => ['column' => 'created_at', 'direction' => 'desc'],
            'headings' => ['ID', 'First Name', 'Last Name', 'Email', 'Mobile Number', 'Role', 'Status', 'Created Date'],
            'mapping' => fn ($model): array => [
                $model->id,
                $model->first_name,
                $model->last_name ?? 'N/A',
                $model->email,
                $model->mobile_number ?? 'N/A',
                $model->roles->first()->name ?? 'N/A',
                $model->status ? 'Active' : 'Inactive',
                $model->created_at->format('Y-m-d H:i:s'),
            ],
            'with_mapping' => true,
        ];
    }
}
