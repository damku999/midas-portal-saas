<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Check for expired trials daily at 2 AM and suspend tenants
        $schedule->command('tenants:check-trial-expiration')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                \Log::error('Trial expiration check failed');
            });

        // Send warning emails for trials expiring in 3 days - runs daily at 9 AM
        $schedule->command('tenants:check-trial-expiration --notify-upcoming')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                \Log::error('Trial expiration warning notifications failed');
            });

        // NEW: Process trial subscriptions - send reminders and auto-convert
        // Runs daily at 8 AM to send reminders (7,3,1 days before expiration)
        $schedule->command('subscriptions:process-trials --send-reminders')
            ->dailyAt('08:00')
            ->withoutOverlapping()
            ->onFailure(function () {
                \Log::error('Trial reminder sending failed');
            });

        // Auto-convert expired trials with payment method on file - runs hourly
        $schedule->command('subscriptions:process-trials --auto-convert')
            ->hourly()
            ->withoutOverlapping()
            ->onFailure(function () {
                \Log::error('Trial auto-conversion failed');
            });

        // Check usage thresholds for all tenants - runs every 6 hours
        $schedule->command('usage:check-thresholds')
            ->everySixHours()
            ->withoutOverlapping()
            ->onFailure(function () {
                \Log::error('Usage threshold check failed');
            });
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
