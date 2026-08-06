<?php

namespace App\Services;

use App\Models\Inventory;

class InventoryService
{
    public function __construct(
        private BatchNumberGeneratorService $batchNumberGenerator
    ) {}

    public function getInventories(array $filters)
    {
        return Inventory::query()
            ->with(['product.category', 'product.unit'])
            ->search($filters['search'] ?? null)
            ->filterCategory($filters['category_id'] ?? null)
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function getSummary(): array
    {
        return [
            'total_items' => Inventory::count(),
            'total_locations' => Inventory::distinct('location')->count('location'),
        ];
    }

    public function create(array $data): Inventory
    {
        if (empty($data['batch_number'])) {
            $data['batch_number'] = $this->batchNumberGenerator->generate();
        }

        $data['remaining_quantity'] = $data['quantity'];

        return Inventory::create($data);
    }

    public function update(Inventory $inventory, array $data): Inventory
    {
        $hasMovement = $inventory->quantity != $inventory->remaining_quantity;

        if ($hasMovement && $data['product_id'] != $inventory->product_id) {
            throw new \Exception('Cannot change product — this batch already has stock movements.');
        }

        $inventory->update($data);

        return $inventory;
    }

    public function archive(Inventory $inventory): void
    {
        $inventory->update(['status' => 'Archived']);
    }
}
