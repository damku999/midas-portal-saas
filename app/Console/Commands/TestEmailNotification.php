<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Models\CustomerInsurance;
use App\Models\Quotation;
use App\Services\EmailService;
use Illuminate\Console\Command;

/**
 * Test Email Notification Command
 *
 * Allows testing email notifications for different scenarios
 */
class TestEmailNotification extends Command
{
    protected $signature = 'test:email
                            {type : Type of email to test (welcome|policy|quotation|renewal)}
                            {--email= : Email address to send to (optional)}
                            {--customer-id= : Specific customer ID (optional)}
                            {--insurance-id= : Specific insurance ID (optional)}
                            {--quotation-id= : Specific quotation ID (optional)}';

    protected $description = 'Test email notification sending for different notification types';

    public function __construct(
        private EmailService $emailService
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $type = $this->argument('type');
        $email = $this->option('email');

        $this->info("Testing email notification: {$type}");
        $this->newLine();

        try {
            switch ($type) {
                case 'welcome':
                    return $this->testWelcomeEmail($email);

                case 'policy':
                    return $this->testPolicyEmail($email);

                case 'quotation':
                    return $this->testQuotationEmail($email);

                case 'renewal':
                    return $this->testRenewalEmail($email);

                default:
                    $this->error("Invalid email type: {$type}");
                    $this->info('Valid types: welcome, policy, quotation, renewal');

                    return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $this->error('Email test failed: '.$e->getMessage());
            $this->newLine();
            $this->line('Stack trace:');
            $this->line($e->getTraceAsString());

            return Command::FAILURE;
        }
    }

    /**
     * Test welcome email.
     */
    protected function testWelcomeEmail(?string $email): int
    {
        $customerId = $this->option('customer-id');

        if ($customerId) {
            $customer = Customer::find($customerId);
        } else {
            $customer = Customer::where('email', '!=', null)->first();
        }

        if (! $customer) {
            $this->error('No customer found with email address');

            return Command::FAILURE;
        }

        // Override email if provided
        if ($email) {
            $originalEmail = $customer->email;
            $customer->email = $email;
            $this->info("Overriding customer email: {$originalEmail} → {$email}");
        }

        $this->info("Sending welcome email to: {$customer->name} ({$customer->email})");
        $this->newLine();

        $sent = $this->emailService->sendFromCustomer('customer_welcome', $customer);

        if ($sent) {
            $this->info('✅ Welcome email sent successfully!');
            $this->newLine();
            $this->info("Check inbox: {$customer->email}");

            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to send welcome email');
            $this->info('Check logs: storage/logs/laravel.log');

            return Command::FAILURE;
        }
    }

    /**
     * Test policy email with attachment.
     */
    protected function testPolicyEmail(?string $email): int
    {
        $insuranceId = $this->option('insurance-id');

        if ($insuranceId) {
            $insurance = CustomerInsurance::find($insuranceId);
        } else {
            $insurance = CustomerInsurance::with('customer')
                ->whereNotNull('policy_document_path')
                ->whereHas('customer', function ($q) {
                    $q->whereNotNull('email');
                })
                ->first();
        }

        if (! $insurance) {
            $this->error('No insurance found with policy document and customer email');

            return Command::FAILURE;
        }

        $customer = $insurance->customer;

        // Override email if provided
        if ($email) {
            $originalEmail = $customer->email;
            $customer->email = $email;
            $this->info("Overriding customer email: {$originalEmail} → {$email}");
        }

        $this->info("Sending policy email to: {$customer->name} ({$customer->email})");
        $this->info("Policy No: {$insurance->policy_no}");
        $this->info("Document: {$insurance->policy_document_path}");
        $this->newLine();

        // Check if document exists
        $filePath = storage_path('app/public/'.$insurance->policy_document_path);
        if (! file_exists($filePath)) {
            $this->warn("⚠️  Policy document not found: {$filePath}");
            $this->info('Continuing anyway (will test email without attachment)...');
        }

        $sent = $this->emailService->sendFromInsurance('policy_created', $insurance, [$filePath]);

        if ($sent) {
            $this->info('✅ Policy email sent successfully!');
            $this->newLine();
            $this->info("Check inbox: {$customer->email}");
            $this->info('Verify PDF attachment is included');

            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to send policy email');
            $this->info('Check logs: storage/logs/laravel.log');

            return Command::FAILURE;
        }
    }

    /**
     * Test quotation email with PDF.
     */
    protected function testQuotationEmail(?string $email): int
    {
        $quotationId = $this->option('quotation-id');

        if ($quotationId) {
            $quotation = Quotation::find($quotationId);
        } else {
            $quotation = Quotation::with('customer')
                ->whereHas('customer', function ($q) {
                    $q->whereNotNull('email');
                })
                ->first();
        }

        if (! $quotation) {
            $this->error('No quotation found with customer email');

            return Command::FAILURE;
        }

        $customer = $quotation->customer;

        // Override email if provided
        if ($email) {
            $originalEmail = $customer->email;
            $customer->email = $email;
            $this->info("Overriding customer email: {$originalEmail} → {$email}");
        }

        $this->info("Sending quotation email to: {$customer->name} ({$customer->email})");
        $this->info("Quotation No: {$quotation->quotation_number}");
        $this->newLine();

        // Generate PDF and send
        try {
            $quotationService = app(\App\Services\QuotationService::class);
            $quotationService->sendQuotationViaEmail($quotation);

            $this->info('✅ Quotation email sent successfully!');
            $this->newLine();
            $this->info("Check inbox: {$customer->email}");
            $this->info('Verify PDF comparison is attached');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('❌ Failed to send quotation email: '.$e->getMessage());
            $this->info('Check logs: storage/logs/laravel.log');

            return Command::FAILURE;
        }
    }

    /**
     * Test renewal reminder email.
     */
    protected function testRenewalEmail(?string $email): int
    {
        $insuranceId = $this->option('insurance-id');

        if ($insuranceId) {
            $insurance = CustomerInsurance::find($insuranceId);
        } else {
            $insurance = CustomerInsurance::with('customer')
                ->whereHas('customer', function ($q) {
                    $q->whereNotNull('email');
                })
                ->first();
        }

        if (! $insurance) {
            $this->error('No insurance found with customer email');

            return Command::FAILURE;
        }

        $customer = $insurance->customer;

        // Override email if provided
        if ($email) {
            $originalEmail = $customer->email;
            $customer->email = $email;
            $this->info("Overriding customer email: {$originalEmail} → {$email}");
        }

        $this->info("Sending renewal reminder to: {$customer->name} ({$customer->email})");
        $this->info("Policy No: {$insurance->policy_no}");
        $this->info("Expiry Date: {$insurance->expired_date}");
        $this->newLine();

        $sent = $this->emailService->sendFromInsurance('renewal_30_days', $insurance);

        if ($sent) {
            $this->info('✅ Renewal email sent successfully!');
            $this->newLine();
            $this->info("Check inbox: {$customer->email}");

            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to send renewal email');
            $this->info('Check logs: storage/logs/laravel.log');

            return Command::FAILURE;
        }
    }
}
