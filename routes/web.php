<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\InventoryController;
use App\Http\Controllers\StockOutController;
use App\Http\Controllers\StockAdjustmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/admin-test', function () {
    return "Welcome Admin";
})->middleware('admin');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::resource('categories', CategoryController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::resource('units', UnitController::class)
    ->only(['index', 'store', 'update', 'destroy']);

Route::resource('products', ProductController::class)
    ->only(['index', 'create', 'store', 'update', 'destroy']);

Route::resource('inventories', InventoryController::class)
    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

Route::resource('stock-outs', StockOutController::class)->only(['create', 'store']);

Route::resource('stock-adjustments', StockAdjustmentController::class)->only(['index', 'create', 'store']);

require __DIR__.'/auth.php';
