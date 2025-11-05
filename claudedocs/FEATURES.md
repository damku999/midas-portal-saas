# Midas Portal - Feature Documentation

**Version:** 1.0
**Last Updated:** November 3, 2025
**Status:** Production Ready

---

## Table of Contents

1. [Lead Management System](#1-lead-management-system)
2. [WhatsApp Integration](#2-whatsapp-integration)
3. [Protection System](#3-protection-system)
4. [Quick Start Guides](#4-quick-start-guides)

---

## 1. Lead Management System

### Overview
Complete CRM system for lead capture, tracking, conversion, and analytics with automated follow-ups and notifications.

### Core Features
- Auto lead number generation (`LD-YYYYMM-XXXX`)
- 6-stage workflow (New → Contacted → Quotation → Interested → Converted/Lost)
- Priority management (Low/Medium/High)
- Activity tracking (8 types: call, email, meeting, follow-up, etc.)
- Document management (10MB max per file)
- Automated conversion to customers
- Real-time analytics and reporting

### Database Tables
- `lead_sources` - Master data for lead sources (10 defaults)
- `lead_statuses` - Workflow statuses with flags
- `leads` - Main entity (30+ fields)
- `lead_activities` - Timeline tracking with scheduling
- `lead_documents` - File attachments with categorization

### Key Models & Relationships
**Lead Model** (`app/Models/Lead.php`)
- Relationships: source, status, assignedUser, activities, documents, whatsappMessages
- Scopes: active, converted, lost, followUpDue, followUpOverdue
- Methods: `generateLeadNumber()`, `getAgeInDays()`, `convertToCustomer()`

### API Endpoints

#### Lead CRUD
```
GET    /leads                    List leads with filters
POST   /leads/store              Create new lead
GET    /leads/show/{id}          View details
PUT    /leads/update/{id}        Update lead
DELETE /leads/delete/{id}        Delete lead
```

#### Workflow Actions
```
POST   /leads/{id}/update-status       Change status
POST   /leads/{id}/assign              Assign to user
POST   /leads/{id}/convert-auto        Auto-convert to customer
POST   /leads/{id}/convert             Link existing customer
POST   /leads/{id}/mark-as-lost        Mark as lost
POST   /leads/bulk-convert             Bulk conversion
```

#### Activities
```
GET    /leads/{id}/activities                  List activities
POST   /leads/{id}/activities/store            Create activity
POST   /leads/{id}/activities/{aid}/complete   Mark complete
DELETE /leads/{id}/activities/{aid}/delete     Delete activity
GET    /activities/upcoming                    Upcoming activities
GET    /activities/overdue                     Overdue activities
```

#### Analytics
```
GET    /leads/dashboard                        Main dashboard
GET    /leads/dashboard/by-status              Status distribution
GET    /leads/dashboard/by-source              Source performance
GET    /leads/dashboard/trend                  Monthly trends (12 months)
GET    /leads/dashboard/top-performers         User leaderboard
GET    /leads/dashboard/conversion-funnel      Funnel visualization
GET    /leads/dashboard/aging-report           Age distribution
```

### Conversion System
**LeadConversionService** (`app/Services/LeadConversionService.php`)
- Auto-create customer from lead data
- Detect existing customers (email/mobile matching)
- Transfer documents to customer record
- Validation (prevents converting converted/lost leads)
- Bulk conversion support
- Conversion statistics and analytics

### Event System
**Events & Listeners:**
- `LeadCreated` → Notify assigned user + managers
- `LeadStatusChanged` → Notify on workflow changes
- `LeadConverted` → Notify + send welcome email
- `LeadAssigned` → Notify new and previous user

**Follow-up Reminders:**
```bash
# Send daily reminders
php artisan leads:send-follow-up-reminders --days-ahead=1

# Schedule in app/Console/Kernel.php
$schedule->command('leads:send-follow-up-reminders')->dailyAt('09:00');
```

### Setup Instructions
```bash
# Run migrations and seeders
php artisan migrate --path=database/migrations/2025_11_01_182021_create_lead_sources_table.php
php artisan migrate --path=database/migrations/2025_11_01_182131_create_lead_statuses_table.php
php artisan migrate --path=database/migrations/2025_11_01_182525_create_leads_table.php
php artisan migrate --path=database/migrations/2025_11_01_182806_create_lead_activities_table.php
php artisan migrate --path=database/migrations/2025_11_01_182941_create_lead_documents_table.php

php artisan db:seed --class=LeadSourceSeeder
php artisan db:seed --class=LeadStatusSeeder

# Start queue worker for notifications
php artisan queue:work
```

---

## 2. WhatsApp Integration

### Overview
Marketing automation system for WhatsApp messaging to leads with campaign management, templates, and delivery tracking.

### Key Features
- Single and bulk messaging
- Campaign management with targeting
- Message templates with variables
- Attachment support (PDF, images, docs up to 5MB)
- Delivery tracking (pending → sent → delivered → read → failed)
- Automatic retry with backoff
- Throttling (messages per minute)
- Real-time analytics

### Database Tables
- `lead_whatsapp_messages` - Message log with delivery status
- `lead_whatsapp_campaigns` - Campaign management
- `lead_whatsapp_campaign_leads` - Campaign-lead pivot
- `lead_whatsapp_templates` - Reusable templates
- `customers.converted_from_lead_id` - Track conversions

### API Endpoints

#### Messaging
```
POST   /leads/whatsapp/{lead}/send        Send single message
POST   /leads/whatsapp/bulk-send          Bulk messaging (>10 = queued)
GET    /leads/whatsapp/{lead}/history     Message history
```

#### Campaigns
```
GET    /leads/whatsapp/campaigns                    List campaigns
POST   /leads/whatsapp/campaigns/store              Create campaign
GET    /leads/whatsapp/campaigns/{id}               Campaign details
POST   /leads/whatsapp/campaigns/{id}/execute       Launch campaign
POST   /leads/whatsapp/campaigns/{id}/pause         Pause campaign
POST   /leads/whatsapp/campaigns/{id}/retry-failed  Retry failures
```

#### Templates & Analytics
```
GET    /leads/whatsapp/templates          List templates
GET    /leads/whatsapp/analytics          Analytics dashboard
```

### Template Variables
Available in all messages and templates:
- `{name}` - Lead's full name
- `{mobile}` - Mobile number
- `{email}` - Email address
- `{source}` - Lead source
- `{status}` - Current status
- `{priority}` - Priority level
- `{assigned_to}` - Assigned user
- `{product_interest}` - Product interest
- `{lead_number}` - Lead ID

**Example Template:**
```
Hi {name},

Thank you for your interest in {product_interest}.
I'm {assigned_to} from Midas Insurance.

Can we schedule a call to discuss your requirements?

Best regards,
Midas Insurance Team
```

### Message Sending

#### Single Message
```php
use App\Services\LeadWhatsAppService;

$service = app(LeadWhatsAppService::class);
$message = $service->sendSingleMessage($leadId, [
    'message' => 'Hi {name}, your quotation is ready!',
    'attachment' => $file, // Optional
]);
```

#### Bulk Messaging
```php
$service->sendBulkMessages([
    'lead_ids' => [1, 2, 3, 4, 5],
    'message' => 'Special offer for {name}!',
    'attachment' => $brochurePdf,
]);
// >10 leads automatically queued
```

### Campaign Management

#### Create Campaign
```php
$campaign = $service->createCampaign([
    'name' => 'Summer Promotion 2025',
    'message_template' => 'Hi {name}, special offer...',
    'target_criteria' => [
        'status_ids' => [1, 2],
        'source_ids' => [3, 5],
        'priority' => 'high',
    ],
    'scheduled_at' => '2025-06-01 09:00:00',
    'messages_per_minute' => 100,
]);
```

#### Execute Campaign
```php
$service->executeCampaign($campaignId);
// Queued for background processing
// Progress tracked in real-time
```

### Queue Jobs
- `SendSingleWhatsAppJob` - Individual message (3 retries: 30s, 60s, 120s)
- `SendBulkWhatsAppJob` - Batch coordinator
- `ExecuteCampaignJob` - Campaign executor (1-hour timeout)

### Permissions (13 total)
```
lead-whatsapp-send
lead-whatsapp-campaign-list
lead-whatsapp-campaign-create
lead-whatsapp-campaign-edit
lead-whatsapp-campaign-delete
lead-whatsapp-campaign-view
lead-whatsapp-campaign-start
lead-whatsapp-campaign-pause
lead-whatsapp-campaign-cancel
lead-whatsapp-template-list
lead-whatsapp-template-create
lead-whatsapp-template-edit
lead-whatsapp-template-delete
```

### Best Practices

**Timing:**
- Weekdays 10 AM - 5 PM for best engagement
- Avoid early mornings (<9 AM) and late evenings (>8 PM)
- Tuesday-Thursday have highest response rates

**Message Content:**
- Keep messages 200-400 characters for best engagement
- Always include clear call-to-action
- Personalize with recipient's name
- Include opt-out instructions

**Rate Limiting:**
- Default: 100 messages/minute
- High-value campaigns: 50/minute
- Space campaigns 1-2 hours apart

**Compliance:**
- Only message leads who consented
- Include opt-out: "Reply STOP to unsubscribe"
- Respect "Do Not Contact" requests
- Follow GDPR/local data protection laws

### Troubleshooting

| Error | Cause | Solution |
|-------|-------|----------|
| "Mobile number required" | Missing phone | Add mobile to lead |
| "Invalid format" | Wrong number format | Use +[country][number] |
| "Rate limit exceeded" | Too many requests | Wait 60s, reduce rate |
| "File too large" | >5MB attachment | Compress or use link |

### Setup
```bash
# Run migrations
php artisan migrate

# Seed permissions
php artisan db:seed --class=UnifiedPermissionsSeeder

# Configure queue worker
php artisan queue:work

# Create storage link
php artisan storage:link
```

---

## 3. Protection System

### Overview
Multi-layer security system protecting critical records from deletion, deactivation, or modification.

### Protected Records
- **Super-Admin:** `webmonks.in@gmail.com` (User ID: 1)
- **Domain:** All `*@webmonks.in` email addresses
- **Scope:** Users, Customers, Leads, Brokers, Branches, Reference Users, Relationship Managers, Insurance Companies

### Protection Rules
1. Cannot be deleted (soft or hard delete)
2. Cannot be deactivated (status change blocked)
3. Email cannot be modified
4. Full system access preserved
5. All violations logged and audited

### Database Schema
Added to 8 tables:
```sql
is_protected BOOLEAN DEFAULT FALSE
protected_reason VARCHAR(255) NULL
INDEX on is_protected
```

### ProtectedRecord Trait
**File:** `app/Traits/ProtectedRecord.php`

**Key Methods:**
- `isProtected()` - Check if record is protected
- `shouldBeProtected()` - Check if email matches patterns
- `canBeDeleted()` - Verify deletion allowed
- `canBeDeactivated()` - Verify status change allowed
- `canChangeEmail()` - Verify email change allowed
- `protect($reason)` - Manually protect record
- `unprotect()` - Remove protection (emergency only)

**Query Scopes:**
```php
User::protected()->get();    // Get protected users
User::unprotected()->get();  // Get normal users
```

**Auto-Protection:**
Records automatically protected on creation if email matches patterns in `config/protection.php`

### Configuration
**File:** `config/protection.php`

```php
'protected_emails' => [
    'webmonks.in@gmail.com',
],

'protected_domains' => [
    'webmonks.in', // All @webmonks.in emails
],

'rules' => [
    'prevent_deletion' => true,
    'prevent_soft_deletion' => true,
    'prevent_force_deletion' => true,
    'prevent_status_deactivation' => true,
    'prevent_email_change' => true,
],
```

### Usage Examples

**Check Protection:**
```php
$user = User::find(1);
if ($user->isProtected()) {
    echo $user->protected_reason;
}
```

**Manual Protection:**
```php
$user->protect('Critical System Account');
// or
$user->is_protected = true;
$user->protected_reason = 'Custom reason';
$user->save();
```

**Permission Checks:**
```php
if (!$user->canBeDeleted()) {
    throw new Exception('Cannot delete protected record');
}
```

### Audit Logging
All protection violations logged to:
- Application log: `storage/logs/laravel.log`
- Database: `audit_logs` table
- Severity: HIGH
- Includes: user_id, ip_address, attempted_action, timestamp

### Exception Handling
**ProtectedRecordException** (`app/Exceptions/ProtectedRecordException.php`)
- HTTP 403 responses
- User-friendly error messages
- Automatic audit logging
- JSON API support

### Emergency Bypass
Disabled by default. Requires:
```env
PROTECTION_EMERGENCY_BYPASS=true
PROTECTION_BYPASS_KEY=<secret_key>
```
All bypasses logged with CRITICAL severity.

### Testing Protection
```bash
php artisan tinker

# Test deletion prevention
>>> $user = User::find(1);
>>> $user->delete();
// Throws ProtectedRecordException

# Test status change prevention
>>> $user->status = false;
>>> $user->save();
// Throws ProtectedRecordException

# Verify protection
>>> $user->is_protected
=> true
>>> $user->protected_reason
=> "Webmonks Super Admin Account"
```

---

## 4. Quick Start Guides

### Lead Management Quick Start

**1. Database Setup (2 minutes)**
```bash
cd C:\xampp\htdocs\webmonks\midas-portal
php artisan migrate:fresh --seed
```

**2. Create First Lead**
```php
use App\Services\LeadService;

$leadService = app(LeadService::class);
$lead = $leadService->createLead([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'mobile_number' => '+919876543210',
    'source_id' => 1,
    'status_id' => 1,
    'priority' => 'high',
    'assigned_to' => auth()->id(),
]);
// Auto-generates: LD-202511-0001
```

**3. View Dashboard**
Navigate to: `http://localhost/midas-portal/leads/dashboard`

**4. Common Queries**
```php
// Follow-up due leads
Lead::followUpDue()->with(['assignedUser', 'status'])->get();

// Overdue leads
Lead::followUpOverdue()->get();

// Converted this month
Lead::converted()->whereMonth('converted_at', now()->month)->get();
```

### WhatsApp Quick Start

**1. Send Single Message**
```php
use App\Services\LeadWhatsAppService;

$service = app(LeadWhatsAppService::class);
$service->sendSingleMessage($leadId, [
    'message' => 'Hi {name}, thank you for your interest!',
]);
```

**2. Send Bulk Messages**
```php
$service->sendBulkMessages([
    'lead_ids' => [1, 2, 3],
    'message' => 'Special offer for {name}!',
]);
```

**3. Create Template**
```php
LeadWhatsAppTemplate::create([
    'name' => 'Welcome Message',
    'category' => 'greeting',
    'message' => 'Hi {name}, welcome to Midas Insurance!',
    'is_active' => true,
]);
```

**4. Launch Campaign**
```php
$campaign = $service->createCampaign([
    'name' => 'New Year Promo',
    'message_template' => 'Hi {name}, special offer...',
    'target_criteria' => ['status_ids' => [1, 2]],
]);

$service->executeCampaign($campaign->id);
```

### Protection Quick Start

**1. Check Protected Records**
```bash
php artisan tinker
>>> User::protected()->get();
>>> Customer::where('email', 'like', '%@webmonks.in')->get();
```

**2. Protect New Record**
```php
$user = User::create([
    'email' => 'admin@webmonks.in',
    // ... other fields
]);
// Auto-protected on creation

// Or manually:
$user->protect('Critical Admin Account');
```

**3. Add Protected Email**
Edit `config/protection.php`:
```php
'protected_emails' => [
    'webmonks.in@gmail.com',
    'new-protected@example.com', // Add here
],
```

**4. View Audit Logs**
```php
$logs = DB::table('audit_logs')
    ->where('event', 'protected_record_violation')
    ->orderBy('created_at', 'desc')
    ->get();
```

### Performance Tips

**1. Eager Load Relationships**
```php
// Good
$leads = Lead::with(['source', 'status', 'assignedUser'])->get();

// Bad (N+1 problem)
$leads = Lead::all();
foreach ($leads as $lead) {
    echo $lead->source->name; // N+1 query
}
```

**2. Cache Master Data**
```php
$sources = Cache::remember('lead_sources', 3600, function() {
    return LeadSource::active()->ordered()->get();
});
```

**3. Queue Long Operations**
```php
// >10 WhatsApp messages automatically queued
$service->sendBulkMessages(['lead_ids' => range(1, 100), ...]);
```

**4. Use Scopes**
```php
// Optimized query
$leads = Lead::active()->followUpDue()->limit(10)->get();
```

---

## Troubleshooting

### Common Issues

**Lead number not generating:**
- Check `Lead::boot()` method in model
- Verify migrations ran successfully

**Foreign key errors:**
- Run migrations in correct order
- Ensure seeders ran (LeadSourceSeeder, LeadStatusSeeder)

**File upload fails:**
- Run `php artisan storage:link`
- Check folder permissions (storage/app/public)

**Events not firing:**
- Clear cache: `php artisan cache:clear`
- Restart queue worker: `php artisan queue:restart`

**WhatsApp messages stuck in "pending":**
- Check WhatsApp API credentials
- Verify queue worker running
- Check mobile number format (+country code)

**Protection not working:**
- Run ProtectedRecordsSeeder
- Check `is_protected` column exists
- Verify trait added to model

---

## Support

**Documentation:** This file
**Logs:** `storage/logs/laravel.log`
**Database:** Check migrations and seeders
**Queue:** Monitor with `php artisan queue:work --verbose`

**Contact:** development@midasportal.com

---

*Generated by Claude Code for Midas Portal Insurance Management System*
