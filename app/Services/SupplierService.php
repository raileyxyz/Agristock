<?php

namespace App\Services;

use App\Models\Supplier;

class SupplierService
{
    public function getSuppliers(array $filters)
    {
        return Supplier::query()
            ->with('categories')
            ->search($filters['search'] ?? null)
            ->filterStatus($filters['status'] ?? 'Active')
            ->filterCategory($filters['category_id'] ?? null)
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data): Supplier
    {
        $categoryIds = $data['supply_categories'] ?? [];
        unset($data['supply_categories']);

        $supplier = Supplier::create($data);
        $supplier->categories()->sync($categoryIds);

        return $supplier;
    }

    public function update(Supplier $supplier, array $data): Supplier
    {
        $categoryIds = $data['supply_categories'] ?? [];
        unset($data['supply_categories']);

        $supplier->update($data);
        $supplier->categories()->sync($categoryIds);

        return $supplier;
    }

    public function archive(Supplier $supplier): void
    {
        $supplier->update(['status' => 'Archived']);
    }
}
