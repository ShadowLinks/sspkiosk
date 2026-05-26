<?php

namespace App\Exceptions;

use Exception;

class StudentAuthenticationException extends Exception
{
    public function __construct(
        string $message,
        private readonly string $userMessage = 'We could not verify your student account. Please sign in with your school Google account or contact technology staff.',
        private readonly string $reasonCode = 'auth_rejected',
    ) {
        parent::__construct($message);
    }

    public function getUserMessage(): string
    {
        return $this->userMessage;
    }

    public function getReasonCode(): string
    {
        return $this->reasonCode;
    }
}
