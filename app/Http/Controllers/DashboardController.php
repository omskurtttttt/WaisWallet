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

        // 5. Income Chart Data (Last 6 Months - Optimized into 1 query)
        $sixMonthsAgo = $now->copy()->subMonths(5)->startOfMonth();
        $monthlyIncomes = $user->transactions()
            ->where('type', 'income')
            ->where('transaction_date', '>=', $sixMonthsAgo)
            ->selectRaw("TO_CHAR(transaction_date, 'YYYY-MM') as month_key, sum(amount) as total")
            ->groupBy('month_key')
            ->get()
            ->pluck('total', 'month_key');

        $incomeChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthKey = $monthStart->format('Y-m');
            $monthIncome = $monthlyIncomes->get($monthKey) ?? 0;
                
            $incomeChartData[] = [
                'month' => $monthStart->format('M Y'),
                'amount' => $monthIncome / 100,
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
            ->take(5)
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Uncategorized',
                    'color' => $item->category->color ?? '#9CA3AF',
                    'amount' => $item->total / 100,
                ];
            });

        // 7. Spending Limits Data (Budgets for current month - Optimized to prevent N+1)
        $categorySpending = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->groupBy('category_id')
            ->selectRaw('category_id, sum(amount) as total')
            ->pluck('total', 'category_id');

        $spendingLimitsData = $user->budgets()
            ->whereDate('month', $startOfMonth->format('Y-m-d'))
            ->with('category:id,name,color')
            ->get()
            ->map(function ($budget) use ($categorySpending) {
                $spent = $categorySpending->get($budget->category_id) ?? 0;
                $allocated = $budget->allocated_amount;
                $percentage = $allocated > 0 ? min(100, round(($spent / $allocated) * 100)) : 0;
                
                return [
                    'id' => $budget->id,
                    'name' => $budget->category->name ?? 'General Budget',
                    'color' => $budget->category->color ?? '#7033ff',
                    'spent' => $spent,
                    'allocated' => $allocated,
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

        // 8. Recent Transactions Data (Latest 5)
        $recentTransactionsData = $user->transactions()
            ->with(['category:id,name,icon,color', 'card:id,last_four,brand'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->take(5)
            ->get()
            ->map(function ($transaction) {
                return [
                    'id' => $transaction->id,
                    'title' => $transaction->title,
                    'amount' => $transaction->amount, // keeping in cents
                    'type' => $transaction->type,
                    'date' => $transaction->transaction_date->format('M d, Y'),
                    'category' => $transaction->category ? [
                        'name' => $transaction->category->name,
                        'icon' => $transaction->category->icon,
                        'color' => $transaction->category->color,
                    ] : null,
                    'card' => $transaction->card ? [
                        'brand' => $transaction->card->brand,
                        'last_four' => $transaction->card->last_four,
                    ] : null,
                ];
            });

        // 9. My Cards Data
        $myCardsData = $user->cards()
            ->withSum(['transactions as income_sum' => function ($query) {
                $query->where('type', 'income');
            }], 'amount')
            ->withSum(['transactions as expense_sum' => function ($query) {
                $query->where('type', 'expense');
            }], 'amount')
            ->where('is_active', true)
            ->get()
            ->map(function ($card) {
                $balance = ($card->income_sum ?? 0) - ($card->expense_sum ?? 0);
                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'brand' => $card->brand,
                    'last_four' => $card->last_four,
                    'expires' => str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT) . '/' . substr($card->expiration_year, -2),
                    'color' => $card->color,
                    'balance' => $balance,
                ];
            });

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
            'recentTransactionsData' => $recentTransactionsData,
            'myCardsData' => $myCardsData,
            'currency' => $user->currency,
        ]);
    }
}
