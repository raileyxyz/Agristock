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
            ->latest()
            ->paginate(10)
            ->withQueryString();
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
        $inventory->update($data);

        return $inventory;
    }

    public function archive(Inventory $inventory): void
    {
        $inventory->update(['status' => 'Archived']);
    }
}
