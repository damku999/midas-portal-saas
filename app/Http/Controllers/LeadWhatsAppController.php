<?php

namespace App\Http\Controllers;

use App\Models\Lead;
use App\Models\LeadWhatsAppMessage;
use App\Models\LeadWhatsAppCampaign;
use App\Models\LeadWhatsAppTemplate;
use App\Services\LeadWhatsAppService;
use App\Jobs\SendBulkWhatsAppJob;
use App\Jobs\ExecuteCampaignJob;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Exception;

class LeadWhatsAppController extends Controller
{
    protected LeadWhatsAppService $whatsappService;

    public function __construct(LeadWhatsAppService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    /**
     * Send WhatsApp message to single lead
     */
    public function sendWhatsApp(Request $request, Lead $lead)
    {
        $validator = Validator::make($request->all(), [
            'message' => 'required|string|max:4096',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $whatsappMessage = $this->whatsappService->sendSingleMessage(
                $lead->id,
                $request->message,
                $request->file('attachment'),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'WhatsApp message sent successfully',
                'data' => $whatsappMessage,
            ]);

        } catch (Exception $e) {
            Log::error('WhatsApp send failed', [
                'lead_id' => $lead->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send WhatsApp message: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Send bulk WhatsApp messages
     */
    public function bulkSend(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lead_ids' => 'required|array|min:1',
            'lead_ids.*' => 'exists:leads,id',
            'message' => 'required|string|max:4096',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $leadIds = $request->lead_ids;

            // For >10 leads, use queue
            if (count($leadIds) > 10) {
                $attachmentPath = null;
                if ($request->hasFile('attachment')) {
                    $attachmentPath = $request->file('attachment')->store('lead-whatsapp-attachments', 'public');
                }

                SendBulkWhatsAppJob::dispatch(
                    $leadIds,
                    $request->message,
                    $attachmentPath,
                    auth()->id()
                );

                return response()->json([
                    'success' => true,
                    'message' => 'Bulk WhatsApp messages queued for processing',
                    'total_leads' => count($leadIds),
                    'status' => 'queued',
                ]);
            }

            // For ≤10 leads, send immediately
            $results = $this->whatsappService->sendBulkMessages(
                $leadIds,
                $request->message,
                $request->file('attachment'),
                auth()->id()
            );

            return response()->json([
                'success' => true,
                'message' => 'Bulk WhatsApp messages sent',
                'data' => $results,
            ]);

        } catch (Exception $e) {
            Log::error('Bulk WhatsApp send failed', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk messages: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all campaigns
     */
    public function campaigns(Request $request)
    {
        $query = LeadWhatsAppCampaign::with('creator')
            ->withCount('campaignLeads');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by creator
        if ($request->has('created_by')) {
            $query->where('created_by', $request->created_by);
        }

        $campaigns = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('leads.whatsapp.campaigns.index', compact('campaigns'));
    }

    /**
     * Show campaign creation form
     */
    public function createCampaign()
    {
        $templates = LeadWhatsAppTemplate::active()->get();
        $leads = Lead::whereNotNull('mobile_number')->get();

        return view('leads.whatsapp.campaigns.create', compact('templates', 'leads'));
    }

    /**
     * Store new campaign
     */
    public function storeCampaign(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'message_template' => 'required|string|max:4096',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
            'status' => 'required|in:draft,scheduled',
            'target_criteria' => 'nullable|array',
            'scheduled_at' => 'nullable|date|after:now',
            'messages_per_minute' => 'nullable|integer|min:1|max:1000',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->all();

            if ($request->hasFile('attachment')) {
                $data['attachment'] = $request->file('attachment');
            }

            $campaign = $this->whatsappService->createCampaign($data);

            return redirect()->route('leads.whatsapp.campaigns.show', $campaign->id)
                ->with('success', 'Campaign created successfully');

        } catch (Exception $e) {
            Log::error('Campaign creation failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create campaign: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show campaign details
     */
    public function showCampaign($id)
    {
        $campaign = LeadWhatsAppCampaign::with(['creator', 'campaignLeads.lead'])
            ->findOrFail($id);

        $statistics = $this->whatsappService->getCampaignStatistics($id);

        return view('leads.whatsapp.campaigns.show', compact('campaign', 'statistics'));
    }

    /**
     * Execute campaign
     */
    public function executeCampaign($id)
    {
        try {
            $campaign = LeadWhatsAppCampaign::findOrFail($id);

            if (!$campaign->canExecute()) {
                return response()->json([
                    'success' => false,
                    'message' => "Campaign cannot be executed in current status: {$campaign->status}",
                ], 400);
            }

            // Queue the campaign execution for large campaigns
            if ($campaign->total_leads > 50) {
                ExecuteCampaignJob::dispatch($id);

                return response()->json([
                    'success' => true,
                    'message' => 'Campaign execution queued',
                    'status' => 'queued',
                ]);
            }

            // Execute immediately for small campaigns
            $results = $this->whatsappService->executeCampaign($id);

            return response()->json([
                'success' => true,
                'message' => 'Campaign executed successfully',
                'data' => $results,
            ]);

        } catch (Exception $e) {
            Log::error('Campaign execution failed', [
                'campaign_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to execute campaign: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Pause campaign
     */
    public function pauseCampaign($id)
    {
        try {
            $campaign = LeadWhatsAppCampaign::findOrFail($id);

            if (!$campaign->canPause()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Campaign cannot be paused in current status',
                ], 400);
            }

            $campaign->markAsPaused();

            return response()->json([
                'success' => true,
                'message' => 'Campaign paused successfully',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to pause campaign: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Retry failed messages in campaign
     */
    public function retryFailed($id)
    {
        try {
            $results = $this->whatsappService->retryFailedMessages($id);

            return response()->json([
                'success' => true,
                'message' => 'Failed messages retry completed',
                'data' => $results,
            ]);

        } catch (Exception $e) {
            Log::error('Campaign retry failed', [
                'campaign_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retry messages: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Show analytics dashboard
     */
    public function analytics(Request $request)
    {
        $filters = [
            'date_from' => $request->input('date_from', now()->subDays(30)->format('Y-m-d')),
            'date_to' => $request->input('date_to', now()->format('Y-m-d')),
        ];

        $analytics = $this->whatsappService->getAnalytics($filters);

        // Get recent campaigns
        $recentCampaigns = LeadWhatsAppCampaign::with('creator')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Get top templates
        $topTemplates = LeadWhatsAppTemplate::active()
            ->orderBy('usage_count', 'desc')
            ->limit(5)
            ->get();

        return view('leads.whatsapp.analytics', compact('analytics', 'recentCampaigns', 'topTemplates', 'filters'));
    }

    /**
     * Get message history for a lead
     */
    public function messageHistory(Lead $lead)
    {
        $messages = $lead->whatsappMessages()
            ->with('sentBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return response()->json([
            'success' => true,
            'data' => $messages,
        ]);
    }

    /**
     * List templates
     */
    public function templates()
    {
        $templates = LeadWhatsAppTemplate::with('creator')
            ->orderBy('usage_count', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    /**
     * Get template by ID
     */
    public function getTemplate($id)
    {
        $template = LeadWhatsAppTemplate::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $template,
        ]);
    }

    /**
     * Display template management page
     */
    public function templatesIndex()
    {
        $templates = LeadWhatsAppTemplate::with('creator')
            ->orderBy('usage_count', 'desc')
            ->paginate(20);

        return view('leads.whatsapp.templates.index', compact('templates'));
    }

    /**
     * Show template creation form
     */
    public function createTemplate()
    {
        return view('leads.whatsapp.templates.create');
    }

    /**
     * Store new template
     */
    public function storeTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:greeting,follow-up,promotion,promotional,reminder,general',
            'message_template' => 'required|string|max:4096',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $data = $request->only(['name', 'category', 'message_template']);
            $data['created_by'] = auth()->id();
            $data['is_active'] = true;

            if ($request->hasFile('attachment')) {
                $data['attachment_path'] = $request->file('attachment')->store('lead-whatsapp-attachments', 'public');
            }

            // Extract variables from template
            preg_match_all('/\{(\w+)\}/', $data['message_template'], $matches);
            $data['variables'] = $matches[1] ?? [];

            $template = LeadWhatsAppTemplate::create($data);

            return redirect()->route('leads.whatsapp.templates.index')
                ->with('success', 'Template created successfully');

        } catch (Exception $e) {
            Log::error('Template creation failed', [
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to create template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show template edit form
     */
    public function editTemplate($id)
    {
        $template = LeadWhatsAppTemplate::findOrFail($id);
        return view('leads.whatsapp.templates.edit', compact('template'));
    }

    /**
     * Update template
     */
    public function updateTemplate(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category' => 'required|string|in:greeting,follow-up,promotion,promotional,reminder,general',
            'message_template' => 'required|string|max:4096',
            'attachment' => 'nullable|file|max:5120|mimes:pdf,jpg,jpeg,png,doc,docx',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $template = LeadWhatsAppTemplate::findOrFail($id);

            $data = $request->only(['name', 'category', 'message_template']);
            $data['is_active'] = $request->has('is_active');

            if ($request->hasFile('attachment')) {
                // Delete old attachment if exists
                if ($template->attachment_path) {
                    \Storage::disk('public')->delete($template->attachment_path);
                }
                $data['attachment_path'] = $request->file('attachment')->store('lead-whatsapp-attachments', 'public');
            }

            // Extract variables from template
            preg_match_all('/\{(\w+)\}/', $data['message_template'], $matches);
            $data['variables'] = $matches[1] ?? [];

            $template->update($data);

            return redirect()->route('leads.whatsapp.templates.index')
                ->with('success', 'Template updated successfully');

        } catch (Exception $e) {
            Log::error('Template update failed', [
                'template_id' => $id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()
                ->with('error', 'Failed to update template: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete template
     */
    public function deleteTemplate($id)
    {
        try {
            $template = LeadWhatsAppTemplate::findOrFail($id);

            // Delete attachment if exists
            if ($template->attachment_path) {
                \Storage::disk('public')->delete($template->attachment_path);
            }

            $template->delete();

            return response()->json([
                'success' => true,
                'message' => 'Template deleted successfully',
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete template: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Webhook handler for WhatsApp delivery status updates
     * This should be called by your WhatsApp API provider
     */
    public function webhookDeliveryStatus(Request $request)
    {
        try {
            // Log incoming webhook for debugging
            Log::info('WhatsApp Webhook Received', $request->all());

            // Validate webhook data
            $messageId = $request->input('message_id');
            $status = $request->input('status'); // sent, delivered, read, failed
            $leadMobile = $request->input('mobile');
            $errorMessage = $request->input('error_message');

            if (!$messageId && !$leadMobile) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid webhook data'
                ], 400);
            }

            // Find the message by API response or mobile number
            $query = LeadWhatsAppMessage::query();

            if ($messageId) {
                $query->whereRaw("JSON_EXTRACT(api_response, '$.message_id') = ?", [$messageId]);
            } else {
                // Find by mobile number and most recent pending/sent message
                $lead = Lead::where('mobile_number', $leadMobile)->first();
                if ($lead) {
                    $query->where('lead_id', $lead->id)
                          ->whereIn('status', ['pending', 'sent'])
                          ->latest();
                }
            }

            $message = $query->first();

            if (!$message) {
                Log::warning('WhatsApp message not found for webhook', [
                    'message_id' => $messageId,
                    'mobile' => $leadMobile
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found'
                ], 404);
            }

            // Update message status based on webhook
            switch (strtolower($status)) {
                case 'delivered':
                    $message->markAsDelivered();
                    // Update campaign stats if part of campaign
                    if ($message->campaign_id) {
                        $message->campaign->incrementDelivered();
                    }
                    break;

                case 'read':
                    $message->markAsRead();
                    // Update campaign stats if part of campaign
                    if ($message->campaign_id) {
                        $message->campaign->incrementRead();
                    }
                    break;

                case 'failed':
                    $message->markAsFailed($errorMessage ?? 'Delivery failed');
                    break;

                case 'sent':
                    // Already marked as sent when initially dispatched
                    break;
            }

            Log::info('WhatsApp status updated', [
                'message_id' => $message->id,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);

        } catch (Exception $e) {
            Log::error('Webhook processing failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Webhook processing failed'
            ], 500);
        }
    }
}
