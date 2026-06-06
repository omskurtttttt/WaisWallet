<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;

class TransactionService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function getTransactionsList(array $filters)
    {
        $query = $this->user->transactions()->with(['category', 'card']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where('title', 'ilike', "%{$search}%"); // ilike for postgres
        }

        // Type filter
        if (!empty($filters['type']) && $filters['type'] !== 'all') {
            $query->where('type', $filters['type']);
        }

        // Category filter
        if (!empty($filters['category']) && $filters['category'] !== 'all') {
            $query->where('category_id', $filters['category']);
        }

        return $query->orderByDesc('transaction_date')
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($tx) => [
                'id' => $tx->id,
                'title' => $tx->title,
                'amount' => $tx->amount,
                'type' => $tx->type,
                'date' => $tx->transaction_date->format('Y-m-d'),
                'display_date' => $tx->transaction_date->format('M d, Y'),
                'notes' => $tx->notes,
                'category' => $tx->category ? [
                    'id' => $tx->category->id,
                    'name' => $tx->category->name,
                    'icon' => $tx->category->icon,
                    'color' => $tx->category->color,
                ] : null,
                'card' => $tx->card ? [
                    'id' => $tx->card->id,
                    'brand' => $tx->card->brand,
                    'last_four' => $tx->card->last_four,
                ] : null,
            ]);
    }

    public function getCategories()
    {
        return $this->user->categories()
            ->select('id', 'name', 'type', 'icon', 'color')
            ->orderBy('name')
            ->get();
    }

    public function getCards()
    {
        return $this->user->cards()
            ->where('is_active', true)
            ->withSum(['transactions as income_sum' => function ($query) {
                $query->where('type', 'income');
            }], 'amount')
            ->withSum(['transactions as expense_sum' => function ($query) {
                $query->where('type', 'expense');
            }], 'amount')
            ->get()
            ->map(function ($card) {
                $balance = ($card->income_sum ?? 0) - ($card->expense_sum ?? 0);
                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'brand' => $card->brand,
                    'last_four' => $card->last_four,
                    'color' => $card->color,
                    'balance' => $balance,
                ];
            });
    }

    public function storeTransaction(array $validated)
    {
        return $this->user->transactions()->create($validated);
    }

    public function updateTransaction(Transaction $transaction, array $validated)
    {
        return $transaction->update($validated);
    }

    public function deleteTransaction(Transaction $transaction)
    {
        return $transaction->delete();
    }
}
