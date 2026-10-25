<?php

declare(strict_types=1);

namespace App\Modules\RolesPermissions\Domain\Enums;

enum Role: string
{
    case Registered = 'registered';
    case Editor = 'editor';
    case Reviewer = 'reviewer';
    case SeniorEditor = 'senior_editor';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
}
