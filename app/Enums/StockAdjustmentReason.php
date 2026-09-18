<?php

namespace App\Enums;

enum StockAdjustmentReason: string
{
    case PHYSICAL_COUNT = 'Physical Count';
    case DAMAGED_GOODS = 'Damaged Goods';
    case THEFT_LOSS = 'Theft/Loss';
    case EXPIRED_REMOVAL = 'Expired Removal';
    case DATA_ENTRY_ERROR = 'Data Entry Error';
    case OTHER = 'Other';

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
