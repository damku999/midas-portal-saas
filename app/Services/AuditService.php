<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuditService
{
    public function getRecentActivity(int $hours = 24, int $limit = 50): Collection
    {
        return AuditLog::with(['auditable', 'actor'])
            ->recentActivity($hours)
            ->orderBy('occurred_at', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getSuspiciousActivity(int $days = 7): Collection
    {
        return AuditLog::with(['auditable', 'actor'])
            ->suspicious()
            ->where('occurred_at', '>=', now()->subDays($days))
            ->orderBy('risk_score', 'desc')
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    public function getHighRiskActivity(int $days = 30): Collection
    {
        return AuditLog::with(['auditable', 'actor'])
            ->highRisk()
            ->where('occurred_at', '>=', now()->subDays($days))
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    public function getActivityByUser($userId, string $userType = User::class, int $days = 30): Collection
    {
        return AuditLog::with(['auditable'])
            ->where('actor_type', $userType)
            ->where('actor_id', $userId)
            ->where('occurred_at', '>=', now()->subDays($days))
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    public function getActivityByEntity($entityId, string $entityType, int $days = 30): Collection
    {
        return AuditLog::with(['actor'])
            ->where('auditable_type', $entityType)
            ->where('auditable_id', $entityId)
            ->where('occurred_at', '>=', now()->subDays($days))
            ->orderBy('occurred_at', 'desc')
            ->get();
    }

    public function getSecurityMetrics(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_events' => AuditLog::query()->where('occurred_at', '>=', $startDate)->count(),
            'suspicious_events' => AuditLog::suspicious()->where('occurred_at', '>=', $startDate)->count(),
            'high_risk_events' => AuditLog::highRisk()->where('occurred_at', '>=', $startDate)->count(),
            'failed_logins' => AuditLog::query()->where('event', 'login_failed')->where('occurred_at', '>=', $startDate)->count(),
            'unique_ips' => AuditLog::query()->where('occurred_at', '>=', $startDate)->distinct('ip_address')->count(),
            'events_by_category' => $this->getEventsByCategory($startDate),
            'risk_distribution' => $this->getRiskDistribution($startDate),
            'hourly_activity' => $this->getHourlyActivity($startDate),
            'top_risk_factors' => $this->getTopRiskFactors($startDate),
        ];
    }

    public function searchLogs(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $builder = AuditLog::with(['auditable', 'actor']);

        if (! empty($filters['event'])) {
            $builder->where('event', $filters['event']);
        }

        if (! empty($filters['event_category'])) {
            $builder->where('event_category', $filters['event_category']);
        }

        if (! empty($filters['risk_level'])) {
            $builder->where('risk_level', $filters['risk_level']);
        }

        if (! empty($filters['is_suspicious'])) {
            $builder->where('is_suspicious', $filters['is_suspicious']);
        }

        if (! empty($filters['ip_address'])) {
            $builder->where('ip_address', $filters['ip_address']);
        }

        if (! empty($filters['actor_id']) && ! empty($filters['actor_type'])) {
            $builder->where('actor_id', $filters['actor_id'])
                ->where('actor_type', $filters['actor_type']);
        }

        if (! empty($filters['date_from'])) {
            $builder->where('occurred_at', '>=', Carbon::parse($filters['date_from']));
        }

        if (! empty($filters['date_to'])) {
            $builder->where('occurred_at', '<=', Carbon::parse($filters['date_to'])->endOfDay());
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $builder->where(static function ($q) use ($search): void {
                $q->where('event', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('ip_address', 'like', sprintf('%%%s%%', $search))
                    ->orWhere('user_agent', 'like', sprintf('%%%s%%', $search))
                    ->orWhereJsonContains('metadata', $search);
            });
        }

        return $builder->orderBy('occurred_at', 'desc')->paginate($perPage);
    }

    public function generateSecurityReport(int $days = 30): array
    {
        $metrics = $this->getSecurityMetrics($days);
        $suspiciousActivity = $this->getSuspiciousActivity($days);
        $highRiskActivity = $this->getHighRiskActivity($days);

        return [
            'period' => [
                'days' => $days,
                'start_date' => now()->subDays($days)->toDateString(),
                'end_date' => now()->toDateString(),
            ],
            'summary' => $metrics,
            'suspicious_activity' => $suspiciousActivity->take(20),
            'high_risk_activity' => $highRiskActivity->take(20),
            'recommendations' => $this->generateRecommendations($metrics),
            'generated_at' => now()->toISOString(),
        ];
    }

    public function exportLogs(array $filters = [], string $format = 'csv'): string
    {
        $logs = $this->searchLogs($filters, 10000)->items();

        return match ($format) {
            'csv' => $this->exportToCsv($logs),
            'json' => $this->exportToJson($logs),
            default => throw new \InvalidArgumentException('Unsupported export format: '.$format),
        };
    }

    protected function getEventsByCategory(Carbon $startDate): array
    {
        return AuditLog::query()->where('occurred_at', '>=', $startDate)
            ->groupBy('event_category')
            ->selectRaw('event_category, count(*) as count')
            ->pluck('count', 'event_category')
            ->toArray();
    }

    protected function getRiskDistribution(Carbon $startDate): array
    {
        return AuditLog::query()->where('occurred_at', '>=', $startDate)
            ->groupBy('risk_level')
            ->selectRaw('risk_level, count(*) as count')
            ->pluck('count', 'risk_level')
            ->toArray();
    }

    protected function getHourlyActivity(Carbon $startDate): array
    {
        return AuditLog::query()->where('occurred_at', '>=', $startDate)
            ->selectRaw('HOUR(occurred_at) as hour, count(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour')
            ->toArray();
    }

    protected function getTopRiskFactors(Carbon $startDate): array
    {
        $logs = AuditLog::query()->where('occurred_at', '>=', $startDate)
            ->whereNotNull('risk_factors')
            ->pluck('risk_factors');

        $factors = [];
        foreach ($logs as $log) {
            foreach ($log as $factor) {
                $factors[$factor] = ($factors[$factor] ?? 0) + 1;
            }
        }

        arsort($factors);

        return array_slice($factors, 0, 10);
    }

    protected function generateRecommendations(array $metrics): array
    {
        $recommendations = [];

        if ($metrics['suspicious_events'] > $metrics['total_events'] * 0.1) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'High Suspicious Activity',
                'description' => 'More than 10% of events are flagged as suspicious. Review security policies.',
                'priority' => 'high',
            ];
        }

        if ($metrics['failed_logins'] > 100) {
            $recommendations[] = [
                'type' => 'warning',
                'title' => 'High Failed Login Attempts',
                'description' => 'Consider implementing account lockout or CAPTCHA mechanisms.',
                'priority' => 'medium',
            ];
        }

        if ($metrics['unique_ips'] > $metrics['total_events'] * 0.5) {
            $recommendations[] = [
                'type' => 'info',
                'title' => 'High IP Diversity',
                'description' => 'Large number of unique IP addresses detected. Monitor for potential bot activity.',
                'priority' => 'low',
            ];
        }

        return $recommendations;
    }

    protected function exportToCsv(array $logs): string
    {
        $csv = "ID,Event,Category,Actor,Auditable,IP Address,Risk Level,Risk Score,Occurred At\n";

        foreach ($logs as $log) {
            $csv .= sprintf(
                "%d,%s,%s,%s,%s,%s,%s,%d,%s\n",
                $log->id,
                $log->event,
                $log->event_category,
                $log->actor_type ? class_basename($log->actor_type).'#'.$log->actor_id : 'System',
                $log->auditable_type ? class_basename($log->auditable_type).'#'.$log->auditable_id : 'N/A',
                $log->ip_address,
                $log->risk_level,
                $log->risk_score,
                $log->occurred_at->toISOString()
            );
        }

        return $csv;
    }

    protected function exportToJson(array $logs): string
    {
        return json_encode($logs, JSON_PRETTY_PRINT);
    }
}
