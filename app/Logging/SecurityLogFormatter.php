<?php

namespace App\Logging;

use Illuminate\Log\Logger as IlluminateLogger;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\LogRecord;

class SecurityLogFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $context = $record->context;

        $formatted = [
            'timestamp' => $record->datetime->format('Y-m-d H:i:s.u'),
            'level' => $record->level->getName(),
            'security_event' => $context['security_event'] ?? $record->message,
            'severity' => $context['severity'] ?? $record->level->getName(),
            'user_id' => $context['user_id'] ?? null,
            'ip_address' => $context['ip_address'] ?? request()?->ip(),
            'user_agent' => $context['user_agent'] ?? request()?->header('User-Agent'),
            'url' => $context['url'] ?? request()?->fullUrl(),
            'method' => request()?->method(),
            'session_id' => $context['session_id'] ?? session()->getId(),
            'headers' => $context['headers'] ?? [],
            'security_data' => $context['security_data'] ?? [],
            'environment' => app()->environment(),
            'server' => request()?->server('SERVER_NAME', 'unknown'),
            'referer' => request()?->header('Referer'),
        ];

        return json_encode($formatted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
    }

    /**
     * Customize the given logger instance.
     */
    public function __invoke(Logger|IlluminateLogger $logger): void
    {
        if ($logger instanceof IlluminateLogger) {
            $logger = $logger->getLogger();
        }

        if ($logger instanceof Logger) {
            foreach ($logger->getHandlers() as $handler) {
                $handler->setFormatter($this);
            }
        }
    }
}
