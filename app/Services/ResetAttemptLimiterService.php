<?php

namespace App\Services;

use App\Enums\PasswordResetRequestStatus;
use App\Models\Kiosk;
use App\Models\PasswordResetRequest;
use App\Models\Student;
use Carbon\Carbon;

class ResetAttemptLimiterService
{
    public function isStudentLockedOut(Student $student): bool
    {
        return $this->failedAttemptsForStudentToday($student) >= config('student-password-reset.max_failed_attempts_per_student');
    }

    public function isKioskLockedOut(Kiosk $kiosk): bool
    {
        return $this->failedAttemptsForKioskToday($kiosk) >= config('student-password-reset.max_failed_attempts_per_kiosk');
    }

    public function failedAttemptsForStudentToday(Student $student): int
    {
        return $this->failedAttemptsQuery()
            ->where('student_id', $student->id)
            ->count();
    }

    public function failedAttemptsForKioskToday(Kiosk $kiosk): int
    {
        return $this->failedAttemptsQuery()
            ->where('kiosk_id', $kiosk->id)
            ->count();
    }

    private function failedAttemptsQuery()
    {
        return PasswordResetRequest::query()
            ->where('status', PasswordResetRequestStatus::Failed)
            ->where('created_at', '>=', Carbon::today());
    }
}
