<?php

namespace App\Services;

use App\DataTransferObjects\GoogleDirectoryUser;
use Google\Client as GoogleClient;
use Google\Service\Directory;
use Illuminate\Support\Facades\Log;

class GoogleWorkspaceDirectoryLookupService
{
    public function isAvailable(): bool
    {
        $path = config('google-workspace.service_account_json_path');

        return $path !== null
            && $path !== ''
            && config('google-workspace.admin_impersonation_email')
            && is_readable($this->resolvePath($path));
    }

    public function findUserByEmail(string $email): ?GoogleDirectoryUser
    {
        if (! $this->isAvailable()) {
            return null;
        }

        try {
            $client = new GoogleClient;
            $client->setAuthConfig($this->resolvePath((string) config('google-workspace.service_account_json_path')));
            $client->setScopes(config('google-workspace.directory_scopes'));
            $client->setSubject(config('google-workspace.admin_impersonation_email'));

            $directory = new Directory($client);
            $user = $directory->users->get($email);

            return new GoogleDirectoryUser(
                email: (string) ($user->getPrimaryEmail() ?? $email),
                googleSub: (string) $user->getId(),
                name: (string) ($user->getName()?->getFullName() ?? $email),
                orgUnitPath: $user->getOrgUnitPath(),
                school: $this->extractCustomField($user, 'school'),
                grade: $this->extractCustomField($user, 'grade'),
            );
        } catch (\Throwable $exception) {
            Log::warning('Google Directory lookup failed.', [
                'email' => $email,
                'message' => $exception->getMessage(),
            ]);

            return null;
        }
    }

    private function extractCustomField(object $user, string $field): ?string
    {
        $customSchemas = $user->getCustomSchemas();

        if (! is_array($customSchemas)) {
            return null;
        }

        foreach ($customSchemas as $schema) {
            if (! is_array($schema) || ! isset($schema[$field])) {
                continue;
            }

            $value = $schema[$field];

            return is_string($value) ? $value : null;
        }

        return null;
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('#^[A-Za-z]:\\\\#', $path)) {
            return $path;
        }

        return base_path($path);
    }
}
