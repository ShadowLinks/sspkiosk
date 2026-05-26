<?php

namespace App\Services;

use App\Enums\PasswordResetRequestStatus;
use App\Models\PasswordResetRequest;
use App\Models\Student;

class PasswordResetRequestRiskService
{
    public function __construct(
        private readonly ResetAttemptLimiterService $attemptLimiter,
    ) {}

    /**
     * @return list<string>
     */
    public function flagsFor(PasswordResetRequest $request): array
    {
        $flags = [];
        $student = $request->student;
        $required = config('student-password-reset.challenge_questions_required_correct');
        $asked = count($request->challenge_questions_presented ?? []);

        if ($request->challenge_score !== null && $request->challenge_score < $required) {
            $flags[] = 'Challenge score below required threshold';
        }

        if ($request->challenge_score !== null && $asked > 0 && $request->challenge_score < $asked) {
            $flags[] = 'One or more challenge answers incorrect';
        }

        if ($this->attemptLimiter->failedAttemptsForStudentToday($student) > 0) {
            $flags[] = 'Prior failed reset attempts today';
        }

        if (! $student->hasRegistrationPhoto()) {
            $flags[] = 'No registration photo on file';
        }

        $lastApproved = $this->lastApprovedResetAt($student);

        if ($lastApproved !== null && $lastApproved->greaterThan(now()->subHours(24))) {
            $flags[] = 'Password was reset within the last 24 hours';
        }

        return $flags;
    }

    private function lastApprovedResetAt(Student $student): ?\Illuminate\Support\Carbon
    {
        return PasswordResetRequest::query()
            ->where('student_id', $student->id)
            ->where('status', PasswordResetRequestStatus::Completed)
            ->whereNotNull('approved_at')
            ->latest('approved_at')
            ->value('approved_at');
    }
}
