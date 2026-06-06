<?php

namespace App\Http\Controllers;

use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, DashboardService $service): Response
    {
        $user = $request->user();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $metrics = $service->getMetrics($now, $startOfMonth, $endOfMonth);
        $incomeChartData = $service->getIncomeChartData($now);
        $budgetChartData = $service->getBudgetChartData($startOfMonth, $endOfMonth);
        $spendingLimitsData = $service->getSpendingLimits(
            $startOfMonth, 
            $metrics['monthlySpending'], 
            $metrics['totalBudgetAllocated'], 
            $metrics['budgetUsedPercentage']
        );
        $recentTransactionsData = $service->getRecentTransactions();
        $myCardsData = $service->getMyCards();

        return Inertia::render('Dashboard', [
            'metrics' => $metrics,
            'incomeChartData' => $incomeChartData,
            'budgetChartData' => $budgetChartData,
            'spendingLimitsData' => $spendingLimitsData,
            'recentTransactionsData' => $recentTransactionsData,
            'myCardsData' => $myCardsData,
            'currency' => $user->currency,
        ]);
    }
}
