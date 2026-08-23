<?php

use App\Models\User;

return [
    User::ROLE_ADMIN => [
        'Dashboard',
        'Product Management (full)',
        'Inventory Management (full)',
        'Supplier Management (full)',
        'Purchase Orders (full)',
        'Reports (all)',
        'User Management (full)',
    ],
    User::ROLE_MANAGER => [
        'Dashboard',
        'Product Management (view/add/edit)',
        'Inventory Management (full)',
        'Supplier Management (view/add)',
        'Purchase Orders (view/create)',
        'Reports (stock, movement, expiry)',
        'User Management (view only)',
    ],
    User::ROLE_STAFF => [
        'Dashboard',
        'Product Management (view)',
        'Inventory Management (stock in/out)',
        'Supplier Management (view)',
        'Purchase Orders (view)',
        'Reports (stock report)',
    ],
];
