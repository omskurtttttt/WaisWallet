<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use App\Services\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

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
    public function store(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'month' => ['required', 'date_format:Y-m'],
            'budgets' => ['required', 'array'],
            'budgets.*.category_id' => [
                'required',
                'uuid',
                Rule::exists('categories', 'id')->where('user_id', $user->id),
            ],
            'budgets.*.amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->service->updateOrCreateBudgets($validated);

        return redirect()->back()->with('success', 'Budgets updated successfully.');
    }
}
