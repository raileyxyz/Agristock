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
    ){}



    public function index(Request $request)
    {

        $categories = Category::query()
            ->search($request->search)
            ->filterStatus($request->status)
            ->paginate(12)
            ->withQueryString();


        return view(
            'categories.index',
            compact('categories')
        );

    }



    public function store(StoreCategoryRequest $request)
    {

        $this->categoryService
            ->create($request->validated());


        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                'Category added successfully.'
            );

    }



    public function update(
        UpdateCategoryRequest $request,
        Category $category
    )
    {

        $this->categoryService
            ->update(
                $category,
                $request->validated()
            );


        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                "{$category->name} updated successfully."
            );

    }



    public function destroy(Category $category)
    {

        $this->categoryService
            ->archive($category);


        return redirect()
            ->route('categories.index')
            ->with(
                'success',
                "{$category->name} archived successfully."
            );

    }

}
