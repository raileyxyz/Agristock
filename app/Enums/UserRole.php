<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'Admin';
    case MANAGER = 'Manager';
    case STAFF = 'Staff';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
