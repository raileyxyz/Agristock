<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function __construct(
        protected ReportService $reportService
    ) {}

    public function stock(): View
    {
        $this->authorize('reports.stock');

        $summary = $this->reportService->getStockSummary();
        $categoryData = $this->reportService->getStockByCategory();

        return view('reports.stock', compact('summary', 'categoryData'));
    }

    public function movement(Request $request): View
    {
        $this->authorize('reports.movement');

        $range = $request->query('range', 'all');
        $customStart = $request->query('start_date');
        $customEnd = $request->query('end_date');

        [$startDate, $endDate] = $this->resolveDateRange($range, $customStart, $customEnd);

        $summary = $this->reportService->getMovementSummary($startDate, $endDate);
        $productData = $this->reportService->getMovementByProduct($startDate, $endDate);

        return view('reports.movement', [
            'summary' => $summary,
            'productData' => $productData,
            'range' => $range,
            'customStart' => $customStart,
            'customEnd' => $customEnd,
        ]);
    }

    /**
     * Resolve a named range (or custom dates) into a [start, end] Carbon pair.
     * Either value may be null, meaning "no lower/upper bound".
     */
    private function resolveDateRange(string $range, ?string $customStart, ?string $customEnd): array
    {
        return match ($range) {
            '7d' => [now()->subDays(7)->startOfDay(), now()->endOfDay()],
            '30d' => [now()->subDays(30)->startOfDay(), now()->endOfDay()],
            '1y' => [now()->subYear()->startOfDay(), now()->endOfDay()],
            'custom' => [
                $customStart ? Carbon::parse($customStart)->startOfDay() : null,
                $customEnd ? Carbon::parse($customEnd)->endOfDay() : null,
            ],
            default => [null, null],
        };
    }

    public function expiry(): View
    {
        $this->authorize('reports.expiry');

        $summary = $this->reportService->getExpirySummary();
        $batches = $this->reportService->getExpiryBatches();

        return view('reports.expiry', compact('summary', 'batches'));
    }
}
