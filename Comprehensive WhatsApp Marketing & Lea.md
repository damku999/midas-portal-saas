 ⎿  Comprehensive WhatsApp Marketing & Lead Communication System Plan

     Overview

     Implement a robust WhatsApp marketing system for leads with single/bulk messaging, attachment support, campaign management, and enhanced
     customer-lead navigation.

     ---
     Phase 1: WhatsApp Messaging Infrastructure (Core Features)

     1.1 Database Migrations

     Create new tables:

     lead_whatsapp_messages table

     - id, lead_id (FK), message, attachment_path, attachment_type, sent_at, status (pending/sent/failed/delivered)
     - sent_by (user_id), campaign_id (nullable), error_message, api_response, timestamps

     lead_whatsapp_campaigns table

     - id, name, description, message_template, attachment_path, status (draft/scheduled/active/completed/paused)
     - target_criteria (JSON: filters for status, source, priority, date ranges)
     - scheduled_at, sent_count, failed_count, delivered_count, created_by, timestamps

     lead_whatsapp_campaign_leads table (pivot)

     - id, campaign_id (FK), lead_id (FK), status (pending/sent/failed), sent_at, delivered_at, error_message

     ---
     1.2 Service Layer Enhancements

     Create LeadWhatsAppService.php

     Methods:
     - sendSingleMessage($leadId, $message, $attachmentPath = null) - Send to single lead
     - sendBulkMessages($leadIds, $message, $attachmentPath = null) - Parallel bulk sending with queue support
     - createCampaign($data) - Campaign setup with target filtering
     - executeCampaign($campaignId) - Send messages to all targeted leads
     - getCampaignStatistics($campaignId) - Real-time delivery tracking
     - retryFailedMessages($campaignId) - Auto-retry logic

     Integration with existing WhatsAppApiTrait:
     - Use whatsAppSendMessage() for text-only
     - Use whatsAppSendMessageWithAttachment() for file uploads
     - Implement notification logging via LogsNotificationsTrait

     ---
     1.3 Controller: LeadWhatsAppController.php

     Routes & Methods:

     Single Lead Messaging

     - POST /leads/{lead}/whatsapp/send → sendWhatsApp()
       - Validate: message (required, max 4096), attachment (optional, max 5MB, types: pdf/jpg/png/doc)
       - Send immediately with real-time status

     Bulk Messaging

     - POST /leads/whatsapp/bulk-send → bulkSend()
       - Validate: lead_ids[], message, attachment
       - Queue processing for >10 leads
       - Return job ID for progress tracking

     Campaign Management

     - GET /leads/whatsapp/campaigns → campaigns() - List all campaigns
     - GET /leads/whatsapp/campaigns/create → createCampaign() - Campaign builder
     - POST /leads/whatsapp/campaigns → storeCampaign() - Save campaign
     - GET /leads/whatsapp/campaigns/{id} → showCampaign() - Campaign details & stats
     - POST /leads/whatsapp/campaigns/{id}/execute → executeCampaign() - Launch campaign
     - POST /leads/whatsapp/campaigns/{id}/pause → pauseCampaign()
     - POST /leads/whatsapp/campaigns/{id}/retry-failed → retryFailed()

     Analytics

     - GET /leads/whatsapp/analytics → analytics() - Dashboard with charts

     ---
     Phase 2: Frontend UI Components

     2.1 Lead Index Page (resources/views/leads/index.blade.php)

     Add Bulk WhatsApp Actions:
     <button type="button" class="btn btn-success btn-sm" onclick="showBulkWhatsAppModal()">
         <i class="fab fa-whatsapp me-1"></i>Send WhatsApp
     </button>

     Bulk WhatsApp Modal:
     - Message textarea (with character counter, emoji picker)
     - Attachment upload (drag-drop support, preview)
     - Template selector (pre-saved message templates)
     - Preview section showing selected leads count
     - Send options: immediate vs scheduled

     ---
     2.2 Lead Show Page (resources/views/leads/show.blade.php)

     Add WhatsApp Communication Tab:
     <div class="card">
         <div class="card-header">
             <h6><i class="fab fa-whatsapp"></i> WhatsApp Communication</h6>
             <button onclick="showSendWhatsAppModal()">Send WhatsApp</button>
         </div>
         <div class="card-body">
             <!-- Message history timeline -->
             <!-- Quick templates -->
             <!-- Attachment gallery -->
         </div>
     </div>

     Single WhatsApp Modal:
     - Message composer
     - Attachment uploader
     - Template library
     - Character counter
     - Send button with loading state

     ---
     2.3 Campaign Management Interface

     New Page: resources/views/leads/whatsapp/campaigns/index.blade.php

     Features:
     - Campaign list with stats (sent/delivered/failed counts)
     - Filter by status (draft/active/completed)
     - Quick actions (execute, pause, view details)
     - Create campaign button

     Campaign Builder: resources/views/leads/whatsapp/campaigns/create.blade.php

     Interactive Builder:
     1. Step 1: Target Selection
       - Visual filter builder (status, source, priority, date range, custom filters)
       - Live preview of matching leads count
       - CSV upload for specific lead IDs
     2. Step 2: Message Composition
       - Rich text editor with variables: {name}, {mobile}, {assigned_to}
       - Template library
       - Attachment uploader with preview
       - Character counter (WhatsApp limit: 4096)
     3. Step 3: Schedule & Settings
       - Send now vs schedule
       - Throttling options (messages per minute to avoid API rate limits)
       - Retry settings for failed messages
     4. Step 4: Review & Launch
       - Preview message with sample data
       - Estimated delivery time
       - Cost estimation (if applicable)
       - Launch button

     Campaign Details: resources/views/leads/whatsapp/campaigns/show.blade.php
     - Real-time statistics dashboard
     - Delivery status breakdown (pie chart)
     - Failed messages table with retry button
     - Individual lead delivery status
     - Export campaign report

     ---
     Phase 3: Analytics & Reporting

     3.1 WhatsApp Analytics Dashboard

     New Page: resources/views/leads/whatsapp/analytics.blade.php

     Metrics:
     - Total messages sent (today/week/month)
     - Delivery rate (%)
     - Campaign performance comparison
     - Top performing message templates
     - Peak sending times
     - Lead response tracking

     Charts:
     - Line chart: Messages sent over time
     - Bar chart: Delivery status breakdown
     - Pie chart: Campaign success rates
     - Heat map: Best sending times

     ---
     Phase 4: Customer-Lead Navigation Enhancement

     4.1 Customer Edit Page Enhancement

     File: resources/views/customers/edit.blade.php

     Add navigation button in card header (line 14-20):
     <div class="card-header py-2 d-flex justify-content-between align-items-center">
         <h6 class="mb-0 fw-bold text-primary">Edit Customer</h6>
         <div class="btn-group">
             @if($customer->converted_from_lead_id)
                 <a href="{{ route('leads.show', $customer->converted_from_lead_id) }}"
                    class="btn btn-outline-info btn-sm">
                     <i class="fas fa-user-tie me-1"></i>View Original Lead
                 </a>
             @endif
             <a href="{{ route('customers.index') }}" class="btn btn-outline-secondary btn-sm">
                 <i class="fas fa-chevron-left me-2"></i>Back
             </a>
         </div>
     </div>

     Add info card showing lead history:
     <!-- Lead Conversion Info Card -->
     @if($customer->converted_from_lead_id)
     <div class="alert alert-info mb-3">
         <div class="d-flex justify-content-between align-items-center">
             <div>
                 <i class="fas fa-info-circle me-2"></i>
                 <strong>Converted from Lead:</strong> {{ $customer->originalLead->lead_number }}
                 <br>
                 <small class="text-muted">Converted on: {{ $customer->converted_at->format('d M Y, h:i A') }}</small>
             </div>
             <a href="{{ route('leads.show', $customer->converted_from_lead_id) }}"
                class="btn btn-sm btn-info">
                 View Full Lead History
             </a>
         </div>
     </div>
     @endif

     4.2 Customer Model Enhancement

     Add relationship in app/Models/Customer.php:
     public function originalLead()
     {
         return $this->belongsTo(Lead::class, 'converted_from_lead_id');
     }

     4.3 Lead Model Enhancement

     Add relationship in app/Models/Lead.php:
     public function convertedCustomer()
     {
         return $this->hasOne(Customer::class, 'converted_from_lead_id');
     }

     ---
     Phase 5: Advanced WhatsApp Marketing Features

     5.1 Message Templates Library

     Database: lead_whatsapp_templates table
     - id, name, category (greeting/follow-up/reminder/promotional), message_template, variables (JSON)
     - attachment_path, is_active, usage_count, created_by, timestamps

     UI: Template Manager
     - CRUD operations for templates
     - Variable placeholders: {name}, {mobile}, {source}, {assigned_to}, {product_interest}
     - Category organization
     - Usage analytics

     5.2 Smart Scheduling

     Features:
     - Quiet hours enforcement (don't send 10 PM - 9 AM)
     - Optimal send time suggestions based on historical response rates
     - Time zone awareness
     - Throttling to avoid API rate limits (configurable: e.g., 100 messages/minute)

     5.3 Follow-up Automation

     Auto-Follow-up Rules:
     - Send automatic follow-up if no response after X days
     - Birthday/anniversary wishes
     - Policy renewal reminders
     - Re-engagement campaigns for cold leads

     Database: lead_whatsapp_automation_rules table
     - id, name, trigger_type (days_since_last_contact/birthday/status_change)
     - trigger_value (e.g., 7 days), message_template_id, is_active, timestamps

     5.4 Response Tracking

     If WhatsApp API supports webhooks:
     - Track message delivery status
     - Track read receipts
     - Log customer replies (if Business API)
     - Auto-create lead activities from responses

     ---
     Phase 6: Permissions & Security

     6.1 New Permissions

     Add to permissions table:
     - lead-whatsapp-send - Send single WhatsApp
     - lead-whatsapp-bulk-send - Send bulk WhatsApp
     - lead-whatsapp-campaign-create - Create campaigns
     - lead-whatsapp-campaign-view - View campaigns
     - lead-whatsapp-campaign-execute - Launch campaigns
     - lead-whatsapp-analytics-view - View analytics
     - lead-whatsapp-template-manage - Manage templates

     6.2 File Upload Security

     Validation Rules:
     - Max size: 5MB (configurable in app settings)
     - Allowed types: PDF, JPEG, PNG, DOC, DOCX
     - Virus scanning (if ClamAV available)
     - Sanitize filenames
     - Store in secure location: storage/app/lead-whatsapp-attachments/

     ---
     Phase 7: Queue & Background Processing

     7.1 Queue Jobs

     Create Jobs:

     SendBulkWhatsAppJob.php

     public function handle()
     {
         foreach ($this->leadIds as $leadId) {
             SendSingleWhatsAppJob::dispatch($leadId, $this->message, $this->attachment);
         }
     }

     SendSingleWhatsAppJob.php

     public function handle(LeadWhatsAppService $service)
     {
         $service->sendSingleMessage($this->leadId, $this->message, $this->attachment);
     }

     ExecuteCampaignJob.php

     public function handle(LeadWhatsAppService $service)
     {
         $service->executeCampaign($this->campaignId);
     }

     7.2 Progress Tracking

     Use Laravel's job batching:
     Bus::batch($jobs)
         ->name('WhatsApp Bulk Send')
         ->dispatch();

     Real-time progress UI:
     - WebSocket or polling for status updates
     - Progress bar showing sent/failed/pending
     - Cancel campaign button

     ---
     Implementation Checklist

     Backend Tasks:

     - Create migrations for 4 new tables
     - Build LeadWhatsAppService with 6 core methods
     - Create LeadWhatsAppController with 10 routes
     - Add customer-lead relationship methods
     - Implement queue jobs for bulk processing
     - Add permissions and middleware

     Frontend Tasks:

     - Add WhatsApp button to leads index bulk actions
     - Create bulk WhatsApp modal with attachment support
     - Add WhatsApp tab to lead show page
     - Build campaign management interface (4 pages)
     - Create analytics dashboard with charts
     - Add "View Lead" button to customer edit page
     - Build template library UI

     Testing Tasks:

     - Test single message sending
     - Test bulk sending with 100+ leads
     - Test attachment uploads (all formats)
     - Test campaign creation & execution
     - Test failure handling & retry logic
     - Test permissions enforcement
     - Test customer-lead navigation

     Configuration:

     - Add WhatsApp settings to app_settings table
     - Configure queue workers
     - Set up file storage limits
     - Configure rate limiting

     ---
     Technical Specifications

     File Structure:
     app/
     ├── Http/Controllers/LeadWhatsAppController.php
     ├── Services/LeadWhatsAppService.php
     ├── Jobs/
     │   ├── SendBulkWhatsAppJob.php
     │   ├── SendSingleWhatsAppJob.php
     │   └── ExecuteCampaignJob.php
     ├── Models/
     │   ├── LeadWhatsAppMessage.php
     │   ├── LeadWhatsAppCampaign.php
     │   └── LeadWhatsAppTemplate.php

     database/migrations/
     ├── 2025_xx_xx_create_lead_whatsapp_messages_table.php
     ├── 2025_xx_xx_create_lead_whatsapp_campaigns_table.php
     ├── 2025_xx_xx_create_lead_whatsapp_templates_table.php
     └── 2025_xx_xx_add_converted_from_lead_id_to_customers.php

     resources/views/leads/
     ├── whatsapp/
     │   ├── campaigns/
     │   │   ├── index.blade.php
     │   │   ├── create.blade.php
     │   │   └── show.blade.php
     │   ├── analytics.blade.php
     │   └── modals/
     │       ├── bulk-send.blade.php
     │       └── single-send.blade.php

     Routes:
     Route::prefix('leads')->group(function() {
         // Single send
         Route::post('{lead}/whatsapp/send', 'sendWhatsApp');

         // Bulk send
         Route::post('whatsapp/bulk-send', 'bulkSend');

         // Campaigns
         Route::resource('whatsapp/campaigns', LeadWhatsAppCampaignController::class);
         Route::post('whatsapp/campaigns/{id}/execute', 'executeCampaign');

         // Analytics
         Route::get('whatsapp/analytics', 'analytics');
     });

     ---
     This plan provides a comprehensive WhatsApp marketing system with professional campaign management, bulk messaging, attachment support, and
     enhanced navigation between customers and their original leads.
