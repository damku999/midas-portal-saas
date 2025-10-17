<?php

namespace App\Console\Commands;

use App\Services\NotificationLoggerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RetryFailedNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:retry-failed
                            {--limit=100 : Maximum number of notifications to retry}
                            {--force : Force retry even if not due yet}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retry failed notifications with exponential backoff';

    protected NotificationLoggerService $loggerService;

    /**
     * Create a new command instance.
     */
    public function __construct(NotificationLoggerService $loggerService)
    {
        parent::__construct();
        $this->loggerService = $loggerService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting failed notifications retry process...');

        try {
            $limit = (int) $this->option('limit');
            $failedNotifications = $this->loggerService->getFailedNotifications($limit);

            if ($failedNotifications->isEmpty()) {
                $this->info('No failed notifications found ready for retry.');

                return Command::SUCCESS;
            }

            $this->info("Found {$failedNotifications->count()} notification(s) to retry.");

            $retried = 0;
            $skipped = 0;
            $failed = 0;

            foreach ($failedNotifications as $log) {
                // Skip if not due for retry (unless --force option used)
                if (! $this->option('force') && $log->next_retry_at && $log->next_retry_at->isFuture()) {
                    $this->warn("Skipping Log #{$log->id} - retry scheduled for {$log->next_retry_at->format('Y-m-d H:i:s')}");
                    $skipped++;

                    continue;
                }

                $attemptNumber = $log->retry_count + 1;
                $this->line("Retrying Log #{$log->id} (Attempt #{$attemptNumber})...");

                try {
                    $success = $this->loggerService->retryNotification($log);

                    if ($success) {
                        $this->info("  ✓ Successfully queued Log #{$log->id}");
                        $retried++;
                    } else {
                        $this->error("  ✗ Failed to queue Log #{$log->id}");
                        $failed++;
                    }

                } catch (\Exception $e) {
                    $this->error("  ✗ Error retrying Log #{$log->id}: {$e->getMessage()}");
                    $failed++;

                    Log::error('Failed to retry notification in command', [
                        'log_id' => $log->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            $this->newLine();
            $this->table(
                ['Status', 'Count'],
                [
                    ['Retried', $retried],
                    ['Skipped', $skipped],
                    ['Failed', $failed],
                    ['Total Processed', $retried + $skipped + $failed],
                ]
            );

            if ($failed > 0) {
                $this->warn('Some notifications failed to retry. Check logs for details.');

                return Command::FAILURE;
            }

            $this->info('Failed notifications retry process completed successfully.');

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Failed to process retry: '.$e->getMessage());
            Log::error('RetryFailedNotifications command failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return Command::FAILURE;
        }
    }
}
