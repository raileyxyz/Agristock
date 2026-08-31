<?php

use App\Enums\UserRole;

return [

    // Product Management
    'products.view'   => [UserRole::MANAGER, UserRole::STAFF],
    'products.create' => [UserRole::MANAGER],
    'products.update' => [UserRole::MANAGER],
    'products.delete' => [],

    // Inventory Management
    'inventory.view'      => [UserRole::MANAGER, UserRole::STAFF],
    'inventory.stock-in'  => [UserRole::MANAGER, UserRole::STAFF],
    'inventory.stock-out' => [UserRole::MANAGER, UserRole::STAFF],
    'inventory.manage'    => [UserRole::MANAGER],

    // Supplier Management
    'suppliers.view'   => [UserRole::MANAGER, UserRole::STAFF],
    'suppliers.create' => [UserRole::MANAGER],
    'suppliers.update' => [],
    'suppliers.delete' => [],

    // Purchase Orders
    'purchase-orders.view'   => [UserRole::MANAGER, UserRole::STAFF],
    'purchase-orders.create' => [UserRole::MANAGER],
    'purchase-orders.update' => [],
    'purchase-orders.delete' => [],

    // Reports
    'reports.stock'    => [UserRole::MANAGER, UserRole::STAFF],
    'reports.movement' => [UserRole::MANAGER],
    'reports.expiry'   => [UserRole::MANAGER],

];
