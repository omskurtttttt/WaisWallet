<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Validation\Rule;

class BudgetController extends Controller
{
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

        $month = Carbon::createFromFormat('Y-m', $validated['month'])->startOfMonth()->format('Y-m-d');

        foreach ($validated['budgets'] as $budgetItem) {
            $categoryId = $budgetItem['category_id'];
            $amountInCents = isset($budgetItem['amount']) ? (int) round($budgetItem['amount'] * 100) : 0;

            if ($amountInCents > 0) {
                $user->budgets()->updateOrCreate(
                    [
                        'category_id' => $categoryId,
                        'month' => $month,
                    ],
                    [
                        'allocated_amount' => $amountInCents,
                    ]
                );
            } else {
                $user->budgets()
                    ->where('category_id', $categoryId)
                    ->whereDate('month', $month)
                    ->delete();
            }
        }

        return redirect()->back()->with('success', 'Budgets updated successfully.');
    }
}
