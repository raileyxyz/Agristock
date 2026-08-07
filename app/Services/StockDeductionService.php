<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;

class StockDeductionService
{
    public function deduct(Product $product, string $location, float $quantity): void
    {
        $batches = Inventory::where('product_id', $product->id)
            ->where('location', $location)
            ->where('remaining_quantity', '>', 0)
            ->when($product->expiry_track, function ($query) {
                $query->orderBy('expiry_date', 'asc');
            }, function ($query) {
                $query->orderBy('created_at', 'asc');
            })
            ->lockForUpdate()
            ->get();

        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deductFromThisBatch = min($batch->remaining_quantity, $remaining);

            $batch->remaining_quantity -= $deductFromThisBatch;
            $batch->save();

            $remaining -= $deductFromThisBatch;
        }

        if ($remaining > 0) {
            throw new \Exception('Insufficient stock available to complete this deduction.');
        }
    }
}
