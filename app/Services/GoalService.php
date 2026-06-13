<?php

namespace App\Services;

use App\Models\User;
use App\Models\Goal;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GoalService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Retrieve and format all savings goals for the user.
     */
    public function getGoals(): Collection
    {
        return $this->user->goals()
            ->orderBy('target_date')
            ->get()
            ->map(function ($goal) {
                $target = $goal->target_amount;
                $current = $goal->current_amount;
                $percentage = $target > 0 ? min(100, round(($current / $target) * 100)) : 0;
                
                return [
                    'id' => $goal->id,
                    'name' => $goal->name,
                    'target_amount' => $target, // in cents
                    'current_amount' => $current, // in cents
                    'target_date' => $goal->target_date ? $goal->target_date->format('Y-m-d') : null,
                    'display_date' => $goal->target_date ? $goal->target_date->format('M d, Y') : 'No Date',
                    'percentage' => $percentage,
                    'remaining' => max(0, $target - $current),
                ];
            });
    }

    /**
     * Store a new savings goal.
     */
    public function storeGoal(array $data): Goal
    {
        return $this->user->goals()->create($data);
    }

    /**
     * Update an existing savings goal.
     */
    public function updateGoal(Goal $goal, array $data): bool
    {
        return $goal->update($data);
    }

    /**
     * Add or withdraw funds from a goal.
     */
    public function addFunds(Goal $goal, array $data): bool
    {
        $amountInCents = $data['amount'];

        DB::transaction(function () use ($goal, $amountInCents, $data) {
            $lockedGoal = Goal::where('id', $goal->id)->lockForUpdate()->firstOrFail();

            if ($data['operation'] === 'add') {
                $lockedGoal->increment('current_amount', $amountInCents);
            } else {
                $newAmount = max(0, $lockedGoal->current_amount - $amountInCents);
                $lockedGoal->update(['current_amount' => $newAmount]);
            }
        });

        return true;
    }

    /**
     * Delete an existing savings goal.
     */
    public function deleteGoal(Goal $goal): bool
    {
        return $goal->delete();
    }
}
