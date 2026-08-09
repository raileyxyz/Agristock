<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;

class StockDeductionService
{
    /**
     * Deducts stock and returns the list of batches consumed,
     * so callers (e.g. Transfer) can mirror the movement elsewhere.
     */
    public function deduct(Product $product, string $location, float $quantity): array
    {
        $batches = Inventory::where('product_id', $product->id)
            ->where('location', $location)
            ->where('remaining_quantity', '>', 0)
            ->when($product->expiry_track,
                fn($q) => $q->orderBy('expiry_date', 'asc'),
                fn($q) => $q->orderBy('created_at', 'asc')
            )
            ->get();

        $remaining = $quantity;
        $consumed = [];

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deductFromThisBatch = min($batch->remaining_quantity, $remaining);

            $batch->remaining_quantity -= $deductFromThisBatch;
            $batch->save();

            $consumed[] = [
                'batch_number' => $batch->batch_number,
                'quantity' => $deductFromThisBatch,
                'expiry_date' => $batch->expiry_date,
            ];

            $remaining -= $deductFromThisBatch;
        }

        if ($remaining > 0) {
            throw new \Exception('Insufficient stock available to complete this deduction.');
        }

        return $consumed;
    }
}
