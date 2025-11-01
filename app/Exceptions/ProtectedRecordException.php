<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ProtectedRecordException extends Exception
{
    /**
     * The protected record that triggered this exception
     */
    protected $protectedRecord;

    /**
     * The action that was attempted
     */
    protected $attemptedAction;

    /**
     * Additional context for the exception
     */
    protected $context;

    /**
     * Create a new ProtectedRecordException instance
     *
     * @param  string  $message
     * @param  mixed  $protectedRecord
     * @param  string  $attemptedAction
     * @param  array  $context
     */
    public function __construct(
        string $message = 'This record is protected and cannot be modified',
        $protectedRecord = null,
        string $attemptedAction = 'unknown',
        array $context = []
    ) {
        parent::__construct($message, 403);

        $this->protectedRecord = $protectedRecord;
        $this->attemptedAction = $attemptedAction;
        $this->context = $context;

        $this->logException();
    }

    /**
     * Get the protected record
     */
    public function getProtectedRecord()
    {
        return $this->protectedRecord;
    }

    /**
     * Get the attempted action
     */
    public function getAttemptedAction(): string
    {
        return $this->attemptedAction;
    }

    /**
     * Get the exception context
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Log the exception with full context
     */
    protected function logException(): void
    {
        if (! config('protection.logging.enabled', true)) {
            return;
        }

        $logData = [
            'exception' => 'ProtectedRecordException',
            'message' => $this->getMessage(),
            'attempted_action' => $this->attemptedAction,
            'record_type' => $this->protectedRecord ? get_class($this->protectedRecord) : null,
            'record_id' => $this->protectedRecord?->id ?? null,
            'record_email' => $this->protectedRecord?->email ?? null,
            'user_id' => auth()->id() ?? null,
            'user_email' => auth()->user()?->email ?? null,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'url' => request()->fullUrl(),
            'context' => $this->context,
            'timestamp' => now()->toISOString(),
        ];

        Log::channel(config('protection.logging.log_channel', 'stack'))
            ->log(
                config('protection.logging.log_level', 'warning'),
                'Protected Record Violation Attempt',
                $logData
            );

        // Log to database if enabled
        if (config('protection.logging.log_to_database', true)) {
            $this->logToDatabase($logData);
        }
    }

    /**
     * Log to database audit table
     */
    protected function logToDatabase(array $logData): void
    {
        try {
            \DB::table(config('protection.logging.log_table', 'audit_logs'))->insert([
                'auditable_type' => $logData['record_type'],
                'auditable_id' => $logData['record_id'],
                'actor_type' => 'App\\Models\\User',
                'actor_id' => $logData['user_id'],
                'action' => 'protected_record_violation',
                'event' => $this->attemptedAction,
                'event_category' => 'security',
                'properties' => json_encode([
                    'message' => $logData['message'],
                    'context' => $logData['context'],
                ]),
                'ip_address' => $logData['ip_address'],
                'user_agent' => $logData['user_agent'],
                'occurred_at' => now(),
                'severity' => 'high',
                'risk_score' => 8.0,
                'risk_level' => 'high',
                'is_suspicious' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Silently fail database logging to avoid breaking the application
            Log::error('Failed to log protected record exception to database', [
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Render the exception as an HTTP response
     */
    public function render(Request $request)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'error' => 'Protected Record',
                'message' => $this->getMessage(),
                'status' => 403,
                'action' => $this->attemptedAction,
            ], 403);
        }

        // Check if custom error view exists, otherwise use generic 403
        if (view()->exists('errors.protected-record')) {
            return response()->view('errors.protected-record', [
                'message' => $this->getMessage(),
                'action' => $this->attemptedAction,
            ], 403);
        }

        return response()->view('errors.403', [
            'message' => $this->getMessage(),
        ], 403);
    }

    /**
     * Create a deletion prevented exception
     */
    public static function deletionPrevented($record, array $context = []): self
    {
        return new self(
            config('protection.messages.deletion_prevented', 'This record is protected and cannot be deleted.'),
            $record,
            'deletion',
            $context
        );
    }

    /**
     * Create a status change prevented exception
     */
    public static function statusChangePrevented($record, array $context = []): self
    {
        return new self(
            config('protection.messages.status_change_prevented', 'This record is protected and cannot be deactivated.'),
            $record,
            'status_change',
            $context
        );
    }

    /**
     * Create an email change prevented exception
     */
    public static function emailChangePrevented($record, array $context = []): self
    {
        return new self(
            config('protection.messages.email_change_prevented', 'This record is protected and the email address cannot be modified.'),
            $record,
            'email_change',
            $context
        );
    }

    /**
     * Create a modification prevented exception
     */
    public static function modificationPrevented($record, string $action, array $context = []): self
    {
        return new self(
            config('protection.messages.modification_prevented', 'This record is protected and cannot be modified in this way.'),
            $record,
            $action,
            $context
        );
    }
}
