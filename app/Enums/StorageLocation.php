<?php

namespace App\Enums;

enum StorageLocation: string
{
    case MAIN_WAREHOUSE = 'Main Warehouse';
    case STORAGE_ROOM_A = 'Storage Room A';
    case STORAGE_ROOM_B = 'Storage Room B';
    case FIELD_STORAGE = 'Field Storage';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
