<?php

use App\Enums\UserRole;

return [
    UserRole::ADMIN->value => [
        'Dashboard',
        'Product Management (full)',
        'Inventory Management (full)',
        'Supplier Management (full)',
        'Purchase Orders (full)',
        'Reports (all)',
        'User Management (full)',
    ],
    UserRole::MANAGER->value => [
        'Dashboard',
        'Product Management (view/add/edit)',
        'Inventory Management (full)',
        'Supplier Management (view/add)',
        'Purchase Orders (view/create)',
        'Reports (stock, movement, expiry)',
        'User Management (view only)',
    ],
    UserRole::STAFF->value => [
        'Dashboard',
        'Product Management (view)',
        'Inventory Management (stock in/out)',
        'Supplier Management (view)',
        'Purchase Orders (view)',
        'Reports (stock report)',
    ],
];
