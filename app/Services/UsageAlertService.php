<?php

namespace App\Services;

use App\Models\Central\AuditLog;
use App\Models\Central\Tenant;
use App\Models\Central\UsageAlert;
use App\Traits\WhatsAppApiTrait;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UsageAlertService
{
    use WhatsAppApiTrait;
    /**
     * Alert threshold constants.
     */
    public const THRESHOLD_WARNING = 80;   // 80% usage
    public const THRESHOLD_CRITICAL = 90;  // 90% usage
    public const THRESHOLD_EXCEEDED = 100; // 100% usage

    /**
     * Cooldown period in hours to prevent alert spam.
     */
    public const ALERT_COOLDOWN_HOURS = 24;

    public function __construct(
        protected UsageTrackingService $usageTrackingService
    ) {}

    /**
     * Check usage thresholds for a specific tenant and create alerts if needed.
     *
     * @param Tenant $tenant The tenant to check
     * @param array $resourceTypes Optional specific resources to check ['users', 'customers', 'storage']
     * @return array Array of created alerts
     */
    public function checkTenantThresholds(Tenant $tenant, array $resourceTypes = []): array
    {
        if (!$tenant->subscription || !$tenant->subscription->isActive()) {
            Log::debug('Skipping usage check for inactive tenant', ['tenant_id' => $tenant->id]);
            return [];
        }

        // Default to all resource types if none specified
        if (empty($resourceTypes)) {
            $resourceTypes = ['users', 'customers', 'storage'];
        }

        $createdAlerts = [];

        foreach ($resourceTypes as $resourceType) {
            $alert = $this->checkResourceThreshold($tenant, $resourceType);

            if ($alert) {
                $createdAlerts[] = $alert;
            }
        }

        // Auto-resolve alerts if usage dropped below thresholds
        $this->autoResolveAlerts($tenant);

        return $createdAlerts;
    }

    /**
     * Check threshold for a specific resource type.
     *
     * @param Tenant $tenant
     * @param string $resourceType 'users', 'customers', or 'storage'
     * @return UsageAlert|null Created alert or null if no alert needed
     */
    protected function checkResourceThreshold(Tenant $tenant, string $resourceType): ?UsageAlert
    {
        $usage = $this->usageTrackingService->getTenantUsage($tenant);
        $plan = $tenant->subscription->plan;

        // Get current usage and limit based on resource type
        [$current, $limit] = match ($resourceType) {
            'users' => [$usage['users'], $plan->max_users],
            'customers' => [$usage['customers'], $plan->max_customers],
            'storage' => [round(($usage['storage_mb'] ?? 0) / 1024, 2), $plan->max_storage_gb],
            default => [0, -1],
        };

        // Skip if unlimited or invalid limit
        if ($limit === -1 || $limit === 0 || $limit === null || $limit < 0) {
            return null;
        }

        // Ensure current is not negative
        $current = max(0, $current);

        // Calculate usage percentage
        $percentage = ($current / $limit) * 100;

        // Determine threshold level
        $thresholdLevel = null;
        if ($percentage >= self::THRESHOLD_EXCEEDED) {
            $thresholdLevel = 'exceeded';
        } elseif ($percentage >= self::THRESHOLD_CRITICAL) {
            $thresholdLevel = 'critical';
        } elseif ($percentage >= self::THRESHOLD_WARNING) {
            $thresholdLevel = 'warning';
        }

        // No alert needed if below warning threshold
        if (!$thresholdLevel) {
            return null;
        }

        // Check if similar alert already exists and is recent (cooldown period)
        if ($this->isAlertInCooldown($tenant->id, $resourceType, $thresholdLevel)) {
            Log::debug('Alert in cooldown period, skipping', [
                'tenant_id' => $tenant->id,
                'resource_type' => $resourceType,
                'threshold_level' => $thresholdLevel,
            ]);
            return null;
        }

        // Create the alert
        $alert = UsageAlert::create([
            'tenant_id' => $tenant->id,
            'resource_type' => $resourceType,
            'threshold_level' => $thresholdLevel,
            'usage_percentage' => round($percentage, 2),
            'current_usage' => $current,
            'limit_value' => $limit,
            'alert_status' => 'pending',
        ]);

        Log::info('Usage alert created', [
            'alert_id' => $alert->id,
            'tenant_id' => $tenant->id,
            'resource_type' => $resourceType,
            'threshold_level' => $thresholdLevel,
            'usage_percentage' => $percentage,
        ]);

        // Send notification immediately
        $this->sendAlertNotification($alert);

        // Log to audit trail
        $companyName = $tenant->data['company_name'] ?? $tenant->domains->first()?->domain ?? 'Unknown';
        AuditLog::log(
            'usage_alert.created',
            "Usage alert: {$resourceType} at {$percentage}% for {$companyName}",
            null,
            $tenant->id,
            [
                'resource_type' => $resourceType,
                'threshold_level' => $thresholdLevel,
                'current' => $current,
                'limit' => $limit,
                'percentage' => round($percentage, 2),
            ]
        );

        return $alert;
    }

    /**
     * Check if an alert is in cooldown period (prevent spam).
     *
     * @param string $tenantId
     * @param string $resourceType
     * @param string $thresholdLevel
     * @return bool
     */
    protected function isAlertInCooldown(string $tenantId, string $resourceType, string $thresholdLevel): bool
    {
        $cooldownTime = now()->subHours(self::ALERT_COOLDOWN_HOURS);

        return UsageAlert::where('tenant_id', $tenantId)
            ->where('resource_type', $resourceType)
            ->where('threshold_level', $thresholdLevel)
            ->where('created_at', '>=', $cooldownTime)
            ->exists();
    }

    /**
     * Auto-resolve alerts when usage drops below threshold.
     *
     * @param Tenant $tenant
     * @return int Number of resolved alerts
     */
    protected function autoResolveAlerts(Tenant $tenant): int
    {
        $activeAlerts = UsageAlert::active()->forTenant($tenant->id)->get();
        $resolvedCount = 0;

        foreach ($activeAlerts as $alert) {
            $currentPercentage = $this->usageTrackingService->getUsagePercentage($tenant, $alert->resource_type);

            // Get threshold value for comparison
            $thresholdValue = match ($alert->threshold_level) {
                'warning' => self::THRESHOLD_WARNING,
                'critical' => self::THRESHOLD_CRITICAL,
                'exceeded' => self::THRESHOLD_EXCEEDED,
                default => 100,
            };

            // Resolve if usage dropped below threshold (with 5% buffer to prevent flapping)
            if ($currentPercentage < ($thresholdValue - 5)) {
                $alert->resolve("Usage dropped to {$currentPercentage}%");
                $resolvedCount++;

                Log::info('Usage alert auto-resolved', [
                    'alert_id' => $alert->id,
                    'tenant_id' => $tenant->id,
                    'resource_type' => $alert->resource_type,
                    'current_percentage' => $currentPercentage,
                ]);
            }
        }

        return $resolvedCount;
    }

    /**
     * Send alert notification via email and WhatsApp.
     *
     * @param UsageAlert $alert
     * @return bool
     */
    public function sendAlertNotification(UsageAlert $alert): bool
    {
        $sentChannels = [];
        $anySuccess = false;

        try {
            $tenant = $alert->tenant;
            $companyEmail = $tenant->data['email'] ?? null;
            $companyPhone = $tenant->data['phone'] ?? null;

            // Prepare notification data
            $notificationData = [
                'company_name' => $tenant->data['company_name'] ?? 'Company',
                'resource_type' => $alert->resource_type_display,
                'threshold_level' => $alert->threshold_display,
                'usage_percentage' => round($alert->usage_percentage, 1),
                'current_usage' => $alert->current_usage,
                'limit_value' => $alert->limit_value,
                'usage_display' => $alert->usage_display,
                'severity' => $alert->severity,
                'plan_name' => $tenant->subscription->plan->name ?? 'Current Plan',
                'upgrade_url' => config('app.url') . '/plans',
            ];

            // Send Email Notification
            if ($companyEmail) {
                try {
                    $template = $this->getEmailTemplate($alert->threshold_level);
                    Mail::send(
                        $template,
                        $notificationData,
                        function ($message) use ($companyEmail, $alert, $notificationData) {
                            $message->to($companyEmail)
                                ->subject($this->getEmailSubject($alert, $notificationData));
                        }
                    );
                    $sentChannels[] = 'email';
                    $anySuccess = true;

                    Log::info('Usage alert email sent', [
                        'alert_id' => $alert->id,
                        'tenant_id' => $tenant->id,
                        'email' => $companyEmail,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send usage alert email', [
                        'alert_id' => $alert->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Send WhatsApp Notification
            if ($companyPhone && $this->isWhatsAppNotificationEnabled()) {
                try {
                    $whatsappMessage = $this->buildWhatsAppMessage($alert, $notificationData);
                    $this->whatsAppSendMessage($whatsappMessage, $companyPhone);
                    $sentChannels[] = 'whatsapp';
                    $anySuccess = true;

                    Log::info('Usage alert WhatsApp sent', [
                        'alert_id' => $alert->id,
                        'tenant_id' => $tenant->id,
                        'phone' => $companyPhone,
                    ]);
                } catch (\Exception $e) {
                    Log::error('Failed to send usage alert WhatsApp', [
                        'alert_id' => $alert->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // Mark as sent if at least one channel succeeded
            if ($anySuccess) {
                $alert->markAsSent($sentChannels);
            }

            return $anySuccess;

        } catch (\Exception $e) {
            Log::error('Failed to send usage alert notification', [
                'alert_id' => $alert->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return false;
        }
    }

    /**
     * Build WhatsApp message for usage alert.
     *
     * @param UsageAlert $alert
     * @param array $data
     * @return string
     */
    protected function buildWhatsAppMessage(UsageAlert $alert, array $data): string
    {
        $emoji = match ($alert->threshold_level) {
            'warning' => '⚠️',
            'critical' => '🚨',
            'exceeded' => '⛔',
            default => '📊',
        };

        $header = match ($alert->threshold_level) {
            'warning' => "*Usage Warning Alert*",
            'critical' => "*🚨 CRITICAL USAGE ALERT 🚨*",
            'exceeded' => "*⛔ USAGE LIMIT EXCEEDED ⛔*",
            default => "*Usage Alert*",
        };

        $message = "{$emoji} {$header}\n\n";
        $message .= "Dear *{$data['company_name']}*,\n\n";

        // Alert details
        $message .= "Your *{$data['resource_type']}* usage has reached:\n";
        $message .= "• *{$data['usage_percentage']}%* ({$data['usage_display']})\n";
        $message .= "• Plan Limit: *{$data['limit_value']}*\n";
        $message .= "• Current Plan: *{$data['plan_name']}*\n\n";

        // Severity-specific message
        if ($alert->threshold_level === 'exceeded') {
            // Fetch Central Tenant model
            $centralTenant = Tenant::find($alert->tenant_id);
            $graceDays = $centralTenant ? $this->getGracePeriodRemaining($centralTenant, $alert->resource_type) : 0;

            if ($graceDays > 0) {
                $message .= "⏰ *Grace Period: {$graceDays} days remaining*\n";
                $message .= "After this period, creating new " . strtolower($data['resource_type']) . " will be restricted.\n\n";
            } else {
                $message .= "⛔ *Grace period expired*\n";
                $message .= "Creating new " . strtolower($data['resource_type']) . " is now restricted.\n\n";
            }
        } elseif ($alert->threshold_level === 'critical') {
            $message .= "🚨 *Immediate Action Required*\n";
            $message .= "You're approaching your plan limit. Please consider upgrading to avoid service interruptions.\n\n";
        } else {
            $message .= "📊 *Monitor your usage*\n";
            $message .= "You're approaching your plan limit. Consider upgrading if you need more resources.\n\n";
        }

        $message .= "To upgrade your plan and increase your limits, please visit your account settings.\n\n";
        $message .= "If you have any questions, feel free to contact our support team.\n\n";
        $message .= "Best regards,\n";
        $message .= "*Midas Portal Team*";

        return $message;
    }

    /**
     * Get email subject based on alert level.
     *
     * @param UsageAlert $alert
     * @param array $data
     * @return string
     */
    protected function getEmailSubject(UsageAlert $alert, array $data): string
    {
        $prefix = match ($alert->threshold_level) {
            'warning' => '⚠️ Usage Warning',
            'critical' => '🚨 Critical Usage Alert',
            'exceeded' => '⛔ Usage Limit Exceeded',
            default => 'Usage Alert',
        };

        return "{$prefix}: {$data['resource_type']} - {$data['company_name']}";
    }

    /**
     * Get email template view name.
     *
     * @param string $thresholdLevel
     * @return string
     */
    protected function getEmailTemplate(string $thresholdLevel): string
    {
        return match ($thresholdLevel) {
            'warning' => 'emails.usage-alerts.warning',
            'critical' => 'emails.usage-alerts.critical',
            'exceeded' => 'emails.usage-alerts.exceeded',
            default => 'emails.usage-alerts.warning',
        };
    }

    /**
     * Get all active alerts for a tenant.
     *
     * @param Tenant $tenant
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActiveAlerts(Tenant $tenant)
    {
        return UsageAlert::active()
            ->forTenant($tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get alert summary for dashboard.
     *
     * @param Tenant $tenant
     * @return array
     */
    public function getAlertSummary(Tenant $tenant): array
    {
        $activeAlerts = $this->getActiveAlerts($tenant);

        return [
            'total_active' => $activeAlerts->count(),
            'warning_count' => $activeAlerts->where('threshold_level', 'warning')->count(),
            'critical_count' => $activeAlerts->where('threshold_level', 'critical')->count(),
            'exceeded_count' => $activeAlerts->where('threshold_level', 'exceeded')->count(),
            'alerts' => $activeAlerts,
            'has_critical' => $activeAlerts->where('threshold_level', 'critical')->isNotEmpty()
                || $activeAlerts->where('threshold_level', 'exceeded')->isNotEmpty(),
        ];
    }

    /**
     * Get statistics for Central Admin dashboard.
     *
     * @return array
     */
    public function getGlobalAlertStatistics(): array
    {
        $cacheKey = 'global_usage_alert_stats';

        return Cache::remember($cacheKey, now()->addMinutes(15), function () {
            $activeAlerts = UsageAlert::active()->get();

            return [
                'total_active_alerts' => $activeAlerts->count(),
                'tenants_with_warnings' => $activeAlerts->where('threshold_level', 'warning')->pluck('tenant_id')->unique()->count(),
                'tenants_with_critical' => $activeAlerts->where('threshold_level', 'critical')->pluck('tenant_id')->unique()->count(),
                'tenants_exceeded' => $activeAlerts->where('threshold_level', 'exceeded')->pluck('tenant_id')->unique()->count(),
                'alerts_by_resource' => [
                    'users' => $activeAlerts->where('resource_type', 'users')->count(),
                    'customers' => $activeAlerts->where('resource_type', 'customers')->count(),
                    'storage' => $activeAlerts->where('resource_type', 'storage')->count(),
                ],
                'recent_alerts' => UsageAlert::recent()
                    ->orderBy('created_at', 'desc')
                    ->limit(10)
                    ->with('tenant')
                    ->get(),
            ];
        });
    }

    /**
     * Clear global statistics cache.
     *
     * @return void
     */
    public function clearStatisticsCache(): void
    {
        Cache::forget('global_usage_alert_stats');
    }

    /**
     * Acknowledge an alert (tenant confirms they've seen it).
     *
     * @param UsageAlert $alert
     * @param string|null $notes
     * @return bool
     */
    public function acknowledgeAlert(UsageAlert $alert, ?string $notes = null): bool
    {
        $result = $alert->acknowledge($notes);

        if ($result) {
            Log::info('Usage alert acknowledged', [
                'alert_id' => $alert->id,
                'tenant_id' => $alert->tenant_id,
            ]);

            $this->clearStatisticsCache();
        }

        return $result;
    }

    /**
     * Check if tenant should be blocked due to usage limits.
     *
     * @param Tenant $tenant
     * @param string $resourceType
     * @return bool
     */
    public function shouldBlockResource(Tenant $tenant, string $resourceType): bool
    {
        // Check if there's an active "exceeded" alert for this resource
        $exceededAlert = UsageAlert::active()
            ->forTenant($tenant->id)
            ->forResource($resourceType)
            ->atThreshold('exceeded')
            ->first();

        if (!$exceededAlert) {
            return false;
        }

        // Check if grace period has passed (3 days after alert creation)
        $gracePeriodEnd = $exceededAlert->created_at->addDays(3);

        return now()->isAfter($gracePeriodEnd);
    }

    /**
     * Get grace period remaining for exceeded limit.
     *
     * @param Tenant $tenant
     * @param string $resourceType
     * @return int Days remaining in grace period, 0 if expired or no alert
     */
    public function getGracePeriodRemaining(Tenant $tenant, string $resourceType): int
    {
        $exceededAlert = UsageAlert::active()
            ->forTenant($tenant->id)
            ->forResource($resourceType)
            ->atThreshold('exceeded')
            ->first();

        if (!$exceededAlert) {
            return 0;
        }

        $gracePeriodEnd = $exceededAlert->created_at->addDays(3);
        $daysRemaining = now()->diffInDays($gracePeriodEnd, false);

        return max(0, (int) $daysRemaining);
    }
}
