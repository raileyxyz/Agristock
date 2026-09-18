<?php

namespace App\Enums;

enum StockOutReason: string
{
    case SALE = 'Sale';
    case DAMAGED = 'Damaged';
    case EXPIRED = 'Expired';
    case TRANSFER = 'Transfer';
    case RETURN_TO_SUPPLIER = 'Return to Supplier';
    case OTHER = 'Other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
