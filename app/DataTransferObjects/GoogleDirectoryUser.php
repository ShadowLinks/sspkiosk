<?php

namespace App\DataTransferObjects;

readonly class GoogleDirectoryUser
{
    public function __construct(
        public string $email,
        public string $googleSub,
        public string $name,
        public ?string $orgUnitPath = null,
        public ?string $school = null,
        public ?string $grade = null,
    ) {}
}
