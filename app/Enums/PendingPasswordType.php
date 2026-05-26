<?php

namespace App\Enums;

enum PendingPasswordType: string
{
    case TemporaryGenerated = 'temporary_generated';
    case StudentSelected = 'student_selected';
}
