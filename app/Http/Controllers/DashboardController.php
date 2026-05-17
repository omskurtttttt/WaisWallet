<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $user = $request->user();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. Total Balance (All Income - All Expenses)
        // Since amounts are in cents, we keep them as integers
        $totalIncome = $user->transactions()->where('type', 'income')->sum('amount');
        $totalExpenses = $user->transactions()->where('type', 'expense')->sum('amount');
        $totalBalance = $totalIncome - $totalExpenses;

        // 2. Monthly Spending (Expenses for current month)
        $monthlySpending = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Previous month spending for trend comparison
        $previousMonthStart = $now->copy()->subMonth()->startOfMonth();
        $previousMonthEnd = $now->copy()->subMonth()->endOfMonth();
        $previousMonthSpending = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$previousMonthStart, $previousMonthEnd])
            ->sum('amount');
            
        $spendingTrend = 0;
        if ($previousMonthSpending > 0) {
            $spendingTrend = round((($monthlySpending - $previousMonthSpending) / $previousMonthSpending) * 100, 1);
        }

        // 3. Total Savings (Sum of all current amounts in goals)
        $totalSavings = $user->goals()->sum('current_amount');

        // 4. Budget Utilization
        // Total allocated for current month
        $totalBudgetAllocated = $user->budgets()
            ->whereDate('month', $startOfMonth->format('Y-m-d'))
            ->sum('allocated_amount');
            
        $budgetUsedPercentage = 0;
        if ($totalBudgetAllocated > 0) {
            $budgetUsedPercentage = min(100, round(($monthlySpending / $totalBudgetAllocated) * 100));
        }

        return Inertia::render('Dashboard', [
            'metrics' => [
                'totalBalance' => $totalBalance,
                'monthlySpending' => $monthlySpending,
                'spendingTrend' => $spendingTrend,
                'totalSavings' => $totalSavings,
                'budgetUsedPercentage' => $budgetUsedPercentage,
                'totalBudgetAllocated' => $totalBudgetAllocated,
            ],
            'currency' => $user->currency,
        ]);
    }
}
