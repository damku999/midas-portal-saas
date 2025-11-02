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

class ExecuteCampaignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 1; // No retry for campaigns

    public $timeout = 3600; // 1 hour timeout for large campaigns

    protected int $campaignId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $campaignId)
    {
        $this->campaignId = $campaignId;
    }

    /**
     * Execute the job.
     */
    public function handle(LeadWhatsAppService $whatsappService): void
    {
        Log::info('Campaign execution job started', [
            'campaign_id' => $this->campaignId,
        ]);

        try {
            $results = $whatsappService->executeCampaign($this->campaignId);

            Log::info('Campaign execution job completed', [
                'campaign_id' => $this->campaignId,
                'results' => $results,
            ]);

        } catch (Exception $e) {
            Log::error('Campaign execution job failed', [
                'campaign_id' => $this->campaignId,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Campaign execution permanently failed', [
            'campaign_id' => $this->campaignId,
            'error' => $exception->getMessage(),
        ]);
    }
}
