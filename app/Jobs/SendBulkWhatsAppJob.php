<?php

namespace App\Jobs;

use App\Services\LeadWhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendBulkWhatsAppJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 600;

    protected array $leadIds;
    protected string $message;
    protected ?string $attachmentPath;
    protected ?int $userId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        array $leadIds,
        string $message,
        ?string $attachmentPath = null,
        ?int $userId = null
    ) {
        $this->leadIds = $leadIds;
        $this->message = $message;
        $this->attachmentPath = $attachmentPath;
        $this->userId = $userId;
    }

    /**
     * Execute the job.
     */
    public function handle(LeadWhatsAppService $whatsappService): void
    {
        Log::info('Bulk WhatsApp job started', [
            'total_leads' => count($this->leadIds),
            'user_id' => $this->userId,
        ]);

        // Dispatch individual jobs for each lead
        foreach ($this->leadIds as $leadId) {
            SendSingleWhatsAppJob::dispatch(
                $leadId,
                $this->message,
                $this->attachmentPath,
                $this->userId
            );
        }

        Log::info('Bulk WhatsApp job completed', [
            'total_dispatched' => count($this->leadIds),
        ]);
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Bulk WhatsApp job failed', [
            'total_leads' => count($this->leadIds),
            'error' => $exception->getMessage(),
        ]);
    }
}
