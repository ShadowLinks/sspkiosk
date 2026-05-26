<?php

namespace App\Enums;

enum AuditActorType: string
{
    case Student = 'student';
    case Tech = 'tech';
    case Admin = 'admin';
    case System = 'system';
    case Kiosk = 'kiosk';
}
