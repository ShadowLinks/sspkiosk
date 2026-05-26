<?php

namespace App\Enums;

enum ResetPasswordMode: string
{
    case TemporaryGenerated = 'temporary_generated';
    case StudentSelectedPendingApproval = 'student_selected_pending_approval';

    public static function tryFromConfig(): ?self
    {
        return self::tryFrom((string) config('student-password-reset.reset_password_mode'));
    }
}
