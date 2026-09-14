@props(['type'])

@php
    $iconMap = [
        'low_stock' => ['icon' => 'triangle-alert', 'bg' => 'bg-amber-50', 'text' => 'text-amber-600'],
        'critical_stock' => ['icon' => 'octagon-alert', 'bg' => 'bg-red-50', 'text' => 'text-red-600'],
        'expiring_soon' => ['icon' => 'clock', 'bg' => 'bg-orange-50', 'text' => 'text-orange-600'],
        'expired_products' => ['icon' => 'calendar-x', 'bg' => 'bg-red-50', 'text' => 'text-red-600'],
        'stock_received' => ['icon' => 'arrow-down-to-line', 'bg' => 'bg-green-50', 'text' => 'text-green-600'],
        'stock_out' => ['icon' => 'arrow-up-from-line', 'bg' => 'bg-orange-50', 'text' => 'text-orange-600'],
        'stock_transfer' => ['icon' => 'shuffle', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
        'stock_adjusted' => ['icon' => 'sliders-horizontal', 'bg' => 'bg-purple-50', 'text' => 'text-purple-600'],
        'user_added' => ['icon' => 'user-plus', 'bg' => 'bg-blue-50', 'text' => 'text-blue-600'],
        'user_archived' => ['icon' => 'user-x', 'bg' => 'bg-gray-100', 'text' => 'text-gray-500'],
        'user_role_changed' => ['icon' => 'shield-check', 'bg' => 'bg-purple-50', 'text' => 'text-purple-600'],
        'weekly_report' => ['icon' => 'calendar-check', 'bg' => 'bg-green-50', 'text' => 'text-green-600'],
        'monthly_report' => ['icon' => 'bar-chart-3', 'bg' => 'bg-green-50', 'text' => 'text-green-600'],
    ];

    $config = $iconMap[$type] ?? ['icon' => 'bell', 'bg' => 'bg-gray-100', 'text' => 'text-gray-500'];
@endphp

<div class="w-9 h-9 rounded-full {{ $config['bg'] }} {{ $config['text'] }} flex items-center justify-center shrink-0">
    <i data-lucide="{{ $config['icon'] }}" class="w-4 h-4"></i>
</div>
