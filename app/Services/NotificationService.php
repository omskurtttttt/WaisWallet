<?php

namespace App\Services;

use App\Models\User;
use App\Models\Notification;
use App\Models\Category;
use App\Models\Budget;
use App\Models\Goal;
use Carbon\Carbon;

class NotificationService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Notification $notification): bool
    {
        return $notification->update(['read_at' => Carbon::now()]);
    }

    /**
     * Mark all notifications for the user as read.
     */
    public function markAllAsRead(): void
    {
        $this->user->notifications()
            ->whereNull('read_at')
            ->update(['read_at' => Carbon::now()]);
    }

    /**
     * Check and generate budget alerts for a transaction.
     */
    public function checkBudgetAlerts(Category $category, Carbon $date): void
    {
        $monthStr = $date->format('Y-m-01');
        
        // Find budget for this category and month
        $budget = $this->user->budgets()
            ->where('category_id', $category->id)
            ->where('month', $monthStr)
            ->first();

        if (!$budget) {
            return;
        }

        $allocated = $budget->allocated_amount;
        if ($allocated <= 0) {
            return;
        }

        // Sum expenses in this category for this month
        $startOfMonth = $date->copy()->startOfMonth();
        $endOfMonth = $date->copy()->endOfMonth();

        $totalSpent = $this->user->transactions()
            ->where('type', 'expense')
            ->where('category_id', $category->id)
            ->whereBetween('transaction_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        if ($totalSpent <= 0) {
            return;
        }

        $percentage = ($totalSpent / $allocated) * 100;

        if ($percentage >= 100) {
            $title = "Budget Exceeded";
            $message = "You have spent 100% or more of your budget for {$category->name} this month.";
            $this->createAlertIfNotExists($title, $message, 'budget_alert');
        } elseif ($percentage >= 80) {
            $title = "Budget Limit Nearing";
            $message = "You have spent 80% or more of your budget for {$category->name} this month.";
            $this->createAlertIfNotExists($title, $message, 'budget_alert');
        }
    }

    /**
     * Check and generate goal milestone alerts for a savings goal.
     */
    public function checkGoalAlerts(Goal $goal): void
    {
        $target = $goal->target_amount;
        $current = $goal->current_amount;
        if ($target <= 0) {
            return;
        }

        $percentage = ($current / $target) * 100;

        if ($percentage >= 100) {
            $title = "Goal Completed 🎉";
            $message = "Congratulations! You have reached your savings target for {$goal->name}!";
            $this->createAlertIfNotExists($title, $message, 'goal_milestone');
        } elseif ($percentage >= 80) {
            $title = "Goal Milestone (80%)";
            $message = "You are almost there! You have saved 80% or more of your target for {$goal->name}.";
            $this->createAlertIfNotExists($title, $message, 'goal_milestone');
        }
    }

    /**
     * Create a notification alert if it doesn't already exist in the current month.
     */
    protected function createAlertIfNotExists(string $title, string $message, string $type): void
    {
        $startOfMonth = Carbon::now()->startOfMonth();

        $exists = $this->user->notifications()
            ->where('type', $type)
            ->where('title', $title)
            ->where('created_at', '>=', $startOfMonth)
            ->exists();

        if (!$exists) {
            $this->user->notifications()->create([
                'title' => $title,
                'message' => $message,
                'type' => $type,
            ]);
        }
    }
}
