<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\User;
use App\Models\StockAdjustment;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function getAdjustments(array $filters)
    {
        return StockAdjustment::query()
            ->with(['inventory.product.category', 'inventory.product.unit', 'user'])
            ->search($filters['search'] ?? null)
            ->filterReason($filters['reason'] ?? null)
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function getStockData(): array
    {
        $inventories = Inventory::query()
            ->where('remaining_quantity', '>', 0)
            ->with('product')
            ->get();

        $stockData = [];

        foreach ($inventories as $inventory) {
            $stockData[$inventory->product_id][$inventory->location][] = [
                'id' => $inventory->id,
                'batch_number' => $inventory->batch_number,
                'remaining_quantity' => (float) $inventory->remaining_quantity,
                'expiry_date' => $inventory->expiry_date?->format('Y-m-d'),
            ];
        }

        return $stockData;
    }

    public function create(array $data, User $actor): array
    {
        try {
            DB::transaction(function () use ($data, $actor) {
                $inventory = Inventory::findOrFail($data['inventory_id']);
                $product = $inventory->product;

                $remainingBefore = $product->inventories()->sum('remaining_quantity');

                $systemQuantity = $inventory->remaining_quantity;

                $inventory->remaining_quantity = $data['actual_quantity'];
                $inventory->save();

                $adjustment = StockAdjustment::create([
                    'inventory_id' => $inventory->id,
                    'user_id' => $actor->id,
                    'system_quantity' => $systemQuantity,
                    'actual_quantity' => $data['actual_quantity'],
                    'reason' => $data['reason'],
                    'notes' => $data['notes'] ?? null,
                ]);

                event(new \App\Events\StockAdjusted($adjustment, $actor));

                $remainingAfter = $product->inventories()->sum('remaining_quantity');
                event(new \App\Events\StockLevelChanged($product->fresh(), $remainingBefore, $remainingAfter, $actor));
            });

            return ['success' => true, 'message' => 'Stock adjustment recorded successfully.'];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
}
