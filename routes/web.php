<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\InventoryHistoryController;
use App\Http\Controllers\LowStockController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockAdjustmentController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Product Management
    Route::middleware('can:products.view')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('/units', [UnitController::class, 'index'])->name('units.index');
    });
    Route::middleware('can:products.create')->group(function () {
        Route::get('/products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::post('/units', [UnitController::class, 'store'])->name('units.store');
    });
    Route::middleware('can:products.update')->group(function () {
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::put('/units/{unit}', [UnitController::class, 'update'])->name('units.update');
    });
    Route::middleware('can:products.delete')->group(function () {
        Route::delete('/products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
        Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::delete('/units/{unit}', [UnitController::class, 'destroy'])->name('units.destroy');
    });

    // Inventory Management
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/inventories', [InventoryController::class, 'index'])->name('inventories.index');
        Route::get('/inventory-history', [InventoryHistoryController::class, 'index'])->name('inventory-history.index');
        Route::get('/low-stock', [LowStockController::class, 'index'])->name('low-stock.index');
    });
    Route::middleware('can:inventory.stock-in')->group(function () {
        Route::get('/inventories/create', [InventoryController::class, 'create'])->name('inventories.create');
        Route::post('/inventories', [InventoryController::class, 'store'])->name('inventories.store');
    });
    Route::middleware('can:inventory.stock-out')->group(function () {
        Route::get('/stock-outs/create', [StockOutController::class, 'create'])->name('stock-outs.create');
        Route::post('/stock-outs', [StockOutController::class, 'store'])->name('stock-outs.store');
    });
    Route::middleware('can:inventory.manage')->group(function () {
        Route::get('/inventories/{inventory}/edit', [InventoryController::class, 'edit'])->name('inventories.edit');
        Route::put('/inventories/{inventory}', [InventoryController::class, 'update'])->name('inventories.update');
        Route::delete('/inventories/{inventory}', [InventoryController::class, 'destroy'])->name('inventories.destroy');
        Route::get('/stock-adjustments/create', [StockAdjustmentController::class, 'create'])->name('stock-adjustments.create');
        Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])->name('stock-adjustments.store');
    });

    // Suppliers
    Route::middleware('can:suppliers.view')->group(function () {
        Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
        Route::get('/suppliers-directory', [SupplierController::class, 'directory'])->name('suppliers.directory');
    });
    Route::middleware('can:suppliers.create')->group(function () {
        Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create');
        Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
    });
    Route::middleware('can:suppliers.update')->group(function () {
        Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit');
        Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
    });
    Route::middleware('can:suppliers.delete')->group(function () {
        Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');
    });

    Route::resource('users', UserController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/users-roles-permissions', [UserController::class, 'rolesPermissions'])
        ->name('users.roles');

    Route::middleware('can:reports.stock')->group(function () {
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    });
});

require __DIR__.'/auth.php';
