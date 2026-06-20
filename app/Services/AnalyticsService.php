<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class AnalyticsService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getIncomeVsExpenses(Carbon $now): array
    {
        $startOfCurrentMonth = $now->copy()->startOfMonth();
        $sixMonthsAgo = $startOfCurrentMonth->copy()->subMonths(5);
        $monthlyTotals = $this->user->transactions()
            ->where('transaction_date', '>=', $sixMonthsAgo)
            ->selectRaw("TO_CHAR(transaction_date, 'YYYY-MM') as month_key, type, sum(amount) as total")
            ->groupBy('month_key', 'type')
            ->get();

        $groupedTotals = $monthlyTotals->groupBy('month_key')->map(function ($items) {
            return $items->keyBy('type')->map(fn ($item) => $item->total);
        });

        $incomeVsExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $startOfCurrentMonth->copy()->subMonths($i);
            $monthKey = $monthStart->format('Y-m');

            $monthData = $groupedTotals->get($monthKey);
            $income = $monthData ? ($monthData->get('income') ?? 0) : 0;
            $expense = $monthData ? ($monthData->get('expense') ?? 0) : 0;

            $incomeVsExpenses[] = [
                'month' => $monthStart->format('M Y'),
                'income' => $income / 100,
                'expense' => $expense / 100,
            ];
        }

        return $incomeVsExpenses;
    }

    public function getCategoryBreakdown(Carbon $startOfMonth, Carbon $endOfMonth)
    {
        return $this->user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('category_id, sum(amount) as total')
            ->groupBy('category_id')
            ->with('category:id,name,color')
            ->get()
            ->map(function ($item) {
                return [
                    'name' => $item->category->name ?? 'Uncategorized',
                    'color' => $item->category->color ?? '#9CA3AF',
                    'value' => $item->total / 100,
                ];
            });
    }

    public function getDailySpending(Carbon $startOfMonth, Carbon $endOfMonth, Carbon $now)
    {
        $dailySpending = $this->user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('DATE(transaction_date) as transaction_date, sum(amount) as total')
            ->groupByRaw('DATE(transaction_date)')
            ->orderBy('transaction_date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->transaction_date->format('M d'),
                    'amount' => $item->total / 100,
                ];
            });

        if ($dailySpending->isEmpty()) {
            return [
                [
                    'date' => $now->format('M d'),
                    'amount' => 0,
                ],
            ];
        }

        return $dailySpending;
    }
}
