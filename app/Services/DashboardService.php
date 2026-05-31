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
        $totalIncome = $this->user->transactions()->where('type', 'income')->sum('amount');
        $totalExpenses = $this->user->transactions()->where('type', 'expense')->sum('amount');
        $totalBalance = $totalIncome - $totalExpenses;

        $monthlySpending = $this->user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        $previousMonthStart = $now->copy()->startOfMonth()->subMonth();
        $previousMonthEnd = $previousMonthStart->copy()->endOfMonth();
        $previousMonthSpending = $this->user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$previousMonthStart, $previousMonthEnd])
            ->sum('amount');
            
        $spendingTrend = 0;
        if ($previousMonthSpending > 0) {
            $spendingTrend = round((($monthlySpending - $previousMonthSpending) / $previousMonthSpending) * 100, 1);
        }

        $totalSavings = $this->user->goals()->sum('current_amount');

        $totalBudgetAllocated = $this->user->budgets()
            ->whereDate('month', $startOfMonth->format('Y-m-d'))
            ->sum('allocated_amount');
            
        $budgetUsedPercentage = 0;
        if ($totalBudgetAllocated > 0) {
            $budgetUsedPercentage = min(100, round(($monthlySpending / $totalBudgetAllocated) * 100));
        }

        return [
            'totalBalance' => $totalBalance,
            'monthlySpending' => $monthlySpending,
            'spendingTrend' => $spendingTrend,
            'totalSavings' => $totalSavings,
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
    }
}
