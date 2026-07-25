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
            ->paginate(12)
            ->withQueryString();
    }

    public function create(array $data)
    {
        return Category::create($data);
    }


    public function update(Category $category, array $data)
    {
        return $category->update($data);
    }


    public function archive(Category $category)
    {
        return $category->update([
            'status' => 'Archived'
        ]);
    }

}
