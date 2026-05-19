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

        // 5. Income Chart Data (Last 6 Months)
        $incomeChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
            
            $monthIncome = $user->transactions()
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
                
            $incomeChartData[] = [
                'month' => $monthStart->format('M Y'),
                'amount' => $monthIncome / 100, // Pass to frontend in standard currency format for chart
            ];
        }

        // 6. Budget Donut Chart Data (Expenses by Category for current month)
        $budgetChartData = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('category_id, sum(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name,color')
            ->orderByDesc('total')
            ->take(5) // Top 5 categories
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Uncategorized',
                    'color' => $item->category->color ?? '#9CA3AF',
                    'amount' => $item->total / 100,
                ];
            });

        // 7. Spending Limits Data (Budgets for current month)
        $spendingLimitsData = $user->budgets()
            ->whereDate('month', $startOfMonth->format('Y-m-d'))
            ->with('category:id,name,color')
            ->get()
            ->map(function ($budget) use ($user, $startOfMonth, $endOfMonth) {
                $spent = $user->transactions()
                    ->where('type', 'expense')
                    ->where('category_id', $budget->category_id)
                    ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
                    ->sum('amount');
                
                $allocated = $budget->allocated_amount;
                $percentage = $allocated > 0 ? min(100, round(($spent / $allocated) * 100)) : 0;
                
                return [
                    'id' => $budget->id,
                    'name' => $budget->category->name ?? 'General Budget',
                    'color' => $budget->category->color ?? '#7033ff',
                    'spent' => $spent, // keeping in cents for formatMoney utility
                    'allocated' => $allocated, // keeping in cents
                    'percentage' => $percentage,
                ];
            })
            ->sortByDesc('percentage')
            ->values();

        // If no budgets are defined, provide the overall monthly spending limit
        if ($spendingLimitsData->isEmpty()) {
            $spendingLimitsData->push([
                'id' => 'overall',
                'name' => 'Overall Monthly Spending',
                'color' => '#7033ff',
                'spent' => $monthlySpending,
                'allocated' => $totalBudgetAllocated > 0 ? $totalBudgetAllocated : max($monthlySpending, 100000), // Default 1000 if none
                'percentage' => $budgetUsedPercentage,
            ]);
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
            'incomeChartData' => $incomeChartData,
            'budgetChartData' => $budgetChartData,
            'spendingLimitsData' => $spendingLimitsData,
            'currency' => $user->currency,
        ]);
    }
}
