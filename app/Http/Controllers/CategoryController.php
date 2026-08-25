<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use App\Services\CategoryService;
use App\Http\Requests\StoreCategoryRequest;
use App\Http\Requests\UpdateCategoryRequest;


class CategoryController extends Controller
{
    public function __construct(
        private CategoryService $categoryService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('products.view');

        $categories = $this->categoryService->getCategories($request->all());

        return view('categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->authorize('products.create');

        $this->categoryService->create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $this->authorize('products.update');

        try {
            $this->categoryService->update($category, $request->validated());

            return redirect()->route('categories.index')->with('success', "{$category->name} updated successfully.");

        } catch (\Exception $e) {
            return redirect()->route('categories.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        $this->authorize('products.delete');

        try {
            $this->categoryService->archive($category);

            return redirect()->route('categories.index')->with('success', "{$category->name} archived successfully.");

        } catch (\Exception $e) {
            return redirect()->route('categories.index')->with('error', $e->getMessage());
        }
    }

}
