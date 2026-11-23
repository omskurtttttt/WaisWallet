<?php

namespace App\Services;

use App\Models\Goal;
use App\Models\User;
use Carbon\Carbon;
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
        $cardId = $data['card_id'] ?? null;

        DB::transaction(function () use ($goal, $amountInCents, $data, $cardId) {
            $lockedGoal = Goal::where('id', $goal->id)->lockForUpdate()->firstOrFail();

            if ($data['operation'] === 'add') {
                $lockedGoal->increment('current_amount', $amountInCents);

                if ($cardId) {
                    $category = $this->user->categories()
                        ->where('type', 'expense')
                        ->where(function ($q) {
                            $q->where('name', 'ilike', '%invest%')
                                ->orWhere('name', 'ilike', '%saving%')
                                ->orWhere('name', 'ilike', '%other%');
                        })
                        ->first() ?? $this->user->categories()->where('type', 'expense')->first();

                    $this->user->transactions()->create([
                        'title' => "Goal Deposit: {$goal->name}",
                        'type' => 'expense',
                        'amount' => $amountInCents,
                        'category_id' => $category?->id,
                        'card_id' => $cardId,
                        'recipient_name' => "Goal: {$goal->name}",
                        'status' => 'completed',
                        'transaction_date' => Carbon::now(),
                        'notes' => "Automatic fund allocation to savings goal: {$goal->name}",
                    ]);
                }
            } else {
                $actualWithdrawn = min($lockedGoal->current_amount, $amountInCents);
                $newAmount = max(0, $lockedGoal->current_amount - $amountInCents);
                $lockedGoal->update(['current_amount' => $newAmount]);

                if ($cardId && $actualWithdrawn > 0) {
                    $category = $this->user->categories()
                        ->where('type', 'income')
                        ->where(function ($q) {
                            $q->where('name', 'ilike', '%saving%')
                                ->orWhere('name', 'ilike', '%other%')
                                ->orWhere('name', 'ilike', '%investment%');
                        })
                        ->first() ?? $this->user->categories()->where('type', 'income')->first();

                    $this->user->transactions()->create([
                        'title' => "Goal Withdrawal: {$goal->name}",
                        'type' => 'income',
                        'amount' => $actualWithdrawn,
                        'category_id' => $category?->id,
                        'card_id' => $cardId,
                        'recipient_name' => "From Goal: {$goal->name}",
                        'status' => 'completed',
                        'transaction_date' => Carbon::now(),
                        'notes' => "Withdrawn from savings goal: {$goal->name}",
                    ]);
                }
            }
        });

        $goal->refresh();
        app(NotificationService::class)->checkGoalAlerts($goal);

        return true;
    }

    /**
     * Mark the goal as complete (set current_amount to target_amount).
     */
    public function markAsComplete(Goal $goal): bool
    {
        $updated = $goal->update([
            'current_amount' => $goal->target_amount,
        ]);

        app(NotificationService::class)->checkGoalAlerts($goal);

        return $updated;
    }

    /**
     * Delete an existing savings goal.
     */
    public function deleteGoal(Goal $goal): bool
    {
        return $goal->delete();
    }
}
