<?php

namespace App\Services;

use App\Models\Inventory;

class BatchNumberGeneratorService
{
    public function generate(): string
    {
        $year = now()->format('Y');
        $countThisYear = Inventory::whereYear('created_at', now()->year)->count();

        return 'BT-' . $year . '-' . str_pad($countThisYear + 1, 3, '0', STR_PAD_LEFT);
    }
}
