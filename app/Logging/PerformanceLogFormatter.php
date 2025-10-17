<?php

namespace App\Logging;

use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\LogRecord;

class PerformanceLogFormatter extends JsonFormatter
{
    public function format(LogRecord $record): string
    {
        // Extract performance data from context
        $context = $record->context;
        $performanceData = $context['performance'] ?? [];

        $formatted = [
            'timestamp' => $record->datetime->format('Y-m-d H:i:s.u'),
            'level' => $record->level->getName(),
            'message' => $record->message,
            'performance' => $performanceData,
            'request' => $context['request'] ?? [],
            'user_id' => $context['user_id'] ?? null,
            'session_id' => $context['session_id'] ?? null,
            'environment' => app()->environment(),
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
