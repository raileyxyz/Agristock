<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Products\CategoryController;
use App\Http\Controllers\Chatbot\ChatbotController;
use App\Http\Controllers\Dashboard\DashboardController;
use App\Http\Controllers\Products\UnitController;
use App\Http\Controllers\Products\ProductController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Inventory\InventoryHistoryController;
use App\Http\Controllers\Inventory\LowStockController;
use App\Http\Controllers\Inventory\StockOutController;
use App\Http\Controllers\Inventory\StockAdjustmentController;
use App\Http\Controllers\Suppliers\SupplierController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Notifications\NotificationPreferenceController;
use App\Http\Controllers\Notifications\NotificationController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('landing');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/avatar', [ProfileController::class, 'updateAvatar'])->name('profile.avatar');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Product Management
    Route::resource('products', ProductController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy'])
        ->middlewareFor('index', 'can:products.view')
        ->middlewareFor(['create', 'store'], 'can:products.create')
        ->middlewareFor('update', 'can:products.update')
        ->middlewareFor('destroy', 'can:products.delete');

    Route::resource('categories', CategoryController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middlewareFor('index', 'can:products.view')
        ->middlewareFor('store', 'can:products.create')
        ->middlewareFor('update', 'can:products.update')
        ->middlewareFor('destroy', 'can:products.delete');

    Route::resource('units', UnitController::class)
        ->only(['index', 'store', 'update', 'destroy'])
        ->middlewareFor('index', 'can:products.view')
        ->middlewareFor('store', 'can:products.create')
        ->middlewareFor('update', 'can:products.update')
        ->middlewareFor('destroy', 'can:products.delete');

    // Inventory Management
    Route::middleware('can:inventory.view')->group(function () {
        Route::get('/inventory-history', [InventoryHistoryController::class, 'index'])->name('inventory-history.index');
        Route::get('/low-stock', [LowStockController::class, 'index'])->name('low-stock.index');
    });

    Route::resource('inventories', InventoryController::class)
        ->only(['index', 'create', 'store', 'update'])
        ->middlewareFor('index', 'can:inventory.view')
        ->middlewareFor(['create', 'store'], 'can:inventory.stock-in')
        ->middlewareFor('update', 'can:inventory.manage');

    Route::resource('stock-outs', StockOutController::class)
        ->only(['create', 'store'])
        ->middleware('can:inventory.stock-out');

    Route::resource('stock-adjustments', StockAdjustmentController::class)
        ->only(['create', 'store'])
        ->middleware('can:inventory.manage');

    // Suppliers
    Route::get('/suppliers-directory', [SupplierController::class, 'directory'])
        ->middleware('can:suppliers.view')
        ->name('suppliers.directory');

    Route::resource('suppliers', SupplierController::class)
        ->only(['index', 'create', 'store', 'update', 'destroy'])
        ->middlewareFor('index', 'can:suppliers.view')
        ->middlewareFor(['create', 'store'], 'can:suppliers.create')
        ->middlewareFor('update', 'can:suppliers.update')
        ->middlewareFor('destroy', 'can:suppliers.delete');

    Route::resource('users', UserController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    Route::get('/users-roles-permissions', [UserController::class, 'rolesPermissions'])
        ->name('users.roles');

    Route::middleware('can:reports.stock')->group(function () {
        Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    });

    Route::middleware('can:reports.movement')->group(function () {
        Route::get('/reports/movement', [ReportController::class, 'movement'])->name('reports.movement');
    });

    Route::middleware('can:reports.expiry')->group(function () {
        Route::get('/reports/expiry', [ReportController::class, 'expiry'])->name('reports.expiry');
    });

    Route::patch('/notification-preferences', [NotificationPreferenceController::class, 'update'])
    ->name('notification-preferences.update');

    Route::patch('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');

    Route::patch('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.read-all');

    Route::prefix('chatbot')->name('chatbot.')->group(function () {
    Route::get('/messages', [ChatbotController::class, 'index'])->name('messages.index');
    Route::post('/messages', [ChatbotController::class, 'store'])->middleware('throttle:6,1')->name('messages.store');
    Route::delete('/messages', [ChatbotController::class, 'destroy'])->name('messages.destroy');
});
});

require __DIR__.'/auth.php';
