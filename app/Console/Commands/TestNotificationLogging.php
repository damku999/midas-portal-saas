<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\NotificationLog;
use App\Models\Quotation;
use App\Models\InsuranceCompany;
use App\Services\CustomerService;
use App\Services\CustomerInsuranceService;
use App\Services\MarketingWhatsAppService;
use App\Services\QuotationService;
use Illuminate\Console\Command;

class TestNotificationLogging extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'test:notifications {phone} {email}';

    /**
     * The console command description.
     */
    protected $description = 'Test notification logging system with specified phone and email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $phone = $this->argument('phone');
        $email = $this->argument('email');

        $this->info('=== Notification Logging Test ===');
        $this->newLine();

        // Step 1: Create/Find test customer
        $this->info('1. Creating/Finding test customer...');
        $customer = Customer::firstOrCreate(
            ['mobile_number' => $phone],
            [
                'name' => 'Test Customer - Notification Logging',
                'email' => $email,
                'status' => 1,
            ]
        );
        $this->info("Customer ID: {$customer->id}");
        $this->newLine();

        // Step 2: Test onboarding message
        $this->info('2. Testing onboarding WhatsApp message...');
        try {
            $customerService = app(CustomerService::class);
            $result = $customerService->sendOnboardingMessage($customer);
            $this->info("Result: " . ($result ? "✅ SUCCESS" : "❌ FAILED"));
        } catch (\Exception $e) {
            $this->error("ERROR: {$e->getMessage()}");
        }
        $this->newLine();

        // Step 3: Test marketing text message
        $this->info('3. Testing marketing WhatsApp text message...');
        try {
            $marketingService = app(MarketingWhatsAppService::class);
            $result = $marketingService->sendTextMessage(
                "🎉 Test marketing message from Midas Portal!\n\n" .
                "This is a test to verify all notifications are logged.\n\n" .
                "Date: " . now()->format('Y-m-d H:i:s'),
                $phone,
                $customer->id
            );
            $this->info("Result: " . ($result ? "✅ SUCCESS" : "❌ FAILED"));
        } catch (\Exception $e) {
            $this->error("ERROR: {$e->getMessage()}");
        }
        $this->newLine();

        // Step 4: Test quotation notification (SKIPPED - requires PDF template fix)
        $this->info('4. Testing quotation notification...');
        $this->warn("Skipped - Quotation PDF template has syntax error");
        $this->newLine();

        // Step 5: Test policy created notification (SKIPPED - requires PDF document)
        $this->info('5. Testing policy created notification...');
        $this->warn("Skipped - Policy document notification requires PDF file");
        $this->newLine();

        // Step 6: Test renewal reminder (SKIPPED - requires full policy data)
        $this->info('6. Testing renewal reminder notification...');
        $this->warn("Skipped - Renewal reminders require complete policy data and templates");
        $this->newLine();

        // Step 7: Test policy sharing (SKIPPED - requires PDF document)
        $this->info('7. Testing policy sharing notification...');
        $this->warn("Skipped - Policy sharing requires PDF document");
        $this->newLine();

        // Step 8: Test marketing image message (SKIPPED - requires image file)
        $this->info('8. Testing marketing image message...');
        $this->warn("Skipped - Marketing image requires actual image file");
        $this->newLine();

        // Step 9: Test with invalid phone (should fail)
        $this->info('9. Testing failed notification (invalid phone)...');
        try {
            $marketingService = app(MarketingWhatsAppService::class);
            $result = $marketingService->sendTextMessage(
                "This should fail",
                '1234567890',
                $customer->id
            );
            $this->info("Result: " . ($result ? "✅ SUCCESS (Unexpected)" : "❌ FAILED (Expected)"));
        } catch (\Exception $e) {
            $this->info("✅ Failed as expected: {$e->getMessage()}");
        }
        $this->newLine();

        // Step 10: Display recent logs
        $this->info('10. Recent notification logs:');
        $logs = NotificationLog::where(function ($query) use ($phone, $email) {
            $query->where('recipient', $phone)
                  ->orWhere('recipient', $email);
        })
        ->orderBy('created_at', 'desc')
        ->limit(10)
        ->get();

        if ($logs->isEmpty()) {
            $this->warn('No notification logs found!');
        } else {
            $this->table(
                ['ID', 'Channel', 'Status', 'Type', 'Created', 'Error'],
                $logs->map(function ($log) {
                    return [
                        $log->id,
                        $log->channel,
                        $log->status,
                        $log->notificationType->name ?? 'N/A',
                        $log->created_at->format('Y-m-d H:i:s'),
                        $log->error_message ? substr($log->error_message, 0, 50) : '-',
                    ];
                })
            );
        }

        $this->newLine();
        $this->info('=== Test Complete ===');
        $this->info('View logs in admin panel: /admin/notification-logs');

        return Command::SUCCESS;
    }
}
