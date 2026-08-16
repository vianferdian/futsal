<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case SUPERVISOR = 'supervisor';
    case TEAM_ADMIN = 'team_admin';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::SUPERVISOR => 'Pengawas Pertandingan',
            self::TEAM_ADMIN => 'Admin Tim',
        };
    }
}
