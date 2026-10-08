@props(['type'])

@php
    $iconMap = [
        'low_stock'         => ['icon' => 'triangle-alert',     'class' => 'bg-amber-50 text-amber-600 dark:bg-amber-950/50 dark:text-amber-400'],
        'critical_stock'    => ['icon' => 'octagon-alert',      'class' => 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400'],
        'expiring_soon'     => ['icon' => 'clock',              'class' => 'bg-orange-50 text-orange-600 dark:bg-amber-950/40 dark:text-orange-400'],
        'expired_products'  => ['icon' => 'calendar-x',         'class' => 'bg-red-50 text-red-600 dark:bg-red-950/50 dark:text-red-400'],
        'stock_received'    => ['icon' => 'arrow-down-to-line', 'class' => 'bg-green-50 text-green-600 dark:bg-green-950/40 dark:text-green-400'],
        'stock_out'         => ['icon' => 'arrow-up-from-line', 'class' => 'bg-orange-50 text-orange-600 dark:bg-red-950/40 dark:text-red-400'],
        'stock_transfer'    => ['icon' => 'shuffle',            'class' => 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400'],
        'stock_adjusted'    => ['icon' => 'sliders-horizontal', 'class' => 'bg-purple-50 text-orange-600 dark:bg-orange-950/50 dark:text-orange-400'],
        'user_added'        => ['icon' => 'user-plus',          'class' => 'bg-blue-50 text-blue-600 dark:bg-blue-950/50 dark:text-blue-400'],
        'user_archived'     => ['icon' => 'user-x',             'class' => 'bg-gray-100 text-gray-500 dark:bg-slate-800/60 dark:text-slate-400'],
        'user_role_changed' => ['icon' => 'shield-check',       'class' => 'bg-purple-50 text-purple-600 dark:bg-purple-950/50 dark:text-purple-400'],
        'weekly_report'     => ['icon' => 'calendar-check',     'class' => 'bg-green-50 text-green-600 dark:bg-green-950/40 dark:text-green-400'],
        'monthly_report'    => ['icon' => 'bar-chart-3',        'class' => 'bg-green-50 text-green-600 dark:bg-green-950/40 dark:text-green-400'],
    ];

    $config = $iconMap[$type] ?? ['icon' => 'bell', 'class' => 'bg-gray-100 text-gray-500 dark:bg-slate-800/60 dark:text-slate-400'];
@endphp

<div class="w-9 h-9 rounded-full {{ $config['class'] }} flex items-center justify-center shrink-0">
    <i data-lucide="{{ $config['icon'] }}" class="w-4 h-4"></i>
</div>
