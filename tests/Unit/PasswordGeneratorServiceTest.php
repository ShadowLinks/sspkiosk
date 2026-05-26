<?php

namespace Tests\Unit;

use App\Services\PasswordGeneratorService;
use Tests\TestCase;

class PasswordGeneratorServiceTest extends TestCase
{
    public function test_generates_password_matching_format_and_min_length(): void
    {
        config([
            'student-password-reset.temp_password.format' => 'word-word-4digits-word',
            'student-password-reset.temp_password.min_length' => 14,
            'student-password-reset.temp_password.word_list' => 'default',
        ]);

        $password = (new PasswordGeneratorService)->generate();

        $this->assertGreaterThanOrEqual(14, mb_strlen($password));
        $this->assertMatchesRegularExpression('/^[A-Za-z]+-[A-Za-z]+-\d{4}-[A-Za-z]+$/', $password);
    }

    public function test_generated_passwords_are_not_identical_each_call(): void
    {
        $service = new PasswordGeneratorService;

        $this->assertNotSame($service->generate(), $service->generate());
    }
}
