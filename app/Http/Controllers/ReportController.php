<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReportController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, ReportService $service)
    {
        $request->validate([
            'type' => ['nullable', 'string', 'in:monthly,yearly'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:'.(Carbon::now()->year + 10)],
            'month' => ['nullable', 'integer', 'min:1', 'max:12'],
            'export' => ['nullable', 'string', 'in:csv'],
        ]);

        $user = $request->user();

        if ($request->input('export') === 'csv') {
            return $service->exportCsv($request->only(['type', 'year', 'month']));
        }

        $reportData = $service->getReportData($request->only(['type', 'year', 'month']));

        return Inertia::render('Reports/Index', array_merge($reportData, [
            'currency' => $user->currency,
        ]));
    }
}
