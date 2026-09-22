<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
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
        $categories = $this->categoryService->getCategories($request->all());

        return view('categories.index', compact('categories'));
    }

    public function store(StoreCategoryRequest $request)
    {
        $this->categoryService->create($request->validated());

        return redirect()->route('categories.index')->with('success', 'Category added successfully.');
    }

    public function update(UpdateCategoryRequest $request, Category $category)
    {
        $data = $request->validated();

        if (! Gate::allows('products.delete')) {
            unset($data['status']);
        }

        try {
            $this->categoryService->update($category, $data);

            return redirect()->route('categories.index')->with('success', "{$category->name} updated successfully.");

        } catch (\Exception $e) {
            return redirect()->route('categories.index')->with('error', $e->getMessage());
        }
    }

    public function destroy(Category $category)
    {
        try {
            $this->categoryService->archive($category);

            return redirect()->route('categories.index')->with('success', "{$category->name} archived successfully.");

        } catch (\Exception $e) {
            return redirect()->route('categories.index')->with('error', $e->getMessage());
        }
    }

}
