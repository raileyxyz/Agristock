<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Http\Request;
use App\Services\SupplierService;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;

class SupplierController extends Controller
{
    public function __construct(
        private SupplierService $supplierService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('suppliers.view');

        $suppliers = $this->supplierService->getSuppliers($request->all());
        $statistics = $this->supplierService->getStatistics();
        $categories = Category::where('status', 'Active')->orderBy('name')->get();

        return view('suppliers.index', compact('suppliers', 'categories', 'statistics'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('suppliers.create');

        $categories = Category::where('status', 'Active')->orderBy('name')->get();

        return view('suppliers.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $this->authorize('suppliers.create');

        $this->supplierService->create($request->validated());

        return redirect()->route('suppliers.create')->with('success', 'Supplier added successfully.');
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
    public function edit(Supplier $supplier)
    {
        $this->authorize('suppliers.update');

        $categories = Category::where('status', 'Active')->orderBy('name')->get();
        $supplier->load('categories');

        return view('suppliers.edit', compact('supplier', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier)
    {
        $this->authorize('suppliers.update');

        $this->supplierService->update($supplier, $request->validated());

        return redirect()->route('suppliers.index')->with('success', 'Supplier updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Supplier $supplier)
    {
        $this->authorize('suppliers.delete');

        $this->supplierService->archive($supplier);

        return redirect()->route('suppliers.index')->with('success', 'Supplier archived successfully.');
    }

    public function directory()
    {
        $this->authorize('suppliers.view');

        $suppliers = $this->supplierService->getDirectory();

        return view('suppliers.directory', compact('suppliers'));
    }
}
