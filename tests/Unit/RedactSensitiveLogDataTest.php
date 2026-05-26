<?php

namespace Tests\Unit;

use App\Logging\RedactSensitiveLogData;
use Monolog\Handler\TestHandler;
use Monolog\Logger;
use Tests\TestCase;

class RedactSensitiveLogDataTest extends TestCase
{
    public function test_redacts_temporary_password_pattern_and_sensitive_keys(): void
    {
        $handler = new TestHandler;
        $logger = new Logger('test');
        $logger->pushHandler($handler);

        (new RedactSensitiveLogData)($logger);

        $logger->info('Issued Mint-River-4321-Sky to kiosk', [
            'temporary_password' => 'Mint-River-4321-Sky',
            'request_id' => 42,
        ]);

        $record = $handler->getRecords()[0];

        $this->assertStringContainsString('[REDACTED]', $record['message']);
        $this->assertSame('[REDACTED]', $record['context']['temporary_password']);
        $this->assertSame(42, $record['context']['request_id']);
    }
}
