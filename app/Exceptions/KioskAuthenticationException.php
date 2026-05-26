<?php

namespace App\Exceptions;

use Exception;

class KioskAuthenticationException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $reasonCode = 'kiosk_auth_failed',
        int $code = 401,
    ) {
        parent::__construct($message, $code);
    }

    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }
}
