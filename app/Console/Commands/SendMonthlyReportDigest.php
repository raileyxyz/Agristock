<?php

namespace App\Console\Commands;

use App\Services\DashboardService;
use App\Services\NotificationDispatchService;
use Illuminate\Console\Command;

class SendMonthlyReportDigest extends Command
{
    protected $signature = 'reports:monthly-digest';

    protected $description = 'Send a monthly summary notification (Monthly Inventory Report Ready)';

    public function handle(DashboardService $dashboardService, NotificationDispatchService $dispatchService): int
    {
        $summary = $dashboardService->getSummaryCards();

        $body = sprintf(
            'Inventory value: ₱%s across %d products (%d categories). %d low stock, %d expiring soon, %d expired.',
            number_format($summary['monthly_inventory_value'], 0),
            $summary['total_products'],
            $summary['total_categories'],
            $summary['low_stock_count'],
            $summary['expiring_soon_count'],
            $summary['expired_count']
        );

        $dispatchService->sendToEligibleUsers(
            'monthly_report',
            'Monthly Inventory Report Ready',
            $body,
            route('dashboard'),
            null
        );

        $this->info('Monthly report digest sent.');

        return self::SUCCESS;
    }
}
