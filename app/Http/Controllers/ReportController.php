<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): Response
    {
        $request->validate([
            'type' => ['nullable', 'string', 'in:monthly,yearly'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:' . (Carbon::now()->year + 10)],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
        ]);

        $user = $request->user();
        
        $type = $request->input('type', 'monthly'); // monthly or yearly
        $year = (int) $request->input('year', Carbon::now()->year);
        $month = (int) $request->input('month', Carbon::now()->month);

        if ($type === 'yearly') {
            $start = Carbon::create($year, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
            
            $prevStart = $start->copy()->subYear()->startOfYear();
            $prevEnd = $prevStart->copy()->endOfYear();
            
            $periodLabel = $start->format('Y');
            $prevPeriodLabel = $prevStart->format('Y');
        } else {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            
            $prevStart = $start->copy()->subMonth()->startOfMonth();
            $prevEnd = $prevStart->copy()->endOfMonth();
            
            $periodLabel = $start->format('F Y');
            $prevPeriodLabel = $prevStart->format('F Y');
        }

        // Fetch all transactions in the current period with their categories
        $transactions = $user->transactions()
            ->whereBetween('transaction_date', [$start, $end])
            ->with('category')
            ->get();

        // Calculate current period metrics
        $incomeTx = $transactions->where('type', 'income');
        $expenseTx = $transactions->where('type', 'expense');

        $income = $incomeTx->sum('amount');
        $expense = $expenseTx->sum('amount');
        $netSavings = $income - $expense;
        $savingsRate = $income > 0 ? round(($netSavings / $income) * 100, 1) : 0;
        $transactionCount = $transactions->count();

        // Calculate previous period metrics for comparison
        $prevIncome = $user->transactions()
            ->where('type', 'income')
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->sum('amount');
            
        $prevExpense = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->sum('amount');
            
        $prevNetSavings = $prevIncome - $prevExpense;
        $prevTransactionCount = $user->transactions()
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->count();

        // Trends (percentage change)
        $incomeTrend = $prevIncome > 0 ? round((($income - $prevIncome) / $prevIncome) * 100, 1) : 0;
        $expenseTrend = $prevExpense > 0 ? round((($expense - $prevExpense) / $prevExpense) * 100, 1) : 0;
        $netSavingsTrend = 0;
        if ($prevNetSavings != 0) {
            $netSavingsTrend = round((($netSavings - $prevNetSavings) / abs($prevNetSavings)) * 100, 1);
        }
        $transactionCountTrend = $prevTransactionCount > 0 ? round((($transactionCount - $prevTransactionCount) / $prevTransactionCount) * 100, 1) : 0;

        // Top Categories
        $topCategories = $expenseTx->groupBy('category_id')
            ->map(function ($group) use ($expense) {
                $first = $group->first();
                $total = $group->sum('amount');
                return [
                    'name' => $first->category->name ?? 'Uncategorized',
                    'color' => $first->category->color ?? '#9CA3AF',
                    'icon' => $first->category->icon ?? 'tag',
                    'amount' => $total, // keeping in cents for formatMoney
                    'count' => $group->count(),
                    'percentage' => $expense > 0 ? round(($total / $expense) * 100, 1) : 0,
                ];
            })
            ->sortByDesc('amount')
            ->values()
            ->all();

        // Chart Data
        $chartData = [];
        if ($type === 'monthly') {
            $daysInMonth = $start->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayDate = $start->copy()->day($d);
                $dayStr = $dayDate->format('Y-m-d');
                $dayLabel = $dayDate->format('d M');
                
                $daySum = $expenseTx->filter(function ($t) use ($dayStr) {
                    return $t->transaction_date->format('Y-m-d') === $dayStr;
                })->sum('amount');
                
                $chartData[] = [
                    'label' => $dayLabel,
                    'amount' => $daySum / 100,
                ];
            }
        } else {
            // Yearly chart data (by month)
            for ($m = 1; $m <= 12; $m++) {
                $monthDate = Carbon::create($year, $m, 1);
                $monthStr = $monthDate->format('Y-m');
                $monthLabel = $monthDate->format('M');
                
                $monthIncome = $incomeTx->filter(function ($t) use ($monthStr) {
                    return $t->transaction_date->format('Y-m') === $monthStr;
                })->sum('amount');
                
                $monthExpense = $expenseTx->filter(function ($t) use ($monthStr) {
                    return $t->transaction_date->format('Y-m') === $monthStr;
                })->sum('amount');
                
                $chartData[] = [
                    'label' => $monthLabel,
                    'income' => $monthIncome / 100,
                    'expense' => $monthExpense / 100,
                ];
            }
        }

        // Available years for dropdown (e.g. from user's first transaction to now, fallback to current year and previous 3 years)
        $firstTransaction = $user->transactions()->orderBy('transaction_date')->first();
        $startYear = $firstTransaction ? $firstTransaction->transaction_date->year : Carbon::now()->year - 2;
        $endYear = Carbon::now()->year;
        $availableYears = range($endYear, min($startYear, $endYear - 3));

        return Inertia::render('Reports/Index', [
            'filters' => [
                'type' => $type,
                'year' => $year,
                'month' => $month,
            ],
            'metrics' => [
                'income' => $income,
                'expense' => $expense,
                'netSavings' => $netSavings,
                'savingsRate' => $savingsRate,
                'transactionCount' => $transactionCount,
                'trends' => [
                    'income' => $incomeTrend,
                    'expense' => $expenseTrend,
                    'netSavings' => $netSavingsTrend,
                    'transactionCount' => $transactionCountTrend,
                ],
            ],
            'periodLabel' => $periodLabel,
            'prevPeriodLabel' => $prevPeriodLabel,
            'topCategories' => $topCategories,
            'chartData' => $chartData,
            'availableYears' => $availableYears,
            'currency' => $user->currency,
        ]);
    }
}
