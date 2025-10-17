<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\RedirectResponse;

/**
 * Abstract Base CRUD Controller
 *
 * Provides common CRUD controller functionality including standardized middleware setup.
 * Eliminates duplicate permission middleware code across all CRUD controllers.
 */
abstract class AbstractBaseCrudController extends Controller
{
    /**
     * Setup permission middleware for CRUD operations
     *
     * This method provides standardized permission middleware setup for all CRUD controllers,
     * ensuring consistent security patterns across the application.
     *
     * @param  string  $entityName  The entity name for permission checks (e.g., 'broker', 'addon-cover')
     */
    protected function setupPermissionMiddleware(string $entityName): void
    {
        $this->middleware('auth');
        $this->middleware(sprintf('permission:%s-list|%s-create|%s-edit|%s-delete', $entityName, $entityName, $entityName, $entityName), ['only' => ['index']]);
        $this->middleware(sprintf('permission:%s-create', $entityName), ['only' => ['create', 'store', 'updateStatus']]);
        $this->middleware(sprintf('permission:%s-edit', $entityName), ['only' => ['edit', 'update']]);
        $this->middleware(sprintf('permission:%s-delete', $entityName), ['only' => ['delete']]);
    }

    /**
     * Setup custom permission middleware
     *
     * For controllers that need custom permission patterns beyond standard CRUD.
     *
     * @param  array  $permissions  Array of permission configurations
     * @return void
     *
     * Example usage:
     * $this->setupCustomPermissionMiddleware([
     *     ['permission' => 'user-list', 'only' => ['index']],
     *     ['permission' => 'user-create', 'only' => ['create', 'store']],
     * ]);
     */
    protected function setupCustomPermissionMiddleware(array $permissions): void
    {
        $this->middleware('auth');

        foreach ($permissions as $permission) {
            $this->middleware('permission:'.$permission['permission'], $permission['only'] ?? []);
        }
    }

    /**
     * Setup authentication middleware only
     *
     * For controllers that need authentication but no specific permissions.
     */
    protected function setupAuthMiddleware(): void
    {
        $this->middleware('auth');
    }

    /**
     * Setup guest middleware
     *
     * For controllers that should only be accessible to guests (not authenticated users).
     */
    protected function setupGuestMiddleware(): void
    {
        $this->middleware('guest');
    }

    /**
     * Get standardized success message for CRUD operations
     *
     * @param  string  $entityName  The entity name (e.g., 'Broker', 'Addon Cover')
     * @param  string  $operation  The operation performed ('created', 'updated', 'deleted')
     */
    protected function getSuccessMessage(string $entityName, string $operation): string
    {
        return sprintf('%s %s successfully!', $entityName, $operation);
    }

    /**
     * Get standardized error message for CRUD operations
     *
     * @param  string  $entityName  The entity name (e.g., 'Broker', 'Addon Cover')
     * @param  string  $operation  The operation attempted ('create', 'update', 'delete')
     */
    protected function getErrorMessage(string $entityName, string $operation): string
    {
        return sprintf('Failed to %s %s. Please try again.', $operation, $entityName);
    }

    /**
     * Get redirect response with success message
     *
     * @param  string|null  $route  The route to redirect to (null for back)
     * @param  string  $message  The success message
     * @param  array  $routeParameters  Optional route parameters
     */
    protected function redirectWithSuccess(?string $route, string $message, array $routeParameters = []): RedirectResponse
    {
        if ($route === null) {
            return redirect()->back()->with('success', $message);
        }

        if ($routeParameters !== []) {
            return redirect()->route($route, $routeParameters)->with('success', $message);
        }

        return redirect()->route($route)->with('success', $message);
    }

    /**
     * Get redirect response with error message
     *
     * @param  string  $message  The error message
     */
    protected function redirectWithError(string $message): RedirectResponse
    {
        return redirect()->back()->with('error', $message);
    }

    /**
     * Get redirect response with validation errors
     *
     * @param  Validator  $validator  The validator instance
     */
    protected function redirectWithValidationErrors(Validator $validator): RedirectResponse
    {
        return redirect()->back()->withErrors($validator)->withInput();
    }
}
