<?php

namespace App\Services;

use App\Exceptions\GoogleWorkspaceException;
use App\Models\Student;
use Google\Client as GoogleClient;
use Google\Service\Directory;
use Google\Service\Directory\User;

class GoogleWorkspaceDirectoryService
{
    public function isConfigured(): bool
    {
        $path = config('google-workspace.service_account_json_path');

        return $path !== null
            && $path !== ''
            && config('google-workspace.admin_impersonation_email')
            && is_readable($this->resolvePath((string) $path));
    }

    public function resetPassword(Student $student, string $password, bool $changePasswordAtNextLogin): void
    {
        if (! $this->isConfigured()) {
            throw new GoogleWorkspaceException('Google Workspace Directory API is not configured.');
        }

        try {
            $directory = $this->createDirectoryService();

            $user = new User;
            $user->setPassword($password);
            $user->setChangePasswordAtNextLogin($changePasswordAtNextLogin);

            $directory->users->update($student->email, $user);
        } catch (\Throwable $exception) {
            throw new GoogleWorkspaceException(
                'Google password reset failed: '.$exception->getMessage(),
                previous: $exception,
            );
        }
    }

    private function createDirectoryService(): Directory
    {
        $client = new GoogleClient;
        $client->setAuthConfig($this->resolvePath((string) config('google-workspace.service_account_json_path')));
        $client->setScopes(config('google-workspace.directory_scopes'));
        $client->setSubject(config('google-workspace.admin_impersonation_email'));

        return new Directory($client);
    }

    private function resolvePath(string $path): string
    {
        if (str_starts_with($path, DIRECTORY_SEPARATOR) || preg_match('#^[A-Za-z]:\\\\#', $path)) {
            return $path;
        }

        return base_path($path);
    }
}
