<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\LogRecord;

class BusinessLogFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $context = $record->context;

        $formatted = [
            'timestamp' => $record->datetime->format('Y-m-d H:i:s.u'),
            'level' => $record->level->getName(),
            'business_event' => $context['business_event'] ?? $context['activity'] ?? $record->message,
            'entity_type' => $context['entity_type'] ?? null,
            'entity_id' => $context['entity_id'] ?? null,
            'business_data' => $context['business_data'] ?? $context['metadata'] ?? [],
            'user_id' => $context['user_id'] ?? null,
            'user_email' => $context['user_email'] ?? auth()->user()?->email,
            'correlation_id' => $context['correlation_id'] ?? null,
            'trace_id' => $context['trace_id'] ?? null,
            'session_id' => $context['session_id'] ?? session()->getId(),
            'ip_address' => $context['ip_address'] ?? request()?->ip(),
            'url' => $context['url'] ?? request()?->fullUrl(),
            'method' => $context['method'] ?? request()?->method(),
            'environment' => app()->environment(),
            'server' => request()?->server('SERVER_NAME', 'unknown'),
        ];

        return json_encode($formatted, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)."\n";
    }

    /**
     * Customize the given logger instance.
     */
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->setFormatter($this);
        }
    }
}
