<?php

namespace App\Enums;

enum Status: string
{
    case ACTIVE = 'Active';
    case ARCHIVED = 'Archived';
    case DELETED = 'Deleted';

    public static function values(): array
    {
        return [self::ACTIVE->value, self::ARCHIVED->value];
    }
}
