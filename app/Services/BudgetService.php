<?php

namespace App\Services;

use App\Models\User;
use App\Models\Budget;
use Carbon\Carbon;

class BudgetService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Store or update budget allocations for the user.
     */
    public function updateOrCreateBudgets(array $data): bool
    {
        $month = Carbon::createFromFormat('Y-m', $data['month'])->startOfMonth()->format('Y-m-d');

        foreach ($data['budgets'] as $budgetItem) {
            $categoryId = $budgetItem['category_id'];
            $amountInCents = isset($budgetItem['amount']) ? (int) round($budgetItem['amount'] * 100) : 0;

            if ($amountInCents > 0) {
                $this->user->budgets()->updateOrCreate(
                    [
                        'category_id' => $categoryId,
                        'month' => $month,
                    ],
                    [
                        'allocated_amount' => $amountInCents,
                    ]
                );
            } else {
                $this->user->budgets()
                    ->where('category_id', $categoryId)
                    ->whereDate('month', $month)
                    ->delete();
            }
        }

        return true;
    }
}
