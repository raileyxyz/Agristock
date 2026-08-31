<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Support\Facades\Auth;

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
            ->filterLocation($filters['location'] ?? null)
            ->filterSupplier($filters['supplier_id'] ?? null)
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

    public function getActiveProducts()
    {
        return Product::active()->with('unit')->orderBy('name')->get();
    }

    public function getActiveSuppliers()
    {
        return Supplier::active()->orderBy('company_name')->get();
    }

    public function create(array $data): Inventory
    {
        if (empty($data['batch_number'])) {
            $data['batch_number'] = $this->batchNumberGenerator->generate();
        }

        $data['remaining_quantity'] = $data['quantity'];
        $data['user_id'] = Auth::id();

        return Inventory::create($data);
    }

    public function update(Inventory $inventory, array $data): Inventory
    {
        unset($data['quantity'], $data['remaining_quantity']);

        if ($inventory->has_movement) {
            unset($data['product_id'], $data['location']);
        }

        if (empty($data['batch_number'])) {
            $data['batch_number'] = $this->batchNumberGenerator->generate();
        }

        $inventory->update($data);

        return $inventory;
    }

    public function archive(Inventory $inventory): void
    {
        $inventory->update(['status' => 'Archived']);
    }

}
