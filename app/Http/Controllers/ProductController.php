<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\ProductService;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;


class ProductController extends Controller
{
    public function __construct(
        private ProductService $productService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('products.view');

        $products = $this->productService->getProducts($request->all());

        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $units = Unit::all();
        $statistics = $this->productService->getStatistics();

        return view('products.index', compact('products', 'categories', 'units', 'statistics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('products.create');

        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $units = Unit::orderBy('name')->get();

        return view('products.create', compact('categories', 'units'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $this->authorize('products.create');

        $this->productService->create($request->validated());

        return redirect()->route('products.create')->with('success', 'Product created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('products.update');

        $data = $request->validated();

        if (! Gate::allows('products.delete')) {
            unset($data['status']);
        }

        $this->productService->update($product, $request->validated());

        return redirect()->route('products.index')->with('success', "\"{$product->name}\" has been updated.");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Product $product)
    {
        $this->authorize('products.delete');

        $this->productService->archive($product);

        return redirect()->route('products.index')->with('success', "\"{$product->name}\" has been archived.");
    }
}
