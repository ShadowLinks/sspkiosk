<?php

namespace App\Services;

use App\DataTransferObjects\ValidatedGoogleStudent;
use App\Exceptions\StudentAuthenticationException;
use Illuminate\Http\RedirectResponse;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;

class GoogleAuthService
{
    public function __construct(
        private readonly GoogleStudentIdentityValidator $identityValidator,
        private readonly GoogleWorkspaceDirectoryLookupService $directoryLookup,
        private readonly ConfigurationValidatorService $configurationValidator,
    ) {}

    public function redirect(): RedirectResponse
    {
        $this->ensureConfigured();

        return Socialite::driver('google')
            ->scopes(['openid', 'email', 'profile'])
            ->with(['hd' => config('google-workspace.student_domain')])
            ->redirect();
    }

    public function handleCallback(): ValidatedGoogleStudent
    {
        $this->ensureConfigured();

        /** @var SocialiteUser $googleUser */
        $googleUser = Socialite::driver('google')->user();

        $email = strtolower((string) $googleUser->getEmail());
        $googleSub = (string) $googleUser->getId();
        $name = (string) ($googleUser->getName() ?: $email);
        $hostedDomain = $googleUser->user['hd'] ?? null;

        $this->identityValidator->validateEmailDomain($email);
        $this->identityValidator->validateHostedDomain(
            is_string($hostedDomain) ? $hostedDomain : null,
            $email,
        );

        $directoryUser = $this->directoryLookup->findUserByEmail($email);

        if ($directoryUser !== null && $directoryUser->googleSub !== $googleSub) {
            throw new StudentAuthenticationException(
                'Google subject does not match Directory user record.',
                'We could not verify your student account. Please contact technology staff.',
                'subject_mismatch',
            );
        }

        $orgUnitPath = $directoryUser?->orgUnitPath;

        if ($this->requiresDirectoryLookup() && $directoryUser === null) {
            throw new StudentAuthenticationException(
                'Directory lookup required but unavailable or user not found.',
                'We could not verify your student account placement. Please contact technology staff.',
                'directory_lookup_failed',
            );
        }

        $this->identityValidator->validateOrgUnit($orgUnitPath);

        return new ValidatedGoogleStudent(
            googleSub: $googleSub,
            email: $email,
            name: $directoryUser?->name ?? $name,
            orgUnitPath: $orgUnitPath,
            school: $directoryUser?->school,
            grade: $directoryUser?->grade,
        );
    }

    private function ensureConfigured(): void
    {
        if (! $this->configurationValidator->isWorkflowConfigured('google_auth')) {
            throw new StudentAuthenticationException(
                'Google OAuth is not fully configured.',
                'Registration is not available right now. Please contact technology staff.',
                'config_incomplete',
            );
        }

        if ($this->requiresDirectoryLookup() && ! $this->configurationValidator->isWorkflowConfigured('google_directory_lookup')) {
            throw new StudentAuthenticationException(
                'Google Directory lookup is required but not configured.',
                'Registration is not available right now. Please contact technology staff.',
                'config_directory_incomplete',
            );
        }
    }

    private function requiresDirectoryLookup(): bool
    {
        return config('google-workspace.allowed_student_org_units') !== []
            || config('google-workspace.blocked_staff_org_units') !== [];
    }
}
