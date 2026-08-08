<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\StockOut;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockOutService
{
    public function __construct(
        private StockDeductionService $stockDeduction
    ) {}

    public function getAvailableStock(int $productId, string $location): float
    {
        return (float) Inventory::query()
            ->where('product_id', $productId)
            ->where('location', $location)
            ->sum('remaining_quantity');
    }

    public function create(array $data): StockOut
    {
        return DB::transaction(function () use ($data) {
            $product = Product::findOrFail($data['product_id']);

            $this->stockDeduction->deduct($product, $data['location'], (float) $data['quantity']);

            return StockOut::create([
                'user_id' => Auth::id(),
                'product_id' => $product->id,
                'location' => $data['location'],
                'quantity' => $data['quantity'],
                'reason' => $data['reason'],
                'transfer_to' => $data['transfer_to'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);
        });
    }
}
