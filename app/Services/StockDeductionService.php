<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use RuntimeException;

class StockDeductionService
{
    public function deduct(Product $product, string $location, float $quantity): void
    {
        $batches = Inventory::query()
            ->where('product_id', $product->id)
            ->where('location', $location)
            ->where('remaining_quantity', '>', 0)
            ->when(
                $product->expiry_track,
                fn ($query) => $query->orderBy('expiry_date'),
                fn ($query) => $query->orderBy('created_at')
            )
            ->lockForUpdate()
            ->get();

        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deductAmount = min((float) $batch->remaining_quantity, $remaining);

            $batch->decrement('remaining_quantity', $deductAmount);

            $remaining -= $deductAmount;
        }

        if ($remaining > 0) {
            throw new RuntimeException(
                'Insufficient stock available to complete this deduction.'
            );
        }
    }
}
