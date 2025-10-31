<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use App\Models\NotificationType;
use App\Services\NotificationLoggerService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class NotificationLogController extends Controller
{
    public function __construct(protected NotificationLoggerService $loggerService)
    {
        $this->middleware('auth');
        $this->middleware('permission:notification-log-list')->only(['index']);
        $this->middleware('permission:notification-log-view')->only(['show']);
        $this->middleware('permission:notification-log-resend')->only(['resend', 'bulkResend']);
        $this->middleware('permission:notification-log-analytics')->only(['analytics']);
    }

    /**
     * Display notification logs
     */
    public function index(Request $request)
    {
        $builder = NotificationLog::with(['notificationType', 'template', 'sender'])
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('channel')) {
            $builder->where('channel', $request->channel);
        }

        if ($request->filled('status')) {
            $builder->where('status', $request->status);
        }

        if ($request->filled('notifiable_type')) {
            $builder->where('notifiable_type', $request->notifiable_type);
        }

        if ($request->filled('from_date')) {
            $builder->whereDate('created_at', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $builder->whereDate('created_at', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $builder->where(static function ($q) use ($request): void {
                $q->where('recipient', 'like', '%'.$request->search.'%')
                    ->orWhere('message_content', 'like', '%'.$request->search.'%');
            });
        }

        $logs = $builder->paginate(pagination_per_page());

        $notificationTypes = NotificationType::query()->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('admin.notification_logs.index', ['logs' => $logs, 'notificationTypes' => $notificationTypes]);
    }

    /**
     * Show notification log details
     */
    public function show(NotificationLog $notificationLog)
    {
        $notificationLog->load(['notificationType', 'template', 'sender', 'deliveryTracking', 'notifiable']);

        return view('admin.notification_logs.show', ['log' => $notificationLog]);
    }

    /**
     * Resend a failed notification
     */
    public function resend(NotificationLog $notificationLog)
    {
        try {
            if (! $notificationLog->canRetry()) {
                return back()->with('error', 'This notification cannot be retried (max attempts reached or not in failed status).');
            }

            $success = $this->loggerService->retryNotification($notificationLog);

            if ($success) {
                return back()->with('success', 'Notification queued for resending.');
            }

            return back()->with('error', 'Failed to queue notification for resending.');

        } catch (\Exception $exception) {
            Log::error('Failed to resend notification', [
                'log_id' => $notificationLog->id,
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Error: '.$exception->getMessage());
        }
    }

    /**
     * Bulk resend failed notifications
     */
    public function bulkResend(Request $request)
    {
        $request->validate([
            'log_ids' => 'required|array',
            'log_ids.*' => 'exists:notification_logs,id',
        ]);

        try {
            $logs = NotificationLog::query()->whereIn('id', $request->log_ids)->get();
            $queued = 0;
            $skipped = 0;

            foreach ($logs as $log) {
                if ($log->canRetry()) {
                    $this->loggerService->retryNotification($log);
                    $queued++;
                } else {
                    $skipped++;
                }
            }

            $message = sprintf('Queued %d notification(s) for resending.', $queued);
            if ($skipped > 0) {
                $message .= sprintf(' Skipped %d notification(s) (max retries or invalid status).', $skipped);
            }

            return back()->with('success', $message);

        } catch (\Exception $exception) {
            Log::error('Failed to bulk resend notifications', [
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Error: '.$exception->getMessage());
        }
    }

    /**
     * Show analytics dashboard
     */
    public function analytics(Request $request)
    {
        // Default to last 30 days
        $fromDate = $request->filled('from_date')
            ? Carbon::parse($request->from_date)
            : now()->subDays(30);

        $toDate = $request->filled('to_date')
            ? Carbon::parse($request->to_date)
            : now();

        $statistics = $this->loggerService->getStatistics([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);

        // Get daily volume for chart
        $dailyVolume = NotificationLog::query()->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Get channel performance
        $channelPerformance = NotificationLog::query()->selectRaw('channel, status, COUNT(*) as count')
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->groupBy('channel', 'status')
            ->get()
            ->groupBy('channel');

        // Get failed notifications requiring attention
        $failedNotifications = NotificationLog::failed()
            ->where('retry_count', '<', 3)
            ->with(['notificationType', 'template'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.notification_logs.analytics', ['statistics' => $statistics, 'dailyVolume' => $dailyVolume, 'channelPerformance' => $channelPerformance, 'failedNotifications' => $failedNotifications, 'fromDate' => $fromDate, 'toDate' => $toDate]);
    }

    /**
     * Delete old notification logs
     */
    public function cleanup(Request $request)
    {
        $request->validate([
            'days_old' => 'required|integer|min:30|max:365',
        ]);

        try {
            $count = $this->loggerService->archiveOldLogs($request->days_old);

            return back()->with('success', sprintf('Archived %d old notification log(s).', $count));

        } catch (\Exception $exception) {
            Log::error('Failed to cleanup notification logs', [
                'error' => $exception->getMessage(),
            ]);

            return back()->with('error', 'Error: '.$exception->getMessage());
        }
    }
}
