<?php

namespace App\Services;

use App\Models\Student;

class StudentLookupService
{
    public function findRegisteredStudent(string $identifier): ?Student
    {
        $identifier = trim($identifier);

        if ($identifier === '') {
            return null;
        }

        $student = $this->findByEmail($identifier);

        if ($student !== null) {
            return $this->eligibleStudent($student);
        }

        if (! config('student-password-reset.allow_student_id_lookup')) {
            return null;
        }

        $student = $this->findByStudentId($identifier);

        return $student !== null ? $this->eligibleStudent($student) : null;
    }

    public function maskedDisplayName(Student $student): string
    {
        $parts = preg_split('/\s+/', trim($student->name), 2);
        $first = $parts[0] ?? 'Student';
        $lastInitial = isset($parts[1]) && $parts[1] !== ''
            ? strtoupper(mb_substr($parts[1], 0, 1)).'.'
            : '';

        return trim($first.' '.$lastInitial);
    }

    private function findByEmail(string $identifier): ?Student
    {
        if (! config('student-password-reset.allow_email_lookup')) {
            return null;
        }

        if (! str_contains($identifier, '@')) {
            return null;
        }

        return Student::query()
            ->whereRaw('LOWER(email) = ?', [strtolower($identifier)])
            ->first();
    }

    private function findByStudentId(string $identifier): ?Student
    {
        $domain = config('google-workspace.student_domain');

        if ($domain === null || $domain === '') {
            return null;
        }

        $email = strtolower($identifier).'@'.strtolower($domain);

        return Student::query()
            ->whereRaw('LOWER(email) = ?', [$email])
            ->first();
    }

    private function eligibleStudent(Student $student): ?Student
    {
        if (! $student->isRegistered() || ! $student->reset_enabled) {
            return null;
        }

        return $student;
    }
}
