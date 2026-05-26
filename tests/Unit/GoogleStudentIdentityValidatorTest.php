<?php

namespace Tests\Unit;

use App\Exceptions\StudentAuthenticationException;
use App\Services\GoogleStudentIdentityValidator;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class GoogleStudentIdentityValidatorTest extends TestCase
{
    private GoogleStudentIdentityValidator $validator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->validator = new GoogleStudentIdentityValidator;
    }

    public function test_accepts_email_on_configured_student_domain(): void
    {
        Config::set('google-workspace.student_domain', 'students.example.org');

        $this->validator->validateEmailDomain('alex@students.example.org');

        $this->assertTrue(true);
    }

    public function test_rejects_email_outside_student_domain(): void
    {
        Config::set('google-workspace.student_domain', 'students.example.org');

        $this->expectException(StudentAuthenticationException::class);

        $this->validator->validateEmailDomain('teacher@staff.example.org');
    }

    public function test_rejects_mismatched_hosted_domain(): void
    {
        Config::set('google-workspace.student_domain', 'students.example.org');

        $this->expectException(StudentAuthenticationException::class);

        $this->validator->validateHostedDomain('other.example.org', 'alex@students.example.org');
    }

    public function test_rejects_blocked_staff_org_unit(): void
    {
        Config::set('google-workspace.blocked_staff_org_units', ['/Staff']);
        Config::set('google-workspace.allowed_student_org_units', []);

        $this->expectException(StudentAuthenticationException::class);

        $this->validator->validateOrgUnit('/Staff/Technology');
    }

    public function test_requires_org_unit_when_allowed_list_configured(): void
    {
        Config::set('google-workspace.allowed_student_org_units', ['/Students']);
        Config::set('google-workspace.blocked_staff_org_units', []);

        $this->expectException(StudentAuthenticationException::class);

        $this->validator->validateOrgUnit(null);
    }

    public function test_accepts_org_unit_under_allowed_path(): void
    {
        Config::set('google-workspace.allowed_student_org_units', ['/Students/High School']);
        Config::set('google-workspace.blocked_staff_org_units', []);

        $this->validator->validateOrgUnit('/Students/High School/Grade 10');

        $this->assertTrue(true);
    }

    public function test_rejects_org_unit_outside_allowed_paths(): void
    {
        Config::set('google-workspace.allowed_student_org_units', ['/Students/High School']);
        Config::set('google-workspace.blocked_staff_org_units', []);

        $this->expectException(StudentAuthenticationException::class);

        $this->validator->validateOrgUnit('/Students/Middle School');
    }
}
