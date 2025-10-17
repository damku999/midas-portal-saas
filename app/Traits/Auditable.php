<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\Auth;

trait Auditable
{
    protected static function bootAuditable()
    {
        static::created(function ($model) {
            $model->auditEvent('created');
        });

        static::updated(function ($model) {
            $model->auditEvent('updated', $model->getOriginal());
        });

        static::deleted(function ($model) {
            $model->auditEvent('deleted');
        });
    }

    public function auditLogs(): MorphMany
    {
        return $this->morphMany(AuditLog::class, 'auditable');
    }

    public function auditEvent(string $event, ?array $oldValues = null): AuditLog
    {
        $actor = Auth::user();
        $request = request();

        return AuditLog::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'actor_type' => $actor ? get_class($actor) : null,
            'actor_id' => $actor?->getKey(),
            'event' => $event,
            'event_category' => $this->getEventCategory($event),
            'old_values' => $oldValues ? $this->filterAuditableAttributes($oldValues) : null,
            'new_values' => $event !== 'deleted' ? $this->filterAuditableAttributes($this->getAttributes()) : null,
            'metadata' => $this->getAuditMetadata(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'request_id' => $request->header('X-Request-ID') ?? uniqid(),
            'risk_score' => $this->calculateRiskScore($event),
            'risk_level' => $this->determineRiskLevel($event),
            'risk_factors' => $this->identifyRiskFactors($event),
            'is_suspicious' => $this->isSuspiciousActivity($event),
            'occurred_at' => now(),
        ]);
    }

    public function auditCustomEvent(string $event, array $metadata = []): AuditLog
    {
        $actor = Auth::user();
        $request = request();

        return AuditLog::create([
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'actor_type' => $actor ? get_class($actor) : null,
            'actor_id' => $actor?->getKey(),
            'event' => $event,
            'event_category' => $this->getEventCategory($event),
            'metadata' => array_merge($this->getAuditMetadata(), $metadata),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->session()?->getId(),
            'request_id' => $request->header('X-Request-ID') ?? uniqid(),
            'risk_score' => $this->calculateRiskScore($event),
            'risk_level' => $this->determineRiskLevel($event),
            'risk_factors' => $this->identifyRiskFactors($event),
            'is_suspicious' => $this->isSuspiciousActivity($event),
            'occurred_at' => now(),
        ]);
    }

    protected function getEventCategory(string $event): string
    {
        $authEvents = ['login', 'logout', 'password_changed', 'two_factor_enabled', 'two_factor_disabled'];
        $dataEvents = ['created', 'updated', 'deleted', 'viewed', 'exported'];
        $systemEvents = ['backup', 'restore', 'maintenance', 'configuration_changed'];

        if (in_array($event, $authEvents)) {
            return 'authentication';
        }

        if (in_array($event, $dataEvents)) {
            return 'data_access';
        }

        if (in_array($event, $systemEvents)) {
            return 'system';
        }

        return 'general';
    }

    protected function filterAuditableAttributes(array $attributes): array
    {
        $hidden = $this->getHidden();
        $auditableExclude = $this->auditableExclude ?? ['password', 'remember_token', 'api_token'];

        $excludeFields = array_merge($hidden, $auditableExclude);

        return array_diff_key($attributes, array_flip($excludeFields));
    }

    protected function getAuditMetadata(): array
    {
        $request = request();

        return [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'referer' => $request->header('Referer'),
            'user_agent_parsed' => $this->parseUserAgent($request->userAgent()),
            'timestamp' => now()->toISOString(),
        ];
    }

    protected function calculateRiskScore(string $event): int
    {
        $baseScore = match ($event) {
            'login' => 10,
            'password_changed' => 20,
            'two_factor_disabled' => 30,
            'deleted' => 25,
            'updated' => 15,
            'created' => 10,
            default => 5,
        };

        $riskFactors = $this->identifyRiskFactors($event);
        $factorScore = count($riskFactors) * 10;

        return min(100, $baseScore + $factorScore);
    }

    protected function determineRiskLevel(string $event): string
    {
        $score = $this->calculateRiskScore($event);

        return match (true) {
            $score >= 80 => 'critical',
            $score >= 60 => 'high',
            $score >= 30 => 'medium',
            default => 'low',
        };
    }

    protected function identifyRiskFactors(string $event): array
    {
        $factors = [];
        $request = request();

        // Check for unusual IP address
        if ($this->isUnusualIP($request->ip())) {
            $factors[] = 'unusual_ip';
        }

        // Check for unusual time
        if ($this->isUnusualTime()) {
            $factors[] = 'unusual_time';
        }

        // Check for unusual user agent
        if ($this->isUnusualUserAgent($request->userAgent())) {
            $factors[] = 'unusual_user_agent';
        }

        // Check for high-risk events
        if (in_array($event, ['deleted', 'two_factor_disabled', 'password_changed'])) {
            $factors[] = 'high_risk_event';
        }

        // Check for rapid succession events
        if ($this->hasRapidSuccessionEvents()) {
            $factors[] = 'rapid_succession';
        }

        return $factors;
    }

    protected function isSuspiciousActivity(string $event): bool
    {
        $riskScore = $this->calculateRiskScore($event);
        $riskFactors = $this->identifyRiskFactors($event);

        return $riskScore >= 70 || count($riskFactors) >= 3;
    }

    protected function isUnusualIP(string $ip): bool
    {
        // Simple check - in production, you'd want to check against known good IPs
        $actor = Auth::user();
        if (! $actor) {
            return false;
        }

        $recentIPs = AuditLog::where('actor_type', get_class($actor))
            ->where('actor_id', $actor->getKey())
            ->where('occurred_at', '>=', now()->subDays(30))
            ->distinct('ip_address')
            ->pluck('ip_address')
            ->toArray();

        return ! in_array($ip, $recentIPs) && count($recentIPs) > 0;
    }

    protected function isUnusualTime(): bool
    {
        $hour = now()->hour;

        // Consider 11 PM to 6 AM as unusual hours
        return $hour >= 23 || $hour <= 6;
    }

    protected function isUnusualUserAgent(string $userAgent): bool
    {
        // Check for bot patterns or unusual user agents
        $botPatterns = ['bot', 'crawler', 'spider', 'scraper'];

        foreach ($botPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    protected function hasRapidSuccessionEvents(): bool
    {
        $actor = Auth::user();
        if (! $actor) {
            return false;
        }

        $recentCount = AuditLog::where('actor_type', get_class($actor))
            ->where('actor_id', $actor->getKey())
            ->where('occurred_at', '>=', now()->subMinutes(5))
            ->count();

        return $recentCount > 10; // More than 10 events in 5 minutes
    }

    protected function parseUserAgent(?string $userAgent): array
    {
        if (! $userAgent) {
            return [];
        }

        // Basic user agent parsing - in production, use a proper library
        $parsed = [
            'browser' => 'Unknown',
            'platform' => 'Unknown',
            'version' => 'Unknown',
        ];

        if (preg_match('/Chrome\/(\d+)/', $userAgent, $matches)) {
            $parsed['browser'] = 'Chrome';
            $parsed['version'] = $matches[1];
        } elseif (preg_match('/Firefox\/(\d+)/', $userAgent, $matches)) {
            $parsed['browser'] = 'Firefox';
            $parsed['version'] = $matches[1];
        } elseif (preg_match('/Safari\/(\d+)/', $userAgent, $matches)) {
            $parsed['browser'] = 'Safari';
            $parsed['version'] = $matches[1];
        }

        if (strpos($userAgent, 'Windows') !== false) {
            $parsed['platform'] = 'Windows';
        } elseif (strpos($userAgent, 'Mac') !== false) {
            $parsed['platform'] = 'macOS';
        } elseif (strpos($userAgent, 'Linux') !== false) {
            $parsed['platform'] = 'Linux';
        }

        return $parsed;
    }
}
