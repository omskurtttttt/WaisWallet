<?php

namespace App\Policies;

use App\Models\RecurringTransaction;
use App\Models\User;

class RecurringTransactionPolicy
{
    /**
     * Determine whether the user can view the recurring transaction.
     */
    public function view(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine whether the user can update the recurring transaction.
     */
    public function update(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }

    /**
     * Determine whether the user can delete the recurring transaction.
     */
    public function delete(User $user, RecurringTransaction $recurringTransaction): bool
    {
        return $user->id === $recurringTransaction->user_id;
    }
}
