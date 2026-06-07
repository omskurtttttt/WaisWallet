<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class DashboardService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getMetrics(Carbon $now, Carbon $startOfMonth, Carbon $endOfMonth): array
    {
        // 1. Total Balance
        $totalIncome = $this->user->transactions()->where('type', 'income')->sum('amount');
        $totalExpenses = $this->user->transactions()->where('type', 'expense')->sum('amount');
        $totalBalance = $totalIncome - $totalExpenses;

        $incomeBefore = $this->user->transactions()
            ->where('type', 'income')
            ->where('transaction_date', '<', $startOfMonth)
            ->sum('amount');
        $expensesBefore = $this->user->transactions()
            ->where('type', 'expense')
            ->where('transaction_date', '<', $startOfMonth)
            ->sum('amount');
        $previousBalance = $incomeBefore - $expensesBefore;

        $balanceTrend = 0;
        if ($previousBalance != 0) {
            $balanceTrend = round((($totalBalance - $previousBalance) / abs($previousBalance)) * 100, 1);
        }

        // 2. Income
        $monthlyIncome = $this->user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $previousMonthStart = $now->copy()->startOfMonth()->subMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();

        $previousMonthIncome = $this->user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$previousMonthStart, $previousMonthEnd])
            ->sum('amount');

        $incomeTrend = 0;
        if ($previousMonthIncome != 0) {
            $incomeTrend = round((($monthlyIncome - $previousMonthIncome) / abs($previousMonthIncome)) * 100, 1);
        }

        // 3. Expense
        $monthlyExpense = $this->user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $previousMonthExpense = $this->user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$previousMonthStart, $previousMonthEnd])
            ->sum('amount');

        $expenseTrend = 0;
        if ($previousMonthExpense != 0) {
            $expenseTrend = round((($monthlyExpense - $previousMonthExpense) / abs($previousMonthExpense)) * 100, 1);
        }

        // 4. Total Savings (income minus expenses for the month)
        $monthlySavings = $monthlyIncome - $monthlyExpense;
        $previousMonthSavings = $previousMonthIncome - $previousMonthExpense;

        $savingsTrend = 0;
        if ($previousMonthSavings != 0) {
            $savingsTrend = round((($monthlySavings - $previousMonthSavings) / abs($previousMonthSavings)) * 100, 1);
        }

        // 5. Budget Used details (for spending limits widget support)
        $totalBudgetAllocated = $this->user->budgets()
            ->whereDate('month', $startOfMonth->format('Y-m-d'))
            ->sum('allocated_amount');
            
        $budgetUsedPercentage = 0;
        if ($totalBudgetAllocated > 0) {
            $budgetUsedPercentage = min(100, round(($monthlyExpense / $totalBudgetAllocated) * 100));
        } else {
            $limit = $this->user->monthly_spending_limit > 0 ? $this->user->monthly_spending_limit : 500000;
            $budgetUsedPercentage = min(100, round(($monthlyExpense / $limit) * 100));
            $totalBudgetAllocated = $limit;
        }

        return [
            'totalBalance' => $totalBalance,
            'balanceTrend' => $balanceTrend,
            'monthlyIncome' => $monthlyIncome,
            'incomeTrend' => $incomeTrend,
            'monthlyExpense' => $monthlyExpense,
            'expenseTrend' => $expenseTrend,
            'monthlySavings' => $monthlySavings,
            'savingsTrend' => $savingsTrend,
            // spendingLimits support
            'monthlySpending' => $monthlyExpense,
            'budgetUsedPercentage' => $budgetUsedPercentage,
            'totalBudgetAllocated' => $totalBudgetAllocated,
        ];
    }

    public function getIncomeChartData(Carbon $now): array
    {
        $startOfCurrentMonth = $now->copy()->startOfMonth();
        $sixMonthsAgo = $startOfCurrentMonth->copy()->subMonths(5);
        $monthlyIncomes = $this->user->transactions()
            ->where('type', 'income')
            ->where('transaction_date', '>=', $sixMonthsAgo)
            ->selectRaw("TO_CHAR(transaction_date, 'YYYY-MM') as month_key, sum(amount) as total")
            ->groupBy('month_key')
            ->get()
            ->pluck('total', 'month_key');

        $incomeChartData = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $startOfCurrentMonth->copy()->subMonths($i);
            $monthKey = $monthStart->format('Y-m');
            $monthIncome = $monthlyIncomes->get($monthKey) ?? 0;
                
            $incomeChartData[] = [
                'month' => $monthStart->format('M Y'),
                'amount' => $monthIncome / 100,
            ];
        }

        return $incomeChartData;
    }

    public function getBudgetChartData(Carbon $startOfMonth, Carbon $endOfMonth)
    {
        return $this->user->transactions()
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
    }

    public function getSpendingLimits(Carbon $startOfMonth, int $monthlySpending, int $totalBudgetAllocated, int $budgetUsedPercentage)
    {
        $categorySpending = $this->user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $startOfMonth->copy()->endOfMonth()])
            ->groupBy('category_id')
            ->selectRaw('category_id, sum(amount) as total')
            ->pluck('total', 'category_id');

        $spendingLimitsData = $this->user->budgets()
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

        // Always prepend the overall monthly spending limit at the top
        $overallLimit = $this->user->monthly_spending_limit > 0 ? $this->user->monthly_spending_limit : 500000;
        $overallPercentage = $overallLimit > 0 ? min(100, round(($monthlySpending / $overallLimit) * 100)) : 0;

        $spendingLimitsData->prepend([
            'id' => 'overall',
            'name' => 'Overall Monthly Limit',
            'color' => '#7033ff',
            'spent' => $monthlySpending,
            'allocated' => $overallLimit,
            'percentage' => $overallPercentage,
            'is_overall' => true,
        ]);

        return $spendingLimitsData;
    }

    public function getRecentTransactions()
    {
        return $this->user->transactions()
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
    }

    public function getMyCards()
    {
        return $this->user->cards()
            ->withBalance()
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
    }
}
