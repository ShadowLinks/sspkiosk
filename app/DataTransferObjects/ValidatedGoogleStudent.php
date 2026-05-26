<?php

namespace App\DataTransferObjects;

readonly class ValidatedGoogleStudent
{
    public function __construct(
        public string $googleSub,
        public string $email,
        public string $name,
        public ?string $orgUnitPath = null,
        public ?string $school = null,
        public ?string $grade = null,
    ) {}
}
