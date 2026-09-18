<?php

namespace App\Enums;

enum Status: string
{
    case ACTIVE = 'Active';
    case ARCHIVED = 'Archived';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
