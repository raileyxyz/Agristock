<?php

namespace App\Services;

use App\Models\Inventory;

class BatchNumberGeneratorService
{
    public function generate(): string
    {
        $today = now()->format('Ymd');
        $countToday = Inventory::whereDate('created_at', now())->count();
        $nextNumber = $countToday + 1;

        return 'BT-' . $today . '-' . str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
    }
}
