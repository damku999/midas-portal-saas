<?php

namespace App\Console\Commands;

use App\Mail\TrialExpiringMail;
use App\Models\Central\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class ProcessTrialSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:process-trials
                            {--send-reminders : Send trial expiration reminders}
                            {--auto-convert : Auto-convert expired trials with payment method}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process trial subscriptions: send reminders and auto-convert';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting trial subscription processing...');

        $sendReminders = $this->option('send-reminders');
        $autoConvert = $this->option('auto-convert');

        // If no options provided, run both
        if (!$sendReminders && !$autoConvert) {
            $sendReminders = true;
            $autoConvert = true;
        }

        if ($sendReminders) {
            $this->sendTrialExpiringReminders();
        }

        if ($autoConvert) {
            $this->autoConvertTrials();
        }

        $this->info('Trial subscription processing completed!');

        return Command::SUCCESS;
    }

    /**
     * Send trial expiration reminders.
     */
    private function sendTrialExpiringReminders(): void
    {
        $this->info('Sending trial expiration reminders...');

        // Reminder schedule: 7 days, 3 days, 1 day before expiration
        $reminderDays = [7, 3, 1];

        foreach ($reminderDays as $days) {
            $subscriptions = Subscription::with(['tenant.domains', 'plan'])
                ->where('is_trial', true)
                ->where('status', 'trial')
                ->whereDate('trial_ends_at', '=', now()->addDays($days)->toDateString())
                ->get();

            foreach ($subscriptions as $subscription) {
                try {
                    // Get admin email from tenant data
                    $adminEmail = $subscription->tenant->data['email'] ?? null;

                    if ($adminEmail) {
                        Mail::to($adminEmail)->send(new TrialExpiringMail($subscription, $days));

                        $this->line("✓ Sent {$days}-day reminder to: {$adminEmail} (Tenant: {$subscription->tenant->id})");

                        Log::info("Trial expiration reminder sent", [
                            'tenant_id' => $subscription->tenant->id,
                            'days_remaining' => $days,
                            'email' => $adminEmail,
                        ]);
                    }
                } catch (\Exception $e) {
                    $this->error("✗ Failed to send reminder to tenant {$subscription->tenant->id}: {$e->getMessage()}");

                    Log::error("Failed to send trial reminder", [
                        'tenant_id' => $subscription->tenant->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            if ($subscriptions->count() > 0) {
                $this->info("Processed {$subscriptions->count()} subscription(s) for {$days}-day reminders");
            }
        }
    }

    /**
     * Auto-convert expired trials with payment method on file.
     */
    private function autoConvertTrials(): void
    {
        $this->info('Auto-converting expired trials with payment method...');

        // Find expired trials with auto_renew enabled and payment method on file
        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->where('is_trial', true)
            ->where('status', 'trial')
            ->where('auto_renew', true)
            ->whereNotNull('payment_method')
            ->whereNotNull('payment_gateway')
            ->where('trial_ends_at', '<=', now())
            ->get();

        $converted = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            try {
                // Calculate subscription end date based on billing interval
                $endsAt = $this->calculateSubscriptionEndDate($subscription->plan->billing_interval);

                // Update subscription to paid status
                $subscription->update([
                    'status' => 'active',
                    'is_trial' => false,
                    'trial_ends_at' => null,
                    'ends_at' => $endsAt,
                    'next_billing_date' => $endsAt,
                ]);

                $this->line("✓ Auto-converted: Tenant {$subscription->tenant->id} to {$subscription->plan->name} (expires: {$endsAt->format('Y-m-d')})");

                Log::info("Trial auto-converted to paid", [
                    'tenant_id' => $subscription->tenant->id,
                    'plan_id' => $subscription->plan_id,
                    'ends_at' => $endsAt->toDateTimeString(),
                    'payment_method' => $subscription->payment_gateway,
                ]);

                $converted++;

                // TODO: Process actual payment with payment gateway
                // $this->processPayment($subscription);

            } catch (\Exception $e) {
                $this->error("✗ Failed to convert tenant {$subscription->tenant->id}: {$e->getMessage()}");

                Log::error("Failed to auto-convert trial", [
                    'tenant_id' => $subscription->tenant->id,
                    'error' => $e->getMessage(),
                ]);

                $failed++;
            }
        }

        $this->info("Auto-conversion completed: {$converted} successful, {$failed} failed");
    }

    /**
     * Calculate subscription end date based on billing interval.
     */
    private function calculateSubscriptionEndDate(string $billingInterval): \Carbon\Carbon
    {
        return match ($billingInterval) {
            'week' => now()->addWeek(),
            'month' => now()->addMonth(),
            'two_month' => now()->addMonths(2),
            'quarter' => now()->addMonths(3),
            'six_month' => now()->addMonths(6),
            'year' => now()->addYear(),
            default => now()->addMonth(),
        };
    }
}
