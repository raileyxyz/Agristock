<?php

namespace App\Console\Commands;

use App\Services\NotificationDispatchService;
use App\Services\ReportService;
use Illuminate\Console\Command;

class SendWeeklyReportDigest extends Command
{
    protected $signature = 'reports:weekly-digest';

    protected $description = 'Send a weekly summary notification (Weekly Report Ready)';

    public function handle(ReportService $reportService, NotificationDispatchService $dispatchService): int
    {
        $movement = $reportService->getMovementSummary(now()->subDays(7), now());
        $stock = $reportService->getStockSummary();
        $expiry = $reportService->getExpirySummary();

        $body = sprintf(
            'This week: %s received, %s consumed, %d adjustments. %d low/critical stock, %d expiring soon.',
            number_format($movement['total_received'], 0),
            number_format($movement['total_consumed'], 0),
            $movement['adjustments_count'],
            $stock['low_critical'],
            $expiry['within_30'] + $expiry['within_60']
        );

        $dispatchService->sendToEligibleUsers(
            'weekly_report',
            'Weekly Report Ready',
            $body,
            route('reports.stock'),
            null
        );

        $this->info('Weekly report digest sent.');

        return self::SUCCESS;
    }
}
