<?php

namespace App\Services;

use App\Models\Category;

class CategoryService
{
    public function getCategories($filters)
    {
        return Category::query()
            ->search($filters['search'] ?? null)
            ->filterStatus($filters['status'] ?? null)
            ->withCount(['products' => function ($query) {
                $query->where('status', 'Active');
            }])
            ->paginate(12)
            ->withQueryString();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }


    public function update(Category $category, array $data)
    {
        $isArchivingNow = ($data['status'] ?? $category->status) === 'Archived'
            && $category->status !== 'Archived';

        if ($isArchivingNow) {
            $activeProductsCount = $category->products()->where('status', 'Active')->count();

            if ($activeProductsCount > 0) {
                throw new \Exception(
                    "Cannot archive \"{$category->name}\" — it still has {$activeProductsCount} active " .
                    ($activeProductsCount === 1 ? 'product' : 'products') . '.'
                );
            }
        }

        return $category->update($data);
    }

    public function archive(Category $category)
    {
        $activeProductsCount = $category->products()->where('status', 'Active')->count();

        if ($activeProductsCount > 0) {
            throw new \Exception(
                "Cannot archive \"{$category->name}\" — it still has {$activeProductsCount} active " .
                ($activeProductsCount === 1 ? 'product' : 'products') . '.'
            );
        }

        return $category->update([
            'status' => 'Archived'
        ]);
    }

}
