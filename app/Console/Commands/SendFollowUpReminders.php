<?php

namespace App\Console\Commands;

use App\Models\Lead;
use App\Models\LeadActivity;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class SendFollowUpReminders extends Command
{
    protected $signature = 'leads:send-follow-up-reminders {--days-ahead=1 : Number of days ahead to check for follow-ups}';

    protected $description = 'Send follow-up reminders for leads with upcoming or overdue follow-ups';

    public function handle()
    {
        $daysAhead = (int) $this->option('days-ahead');

        $this->info("Checking for follow-ups due within {$daysAhead} days...");

        // Get leads with follow-ups due today or overdue
        $overdueLeads = Lead::with(['assignedUser', 'status', 'source'])
            ->followUpOverdue()
            ->active()
            ->get();

        // Get leads with follow-ups due in the next N days
        $upcomingLeads = Lead::with(['assignedUser', 'status', 'source'])
            ->whereNotNull('next_follow_up_date')
            ->whereDate('next_follow_up_date', '>', now())
            ->whereDate('next_follow_up_date', '<=', now()->addDays($daysAhead))
            ->active()
            ->get();

        $this->info("Found {$overdueLeads->count()} overdue follow-ups");
        $this->info("Found {$upcomingLeads->count()} upcoming follow-ups");

        // Process overdue leads
        foreach ($overdueLeads as $lead) {
            $this->sendOverdueReminder($lead);
        }

        // Process upcoming leads
        foreach ($upcomingLeads as $lead) {
            $this->sendUpcomingReminder($lead);
        }

        // Check for activities scheduled today
        $todayActivities = LeadActivity::with(['lead.assignedUser', 'creator'])
            ->today()
            ->pending()
            ->get();

        $this->info("Found {$todayActivities->count()} activities scheduled for today");

        foreach ($todayActivities as $activity) {
            $this->sendActivityReminder($activity);
        }

        $this->info('Follow-up reminders sent successfully!');

        return Command::SUCCESS;
    }

    /**
     * Send overdue follow-up reminder
     */
    protected function sendOverdueReminder(Lead $lead): void
    {
        if (!$lead->assignedUser) {
            $this->warn("Lead {$lead->lead_number} has no assigned user, skipping reminder");
            return;
        }

        $daysOverdue = Carbon::parse($lead->next_follow_up_date)->diffInDays(now());

        $message = "🚨 OVERDUE FOLLOW-UP\n\n" .
            "Lead: {$lead->name} ({$lead->lead_number})\n" .
            "Status: {$lead->status->name}\n" .
            "Source: {$lead->source->name}\n" .
            "Follow-up Date: {$lead->next_follow_up_date->format('Y-m-d')}\n" .
            "Days Overdue: {$daysOverdue}\n" .
            "Mobile: {$lead->mobile_number}\n" .
            "Email: {$lead->email}";

        $this->sendNotification(
            $lead->assignedUser->email,
            "Overdue Follow-up: {$lead->name}",
            $message
        );

        // Log activity
        LeadActivity::create([
            'lead_id' => $lead->id,
            'activity_type' => LeadActivity::TYPE_NOTE,
            'subject' => 'Follow-up Reminder Sent',
            'description' => "Overdue follow-up reminder sent to {$lead->assignedUser->name} ({$daysOverdue} days overdue)",
            'created_by' => 1, // System user
        ]);

        $this->line("Sent overdue reminder for lead {$lead->lead_number} to {$lead->assignedUser->name}");
    }

    /**
     * Send upcoming follow-up reminder
     */
    protected function sendUpcomingReminder(Lead $lead): void
    {
        if (!$lead->assignedUser) {
            return;
        }

        $daysUntil = now()->diffInDays(Carbon::parse($lead->next_follow_up_date));

        $message = "📅 UPCOMING FOLLOW-UP\n\n" .
            "Lead: {$lead->name} ({$lead->lead_number})\n" .
            "Status: {$lead->status->name}\n" .
            "Source: {$lead->source->name}\n" .
            "Follow-up Date: {$lead->next_follow_up_date->format('Y-m-d')}\n" .
            "Days Until: {$daysUntil}\n" .
            "Mobile: {$lead->mobile_number}\n" .
            "Email: {$lead->email}";

        $this->sendNotification(
            $lead->assignedUser->email,
            "Upcoming Follow-up: {$lead->name}",
            $message
        );

        $this->line("Sent upcoming reminder for lead {$lead->lead_number} to {$lead->assignedUser->name}");
    }

    /**
     * Send activity reminder
     */
    protected function sendActivityReminder(LeadActivity $activity): void
    {
        $lead = $activity->lead;

        if (!$lead->assignedUser) {
            return;
        }

        $message = "📋 ACTIVITY SCHEDULED TODAY\n\n" .
            "Activity: {$activity->subject}\n" .
            "Type: {$activity->activity_type}\n" .
            "Lead: {$lead->name} ({$lead->lead_number})\n" .
            "Scheduled Time: {$activity->scheduled_at->format('H:i')}\n" .
            "Description: {$activity->description}";

        $this->sendNotification(
            $lead->assignedUser->email,
            "Activity Reminder: {$activity->subject}",
            $message
        );

        $this->line("Sent activity reminder for {$activity->subject} to {$lead->assignedUser->name}");
    }

    /**
     * Send notification to user
     */
    protected function sendNotification(string $email, string $subject, string $message): void
    {
        try {
            Log::info("Follow-up reminder sent to {$email}: {$subject}");

            // Placeholder - implement based on your notification system
            // Example email notification (uncomment when mail is configured):
            /*
            Mail::raw($message, function ($mail) use ($email, $subject) {
                $mail->to($email)->subject($subject);
            });
            */

            // You could also use WhatsApp, SMS, or your notification service here
        } catch (\Exception $e) {
            Log::error("Failed to send follow-up reminder: " . $e->getMessage());
            $this->error("Failed to send reminder to {$email}: " . $e->getMessage());
        }
    }
}
