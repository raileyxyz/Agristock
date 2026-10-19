<?php

namespace App\Services;

use App\Models\Inventory;

class BatchNumberGeneratorService
{
    public function generate(): string
    {
        $prefix = 'BT-' . now()->format('Y') . '-';

        $last = Inventory::where('batch_number', 'like', $prefix . '%')
            ->orderByRaw('LENGTH(batch_number) DESC')
            ->orderByDesc('batch_number')
            ->value('batch_number');

        $next = $last ? ((int) substr($last, strlen($prefix))) + 1 : 1;

        return $prefix . str_pad($next, 3, '0', STR_PAD_LEFT);
    }
}
