<?php

namespace App\Services;

use App\Exceptions\StudentAuthenticationException;

class GoogleStudentIdentityValidator
{
    public function validateEmailDomain(string $email): void
    {
        $domain = config('google-workspace.student_domain');

        if ($domain === null || $domain === '') {
            throw new StudentAuthenticationException(
                'Student Google domain is not configured.',
                'Registration is not available right now. Please contact technology staff.',
                'config_missing_domain',
            );
        }

        $emailDomain = strtolower((string) substr(strrchr($email, '@'), 1));

        if ($emailDomain !== strtolower($domain)) {
            throw new StudentAuthenticationException(
                "Email domain mismatch for {$email}.",
                'Please sign in with your school student Google account.',
                'invalid_email_domain',
            );
        }
    }

    public function validateHostedDomain(?string $hostedDomain, string $email): void
    {
        $expectedDomain = strtolower((string) config('google-workspace.student_domain'));

        if ($hostedDomain === null || $hostedDomain === '') {
            return;
        }

        if (strtolower($hostedDomain) !== $expectedDomain) {
            throw new StudentAuthenticationException(
                "Hosted domain mismatch for {$email}: {$hostedDomain}.",
                'Please sign in with your school student Google account.',
                'invalid_hosted_domain',
            );
        }
    }

    public function validateOrgUnit(?string $orgUnitPath): void
    {
        $blocked = config('google-workspace.blocked_staff_org_units', []);
        $allowed = config('google-workspace.allowed_student_org_units', []);

        if ($orgUnitPath === null || $orgUnitPath === '') {
            if ($allowed !== []) {
                throw new StudentAuthenticationException(
                    'Org unit path missing but allowed org units are configured.',
                    'We could not verify your student account placement. Please contact technology staff.',
                    'missing_org_unit',
                );
            }

            return;
        }

        foreach ($blocked as $blockedPath) {
            if ($this->orgUnitMatches($orgUnitPath, $blockedPath)) {
                throw new StudentAuthenticationException(
                    "Account in blocked org unit: {$orgUnitPath}.",
                    'This account is not eligible for student password reset registration.',
                    'blocked_org_unit',
                );
            }
        }

        if ($allowed === []) {
            return;
        }

        foreach ($allowed as $allowedPath) {
            if ($this->orgUnitMatches($orgUnitPath, $allowedPath)) {
                return;
            }
        }

        throw new StudentAuthenticationException(
            "Account org unit not allowed: {$orgUnitPath}.",
            'This account is not eligible for student password reset registration.',
            'org_unit_not_allowed',
        );
    }

    private function orgUnitMatches(string $userPath, string $configuredPath): bool
    {
        $userPath = rtrim($userPath, '/');
        $configuredPath = rtrim($configuredPath, '/');

        return $userPath === $configuredPath
            || str_starts_with($userPath, $configuredPath.'/');
    }
}
