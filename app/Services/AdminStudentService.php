<?php

namespace App\Services;

use App\Models\Student;

class AdminStudentService
{
    public function __construct(
        private readonly AuditLogService $auditLog,
    ) {}

    public function setResetEnabled(Student $student, bool $enabled, int $adminUserId): void
    {
        $student->update(['reset_enabled' => $enabled]);

        $this->auditLog->logAdmin(
            $enabled ? 'admin.student.reset_enabled' : 'admin.student.reset_disabled',
            $adminUserId,
            'student',
            (string) $student->id,
            ['email' => $student->email],
        );
    }
}
