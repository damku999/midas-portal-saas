# Notification Logging Integration Examples

## Overview
This document provides step-by-step examples for integrating the notification logging system into your existing services, listeners, and controllers.

---

## Example 1: Update QuotationService

### Before (Without Logging)
```php
<?php

namespace App\Services;

use App\Models\Quotation;
use App\Traits\WhatsAppApiTrait;

class QuotationService
{
    use WhatsAppApiTrait;

    public function sendQuotationWhatsApp(Quotation $quotation)
    {
        $customer = $quotation->customer;
        $message = $this->getQuotationMessage($quotation);

        // Old way - no logging
        $response = $this->whatsAppSendMessage(
            $message,
            $customer->mobile_number,
            $customer->id,
            'quotation_ready'
        );

        return $response;
    }
}
```

### After (With Logging)
```php
<?php

namespace App\Services;

use App\Models\Quotation;
use App\Traits\WhatsAppApiTrait;
use App\Traits\LogsNotificationsTrait;
use Illuminate\Support\Facades\Log;

class QuotationService
{
    use WhatsAppApiTrait, LogsNotificationsTrait;

    public function sendQuotationWhatsApp(Quotation $quotation)
    {
        $customer = $quotation->customer;
        $message = $this->getQuotationMessage($quotation);

        // New way - with automatic logging
        $result = $this->logAndSendWhatsApp(
            notifiable: $quotation,
            message: $message,
            recipient: $customer->mobile_number,
            options: [
                'notification_type_code' => 'quotation_ready',
                'customer_id' => $customer->id,
                'variables' => [
                    'customer_name' => $customer->name,
                    'quotation_id' => $quotation->id,
                    'amount' => $quotation->total_amount,
                ],
            ]
        );

        if (!$result['success']) {
            Log::warning('Quotation WhatsApp failed', [
                'quotation_id' => $quotation->id,
                'log_id' => $result['log']->id,
                'error' => $result['error'],
            ]);
        }

        return $result;
    }

    protected function getQuotationMessage(Quotation $quotation): string
    {
        // Use template service if available
        $templateService = app(\App\Services\TemplateService::class);
        $message = $templateService->renderFromQuotation(
            'quotation_ready',
            'whatsapp',
            $quotation
        );

        // Fallback to hardcoded message if no template
        if (!$message) {
            $message = "Dear {$quotation->customer->name},\n\n"
                . "Your insurance quotation is ready!\n"
                . "Quotation ID: {$quotation->id}\n"
                . "Total Amount: ₹{$quotation->total_amount}\n\n"
                . "Thank you!";
        }

        return $message;
    }
}
```

---

## Example 2: Update PolicyService with Document Attachment

### Before
```php
public function sendPolicyDocument(CustomerInsurance $insurance)
{
    $customer = $insurance->customer;
    $message = $this->insuranceAdded($insurance);
    $filePath = storage_path("app/policies/{$insurance->policy_document}");

    $response = $this->whatsAppSendMessageWithAttachment(
        $message,
        $customer->mobile_number,
        $filePath
    );

    return $response;
}
```

### After
```php
use App\Traits\WhatsAppApiTrait;
use App\Traits\LogsNotificationsTrait;

public function sendPolicyDocument(CustomerInsurance $insurance)
{
    $customer = $insurance->customer;
    $message = $this->insuranceAdded($insurance);
    $filePath = storage_path("app/policies/{$insurance->policy_document}");

    // Send with logging and attachment
    $result = $this->logAndSendWhatsAppWithAttachment(
        notifiable: $insurance,
        message: $message,
        recipient: $customer->mobile_number,
        filePath: $filePath,
        options: [
            'notification_type_code' => 'policy_issued',
            'customer_id' => $customer->id,
            'template_id' => null, // hardcoded message
            'variables' => [
                'customer_name' => $customer->name,
                'policy_no' => $insurance->policy_no,
                'expired_date' => $insurance->expired_date->format('d-m-Y'),
            ],
        ]
    );

    if (!$result['success']) {
        // Log failure but don't throw - allow process to continue
        \Log::error('Failed to send policy document', [
            'insurance_id' => $insurance->id,
            'log_id' => $result['log']->id,
            'error' => $result['error'],
        ]);
    }

    return $result;
}
```

---

## Example 3: Update Event Listener

### Before
```php
<?php

namespace App\Listeners\Insurance;

use App\Events\Insurance\PolicyRenewalDue;
use App\Services\PolicyService;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPolicyRenewalReminder implements ShouldQueue
{
    public function __construct(
        private PolicyService $policyService
    ) {}

    public function handle(PolicyRenewalDue $event): void
    {
        $insurance = $event->insurance;
        $this->policyService->sendRenewalReminder($insurance);
    }
}
```

### After
```php
<?php

namespace App\Listeners\Insurance;

use App\Events\Insurance\PolicyRenewalDue;
use App\Services\PolicyService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendPolicyRenewalReminder implements ShouldQueue
{
    use InteractsWithQueue;

    public function __construct(
        private PolicyService $policyService
    ) {}

    public function handle(PolicyRenewalDue $event): void
    {
        $insurance = $event->insurance;

        // Send with logging
        $result = $this->policyService->sendRenewalReminder($insurance);

        // If failed, throw exception to trigger job retry
        if (!$result['success']) {
            Log::warning('Renewal reminder failed, will retry', [
                'insurance_id' => $insurance->id,
                'log_id' => $result['log']->id,
                'attempt' => $this->attempts(),
            ]);

            // Throw to trigger Laravel queue retry mechanism
            throw new \Exception($result['error']);
        }

        Log::info('Renewal reminder sent successfully', [
            'insurance_id' => $insurance->id,
            'log_id' => $result['log']->id,
        ]);
    }

    public function failed(PolicyRenewalDue $event, \Throwable $exception): void
    {
        Log::error('Renewal reminder failed permanently', [
            'insurance_id' => $event->insurance->id,
            'error' => $exception->getMessage(),
        ]);

        // Optionally notify admin of permanent failure
    }
}
```

---

## Example 4: Email Notification Integration

```php
<?php

namespace App\Services;

use App\Models\Quotation;
use App\Traits\LogsNotificationsTrait;
use Illuminate\Support\Facades\Mail;
use App\Mail\QuotationMail;

class EmailNotificationService
{
    use LogsNotificationsTrait;

    public function sendQuotationEmail(Quotation $quotation)
    {
        $customer = $quotation->customer;
        $email = $quotation->email ?? $customer->email;

        if (!$email) {
            \Log::warning('No email address for quotation', [
                'quotation_id' => $quotation->id,
            ]);
            return ['success' => false, 'error' => 'No email address'];
        }

        // Prepare email content
        $subject = "Your Insurance Quotation #{$quotation->id}";
        $message = view('emails.quotation', compact('quotation'))->render();

        // Log and send email
        $result = $this->logAndSendEmail(
            notifiable: $quotation,
            recipient: $email,
            subject: $subject,
            message: $message,
            options: [
                'notification_type_code' => 'quotation_ready',
                'template_id' => 8, // Email template ID
                'variables' => [
                    'customer_name' => $customer->name,
                    'quotation_id' => $quotation->id,
                    'amount' => $quotation->total_amount,
                ],
            ]
        );

        return $result;
    }

    /**
     * Override the email sending logic in LogsNotificationsTrait
     */
    protected function sendEmailViaProvider(string $recipient, string $subject, string $message)
    {
        // Use Laravel Mail
        Mail::to($recipient)->send(new QuotationMail($subject, $message));
    }
}
```

---

## Example 5: Controller Integration - Manual Send

```php
<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        $this->customerService = $customerService;
    }

    /**
     * Send manual notification to customer
     */
    public function sendNotification(Request $request, Customer $customer)
    {
        $request->validate([
            'channel' => 'required|in:whatsapp,email,sms',
            'message' => 'required|string|max:1000',
            'notification_type' => 'nullable|exists:notification_types,code',
        ]);

        $result = null;

        // Send based on selected channel
        switch ($request->channel) {
            case 'whatsapp':
                $result = $this->customerService->sendCustomWhatsApp(
                    $customer,
                    $request->message,
                    $request->notification_type
                );
                break;

            case 'email':
                $result = $this->customerService->sendCustomEmail(
                    $customer,
                    $request->message,
                    $request->notification_type
                );
                break;
        }

        if ($result && $result['success']) {
            return back()->with('success', 'Notification sent successfully!');
        }

        return back()->with('error', 'Failed to send notification: ' . ($result['error'] ?? 'Unknown error'));
    }
}
```

**CustomerService Implementation:**
```php
use App\Traits\WhatsAppApiTrait;
use App\Traits\LogsNotificationsTrait;

class CustomerService
{
    use WhatsAppApiTrait, LogsNotificationsTrait;

    public function sendCustomWhatsApp(Customer $customer, string $message, ?string $notificationType = null)
    {
        return $this->logAndSendWhatsApp(
            notifiable: $customer,
            message: $message,
            recipient: $customer->mobile_number,
            options: [
                'notification_type_code' => $notificationType ?? 'custom_message',
                'sent_by' => auth()->id(),
            ]
        );
    }

    public function sendCustomEmail(Customer $customer, string $message, ?string $notificationType = null)
    {
        return $this->logAndSendEmail(
            notifiable: $customer,
            recipient: $customer->email,
            subject: 'Message from ' . config('app.name'),
            message: $message,
            options: [
                'notification_type_code' => $notificationType ?? 'custom_message',
                'sent_by' => auth()->id(),
            ]
        );
    }
}
```

---

## Example 6: Viewing Notification History in Customer Profile

### CustomerController
```php
public function show(Customer $customer)
{
    // Get customer data
    $customer->load(['familyGroup', 'insurances', 'quotations']);

    // Get notification history
    $notificationHistory = $this->getCustomerNotificationHistory($customer);

    return view('customers.show', compact('customer', 'notificationHistory'));
}

protected function getCustomerNotificationHistory(Customer $customer)
{
    $loggerService = app(\App\Services\NotificationLoggerService::class);

    return $loggerService->getNotificationHistory($customer, [
        'per_page' => 10,
    ]);
}
```

### Blade View (customers/show.blade.php)
```blade
<!-- Add notification history section -->
<div class="card mt-4">
    <div class="card-header">
        <h5>
            <i class="fas fa-bell mr-2"></i>Notification History
            <a href="{{ route('admin.notification-logs.index', ['notifiable_type' => 'App\Models\Customer', 'notifiable_id' => $customer->id]) }}"
               class="btn btn-sm btn-info float-right">
                View All
            </a>
        </h5>
    </div>
    <div class="card-body">
        @if($notificationHistory->isEmpty())
            <p class="text-muted">No notifications sent yet.</p>
        @else
            <div class="table-responsive">
                <table class="table table-sm">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Channel</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($notificationHistory as $log)
                        <tr>
                            <td>{{ $log->sent_at?->format('d M Y H:i') ?? $log->created_at->format('d M Y H:i') }}</td>
                            <td>
                                <i class="{{ $log->channel_icon }}"></i>
                                {{ ucfirst($log->channel) }}
                            </td>
                            <td>
                                @if($log->notificationType)
                                    <span class="badge badge-secondary">{{ $log->notificationType->name }}</span>
                                @else
                                    -
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-{{ $log->status_color }}">
                                    {{ ucfirst($log->status) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.notification-logs.show', $log) }}"
                                   class="btn btn-xs btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($log->canRetry())
                                    <form action="{{ route('admin.notification-logs.resend', $log) }}"
                                          method="POST"
                                          style="display:inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-xs btn-warning"
                                                onclick="return confirm('Resend this notification?')">
                                            <i class="fas fa-redo"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            {{ $notificationHistory->links() }}
        @endif
    </div>
</div>
```

---

## Example 7: Scheduled Command Integration

### SendBirthdayWishes Command
```php
<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Services\CustomerService;
use Illuminate\Console\Command;
use Carbon\Carbon;

class SendBirthdayWishes extends Command
{
    protected $signature = 'customers:send-birthday-wishes';
    protected $description = 'Send birthday wishes to customers';

    protected CustomerService $customerService;

    public function __construct(CustomerService $customerService)
    {
        parent::__construct();
        $this->customerService = $customerService;
    }

    public function handle(): int
    {
        $this->info('Finding customers with birthdays today...');

        $customers = Customer::whereMonth('dob', Carbon::today()->month)
            ->whereDay('dob', Carbon::today()->day)
            ->whereNotNull('mobile_number')
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No birthdays today.');
            return Command::SUCCESS;
        }

        $this->info("Found {$customers->count()} customer(s) with birthdays today.");

        $sent = 0;
        $failed = 0;

        foreach ($customers as $customer) {
            $this->line("Sending birthday wish to {$customer->name}...");

            $result = $this->customerService->sendBirthdayWish($customer);

            if ($result['success']) {
                $this->info("  ✓ Sent (Log ID: {$result['log']->id})");
                $sent++;
            } else {
                $this->error("  ✗ Failed: {$result['error']}");
                $failed++;
            }
        }

        $this->newLine();
        $this->table(
            ['Status', 'Count'],
            [
                ['Sent', $sent],
                ['Failed', $failed],
            ]
        );

        return Command::SUCCESS;
    }
}
```

**CustomerService Method:**
```php
public function sendBirthdayWish(Customer $customer)
{
    $message = "🎉 Happy Birthday {$customer->name}! 🎂\n\n"
        . "Wishing you a wonderful day filled with joy and happiness.\n\n"
        . "Best regards,\n"
        . config('app.name');

    return $this->logAndSendWhatsApp(
        notifiable: $customer,
        message: $message,
        recipient: $customer->mobile_number,
        options: [
            'notification_type_code' => 'birthday_wish',
        ]
    );
}
```

---

## Summary of Changes Required

### 1. Add Trait to Services
```php
use App\Traits\LogsNotificationsTrait;

class YourService
{
    use LogsNotificationsTrait; // Add this
}
```

### 2. Replace Direct Calls
**Before:**
```php
$this->whatsAppSendMessage($message, $recipient);
```

**After:**
```php
$result = $this->logAndSendWhatsApp($notifiable, $message, $recipient, $options);
```

### 3. Handle Results
```php
if ($result['success']) {
    // Success - log available at $result['log']
} else {
    // Failed - error at $result['error'], log at $result['log']
}
```

### 4. Add Notification Type Codes
Ensure all notification types are in `notification_types` table with unique codes.

### 5. Run Migrations
```bash
php artisan migrate
```

### 6. Test Integration
```bash
php artisan tinker

$customer = Customer::first();
$service = app(CustomerService::class);
$result = $service->sendBirthdayWish($customer);
dd($result);
```

---

## Best Practices

1. **Always use logAndSend methods** instead of direct API calls
2. **Provide notification_type_code** for proper categorization
3. **Include template_id** when using templates
4. **Pass resolved variables** for debugging purposes
5. **Check result['success']** before assuming sent
6. **Log failures** for monitoring
7. **Don't throw exceptions** unless you want queue to retry
8. **Use polymorphic notifiable** (Customer, Insurance, Quotation, Claim)
9. **Set sent_by** for manual sends from admin panel
10. **Review logs regularly** via analytics dashboard
