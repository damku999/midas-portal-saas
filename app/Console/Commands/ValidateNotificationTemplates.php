<?php

namespace App\Console\Commands;

use App\Models\NotificationTemplate;
use App\Models\NotificationType;
use App\Services\Notification\VariableResolverService;
use Illuminate\Console\Command;

class ValidateNotificationTemplates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notification:validate
                            {--fix : Attempt to fix common validation issues}
                            {--channel= : Validate specific channel only (whatsapp, email)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Validate notification templates for completeness and correctness';

    protected VariableResolverService $resolver;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->resolver = app(VariableResolverService::class);

        $this->info('🔍 Validating Notification Templates');
        $this->newLine();

        $issues = [];

        // Check 1: All active notification types have templates
        $this->info('✓ Checking template coverage...');
        $coverageIssues = $this->checkTemplateCoverage();
        $issues = array_merge($issues, $coverageIssues);

        // Check 2: Template variables are correctly defined
        $this->newLine();
        $this->info('✓ Checking template variable definitions...');
        $variableIssues = $this->checkTemplateVariables();
        $issues = array_merge($issues, $variableIssues);

        // Check 3: Email templates have subjects
        $this->newLine();
        $this->info('✓ Checking email template subjects...');
        $subjectIssues = $this->checkEmailSubjects();
        $issues = array_merge($issues, $subjectIssues);

        // Check 4: Template content is not empty
        $this->newLine();
        $this->info('✓ Checking template content...');
        $contentIssues = $this->checkTemplateContent();
        $issues = array_merge($issues, $contentIssues);

        // Display results
        $this->newLine();
        if (empty($issues)) {
            $this->info('✅ All templates validated successfully! No issues found.');

            return self::SUCCESS;
        }

        $this->error('❌ Found '.count($issues).' validation issues:');
        $this->newLine();

        foreach ($issues as $index => $issue) {
            $this->line(($index + 1).". {$issue['type']}: {$issue['message']}");
            if (isset($issue['details'])) {
                $this->line("   Details: {$issue['details']}");
            }
        }

        return self::FAILURE;
    }

    /**
     * Check template coverage for all notification types
     */
    protected function checkTemplateCoverage(): array
    {
        $issues = [];
        $channel = $this->option('channel');

        $notificationTypes = NotificationType::where('is_active', true)->get();

        foreach ($notificationTypes as $type) {
            $channels = $channel ? [$channel] : ['whatsapp', 'email'];

            foreach ($channels as $ch) {
                $template = NotificationTemplate::where('notification_type_id', $type->id)
                    ->where('channel', $ch)
                    ->where('is_active', true)
                    ->first();

                if (! $template) {
                    $issues[] = [
                        'type' => 'Missing Template',
                        'message' => "No active {$ch} template for notification type: {$type->code} ({$type->name})",
                        'details' => 'Consider creating a template for this notification type and channel',
                    ];
                }
            }
        }

        return $issues;
    }

    /**
     * Check template variable definitions
     */
    protected function checkTemplateVariables(): array
    {
        $issues = [];
        $channel = $this->option('channel');

        $query = NotificationTemplate::with('notificationType')
            ->where('is_active', true);

        if ($channel) {
            $query->where('channel', $channel);
        }

        $templates = $query->get();

        foreach ($templates as $template) {
            // Check if available_variables is valid JSON or already an array
            $variables = is_array($template->available_variables)
                ? $template->available_variables
                : json_decode($template->available_variables ?? '[]', true);

            if (! is_array($variables) && json_last_error() !== JSON_ERROR_NONE) {
                $issues[] = [
                    'type' => 'Invalid JSON',
                    'message' => "Template ID {$template->id} ({$template->notificationType->code} / {$template->channel}) has invalid available_variables JSON",
                    'details' => json_last_error_msg(),
                ];

                continue;
            }

            // Extract variables from template content
            preg_match_all('/\{\{([a-z_0-9\.]+)\}\}/i', $template->template_content, $matches);
            $usedVariables = array_unique($matches[1]);

            // Check if all used variables are documented
            $undocumented = array_diff($usedVariables, $variables);

            if (! empty($undocumented)) {
                $issues[] = [
                    'type' => 'Undocumented Variables',
                    'message' => "Template ID {$template->id} ({$template->notificationType->code} / {$template->channel}) uses undocumented variables",
                    'details' => 'Variables: '.implode(', ', $undocumented),
                ];
            }

            // Check if documented variables are actually used
            $unused = array_diff($variables, $usedVariables);

            if (! empty($unused)) {
                $issues[] = [
                    'type' => 'Unused Variables',
                    'message' => "Template ID {$template->id} ({$template->notificationType->code} / {$template->channel}) documents unused variables",
                    'details' => 'Variables: '.implode(', ', $unused),
                ];
            }
        }

        return $issues;
    }

    /**
     * Check email templates have subjects
     */
    protected function checkEmailSubjects(): array
    {
        $issues = [];

        $emailTemplates = NotificationTemplate::with('notificationType')
            ->where('channel', 'email')
            ->where('is_active', true)
            ->get();

        foreach ($emailTemplates as $template) {
            if (empty($template->subject)) {
                $issues[] = [
                    'type' => 'Missing Subject',
                    'message' => "Email template ID {$template->id} ({$template->notificationType->code}) is missing subject line",
                    'details' => 'Email templates require a subject line',
                ];
            }
        }

        return $issues;
    }

    /**
     * Check template content is not empty
     */
    protected function checkTemplateContent(): array
    {
        $issues = [];
        $channel = $this->option('channel');

        $query = NotificationTemplate::with('notificationType')
            ->where('is_active', true);

        if ($channel) {
            $query->where('channel', $channel);
        }

        $templates = $query->get();

        foreach ($templates as $template) {
            if (empty(trim($template->template_content))) {
                $issues[] = [
                    'type' => 'Empty Template',
                    'message' => "Template ID {$template->id} ({$template->notificationType->code} / {$template->channel}) has empty content",
                    'details' => 'Template content cannot be empty',
                ];
            }
        }

        return $issues;
    }
}
