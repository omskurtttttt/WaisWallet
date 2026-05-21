<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display a listing of analytics data.
     */
    public function __invoke(Request $request)
    {
        $user = $request->user();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        // 1. Income vs Expenses (Last 6 Months)
        $incomeVsExpenses = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $now->copy()->subMonths($i)->endOfMonth();
            
            $monthIncome = $user->transactions()
                ->where('type', 'income')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
                
            $monthExpense = $user->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$monthStart, $monthEnd])
                ->sum('amount');
                
            $incomeVsExpenses[] = [
                'month' => $monthStart->format('M Y'),
                'income' => $monthIncome / 100, // in dollars
                'expense' => $monthExpense / 100, // in dollars
            ];
        }

        // 2. Category Breakdown (Current Month)
        $categoryBreakdown = $user->transactions()
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

        // 3. Daily Spending Trends (Current Month)
        $dailySpending = $user->transactions()
            ->where('type', 'expense')
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->selectRaw('transaction_date, sum(amount) as total')
            ->groupBy('transaction_date')
            ->orderBy('transaction_date')
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->transaction_date->format('M d'),
                    'amount' => $item->total / 100,
                ];
            });

        // Fallback for daily spending trends if empty
        if ($dailySpending->isEmpty()) {
            $dailySpending[] = [
                'date' => $now->format('M d'),
                'amount' => 0,
            ];
        }

        return Inertia::render('Analytics/Index', [
            'incomeVsExpenses' => $incomeVsExpenses,
            'categoryBreakdown' => $categoryBreakdown,
            'dailySpending' => $dailySpending,
            'currency' => $user->currency,
        ]);
    }
}
