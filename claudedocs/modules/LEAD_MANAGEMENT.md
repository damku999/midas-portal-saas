# Lead Management

**Version:** 1.0
**Last Updated:** 2025-11-06
**Status:** Production

## Overview

Complete lead management system with CRUD operations, activity tracking, lead conversion to customers, WhatsApp campaigns, follow-up management, and comprehensive analytics.

### Key Features

- **Lead CRUD**: Create, read, update, delete lead records
- **Lead Number Generation**: Auto-generated sequential lead numbers (LD-YYYYMM-XXXX)
- **Lead Status Workflow**: Customizable status pipeline with conversion/lost tracking
- **Lead Activities**: Track calls, emails, meetings, notes, documents, quotations
- **Lead Assignment**: Assign leads to users/relationship managers
- **Follow-up Management**: Schedule and track follow-up dates with overdue alerts
- **Lead Conversion**: Convert leads to customers (auto-create or link existing)
- **Bulk Operations**: Bulk assign, bulk convert, bulk status update
- **WhatsApp Campaigns**: Target lead segments with automated WhatsApp campaigns
- **Document Management**: Upload and track lead documents
- **Priority Management**: Low, medium, high priority classification
- **Lead Sources**: Track lead origin (website, referral, campaign, etc.)
- **Reference Tracking**: Track referral sources and relationship managers
- **Export**: CSV export with full lead data and relationships
- **Statistics**: Conversion rates, source performance, user performance

## Lead Model

**File**: `app/Models/Lead.php`

### Traits Used

- `HasFactory` - Factory support
- `SoftDeletes` - Soft delete support
- `ProtectedRecord` - Protection from deletion

### Key Attributes

**Personal Information**:
- `lead_number` (string) - Auto-generated unique identifier (LD-202511-0001)
- `name` (string) - Lead full name
- `email` (string, nullable) - Email address
- `mobile_number` (string) - Primary mobile number
- `alternate_mobile` (string, nullable) - Alternate contact number
- `date_of_birth` (date, nullable) - Birth date
- `age` (int, nullable) - Calculated from date_of_birth

**Address**:
- `address` (text, nullable) - Full address
- `city` (string, nullable) - City
- `state` (string, nullable) - State
- `pincode` (string, nullable) - PIN code

**Professional**:
- `occupation` (string, nullable) - Occupation/profession

**Lead Management**:
- `source_id` (bigint) - Foreign key to lead_sources
- `product_interest` (string, nullable) - Interested insurance product
- `status_id` (bigint) - Foreign key to lead_statuses
- `priority` (enum: 'low', 'medium', 'high', nullable) - Lead priority
- `assigned_to` (bigint, nullable) - Foreign key to users (assigned staff)
- `relationship_manager_id` (bigint, nullable) - Foreign key to relationship_managers
- `reference_user_id` (bigint, nullable) - Foreign key to reference_users (referrer)

**Follow-up**:
- `next_follow_up_date` (date, nullable) - Scheduled follow-up date
- `remarks` (text, nullable) - General notes/remarks

**Conversion**:
- `converted_customer_id` (bigint, nullable) - Foreign key to customers (if converted)
- `converted_at` (timestamp, nullable) - Conversion timestamp
- `conversion_notes` (text, nullable) - Conversion notes

**Lost Lead**:
- `lost_reason` (text, nullable) - Reason for lost lead
- `lost_at` (timestamp, nullable) - Lost timestamp

**System**:
- `is_protected` (boolean) - Protection from deletion
- `protected_reason` (text, nullable) - Why record is protected
- `created_by/updated_by` - Audit trail

### Relationships

```php
// Lead Source & Status
$lead->source(); // BelongsTo LeadSource
$lead->status(); // BelongsTo LeadStatus

// Assignment
$lead->assignedUser(); // BelongsTo User (assigned staff member)
$lead->relationshipManager(); // BelongsTo RelationshipManager
$lead->referenceUser(); // BelongsTo ReferenceUser (referrer)

// Conversion
$lead->convertedCustomer(); // BelongsTo Customer

// Activities & Documents
$lead->activities(); // HasMany LeadActivity (ordered by created_at desc)
$lead->documents(); // HasMany LeadDocument

// WhatsApp
$lead->whatsappMessages(); // HasMany LeadWhatsAppMessage
$lead->whatsappCampaigns(); // BelongsToMany LeadWhatsAppCampaign
// Pivot: ['status', 'sent_at', 'delivered_at', 'read_at', 'error_message', 'retry_count', 'last_retry_at']

// Audit
$lead->creator(); // BelongsTo User (created_by)
$lead->updater(); // BelongsTo User (updated_by)
```

### Query Scopes

```php
Lead::active()->get(); // Active leads (not converted, not lost)
Lead::converted()->get(); // Converted leads only
Lead::lost()->get(); // Lost leads only
Lead::byStatus($statusId)->get(); // Filter by status ID
Lead::bySource($sourceId)->get(); // Filter by source ID
Lead::assignedTo($userId)->get(); // Filter by assigned user
Lead::byPriority('high')->get(); // Filter by priority
Lead::followUpDue()->get(); // Follow-up due today or earlier
Lead::followUpOverdue()->get(); // Follow-up overdue (past date)
```

### Methods

#### Lead Number Generation

```php
Lead::generateLeadNumber(): string
```

**Format**: `LD-YYYYMM-XXXX`
- Example: `LD-202511-0001`
- Auto-increments within each month
- Pads number to 4 digits

**Boot Hook**:
```php
static::creating(function ($lead) {
    if (empty($lead->lead_number)) {
        $lead->lead_number = static::generateLeadNumber();
    }

    // Auto-calculate age from date_of_birth
    if (!empty($lead->date_of_birth) && empty($lead->age)) {
        $lead->age = Carbon::parse($lead->date_of_birth)->age;
    }
});
```

#### Status Checks

```php
$lead->isConverted(): bool // Check if status->is_converted == true
$lead->isLost(): bool // Check if status->is_lost == true
$lead->isActive(): bool // Check if not converted and not lost
$lead->hasFollowUpDue(): bool // Check if follow-up date is past
```

## LeadStatus Model

**File**: `app/Models/LeadStatus.php`

### Attributes

- `name` (string) - Status name (e.g., "New Lead", "Contacted", "Qualified", "Converted")
- `description` (text, nullable) - Status description
- `color` (string, nullable) - Color code for UI display
- `order` (int) - Display order
- `is_converted` (boolean) - Marks lead as converted
- `is_lost` (boolean) - Marks lead as lost
- `is_active` (boolean) - Enable/disable status
- `is_default` (boolean) - Default status for new leads

### Scopes

```php
LeadStatus::active()->get(); // Active statuses only
LeadStatus::ordered()->get(); // Ordered by 'order' column
LeadStatus::default()->first(); // Get default status
```

## LeadSource Model

**File**: `app/Models/LeadSource.php`

### Attributes

- `name` (string) - Source name (e.g., "Website", "Referral", "Facebook", "Google Ads")
- `description` (text, nullable) - Source description
- `is_active` (boolean) - Enable/disable source
- `order` (int) - Display order

### Scopes

```php
LeadSource::active()->get(); // Active sources only
LeadSource::ordered()->get(); // Ordered by 'order' column
```

## LeadActivity Model

**File**: `app/Models/LeadActivity.php`

### Activity Types

```php
const TYPE_CALL = 'call';
const TYPE_EMAIL = 'email';
const TYPE_MEETING = 'meeting';
const TYPE_NOTE = 'note';
const TYPE_STATUS_CHANGE = 'status_change';
const TYPE_ASSIGNMENT = 'assignment';
const TYPE_DOCUMENT = 'document';
const TYPE_QUOTATION = 'quotation';
```

### Attributes

- `lead_id` (bigint) - Foreign key to leads
- `activity_type` (string) - Activity type constant
- `subject` (string) - Activity subject/title
- `description` (text, nullable) - Detailed description
- `outcome` (text, nullable) - Activity outcome/result
- `next_action` (text, nullable) - Recommended next action
- `scheduled_at` (timestamp, nullable) - Scheduled date/time
- `completed_at` (timestamp, nullable) - Completion timestamp
- `created_by` (bigint) - Foreign key to users

### Scopes

```php
LeadActivity::ofType('call')->get(); // Filter by activity type
LeadActivity::scheduled()->get(); // Scheduled but not completed
LeadActivity::completed()->get(); // Completed activities
LeadActivity::pending()->get(); // Not yet completed
LeadActivity::overdue()->get(); // Scheduled but past due
LeadActivity::today()->get(); // Scheduled for today
LeadActivity::upcoming()->get(); // Scheduled for future
LeadActivity::byLead($leadId)->get(); // Filter by lead
LeadActivity::byCreator($userId)->get(); // Filter by creator
LeadActivity::recent(7)->get(); // Created in last N days
```

### Methods

```php
$activity->isCompleted(): bool // Check if completed
$activity->isPending(): bool // Check if not completed
$activity->isOverdue(): bool // Check if scheduled + past due + not completed
$activity->markAsCompleted(): void // Set completed_at to now()
```

### Usage Example

```php
use App\Models\LeadActivity;

// Log a call activity
LeadActivity::create([
    'lead_id' => $lead->id,
    'activity_type' => LeadActivity::TYPE_CALL,
    'subject' => 'Initial contact call',
    'description' => 'Discussed insurance needs for car and home',
    'outcome' => 'Interested in comprehensive car insurance',
    'next_action' => 'Send quotation for comprehensive plan',
    'scheduled_at' => now(),
    'completed_at' => now(),
    'created_by' => auth()->id(),
]);

// Schedule a meeting
$meeting = LeadActivity::create([
    'lead_id' => $lead->id,
    'activity_type' => LeadActivity::TYPE_MEETING,
    'subject' => 'Policy discussion meeting',
    'description' => 'Discuss policy options and premium calculations',
    'scheduled_at' => now()->addDays(2)->setTime(14, 0),
    'created_by' => auth()->id(),
]);

// Check if meeting is overdue
if ($meeting->isOverdue()) {
    // Send reminder
}

// Mark as completed
$meeting->markAsCompleted();
```

## WhatsApp Campaigns

### LeadWhatsAppCampaign Model

**File**: `app/Models/LeadWhatsAppCampaign.php`

#### Attributes

**Campaign Details**:
- `name` (string) - Campaign name
- `description` (text, nullable) - Campaign description
- `message_template` (text) - WhatsApp message template
- `attachment_path` (string, nullable) - Attachment file path
- `attachment_type` (string, nullable) - Attachment MIME type

**Status & Scheduling**:
- `status` (string) - Campaign status: draft, scheduled, active, paused, completed, cancelled
- `scheduled_at` (timestamp, nullable) - Scheduled start time
- `started_at` (timestamp, nullable) - Actual start time
- `completed_at` (timestamp, nullable) - Completion time

**Targeting**:
- `target_criteria` (json) - Lead filtering criteria (status_id, source_id, priority, etc.)

**Statistics**:
- `total_leads` (int) - Total leads in campaign
- `sent_count` (int) - Messages sent successfully
- `failed_count` (int) - Failed message attempts
- `delivered_count` (int) - Messages delivered
- `read_count` (int) - Messages read by recipients

**Configuration**:
- `messages_per_minute` (int) - Rate limiting (default: 60)
- `auto_retry_failed` (boolean) - Auto-retry failed messages
- `max_retry_attempts` (int) - Maximum retry attempts (default: 3)

**System**:
- `created_by` (bigint) - Foreign key to users

#### Campaign Status Flow

```
draft → scheduled → active → completed
           ↓           ↓
       cancelled    paused → active
```

#### Methods

**Status Checks**:
```php
$campaign->isDraft(): bool
$campaign->isScheduled(): bool
$campaign->isActive(): bool
$campaign->isCompleted(): bool
$campaign->isPaused(): bool
$campaign->isCancelled(): bool
$campaign->canExecute(): bool // Can start execution (draft, scheduled, paused)
$campaign->canPause(): bool // Can pause (active)
```

**Attachment**:
```php
$campaign->hasAttachment(): bool
$campaign->getAttachmentUrl(): ?string // Returns asset URL
```

**Statistics**:
```php
$campaign->getSuccessRate(): float // (delivered / sent) * 100
$campaign->getDeliveryRate(): float // (delivered / total_leads) * 100
$campaign->getReadRate(): float // (read / delivered) * 100
$campaign->getFailureRate(): float // (failed / total_leads) * 100
$campaign->getPendingCount(): int // total_leads - sent - failed
```

**Counters**:
```php
$campaign->incrementSent(): void
$campaign->incrementFailed(): void
$campaign->incrementDelivered(): void
$campaign->incrementRead(): void
```

**Status Updates**:
```php
$campaign->markAsActive(): void // status = 'active', started_at = now()
$campaign->markAsCompleted(): void // status = 'completed', completed_at = now()
$campaign->markAsPaused(): void
$campaign->markAsCancelled(): void
```

#### Relationships

```php
$campaign->creator(); // BelongsTo User
$campaign->messages(); // HasMany LeadWhatsAppMessage
$campaign->campaignLeads(); // HasMany LeadWhatsAppCampaignLead (pivot records)
$campaign->leads(); // BelongsToMany Lead with pivot data
```

#### Pivot Table (lead_whatsapp_campaign_leads)

Tracks individual lead message status within campaign:
- `lead_id` - Foreign key to leads
- `campaign_id` - Foreign key to lead_whatsapp_campaigns
- `status` - pending, sent, delivered, read, failed
- `sent_at` (timestamp, nullable) - Message sent time
- `delivered_at` (timestamp, nullable) - Delivery confirmation time
- `read_at` (timestamp, nullable) - Read receipt time
- `error_message` (text, nullable) - Error details if failed
- `retry_count` (int) - Number of retry attempts
- `last_retry_at` (timestamp, nullable) - Last retry timestamp

#### Scopes

```php
LeadWhatsAppCampaign::draft()->get();
LeadWhatsAppCampaign::scheduled()->get();
LeadWhatsAppCampaign::active()->get();
LeadWhatsAppCampaign::completed()->get();
LeadWhatsAppCampaign::paused()->get();
LeadWhatsAppCampaign::dueForExecution()->get(); // status='scheduled' AND scheduled_at <= now()
LeadWhatsAppCampaign::createdBy($userId)->get();
```

### Campaign Usage Examples

#### Create Campaign

```php
use App\Models\LeadWhatsAppCampaign;

$campaign = LeadWhatsAppCampaign::create([
    'name' => 'Q4 Insurance Renewal Campaign',
    'description' => 'Target high-priority leads for policy renewals',
    'message_template' => "Hi {name}, your insurance policy is due for renewal. We have exclusive offers for you! Contact us at {mobile}.",
    'status' => 'draft',
    'target_criteria' => [
        'priority' => 'high',
        'status_id' => [1, 2, 3], // New, Contacted, Qualified
        'source_id' => [5, 6], // Referral, Website
    ],
    'messages_per_minute' => 60,
    'auto_retry_failed' => true,
    'max_retry_attempts' => 3,
    'created_by' => auth()->id(),
]);

// Count target leads
$targetLeads = Lead::where('priority', 'high')
    ->whereIn('status_id', [1, 2, 3])
    ->whereIn('source_id', [5, 6])
    ->active()
    ->count();

$campaign->update(['total_leads' => $targetLeads]);
```

#### Schedule Campaign

```php
$campaign->update([
    'status' => 'scheduled',
    'scheduled_at' => now()->addHours(2), // Execute in 2 hours
]);
```

#### Execute Campaign (Background Job)

```php
use App\Jobs\ExecuteWhatsAppCampaignJob;

// Dispatch job when scheduled_at <= now()
ExecuteWhatsAppCampaignJob::dispatch($campaign);
```

**Job Logic**:
1. Mark campaign as active
2. Get target leads based on criteria
3. Attach leads to campaign via pivot table
4. Send messages with rate limiting
5. Update statistics (sent_count, failed_count)
6. Handle retries for failed messages
7. Update delivery/read status from webhooks
8. Mark campaign as completed when done

#### Monitor Campaign

```php
echo "Campaign: {$campaign->name}\n";
echo "Status: {$campaign->status}\n";
echo "Total Leads: {$campaign->total_leads}\n";
echo "Sent: {$campaign->sent_count}\n";
echo "Delivered: {$campaign->delivered_count} ({$campaign->getDeliveryRate()}%)\n";
echo "Read: {$campaign->read_count} ({$campaign->getReadRate()}%)\n";
echo "Failed: {$campaign->failed_count} ({$campaign->getFailureRate()}%)\n";
echo "Pending: {$campaign->getPendingCount()}\n";
echo "Success Rate: {$campaign->getSuccessRate()}%\n";
```

## Lead Conversion

### Conversion Methods

#### Auto-Conversion (Create New Customer)

```php
use App\Services\LeadConversionService;

$conversionService = app(LeadConversionService::class);

$result = $conversionService->convertLeadToCustomer($leadId, [
    'type' => 'Retail', // or 'Corporate'
    'pan_card_number' => 'ABCDE1234F',
    'aadhar_card_number' => '123456789012',
    'gst_number' => 'GST123', // For corporate
    'family_group_id' => 5, // Optional
    'conversion_notes' => 'Converted after quotation acceptance',
]);

// Result structure:
[
    'success' => true,
    'lead' => Lead instance,
    'customer' => Customer instance,
    'message' => 'Lead converted to customer successfully',
]
```

**Process**:
1. Validate lead (not already converted/lost)
2. Check for duplicate customer (email/mobile)
3. Create customer with lead data
4. Copy documents from lead to customer
5. Update lead with conversion details
6. Create LeadActivity record (TYPE_STATUS_CHANGE)
7. Fire LeadConverted event
8. Return result array

#### Link to Existing Customer

```php
$lead = $leadService->convertLeadToCustomer($leadId, $existingCustomerId, $notes);
```

**Process**:
1. Validate lead and customer
2. Link lead to customer (converted_customer_id)
3. Update conversion timestamps and notes
4. Create activity record
5. Fire LeadConverted event

#### Bulk Conversion

```php
$results = $conversionService->bulkConvertLeads([1, 2, 3, 4, 5]);

// Results structure:
[
    'total' => 5,
    'successful' => [
        ['lead_id' => 1, 'customer_id' => 10, 'lead_number' => 'LD-202511-0001'],
        ['lead_id' => 2, 'customer_id' => 11, 'lead_number' => 'LD-202511-0002'],
    ],
    'failed' => [
        ['lead_id' => 3, 'error' => 'Lead already converted', 'lead_number' => 'LD-202511-0003'],
        ['lead_id' => 4, 'error' => 'Duplicate customer email', 'lead_number' => 'LD-202511-0004'],
    ],
]
```

### Conversion Statistics

```php
$stats = $conversionService->getConversionStatistics([
    'date_from' => '2025-01-01',
    'date_to' => '2025-11-06',
    'source_id' => 5,
    'assigned_to' => 10,
]);

// Returns:
[
    'total_leads' => 500,
    'converted_count' => 120,
    'lost_count' => 50,
    'active_count' => 330,
    'conversion_rate' => 24.0, // percentage
    'lost_rate' => 10.0,
    'average_conversion_time_days' => 12.5,
    'by_source' => [
        ['source_name' => 'Website', 'total' => 200, 'converted' => 50, 'rate' => 25.0],
        ['source_name' => 'Referral', 'total' => 150, 'converted' => 45, 'rate' => 30.0],
        // ...
    ],
    'by_user' => [
        ['user_name' => 'John Doe', 'total' => 100, 'converted' => 30, 'rate' => 30.0],
        // ...
    ],
]
```

## LeadController

**File**: `app/Http/Controllers/LeadController.php`

### Routes & Actions

**List Leads**:
```php
GET /leads
public function index(Request $request)
```

**Filters**:
- `status_id` - Filter by status
- `source_id` - Filter by source
- `assigned_to` - Filter by assigned user
- `priority` - Filter by priority (low/medium/high)
- `search` - Search name, email, mobile
- `date_from / date_to` - Date range filter
- `sort_by` - Sort column (default: created_at)
- `sort_order` - Sort direction (default: desc)
- `per_page` - Results per page (default: 15)

**Create Lead**:
```php
GET /leads/create
public function create()

POST /leads
public function store(Request $request)
```

**Show Lead**:
```php
GET /leads/{id}
public function show(int $id)
```

Loads relationships:
- source, status
- assignedUser, relationshipManager, referenceUser
- convertedCustomer
- creator, updater
- activities.creator
- documents.uploader

**Edit Lead**:
```php
GET /leads/{id}/edit
public function edit(int $id)

PUT /leads/{id}
public function update(Request $request, int $id)
```

**Delete Lead**:
```php
DELETE /leads/{id}
public function destroy(int $id)
```

Soft deletes lead.

**Update Status**:
```php
POST /leads/{id}/update-status
public function updateStatus(Request $request, int $id)
```

Validates:
- `status_id` - Required, exists in lead_statuses
- `notes` - Optional notes

Creates LeadActivity record for status change.

**Assign Lead**:
```php
POST /leads/{id}/assign
public function assign(Request $request, int $id)
```

Validates:
- `assigned_to` - Required, exists in users

Creates LeadActivity record for assignment.

**Convert Lead (Auto)**:
```php
POST /leads/{id}/convert-auto
public function convertAuto(Request $request, int $id)
```

Validates:
- `type` - Optional (Corporate/Retail)
- `pan_card_number` - Optional
- `aadhar_card_number` - Optional
- `gst_number` - Optional
- `family_group_id` - Optional, exists in family_groups
- `conversion_notes` - Optional

Auto-creates new customer from lead data.

**Convert Lead (Link)**:
```php
POST /leads/{id}/convert
public function convert(Request $request, int $id)
```

Validates:
- `customer_id` - Required, exists in customers
- `notes` - Optional

Links lead to existing customer.

**Bulk Convert**:
```php
POST /leads/bulk-convert
public function bulkConvert(Request $request)
```

Validates:
- `lead_ids` - Required array, each exists in leads

Converts multiple leads to customers in batch.

**Mark as Lost**:
```php
POST /leads/{id}/mark-as-lost
public function markAsLost(Request $request, int $id)
```

Validates:
- `reason` - Required string

Updates lead with lost status and reason.

**Bulk Assign**:
```php
POST /leads/bulk-assign
public function bulkAssign(Request $request)
```

Validates:
- `lead_ids` - Required array
- `assigned_to` - Required, exists in users

Assigns multiple leads to a user.

**Export to CSV**:
```php
GET /leads/export
public function export(Request $request)
```

Exports all leads matching filters to CSV with full data.

**Statistics**:
```php
GET /leads/statistics
public function statistics()
```

Returns JSON with lead statistics.

**Conversion Statistics**:
```php
GET /leads/conversion-stats
public function conversionStats(Request $request)
```

Returns JSON with conversion analytics.

## LeadService

**File**: `app/Services/LeadService.php`

### Core Methods

```php
getAllLeads(array $filters, ?int $perPage = 15): LengthAwarePaginator
getLeadById(int $id): ?Lead
createLead(array $data): Lead
updateLead(int $id, array $data): Lead
deleteLead(int $id): bool
updateLeadStatus(int $id, int $statusId, ?string $notes = null): Lead
assignLeadTo(int $id, int $userId): Lead
markLeadAsLost(int $id, string $reason): Lead
convertLeadToCustomer(int $leadId, int $customerId, ?string $notes = null): Lead
getStatistics(): array
getLeadsByStatus(int $statusId): Collection
getLeadsBySource(int $sourceId): Collection
getLeadsByUser(int $userId): Collection
getOverdueFollowUps(): Collection
getTodayFollowUps(): Collection
searchLeads(string $query): Collection
```

## Database Schema

### leads Table

```sql
CREATE TABLE leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_number VARCHAR(50) UNIQUE NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NULL,
    mobile_number VARCHAR(20) NOT NULL,
    alternate_mobile VARCHAR(20) NULL,
    city VARCHAR(100) NULL,
    state VARCHAR(100) NULL,
    pincode VARCHAR(10) NULL,
    address TEXT NULL,
    date_of_birth DATE NULL,
    age INT NULL,
    occupation VARCHAR(255) NULL,
    source_id BIGINT UNSIGNED NOT NULL,
    product_interest VARCHAR(255) NULL,
    status_id BIGINT UNSIGNED NOT NULL,
    priority ENUM('low', 'medium', 'high') NULL,
    assigned_to BIGINT UNSIGNED NULL,
    relationship_manager_id BIGINT UNSIGNED NULL,
    reference_user_id BIGINT UNSIGNED NULL,
    next_follow_up_date DATE NULL,
    remarks TEXT NULL,
    converted_customer_id BIGINT UNSIGNED NULL,
    converted_at TIMESTAMP NULL,
    conversion_notes TEXT NULL,
    lost_reason TEXT NULL,
    lost_at TIMESTAMP NULL,
    is_protected BOOLEAN DEFAULT 0,
    protected_reason TEXT NULL,
    created_by BIGINT UNSIGNED NULL,
    updated_by BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_lead_number (lead_number),
    INDEX idx_mobile (mobile_number),
    INDEX idx_email (email),
    INDEX idx_status (status_id),
    INDEX idx_source (source_id),
    INDEX idx_assigned (assigned_to),
    INDEX idx_follow_up (next_follow_up_date),
    INDEX idx_priority (priority),
    FOREIGN KEY (source_id) REFERENCES lead_sources(id),
    FOREIGN KEY (status_id) REFERENCES lead_statuses(id),
    FOREIGN KEY (assigned_to) REFERENCES users(id),
    FOREIGN KEY (converted_customer_id) REFERENCES customers(id)
);
```

### lead_activities Table

```sql
CREATE TABLE lead_activities (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lead_id BIGINT UNSIGNED NOT NULL,
    activity_type VARCHAR(50) NOT NULL,
    subject VARCHAR(255) NOT NULL,
    description TEXT NULL,
    outcome TEXT NULL,
    next_action TEXT NULL,
    scheduled_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_lead (lead_id),
    INDEX idx_type (activity_type),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_completed (completed_at),
    INDEX idx_creator (created_by),
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE,
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### lead_whatsapp_campaigns Table

```sql
CREATE TABLE lead_whatsapp_campaigns (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT NULL,
    message_template TEXT NOT NULL,
    attachment_path VARCHAR(255) NULL,
    attachment_type VARCHAR(100) NULL,
    status VARCHAR(50) NOT NULL DEFAULT 'draft',
    target_criteria JSON NULL,
    scheduled_at TIMESTAMP NULL,
    started_at TIMESTAMP NULL,
    completed_at TIMESTAMP NULL,
    total_leads INT DEFAULT 0,
    sent_count INT DEFAULT 0,
    failed_count INT DEFAULT 0,
    delivered_count INT DEFAULT 0,
    read_count INT DEFAULT 0,
    messages_per_minute INT DEFAULT 60,
    auto_retry_failed BOOLEAN DEFAULT 1,
    max_retry_attempts INT DEFAULT 3,
    created_by BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,

    INDEX idx_status (status),
    INDEX idx_scheduled (scheduled_at),
    INDEX idx_creator (created_by),
    FOREIGN KEY (created_by) REFERENCES users(id)
);
```

### lead_whatsapp_campaign_leads Table (Pivot)

```sql
CREATE TABLE lead_whatsapp_campaign_leads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    campaign_id BIGINT UNSIGNED NOT NULL,
    lead_id BIGINT UNSIGNED NOT NULL,
    status VARCHAR(50) DEFAULT 'pending',
    sent_at TIMESTAMP NULL,
    delivered_at TIMESTAMP NULL,
    read_at TIMESTAMP NULL,
    error_message TEXT NULL,
    retry_count INT DEFAULT 0,
    last_retry_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,

    INDEX idx_campaign (campaign_id),
    INDEX idx_lead (lead_id),
    INDEX idx_status (status),
    UNIQUE KEY unique_campaign_lead (campaign_id, lead_id),
    FOREIGN KEY (campaign_id) REFERENCES lead_whatsapp_campaigns(id) ON DELETE CASCADE,
    FOREIGN KEY (lead_id) REFERENCES leads(id) ON DELETE CASCADE
);
```

## Best Practices

1. **Always Generate Lead Number**: Let boot() hook auto-generate, don't set manually
2. **Track All Activities**: Log every interaction (call, email, meeting, note)
3. **Set Follow-up Dates**: Always schedule next follow-up to prevent lead loss
4. **Use Priority Wisely**: High priority for hot leads requiring immediate attention
5. **Assign Ownership**: Assign leads to specific users for accountability
6. **Campaign Rate Limiting**: Respect WhatsApp rate limits (default 60/min)
7. **Monitor Campaign Health**: Check delivery rates, pause if failing
8. **Conversion Tracking**: Always log conversion notes for analytics
9. **Lost Lead Documentation**: Always document lost reasons for improvement
10. **Bulk Operations**: Use bulk actions for efficiency on large lead lists

## Related Documentation

- **[CUSTOMER_MANAGEMENT.md](CUSTOMER_MANAGEMENT.md)** - Lead to customer conversion
- **[QUOTATION_SYSTEM.md](QUOTATION_SYSTEM.md)** - Generating quotations for leads
- **[NOTIFICATION_SYSTEM.md](../features/NOTIFICATION_SYSTEM.md)** - WhatsApp notifications
- **[DATABASE_SCHEMA.md](../core/DATABASE_SCHEMA.md)** - Lead tables schema
- **[SERVICE_LAYER.md](../core/SERVICE_LAYER.md)** - LeadService and LeadConversionService

---

**Last Updated**: 2025-11-06
**Document Version**: 1.0
