<?php

namespace App\Services;

class StudentPhotoStorageService
{
    public function isSafeStoragePath(string $path): bool
    {
        if ($path === '' || str_contains($path, '..') || str_starts_with($path, '/')) {
            return false;
        }

        return str_starts_with($path, 'student-photos/');
    }
}
