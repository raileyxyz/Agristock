<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Supplier;

class SupplierService
{
    public function getSuppliers(array $filters)
    {
        return Supplier::query()
            ->with('categories')
            ->search($filters['search'] ?? null)
            ->filterStatus($filters['status'] ?? Status::ACTIVE->value)
            ->filterCategory($filters['category_id'] ?? null)
            ->latest()
            ->paginate(15)
            ->withQueryString();
    }

    public function getDirectory()
    {
        return Supplier::with('categories')
            ->orderByRaw(sprintf("FIELD(status, '%s', '%s')", Status::ACTIVE->value, Status::ARCHIVED->value))
            ->orderBy('company_name')
            ->paginate(15)
            ->withQueryString();
    }

    public function getStatistics(): array
    {
        return [
            'total' => Supplier::count(),
            'active' => Supplier::where('status', Status::ACTIVE->value)->count(),
            'archived' => Supplier::where('status', Status::ARCHIVED->value)->count(),
        ];
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
        $supplier->update(['status' => Status::ARCHIVED]);
    }

    public function addSupplyCategoryIfMissing(int $supplierId, int $categoryId): void
    {
        Supplier::find($supplierId)?->categories()->syncWithoutDetaching([$categoryId]);
    }
}
