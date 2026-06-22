<?php

namespace App\Services;

use App\Models\User;
use Carbon\Carbon;

class ReportService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getReportData(array $data): array
    {
        $type = $data['type'] ?? 'monthly'; // monthly or yearly
        $year = (int) ($data['year'] ?? Carbon::now()->year);
        $month = (int) ($data['month'] ?? Carbon::now()->month);

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

        // Fetch current period metrics aggregated at database level
        $metrics = $this->user->transactions()
            ->whereBetween('transaction_date', [$start, $end])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense,
                COUNT(*) as total_count
            ")
            ->first();

        $income = (int) $metrics->income;
        $expense = (int) $metrics->expense;
        $netSavings = $income - $expense;
        $savingsRate = $income > 0 ? round(($netSavings / $income) * 100, 1) : 0;
        $transactionCount = (int) $metrics->total_count;

        // Fetch previous period metrics aggregated at database level
        $prevMetrics = $this->user->transactions()
            ->whereBetween('transaction_date', [$prevStart, $prevEnd])
            ->selectRaw("
                COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense,
                COUNT(*) as total_count
            ")
            ->first();

        $prevIncome = (int) $prevMetrics->income;
        $prevExpense = (int) $prevMetrics->expense;
        $prevNetSavings = $prevIncome - $prevExpense;
        $prevTransactionCount = (int) $prevMetrics->total_count;

        // Trends (percentage change)
        $incomeTrend = $prevIncome > 0 ? round((($income - $prevIncome) / $prevIncome) * 100, 1) : 0;
        $expenseTrend = $prevExpense > 0 ? round((($expense - $prevExpense) / $prevExpense) * 100, 1) : 0;
        $netSavingsTrend = 0;
        if ($prevNetSavings != 0) {
            $netSavingsTrend = round((($netSavings - $prevNetSavings) / abs($prevNetSavings)) * 100, 1);
        }
        $transactionCountTrend = $prevTransactionCount > 0 ? round((($transactionCount - $prevTransactionCount) / $prevTransactionCount) * 100, 1) : 0;

        // Top Categories grouped and summed at database level
        $topCategoriesData = $this->user->transactions()
            ->where('transactions.type', 'expense')
            ->whereBetween('transactions.transaction_date', [$start, $end])
            ->leftJoin('categories', 'transactions.category_id', '=', 'categories.id')
            ->groupBy('transactions.category_id', 'categories.name', 'categories.color', 'categories.icon')
            ->selectRaw('
                transactions.category_id,
                categories.name as category_name,
                categories.color as category_color,
                categories.icon as category_icon,
                COALESCE(SUM(transactions.amount), 0) as total_amount,
                COUNT(transactions.id) as transaction_count
            ')
            ->orderByDesc('total_amount')
            ->get();

        $topCategories = $topCategoriesData->map(function ($row) use ($expense) {
            $total = (int) $row->total_amount;

            return [
                'name' => $row->category_name ?? 'Uncategorized',
                'color' => $row->category_color ?? '#9CA3AF',
                'icon' => $row->category_icon ?? 'tag',
                'amount' => $total,
                'count' => (int) $row->transaction_count,
                'percentage' => $expense > 0 ? round(($total / $expense) * 100, 1) : 0,
            ];
        })->all();

        // Chart Data aggregated at database level
        $chartData = [];
        if ($type === 'monthly') {
            $dailyExpenses = $this->user->transactions()
                ->where('type', 'expense')
                ->whereBetween('transaction_date', [$start, $end])
                ->groupByRaw("TO_CHAR(transaction_date, 'YYYY-MM-DD')")
                ->selectRaw("TO_CHAR(transaction_date, 'YYYY-MM-DD') as date_str, COALESCE(SUM(amount), 0) as total_amount")
                ->get()
                ->pluck('total_amount', 'date_str')
                ->toArray();

            $daysInMonth = $start->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $dayDate = $start->copy()->day($d);
                $dayStr = $dayDate->format('Y-m-d');
                $dayLabel = $dayDate->format('d M');

                $daySum = (int) ($dailyExpenses[$dayStr] ?? 0);

                $chartData[] = [
                    'label' => $dayLabel,
                    'amount' => $daySum / 100,
                ];
            }
        } else {
            $monthlyData = $this->user->transactions()
                ->whereBetween('transaction_date', [$start, $end])
                ->groupByRaw("TO_CHAR(transaction_date, 'YYYY-MM')")
                ->selectRaw("
                    TO_CHAR(transaction_date, 'YYYY-MM') as month_str,
                    COALESCE(SUM(CASE WHEN type = 'income' THEN amount ELSE 0 END), 0) as income,
                    COALESCE(SUM(CASE WHEN type = 'expense' THEN amount ELSE 0 END), 0) as expense
                ")
                ->get()
                ->keyBy('month_str');

            // Yearly chart data (by month)
            for ($m = 1; $m <= 12; $m++) {
                $monthDate = Carbon::create($year, $m, 1);
                $monthStr = $monthDate->format('Y-m');
                $monthLabel = $monthDate->format('M');

                $row = $monthlyData->get($monthStr);
                $monthIncome = $row ? (int) $row->income : 0;
                $monthExpense = $row ? (int) $row->expense : 0;

                $chartData[] = [
                    'label' => $monthLabel,
                    'income' => $monthIncome / 100,
                    'expense' => $monthExpense / 100,
                ];
            }
        }

        // Available years for dropdown
        $firstTransaction = $this->user->transactions()->orderBy('transaction_date')->first();
        $startYear = $firstTransaction ? $firstTransaction->transaction_date->year : Carbon::now()->year - 2;
        $endYear = Carbon::now()->year;
        $availableYears = range($endYear, min($startYear, $endYear - 3));

        return [
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
        ];
    }

    /**
     * Export transactions for the selected period to a CSV file.
     */
    public function exportCsv(array $data)
    {
        $type = $data['type'] ?? 'monthly';
        $year = (int) ($data['year'] ?? Carbon::now()->year);
        $month = (int) ($data['month'] ?? Carbon::now()->month);

        if ($type === 'yearly') {
            $start = Carbon::create($year, 1, 1)->startOfYear();
            $end = $start->copy()->endOfYear();
            $filename = "waiswallet_report_{$year}.csv";
        } else {
            $start = Carbon::create($year, $month, 1)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $monthName = strtolower($start->format('F'));
            $filename = "waiswallet_report_{$monthName}_{$year}.csv";
        }

        $transactions = $this->user->transactions()
            ->whereBetween('transaction_date', [$start, $end])
            ->with(['category', 'card'])
            ->orderByDesc('transaction_date')
            ->get();

        $headers = [
            'Content-type' => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($transactions) {
            $file = fopen('php://output', 'w');

            // Add UTF-8 BOM for proper Excel encoding
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));

            // CSV Headers
            fputcsv($file, [
                'Date',
                'Title',
                'Type',
                'Amount',
                'Category',
                'Payment Method',
                'Recipient/Source',
                'Status',
                'Notes',
            ]);

            foreach ($transactions as $transaction) {
                fputcsv($file, [
                    $transaction->transaction_date->format('Y-m-d H:i:s'),
                    $transaction->title,
                    ucfirst($transaction->type),
                    number_format($transaction->amount / 100, 2),
                    $transaction->category ? $transaction->category->name : 'Uncategorized',
                    $transaction->card ? "{$transaction->card->brand} (•••• {$transaction->card->last_four})" : 'Cash',
                    $transaction->recipient_name ?? 'N/A',
                    ucfirst($transaction->status),
                    $transaction->notes ?? '',
                ]);
            }

            fclose($file);
        };

        return response()->streamDownload($callback, $filename, $headers);
    }
}
