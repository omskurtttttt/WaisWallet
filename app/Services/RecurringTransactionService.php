<?php

namespace App\Services;

use App\Models\RecurringTransaction;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

class RecurringTransactionService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Retrieve paginated list of recurring transactions for the user.
     */
    public function getRecurringTransactionsList(array $filters): LengthAwarePaginator
    {
        $query = $this->user->recurringTransactions()
            ->with(['category', 'card'])
            ->orderBy('next_run_date')
            ->orderByDesc('created_at');

        return $query->paginate(10)
            ->through(fn ($rt) => [
                'id' => $rt->id,
                'title' => $rt->title,
                'amount' => $rt->amount,
                'type' => $rt->type,
                'frequency' => $rt->frequency,
                'is_active' => $rt->is_active,
                'next_run_date' => $rt->next_run_date->format('Y-m-d'),
                'display_next_run' => $rt->next_run_date->format('M d, Y'),
                'last_run_date' => $rt->last_run_date ? $rt->last_run_date->format('Y-m-d') : null,
                'display_last_run' => $rt->last_run_date ? $rt->last_run_date->format('M d, Y') : 'Never',
                'recipient_name' => $rt->recipient_name,
                'notes' => $rt->notes,
                'category' => $rt->category ? [
                    'id' => $rt->category->id,
                    'name' => $rt->category->name,
                    'icon' => $rt->category->icon,
                    'color' => $rt->category->color,
                ] : null,
                'card' => $rt->card ? [
                    'id' => $rt->card->id,
                    'brand' => $rt->card->brand,
                    'last_four' => $rt->card->last_four,
                ] : null,
            ]);
    }

    /**
     * Retrieve categories for dropdowns.
     */
    public function getCategories()
    {
        return $this->user->categories()
            ->select('id', 'name', 'type', 'icon', 'color')
            ->orderBy('name')
            ->get();
    }

    /**
     * Retrieve active cards for dropdowns.
     */
    public function getCards()
    {
        return $this->user->cards()
            ->where('is_active', true)
            ->get()
            ->map(fn ($card) => [
                'id' => $card->id,
                'name' => $card->name,
                'brand' => $card->brand,
                'last_four' => $card->last_four,
                'color' => $card->color,
            ]);
    }

    /**
     * Store a new recurring transaction.
     */
    public function storeRecurringTransaction(array $validated): RecurringTransaction
    {
        return $this->user->recurringTransactions()->create($validated);
    }

    /**
     * Update an existing recurring transaction.
     */
    public function updateRecurringTransaction(RecurringTransaction $recurring, array $validated): bool
    {
        return $recurring->update($validated);
    }

    /**
     * Delete a recurring transaction.
     */
    public function deleteRecurringTransaction(RecurringTransaction $recurring): bool
    {
        return $recurring->delete();
    }
}
