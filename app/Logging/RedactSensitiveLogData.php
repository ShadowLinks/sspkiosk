<?php

namespace App\Logging;

use Monolog\LogRecord;

class RedactSensitiveLogData
{
    /**
     * @param  \Illuminate\Log\Logger  $logger
     */
    public function __invoke($logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(function (LogRecord $record): LogRecord {
                return $record->with(
                    message: $this->redactString($record->message),
                    context: $this->redactValue($record->context),
                    extra: $this->redactValue($record->extra),
                );
            });
        }
    }

    private function redactValue(mixed $value): mixed
    {
        if (is_string($value)) {
            return $this->redactString($value);
        }

        if (! is_array($value)) {
            return $value;
        }

        $redacted = [];

        foreach ($value as $key => $item) {
            if ($this->isSensitiveKey((string) $key)) {
                $redacted[$key] = '[REDACTED]';

                continue;
            }

            $redacted[$key] = $this->redactValue($item);
        }

        return $redacted;
    }

    private function isSensitiveKey(string $key): bool
    {
        $normalized = strtolower($key);

        return str_contains($normalized, 'password')
            || str_contains($normalized, 'secret')
            || str_contains($normalized, 'token')
            || str_contains($normalized, 'authorization');
    }

    private function redactString(string $value): string
    {
        $redacted = preg_replace(
            '/\b[A-Za-z]+-[A-Za-z]+-\d{4}-[A-Za-z]+\b/',
            '[REDACTED]',
            $value,
        );

        return is_string($redacted) ? $redacted : $value;
    }
}
