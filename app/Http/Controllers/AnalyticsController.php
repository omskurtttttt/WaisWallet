<?php

namespace App\Http\Controllers;

use App\Services\AnalyticsService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnalyticsController extends Controller
{
    /**
     * Display a listing of analytics data.
     */
    public function __invoke(Request $request, AnalyticsService $service)
    {
        $user = $request->user();
        $now = Carbon::now();
        $startOfMonth = $now->copy()->startOfMonth();
        $endOfMonth = $now->copy()->endOfMonth();

        $incomeVsExpenses = $service->getIncomeVsExpenses($now);
        $categoryBreakdown = $service->getCategoryBreakdown($startOfMonth, $endOfMonth);
        $dailySpending = $service->getDailySpending($startOfMonth, $endOfMonth, $now);

        return Inertia::render('Analytics/Index', [
            'incomeVsExpenses' => $incomeVsExpenses,
            'categoryBreakdown' => $categoryBreakdown,
            'dailySpending' => $dailySpending,
            'currency' => $user->currency,
        ]);
    }
}
