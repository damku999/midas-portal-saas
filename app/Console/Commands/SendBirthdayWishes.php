<?php

namespace App\Console\Commands;

use App\Models\Customer;
use App\Traits\WhatsAppApiTrait;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SendBirthdayWishes extends Command
{
    use WhatsAppApiTrait;

    protected $signature = 'send:birthday-wishes';

    protected $description = 'Send birthday wishes to customers whose birthday is today';

    /**
     * Execute the console command.
     *
     * Sends birthday wishes via WhatsApp to customers whose birthday is today.
     * Can be scheduled to run daily.
     *
     * @return void
     */
    public function handle()
    {
        // Check if birthday wishes feature is enabled
        if (! is_birthday_wishes_enabled()) {
            $this->info('Birthday wishes feature is disabled in settings.');

            return;
        }

        $today = Carbon::now();

        // Find customers with birthday today (ignore year)
        $customers = Customer::whereMonth('date_of_birth', $today->month)
            ->whereDay('date_of_birth', $today->day)
            ->where('status', 1)
            ->whereNotNull('mobile_number')
            ->whereNotNull('date_of_birth')
            ->get();

        if ($customers->isEmpty()) {
            $this->info('No birthdays today.');

            return;
        }

        $this->info("Found {$customers->count()} birthday(s) today!");

        $sentCount = 0;
        $skippedCount = 0;

        foreach ($customers as $customer) {
            try {
                // Try to get message from template, fallback to hardcoded
                $templateService = app(\App\Services\TemplateService::class);
                $message = $templateService->renderFromCustomer('birthday_wish', 'whatsapp', $customer);

                if (! $message) {
                    // Fallback to old hardcoded method
                    $message = $this->getBirthdayMessage($customer);
                }

                $this->whatsAppSendMessage($message, $customer->mobile_number, $customer->id, 'birthday_wish');

                $this->info("✓ Sent birthday wish to {$customer->name} ({$customer->mobile_number})");
                $sentCount++;
            } catch (\Exception $e) {
                $this->error("✗ Failed for {$customer->name}: ".$e->getMessage());
                $skippedCount++;
            }
        }

        $this->info("\n🎉 Birthday wishes completed!");
        $this->info("Total: {$customers->count()}, Sent: {$sentCount}, Skipped: {$skippedCount}");
    }

    /**
     * Generate birthday wish message
     */
    private function getBirthdayMessage(Customer $customer): string
    {
        return "🎉 *Happy Birthday, {$customer->name}!* 🎂

Wishing you a wonderful day filled with joy, happiness, and blessings. May this year bring you good health, prosperity, and all the success you deserve.

Thank you for trusting us with your insurance needs. We're honored to be part of your journey!

Warm wishes,
".company_advisor_name().'
'.company_website().'
'.company_title().'
"'.company_tagline().'"';
    }
}
