<?php

namespace App\Enums;

enum StudentPhotoType: string
{
    case Registration = 'registration';
    case ResetRequest = 'reset_request';
}
