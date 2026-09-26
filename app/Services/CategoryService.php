<?php

namespace App\Services;

use App\Enums\Status;
use App\Models\Category;

class CategoryService
{
    public function getCategories($filters)
    {
        return Category::query()
            ->search($filters['search'] ?? null)
            ->filterStatus($filters['status'] ?? null)
            ->withCount(['products' => function ($query) {
                $query->where('status', Status::ACTIVE->value);
            }])
            ->paginate(15)
            ->withQueryString();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }


    public function update(Category $category, array $data): array
    {
        try {
            $newStatus = $data['status'] ?? $category->status;
            $newStatus = $newStatus instanceof Status ? $newStatus->value : $newStatus;

            $isArchivingNow = $newStatus === Status::ARCHIVED->value && $category->status !== Status::ARCHIVED;

            if ($isArchivingNow) {
                $activeProductsCount = $category->products()->where('status', Status::ACTIVE->value)->count();

                if ($activeProductsCount > 0) {
                    throw new \Exception(
                        "Cannot archive \"{$category->name}\" — it still has {$activeProductsCount} active " .
                        ($activeProductsCount === 1 ? 'product' : 'products') . '.'
                    );
                }
            }

            $category->update($data);

            return ['success' => true, 'message' => "{$category->name} updated successfully."];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function archive(Category $category): array
    {
        try {
            $activeProductsCount = $category->products()->where('status', Status::ACTIVE->value)->count();

            if ($activeProductsCount > 0) {
                throw new \Exception(
                    "Cannot archive \"{$category->name}\" — it still has {$activeProductsCount} active " .
                    ($activeProductsCount === 1 ? 'product' : 'products') . '.'
                );
            }

            $category->update([
                'status' => Status::ARCHIVED,
            ]);

            return ['success' => true, 'message' => "{$category->name} archived successfully."];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

}
