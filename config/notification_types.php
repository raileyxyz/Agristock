<?php

use App\Enums\UserRole;

return [

    // Inventory Alerts
    'low_stock' => [
        'label' => 'Low Stock Alert',
        'description' => 'Receive an alert when a product reaches its reorder level.',
        'category' => 'Inventory Alerts',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER, UserRole::STAFF],
        'default_enabled' => true,
    ],
    'critical_stock' => [
        'label' => 'Critical Stock Alert',
        'description' => 'Notify when stock reaches critical levels or runs out.',
        'category' => 'Inventory Alerts',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER, UserRole::STAFF],
        'default_enabled' => true,
    ],
    'expiring_soon' => [
        'label' => 'Expiring Soon',
        'description' => 'Notify 60 days before a product batch expires.',
        'category' => 'Inventory Alerts',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER, UserRole::STAFF],
        'default_enabled' => true,
    ],
    'expired_products' => [
        'label' => 'Expired Products',
        'description' => 'Notify when a product batch expires.',
        'category' => 'Inventory Alerts',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER, UserRole::STAFF],
        'default_enabled' => true,
    ],

    // Stock Movement
    'stock_received' => [
        'label' => 'Stock Received',
        'description' => 'Notify when new stock is recorded into inventory.',
        'category' => 'Stock Movement',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER, UserRole::STAFF],
        'default_enabled' => true,
    ],
    'stock_out' => [
        'label' => 'Stock Out Recorded',
        'description' => 'Notify when stock is removed from inventory.',
        'category' => 'Stock Movement',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER, UserRole::STAFF],
        'default_enabled' => true,
    ],
    'stock_transfer' => [
        'label' => 'Stock Transfer Completed',
        'description' => 'Notify when stock is transferred between locations.',
        'category' => 'Stock Movement',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER, UserRole::STAFF],
        'default_enabled' => true,
    ],
    'stock_adjusted' => [
        'label' => 'Stock Adjustment Made',
        'description' => 'Notify when a stock count is manually adjusted.',
        'category' => 'Stock Movement',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER],
        'default_enabled' => true,
    ],

    // User & Security
    'user_added' => [
        'label' => 'New User Added',
        'description' => 'Notify when a new user account is created.',
        'category' => 'User & Security',
        'roles' => [UserRole::ADMIN],
        'default_enabled' => true,
    ],
    'user_archived' => [
        'label' => 'User Account Archived',
        'description' => 'Notify when a user account is archived.',
        'category' => 'User & Security',
        'roles' => [UserRole::ADMIN],
        'default_enabled' => true,
    ],
    'user_role_changed' => [
        'label' => 'User Role Changed',
        'description' => "Notify when a user's role is changed.",
        'category' => 'User & Security',
        'roles' => [UserRole::ADMIN],
        'default_enabled' => true,
    ],

    // Reports
    'weekly_report' => [
        'label' => 'Weekly Report Ready',
        'description' => 'Notify when weekly summary reports are available.',
        'category' => 'Reports',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER],
        'default_enabled' => true,
    ],
    'monthly_report' => [
        'label' => 'Monthly Inventory Report Ready',
        'description' => 'Notify when the monthly inventory report is available.',
        'category' => 'Reports',
        'roles' => [UserRole::ADMIN, UserRole::MANAGER],
        'default_enabled' => false,
    ],

];
