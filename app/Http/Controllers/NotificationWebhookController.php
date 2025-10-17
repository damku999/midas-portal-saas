<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Services\NotificationLoggerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class NotificationWebhookController extends Controller
{
    public function __construct(protected NotificationLoggerService $loggerService) {}

    /**
     * Handle WhatsApp delivery status webhook
     *
     * Expected payload from BotMasterSender:
     * {
     *   "log_id": 123,
     *   "status": "delivered|read|failed",
     *   "timestamp": "2025-10-08 12:00:00",
     *   "message_id": "wamid.xxxxx",
     *   "error": "error message if failed"
     * }
     */
    public function whatsappDeliveryStatus(Request $request)
    {
        try {
            // Validate webhook payload
            $validated = $request->validate([
                'log_id' => 'required|integer|exists:notification_logs,id',
                'status' => 'required|string|in:sent,delivered,read,failed',
                'timestamp' => 'nullable|string',
                'message_id' => 'nullable|string',
                'error' => 'nullable|string',
            ]);

            Log::info('WhatsApp webhook received', $validated);

            // Update notification status
            $log = $this->loggerService->updateStatusFromWebhook(
                $validated['log_id'],
                $validated['status'],
                [
                    'timestamp' => $validated['timestamp'] ?? now()->toDateTimeString(),
                    'message_id' => $validated['message_id'] ?? null,
                    'error' => $validated['error'] ?? null,
                    'provider' => 'whatsapp',
                    'webhook_ip' => $request->ip(),
                ]
            );

            if ($log instanceof NotificationLog) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully',
                    'log_id' => $log->id,
                    'new_status' => $log->status,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Notification log not found',
            ], 404);

        } catch (ValidationException $e) {
            Log::warning('WhatsApp webhook validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('WhatsApp webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle Email delivery status webhook
     *
     * Expected payload (from email service provider):
     * {
     *   "log_id": 123,
     *   "status": "delivered|opened|bounced|failed",
     *   "timestamp": "2025-10-08 12:00:00",
     *   "email_id": "xxx",
     *   "bounce_reason": "reason if bounced"
     * }
     */
    public function emailDeliveryStatus(Request $request)
    {
        try {
            // Validate webhook payload
            $validated = $request->validate([
                'log_id' => 'required|integer|exists:notification_logs,id',
                'status' => 'required|string|in:sent,delivered,opened,bounced,failed',
                'timestamp' => 'nullable|string',
                'email_id' => 'nullable|string',
                'bounce_reason' => 'nullable|string',
            ]);

            Log::info('Email webhook received', $validated);

            // Map email-specific statuses to our standard statuses
            $mappedStatus = match ($validated['status']) {
                'opened' => 'read',
                'bounced' => 'failed',
                default => $validated['status'],
            };

            // Update notification status
            $log = $this->loggerService->updateStatusFromWebhook(
                $validated['log_id'],
                $mappedStatus,
                [
                    'timestamp' => $validated['timestamp'] ?? now()->toDateTimeString(),
                    'email_id' => $validated['email_id'] ?? null,
                    'bounce_reason' => $validated['bounce_reason'] ?? null,
                    'error' => $validated['bounce_reason'] ?? null,
                    'provider' => 'email',
                    'webhook_ip' => $request->ip(),
                ]
            );

            if ($log instanceof NotificationLog) {
                return response()->json([
                    'success' => true,
                    'message' => 'Status updated successfully',
                    'log_id' => $log->id,
                    'new_status' => $log->status,
                ], 200);
            }

            return response()->json([
                'success' => false,
                'message' => 'Notification log not found',
            ], 404);

        } catch (ValidationException $e) {
            Log::warning('Email webhook validation failed', [
                'errors' => $e->errors(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            Log::error('Email webhook processing failed', [
                'error' => $e->getMessage(),
                'payload' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Internal server error',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test webhook endpoint (for development/testing)
     */
    public function test(Request $request)
    {
        Log::info('Webhook test endpoint called', [
            'payload' => $request->all(),
            'ip' => $request->ip(),
            'headers' => $request->headers->all(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Webhook endpoint is working',
            'received_data' => $request->all(),
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
}
