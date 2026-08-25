<?php

use App\Models\User;

return [

    // Product Management
    'products.view'   => [User::ROLE_MANAGER, User::ROLE_STAFF],
    'products.create' => [User::ROLE_MANAGER],
    'products.update' => [User::ROLE_MANAGER],
    'products.delete' => [],

    // Inventory Management
    'inventory.view'      => [User::ROLE_MANAGER, User::ROLE_STAFF],
    'inventory.stock-in'  => [User::ROLE_MANAGER, User::ROLE_STAFF],
    'inventory.stock-out' => [User::ROLE_MANAGER, User::ROLE_STAFF],
    'inventory.manage'    => [User::ROLE_MANAGER],

    // Supplier Management
    'suppliers.view'   => [User::ROLE_MANAGER, User::ROLE_STAFF],
    'suppliers.create' => [User::ROLE_MANAGER],
    'suppliers.update' => [],
    'suppliers.delete' => [],

    // Purchase Orders
    'purchase-orders.view'   => [User::ROLE_MANAGER, User::ROLE_STAFF],
    'purchase-orders.create' => [User::ROLE_MANAGER],
    'purchase-orders.update' => [],
    'purchase-orders.delete' => [],

    // Reports
    'reports.stock'    => [User::ROLE_MANAGER, User::ROLE_STAFF],
    'reports.movement' => [User::ROLE_MANAGER],
    'reports.expiry'   => [User::ROLE_MANAGER],

];
