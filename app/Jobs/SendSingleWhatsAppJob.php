<?php

namespace App\Jobs;

use App\Services\LeadWhatsAppService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendSingleWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $timeout = 120;

    public $backoff = [30, 60, 120]; // Retry delays in seconds

    protected int $leadId;

    protected string $message;

    protected ?string $attachmentPath;

    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        int $leadId,
        string $message,
        ?string $attachmentPath = null,
        ?int $userId = null
    ) {
        $this->leadId = $leadId;
        $this->message = $message;
        $this->attachmentPath = $attachmentPath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(LeadWhatsAppService $whatsappService): void
    {
        try {
            $whatsappService->sendSingleMessage(
                $this->leadId,
                $this->message,
                $this->attachmentPath,
                $this->userId
            );

            Log::info('WhatsApp message sent via job', [
                'lead_id' => $this->leadId,
                'attempt' => $this->attempts(),
            ]);

        } catch (Exception $e) {
            Log::error('WhatsApp job failed', [
                'lead_id' => $this->leadId,
                'attempt' => $this->attempts(),
                'error' => $e->getMessage(),
            ]);

            // Re-throw to trigger retry
            throw $e;
        }
    }

    /**
     * Handle job failure after all retries.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('WhatsApp job permanently failed', [
            'lead_id' => $this->leadId,
            'attempts' => $this->attempts(),
            'error' => $exception->getMessage(),
        ]);
    }
}
