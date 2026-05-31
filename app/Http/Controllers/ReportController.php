<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
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
        $service = new ReportService($user);

        $reportData = $service->getReportData($request->only(['type', 'year', 'month']));

        return Inertia::render('Reports/Index', array_merge($reportData, [
            'currency' => $user->currency,
        ]));
    }
}
