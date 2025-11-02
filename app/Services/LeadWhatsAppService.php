<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\LeadWhatsAppCampaign;
use App\Models\LeadWhatsAppCampaignLead;
use App\Models\LeadWhatsAppMessage;
use App\Traits\WhatsAppApiTrait;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class LeadWhatsAppService
{
    use WhatsAppApiTrait;

    /**
     * Send WhatsApp message to a single lead
     *
     * @param  int  $leadId  Lead ID
     * @param  string  $message  Message content
     * @param  UploadedFile|string|null  $attachment  File path or UploadedFile
     * @param  int|null  $userId  User ID sending the message
     * @param  int|null  $campaignId  Campaign ID if part of campaign
     *
     * @throws Exception
     */
    public function sendSingleMessage(
        int $leadId,
        string $message,
        $attachment = null,
        ?int $userId = null,
        ?int $campaignId = null
    ): LeadWhatsAppMessage {
        $lead = Lead::findOrFail($leadId);

        if (empty($lead->mobile_number)) {
            throw new Exception("Lead {$lead->name} does not have a mobile number.");
        }

        // Handle attachment upload if provided
        $attachmentPath = null;
        $attachmentType = null;

        if ($attachment instanceof UploadedFile) {
            $attachmentPath = $attachment->store('lead-whatsapp-attachments', 'public');
            $attachmentType = $this->getAttachmentType($attachment->getMimeType());
        } elseif (is_string($attachment) && file_exists($attachment)) {
            $attachmentPath = $attachment;
            $attachmentType = $this->getAttachmentType(mime_content_type($attachment));
        }

        // Create message record
        $whatsappMessage = LeadWhatsAppMessage::create([
            'lead_id' => $leadId,
            'message' => $message,
            'attachment_path' => $attachmentPath,
            'attachment_type' => $attachmentType,
            'status' => 'pending',
            'sent_by' => $userId ?? auth()->id(),
            'campaign_id' => $campaignId,
        ]);

        try {
            // Send via WhatsApp API
            if ($attachmentPath) {
                $fullPath = storage_path('app/public/'.$attachmentPath);
                $apiResponse = $this->whatsAppSendMessageWithAttachment(
                    $message,
                    $lead->mobile_number,
                    $fullPath
                );
            } else {
                $apiResponse = $this->whatsAppSendMessage(
                    $message,
                    $lead->mobile_number
                );
            }

            // Mark as sent
            $whatsappMessage->markAsSent(json_decode($apiResponse, true) ?? []);

            Log::info('WhatsApp message sent to lead', [
                'lead_id' => $leadId,
                'message_id' => $whatsappMessage->id,
                'has_attachment' => ! empty($attachmentPath),
            ]);

            return $whatsappMessage;

        } catch (Exception $e) {
            // Mark as failed
            $whatsappMessage->markAsFailed($e->getMessage());

            Log::error('WhatsApp message failed', [
                'lead_id' => $leadId,
                'message_id' => $whatsappMessage->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Send WhatsApp messages to multiple leads (bulk)
     *
     * @param  array  $leadIds  Array of lead IDs
     * @param  string  $message  Message content
     * @param  UploadedFile|string|null  $attachment  File path or UploadedFile
     * @param  int|null  $userId  User ID sending the messages
     * @return array ['success' => int, 'failed' => int, 'messages' => array]
     */
    public function sendBulkMessages(
        array $leadIds,
        string $message,
        $attachment = null,
        ?int $userId = null
    ): array {
        $results = [
            'success' => 0,
            'failed' => 0,
            'messages' => [],
            'errors' => [],
        ];

        // Handle attachment once for all messages
        $attachmentPath = null;
        if ($attachment instanceof UploadedFile) {
            $attachmentPath = $attachment->store('lead-whatsapp-attachments', 'public');
        } elseif (is_string($attachment)) {
            $attachmentPath = $attachment;
        }

        foreach ($leadIds as $leadId) {
            try {
                $whatsappMessage = $this->sendSingleMessage(
                    $leadId,
                    $message,
                    $attachmentPath,
                    $userId
                );

                $results['success']++;
                $results['messages'][] = $whatsappMessage;

            } catch (Exception $e) {
                $results['failed']++;
                $results['errors'][] = [
                    'lead_id' => $leadId,
                    'error' => $e->getMessage(),
                ];
            }
        }

        return $results;
    }

    /**
     * Create a new WhatsApp campaign
     *
     * @param  array  $data  Campaign data
     */
    public function createCampaign(array $data): LeadWhatsAppCampaign
    {
        // Handle attachment if provided
        if (isset($data['attachment']) && $data['attachment'] instanceof UploadedFile) {
            $data['attachment_path'] = $data['attachment']->store('lead-whatsapp-attachments', 'public');
            $data['attachment_type'] = $this->getAttachmentType($data['attachment']->getMimeType());
            unset($data['attachment']);
        }

        // Set created_by if not provided
        $data['created_by'] = $data['created_by'] ?? auth()->id();

        // Get target leads based on criteria
        $targetLeads = $this->getTargetLeads($data['target_criteria'] ?? []);
        $data['total_leads'] = $targetLeads->count();

        $campaign = LeadWhatsAppCampaign::create($data);

        // Create campaign-lead relationships
        foreach ($targetLeads as $lead) {
            LeadWhatsAppCampaignLead::create([
                'campaign_id' => $campaign->id,
                'lead_id' => $lead->id,
                'status' => 'pending',
            ]);
        }

        return $campaign;
    }

    /**
     * Execute a campaign (send messages to all targeted leads)
     *
     * @param  int  $campaignId  Campaign ID
     * @return array Execution results
     *
     * @throws Exception
     */
    public function executeCampaign(int $campaignId): array
    {
        $campaign = LeadWhatsAppCampaign::findOrFail($campaignId);

        if (! $campaign->canExecute()) {
            throw new Exception("Campaign cannot be executed in current status: {$campaign->status}");
        }

        $campaign->markAsActive();

        $results = [
            'campaign_id' => $campaignId,
            'started_at' => now()->toDateTimeString(),
            'sent' => 0,
            'failed' => 0,
            'pending' => 0,
        ];

        $pendingLeads = $campaign->campaignLeads()->pending()->with('lead')->get();

        foreach ($pendingLeads as $campaignLead) {
            try {
                // Replace template variables with lead data
                $message = $this->renderMessageTemplate(
                    $campaign->message_template,
                    $campaignLead->lead
                );

                // Send message
                $whatsappMessage = $this->sendSingleMessage(
                    $campaignLead->lead_id,
                    $message,
                    $campaign->attachment_path,
                    $campaign->created_by,
                    $campaign->id
                );

                // Update campaign lead status
                $campaignLead->markAsSent();
                $campaign->incrementSent();
                $results['sent']++;

            } catch (Exception $e) {
                $campaignLead->markAsFailed($e->getMessage());
                $campaign->incrementFailed();
                $results['failed']++;

                Log::error('Campaign message failed', [
                    'campaign_id' => $campaignId,
                    'lead_id' => $campaignLead->lead_id,
                    'error' => $e->getMessage(),
                ]);
            }

            // Throttling to respect messages per minute limit
            if ($campaign->messages_per_minute > 0) {
                $delayMicroseconds = (60 / $campaign->messages_per_minute) * 1000000;
                usleep((int) $delayMicroseconds);
            }
        }

        // Mark campaign as completed if all messages processed
        $results['pending'] = $campaign->campaignLeads()->pending()->count();
        if ($results['pending'] === 0) {
            $campaign->markAsCompleted();
        }

        $results['completed_at'] = now()->toDateTimeString();

        return $results;
    }

    /**
     * Get campaign statistics
     *
     * @param  int  $campaignId  Campaign ID
     * @return array Statistics
     */
    public function getCampaignStatistics(int $campaignId): array
    {
        $campaign = LeadWhatsAppCampaign::with('campaignLeads')->findOrFail($campaignId);

        return [
            'campaign_id' => $campaignId,
            'name' => $campaign->name,
            'status' => $campaign->status,
            'total_leads' => $campaign->total_leads,
            'sent_count' => $campaign->sent_count,
            'failed_count' => $campaign->failed_count,
            'delivered_count' => $campaign->delivered_count,
            'read_count' => $campaign->read_count,
            'pending_count' => $campaign->getPendingCount(),
            'success_rate' => $campaign->getSuccessRate(),
            'delivery_rate' => $campaign->getDeliveryRate(),
            'read_rate' => $campaign->getReadRate(),
            'failure_rate' => $campaign->getFailureRate(),
            'started_at' => $campaign->started_at?->toDateTimeString(),
            'completed_at' => $campaign->completed_at?->toDateTimeString(),
        ];
    }

    /**
     * Retry failed messages in a campaign
     *
     * @param  int  $campaignId  Campaign ID
     * @return array Retry results
     */
    public function retryFailedMessages(int $campaignId): array
    {
        $campaign = LeadWhatsAppCampaign::findOrFail($campaignId);

        if (! $campaign->auto_retry_failed) {
            throw new Exception('Auto-retry is disabled for this campaign.');
        }

        $results = [
            'campaign_id' => $campaignId,
            'retried' => 0,
            'success' => 0,
            'failed' => 0,
            'skipped' => 0,
        ];

        $failedLeads = $campaign->campaignLeads()->retryable()->with('lead')->get();

        foreach ($failedLeads as $campaignLead) {
            if (! $campaignLead->canRetry()) {
                $results['skipped']++;

                continue;
            }

            try {
                $message = $this->renderMessageTemplate(
                    $campaign->message_template,
                    $campaignLead->lead
                );

                $this->sendSingleMessage(
                    $campaignLead->lead_id,
                    $message,
                    $campaign->attachment_path,
                    $campaign->created_by,
                    $campaign->id
                );

                $campaignLead->markAsSent();
                $campaignLead->incrementRetryCount();
                $campaign->increment('sent_count');
                $campaign->decrement('failed_count');

                $results['retried']++;
                $results['success']++;

            } catch (Exception $e) {
                $campaignLead->incrementRetryCount();
                $results['retried']++;
                $results['failed']++;

                Log::error('Campaign retry failed', [
                    'campaign_id' => $campaignId,
                    'lead_id' => $campaignLead->lead_id,
                    'retry_count' => $campaignLead->retry_count,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $results;
    }

    /**
     * Get target leads based on criteria
     *
     * @param  array|null  $criteria  Filter criteria
     * @return \Illuminate\Database\Eloquent\Collection
     */
    protected function getTargetLeads($criteria = null)
    {
        $query = Lead::query()->whereNotNull('mobile_number');

        // If no criteria provided or criteria is null, return all leads with mobile numbers
        if (empty($criteria)) {
            return $query->get();
        }

        if (isset($criteria['status_id']) && ! empty($criteria['status_id'])) {
            $query->where('status_id', $criteria['status_id']);
        }

        if (isset($criteria['source_id']) && ! empty($criteria['source_id'])) {
            $query->where('source_id', $criteria['source_id']);
        }

        if (isset($criteria['priority']) && ! empty($criteria['priority'])) {
            $query->where('priority', $criteria['priority']);
        }

        if (isset($criteria['assigned_to']) && ! empty($criteria['assigned_to'])) {
            $query->where('assigned_to', $criteria['assigned_to']);
        }

        if (isset($criteria['date_from']) && ! empty($criteria['date_from'])) {
            $query->whereDate('created_at', '>=', $criteria['date_from']);
        }

        if (isset($criteria['date_to']) && ! empty($criteria['date_to'])) {
            $query->whereDate('created_at', '<=', $criteria['date_to']);
        }

        if (isset($criteria['lead_ids']) && is_array($criteria['lead_ids']) && ! empty($criteria['lead_ids'])) {
            $query->whereIn('id', $criteria['lead_ids']);
        }

        return $query->get();
    }

    /**
     * Render message template with lead data and system variables
     *
     * @param  string  $template  Message template
     * @param  Lead  $lead  Lead model
     * @return string Rendered message
     */
    protected function renderMessageTemplate(string $template, Lead $lead): string
    {
        // Lead-specific variables
        $variables = [
            '{name}' => $lead->name,
            '{mobile}' => $lead->mobile_number,
            '{email}' => $lead->email ?? 'N/A',
            '{source}' => $lead->source->name ?? 'N/A',
            '{status}' => $lead->status->name ?? 'N/A',
            '{priority}' => $lead->priority ?? 'N/A',
            '{assigned_to}' => $lead->assignedUser?->name ?? 'N/A',
            '{product_interest}' => $lead->product_interest ?? 'N/A',
            '{lead_number}' => $lead->lead_number,

            // System/App variables (dynamic)
            '{company_name}' => config('app.name', 'Midas Portal'),
            '{company_website}' => url('/'),
            '{company_phone}' => config('app.phone', '+91-1234567890'),
            '{company_email}' => config('mail.from.address', 'info@example.com'),
            '{advisor_name}' => $lead->assignedUser?->name ?? auth()->user()?->name ?? 'Insurance Team',
            '{current_date}' => now()->format('d M Y'),
            '{current_year}' => now()->format('Y'),
            '{portal_url}' => route('login'),
        ];

        return str_replace(array_keys($variables), array_values($variables), $template);
    }

    /**
     * Determine attachment type from MIME type
     *
     * @param  string  $mimeType  MIME type
     * @return string Attachment type
     */
    protected function getAttachmentType(string $mimeType): string
    {
        if (str_starts_with($mimeType, 'image/')) {
            return 'image';
        }

        if (str_starts_with($mimeType, 'video/')) {
            return 'video';
        }

        if (str_starts_with($mimeType, 'audio/')) {
            return 'audio';
        }

        if (in_array($mimeType, ['application/pdf'])) {
            return 'pdf';
        }

        if (in_array($mimeType, [
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])) {
            return 'document';
        }

        return 'file';
    }

    /**
     * Get WhatsApp analytics data
     *
     * @param  array  $filters  Date range and other filters
     * @return array Analytics data
     */
    public function getAnalytics(array $filters = []): array
    {
        $query = LeadWhatsAppMessage::query();

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $totalMessages = (clone $query)->count();
        $sentMessages = (clone $query)->sent()->count();
        $failedMessages = (clone $query)->failed()->count();
        $deliveredMessages = (clone $query)->delivered()->count();

        return [
            'total_messages' => $totalMessages,
            'sent_messages' => $sentMessages,
            'failed_messages' => $failedMessages,
            'delivered_messages' => $deliveredMessages,
            'pending_messages' => $totalMessages - $sentMessages - $failedMessages,
            'delivery_rate' => $sentMessages > 0 ? round(($deliveredMessages / $sentMessages) * 100, 2) : 0,
            'failure_rate' => $totalMessages > 0 ? round(($failedMessages / $totalMessages) * 100, 2) : 0,
            'messages_with_attachment' => (clone $query)->withAttachment()->count(),
        ];
    }
}
