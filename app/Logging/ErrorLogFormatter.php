<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\LogRecord;

class ErrorLogFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        $context = $record->context;

        $formatted = [
            'timestamp' => $record->datetime->format('Y-m-d H:i:s.u'),
            'level' => $record->level->getName(),
            'message' => $record->message,
            'exception' => $context['exception'] ?? null,
            'file' => $context['file'] ?? null,
            'line' => $context['line'] ?? null,
            'trace' => $context['trace'] ?? null,
            'fingerprint' => $context['fingerprint'] ?? null,
            'category' => $context['error_category'] ?? null,
            'severity' => $context['severity'] ?? null,
            'user_id' => $context['user_id'] ?? null,
            'url' => $context['url'] ?? null,
            'method' => $context['method'] ?? null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->header('User-Agent'),
            'environment' => app()->environment(),
            'context' => array_diff_key($context, array_flip([
                'exception', 'file', 'line', 'trace', 'fingerprint',
                'error_category', 'severity', 'user_id', 'url', 'method',
            ])),
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
