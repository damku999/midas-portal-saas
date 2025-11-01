# Lead Management Module - Quick Start Guide

**For**: Development Team
**Last Updated**: 2025-11-02

---

## 🚀 Quick Setup (5 Minutes)

### Step 1: Run Migrations
```bash
cd C:\xampp\htdocs\webmonks\midas-portal

# Run migrations and seeders
php artisan migrate:fresh --seed
```

**What this does**:
- Creates 5 lead tables (leads, lead_sources, lead_statuses, lead_activities, lead_documents)
- Populates 10 lead sources (Website, Referral, Social Media, etc.)
- Populates 6 lead statuses (New, Contacted, Quotation Sent, Interested, Converted, Lost)

### Step 2: Test in Browser
```
1. Navigate to: http://localhost/midas-portal/leads
2. Click "Create New Lead"
3. Fill in basic info (Name, Mobile, Email, Source, Status)
4. Submit → Lead number auto-generated (e.g., LD-202511-0001)
5. View lead details → Add activities, upload documents
```

### Step 3: Test Dashboard
```
Navigate to: http://localhost/midas-portal/leads/dashboard
View: Statistics, charts, recent activities
```

---

## 📱 Common Use Cases

### Create a Lead
```php
use App\Services\LeadService;

$leadService = app(LeadService::class);

$lead = $leadService->createLead([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'mobile_number' => '9876543210',
    'source_id' => 1, // Website
    'status_id' => 1, // New
    'priority' => 'high',
    'product_interest' => 'Vehicle Insurance',
    'assigned_to' => auth()->id(),
    'next_follow_up_date' => now()->addDays(2),
]);
```

### Convert Lead to Customer
```php
use App\Services\LeadConversionService;

$conversionService = app(LeadConversionService::class);

// Auto-create customer
$result = $conversionService->convertLeadToCustomer($leadId, [
    'type' => 'Retail',
    'pan_card_number' => 'ABCDE1234F',
    'conversion_notes' => 'Converted after 3 follow-ups',
]);

// Result contains:
// - customer (Customer model)
// - lead (updated Lead model)
// - is_new_customer (boolean)
// - message (string)
```

### Add Activity
```php
use App\Models\LeadActivity;

LeadActivity::create([
    'lead_id' => $leadId,
    'activity_type' => 'call',
    'subject' => 'Follow-up Call',
    'description' => 'Discussed vehicle insurance options',
    'outcome' => 'Interested in comprehensive coverage',
    'next_action' => 'Send quotation',
    'scheduled_at' => now()->addDays(1),
    'created_by' => auth()->id(),
]);
```

### Upload Document
```php
// In controller
$file = $request->file('file');

LeadDocument::create([
    'lead_id' => $leadId,
    'document_type' => 'ID Proof',
    'file_name' => $file->getClientOriginalName(),
    'file_path' => $file->store('lead-documents/' . $leadId, 'public'),
    'file_size' => $file->getSize(),
    'mime_type' => $file->getMimeType(),
    'uploaded_by' => auth()->id(),
]);
```

### Get Lead Statistics
```php
use App\Services\LeadService;

$leadService = app(LeadService::class);
$stats = $leadService->getStatistics();

// Returns:
// - total: total leads count
// - active: active leads count
// - converted: converted leads count
// - lost: lost leads count
// - follow_up_due: leads with due follow-ups
// - follow_up_overdue: overdue follow-ups
// - by_priority: [high => count, medium => count, low => count]
// - by_status: [status_name => count, ...]
// - by_source: [source_name => count, ...]
```

---

## 🔍 Query Examples

### Find Leads Needing Follow-up
```php
$leads = Lead::followUpDue()
    ->with(['assignedUser', 'status', 'source'])
    ->get();
```

### Get User's Overdue Leads
```php
$overdueLeads = Lead::where('assigned_to', auth()->id())
    ->followUpOverdue()
    ->active()
    ->get();
```

### Get Converted Leads This Month
```php
$converted = Lead::converted()
    ->whereMonth('converted_at', now()->month)
    ->with('convertedCustomer')
    ->get();
```

### Search Leads
```php
$leads = Lead::where(function ($q) use ($search) {
        $q->where('name', 'like', "%{$search}%")
          ->orWhere('email', 'like', "%{$search}%")
          ->orWhere('mobile_number', 'like', "%{$search}%")
          ->orWhere('lead_number', 'like', "%{$search}%");
    })
    ->with(['source', 'status', 'assignedUser'])
    ->paginate(15);
```

---

## 🎨 Frontend Integration Points

### Lead List Page
**Route**: `/leads`
**Props Needed**:
- leads (paginated)
- sources (for filter dropdown)
- statuses (for filter dropdown)
- users (for assigned filter)

### Lead Create/Edit Page
**Route**: `/leads/create` or `/leads/edit/{id}`
**Props Needed**:
- sources (dropdown)
- statuses (dropdown)
- users (dropdown for assignment)
- relationshipManagers (dropdown)
- referenceUsers (dropdown)

### Lead Detail Page
**Route**: `/leads/show/{id}`
**Props Needed**:
- lead (with all relationships)
- statuses (for status change dropdown)

### Dashboard Page
**Route**: `/leads/dashboard`
**API Endpoints for Charts**:
- `/leads/dashboard/by-status` → Pie/Donut chart
- `/leads/dashboard/by-source` → Bar chart
- `/leads/dashboard/by-priority` → Pie chart
- `/leads/dashboard/trend` → Line chart (monthly)
- `/leads/dashboard/top-performers` → Leaderboard table
- `/leads/dashboard/conversion-funnel` → Funnel chart

---

## 🔔 Notification Setup

### Configure Email (Optional)
Edit `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@midasportal.com
MAIL_FROM_NAME="${APP_NAME}"
```

Uncomment email code in `SendLeadNotification.php`:
```php
Mail::raw($message, function ($mail) use ($email, $subject) {
    $mail->to($email)->subject($subject);
});
```

### Setup Queue Worker
```bash
# Start queue worker
php artisan queue:work

# Or use Supervisor for production
```

### Schedule Follow-up Reminders
Add to `app/Console/Kernel.php`:
```php
protected function schedule(Schedule $schedule)
{
    $schedule->command('leads:send-follow-up-reminders')
             ->dailyAt('09:00');
}
```

Run scheduler:
```bash
php artisan schedule:work
```

---

## 🐛 Troubleshooting

### Issue: Lead number not generating
**Solution**: Check `Lead::boot()` method in `app/Models/Lead.php`

### Issue: Foreign key constraint errors
**Solution**: Ensure migrations ran in order:
1. lead_sources
2. lead_statuses
3. leads (depends on 1 & 2)
4. lead_activities (depends on 3)
5. lead_documents (depends on 3)

### Issue: File upload fails
**Solution**:
1. Check storage permissions: `php artisan storage:link`
2. Verify `storage/app/public` exists
3. Check file size limit in `LeadDocumentController.php`

### Issue: Events not firing
**Solution**:
1. Check `EventServiceProvider.php` for registered events
2. Clear cache: `php artisan config:clear && php artisan cache:clear`
3. Restart queue worker if using queues

---

## 📊 Database Schema Quick Reference

### leads Table (Main)
**Key Columns**:
- `lead_number` VARCHAR(50) UNIQUE - Auto-generated (LD-YYYYMM-XXXX)
- `name` VARCHAR(255) - Lead name
- `email` VARCHAR(255) NULLABLE
- `mobile_number` VARCHAR(20) - Primary contact
- `source_id` FK → lead_sources.id
- `status_id` FK → lead_statuses.id
- `assigned_to` FK → users.id
- `next_follow_up_date` DATE - For reminders
- `converted_customer_id` FK → customers.id - When converted
- `converted_at` TIMESTAMP - Conversion timestamp
- `lost_reason` TEXT - Why lead was lost
- `lost_at` TIMESTAMP - Loss timestamp

### lead_activities Table
**Key Columns**:
- `activity_type` ENUM (call, email, meeting, note, status_change, assignment, document, quotation)
- `scheduled_at` TIMESTAMP - When activity is scheduled
- `completed_at` TIMESTAMP - When marked complete

### lead_documents Table
**Key Columns**:
- `document_type` VARCHAR(100) - Category
- `file_path` VARCHAR(500) - Storage path

---

## 🎯 Performance Tips

### 1. Eager Load Relationships
```php
// Good
$leads = Lead::with(['source', 'status', 'assignedUser'])->get();

// Bad (N+1 problem)
$leads = Lead::all(); // Then accessing $lead->source in loop
```

### 2. Use Scopes for Common Queries
```php
// Good
$activeLeads = Lead::active()->get();

// Instead of
$activeLeads = Lead::whereHas('status', function($q) {
    $q->where('is_converted', false)->where('is_lost', false);
})->get();
```

### 3. Cache Master Data
```php
// Cache sources and statuses (they rarely change)
$sources = Cache::remember('lead_sources', 3600, function () {
    return LeadSource::active()->ordered()->get();
});
```

---

## 📞 Support

**Issues?** Create ticket with:
1. Error message
2. Steps to reproduce
3. Expected vs actual behavior

**Documentation**:
- Full Plan: `claudedocs/LEAD_MANAGEMENT_PLAN.md`
- Completion Report: `claudedocs/LEAD_MANAGEMENT_COMPLETE.md`
- This Guide: `claudedocs/LEAD_MANAGEMENT_QUICKSTART.md`

---

**Last Updated**: 2025-11-02
**Version**: 1.0
