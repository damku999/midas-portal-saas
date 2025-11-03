<?php

namespace App\Console\Commands;

use App\Models\Central\AuditLog;
use App\Models\Central\Subscription;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class CheckTrialExpiration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenants:check-trial-expiration {--notify-upcoming : Send notifications for trials expiring in 3 days}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check for expired trials and suspend tenants, optionally send upcoming expiration warnings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting trial expiration check...');

        // Handle upcoming expiration warnings (3 days before)
        if ($this->option('notify-upcoming')) {
            $this->handleUpcomingExpirations();
        }

        // Handle expired trials
        $this->handleExpiredTrials();

        $this->info('Trial expiration check completed!');

        return Command::SUCCESS;
    }

    /**
     * Handle trials expiring in 3 days - send warning emails
     */
    protected function handleUpcomingExpirations()
    {
        $this->info('Checking for trials expiring in 3 days...');

        $upcomingExpirations = Subscription::where('is_trial', true)
            ->where('status', 'trial')
            ->whereDate('trial_ends_at', now()->addDays(3)->toDateString())
            ->with('tenant.domains')
            ->get();

        if ($upcomingExpirations->isEmpty()) {
            $this->info('No trials expiring in 3 days.');
            return;
        }

        $this->info("Found {$upcomingExpirations->count()} trial(s) expiring in 3 days.");

        foreach ($upcomingExpirations as $subscription) {
            $tenant = $subscription->tenant;
            $companyName = $tenant->data['company_name'] ?? 'Unknown';
            $email = $tenant->data['email'] ?? null;

            if (!$email) {
                $this->warn("No email found for tenant: {$companyName}");
                continue;
            }

            try {
                // Send warning email
                Mail::send('emails.trial-expiring-warning', [
                    'companyName' => $companyName,
                    'trialEndsAt' => $subscription->trial_ends_at,
                    'domain' => $tenant->domains->first()->domain ?? 'N/A',
                ], function ($message) use ($email, $companyName) {
                    $message->to($email)
                        ->subject("Trial Expiring Soon - {$companyName}");
                });

                $this->info("Sent warning email to: {$email} ({$companyName})");

                // Log action
                AuditLog::log(
                    'trial.warning_sent',
                    "Trial expiration warning sent for: {$companyName}",
                    null,
                    $tenant->id,
                    ['email' => $email, 'expires_in_days' => 3]
                );

            } catch (\Exception $e) {
                $this->error("Failed to send email to {$email}: {$e->getMessage()}");
            }
        }
    }

    /**
     * Handle expired trials - suspend tenants and send notifications
     */
    protected function handleExpiredTrials()
    {
        $this->info('Checking for expired trials...');

        // Find all trials that have expired but are still active
        $expiredTrials = Subscription::where('is_trial', true)
            ->where('status', 'trial')
            ->where('trial_ends_at', '<', now())
            ->with('tenant.domains')
            ->get();

        if ($expiredTrials->isEmpty()) {
            $this->info('No expired trials found.');
            return;
        }

        $this->info("Found {$expiredTrials->count()} expired trial(s). Suspending...");

        $suspended = 0;
        $failed = 0;

        foreach ($expiredTrials as $subscription) {
            $tenant = $subscription->tenant;
            $companyName = $tenant->data['company_name'] ?? 'Unknown';
            $email = $tenant->data['email'] ?? null;

            try {
                // Suspend the subscription
                $subscription->update(['status' => 'suspended']);

                $this->info("Suspended trial for: {$companyName}");
                $suspended++;

                // Send expiration notification email
                if ($email) {
                    try {
                        Mail::send('emails.trial-expired', [
                            'companyName' => $companyName,
                            'trialEndsAt' => $subscription->trial_ends_at,
                            'domain' => $tenant->domains->first()->domain ?? 'N/A',
                        ], function ($message) use ($email, $companyName) {
                            $message->to($email)
                                ->subject("Trial Expired - Action Required - {$companyName}");
                        });

                        $this->info("Sent expiration email to: {$email}");
                    } catch (\Exception $e) {
                        $this->warn("Failed to send email to {$email}: {$e->getMessage()}");
                    }
                }

                // Log action
                AuditLog::log(
                    'trial.expired_auto_suspend',
                    "Trial expired and tenant auto-suspended: {$companyName}",
                    null,
                    $tenant->id,
                    [
                        'expired_at' => $subscription->trial_ends_at,
                        'auto_suspended' => true,
                    ]
                );

            } catch (\Exception $e) {
                $this->error("Failed to suspend trial for {$companyName}: {$e->getMessage()}");
                $failed++;
            }
        }

        $this->info("Successfully suspended: {$suspended} tenant(s)");
        if ($failed > 0) {
            $this->warn("Failed to suspend: {$failed} tenant(s)");
        }
    }
}
