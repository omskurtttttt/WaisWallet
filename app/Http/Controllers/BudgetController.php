<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBudgetRequest;
use App\Models\Budget;
use App\Models\Category;
use App\Services\BudgetService;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class BudgetController extends Controller
{
    protected BudgetService $service;

    public function __construct(BudgetService $service)
    {
        $this->service = $service;
    }

    /**
     * Store or update budgets.
     */
    public function store(StoreBudgetRequest $request)
    {
        Gate::authorize('create', Budget::class);

        $month = Carbon::createFromFormat('Y-m', $request->input('month'))->startOfMonth()->format('Y-m-d');

        foreach ($request->input('budgets') as $budgetItem) {
            $category = Category::findOrFail($budgetItem['category_id']);
            Gate::authorize('update', $category);

            $budget = Budget::where('category_id', $category->id)
                ->whereDate('month', $month)
                ->first();

            if ($budget) {
                Gate::authorize('update', $budget);
            }
        }

        $this->service->updateOrCreateBudgets($request->validated());

        return redirect()->back()->with('success', 'Budgets updated successfully.');
    }
}
