<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Exceptions\TenantCouldNotBeIdentifiedOnDomainException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {});

        // Handle tenant identification failures
        $this->renderable(function (TenantCouldNotBeIdentifiedOnDomainException $e, $request) {
            // Log the error for debugging
            Log::error('Tenant could not be identified', [
                'domain' => $request->getHost(),
                'url' => $request->fullUrl(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'exception' => $e->getMessage(),
            ]);

            // Return user-friendly error page
            return response()->view('errors.tenant', [
                'domain' => $request->getHost(),
            ], 404);
        });
    }

    /**
     * Render an exception into an HTTP response.
     *
     * SECURITY FIX #17: Prevent verbose error messages in production
     * - Returns generic error messages in production to prevent information disclosure
     * - Logs full error details for debugging
     * - Generates error_id for support reference
     */
    public function render($request, Throwable $e): Response
    {
        // SECURITY: In production, hide detailed error messages from users
        if (!config('app.debug') && !$this->shouldntReport($e)) {
            // Generate unique error ID for support reference
            $errorId = uniqid('err_', true);

            // Log full error details with error ID
            Log::error('Application error', [
                'error_id' => $errorId,
                'exception' => get_class($e),
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_id' => auth()->id(),
            ]);

            // Return generic error message to user
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'An error occurred while processing your request.',
                    'error_id' => $errorId,
                ], 500);
            }

            // For web requests, show generic error view
            return response()->view('errors.500', [
                'error_id' => $errorId,
                'message' => 'An error occurred while processing your request. Please contact support with error ID: ' . $errorId,
            ], 500);
        }

        return parent::render($request, $e);
    }
}
