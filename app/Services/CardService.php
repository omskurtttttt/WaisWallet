<?php

namespace App\Services;

use App\Models\User;
use App\Models\Card;
use Illuminate\Support\Collection;

class CardService
{
    protected User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    /**
     * Retrieve and format all cards for the user.
     */
    public function getCards(): Collection
    {
        return $this->user->cards()
            ->withBalance()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(function ($card) {
                $balance = ($card->income_sum ?? 0) - ($card->expense_sum ?? 0);
                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'brand' => $card->brand,
                    'last_four' => $card->last_four,
                    'expiration_month' => str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT),
                    'expiration_year' => $card->expiration_year,
                    'color' => $card->color,
                    'is_active' => $card->is_active,
                    'balance' => $balance,
                    'created_at' => $card->created_at->format('M d, Y'),
                ];
            });
    }

    /**
     * Store a new card.
     */
    public function storeCard(array $data): Card
    {
        return $this->user->cards()->create($data);
    }

    /**
     * Update an existing card.
     */
    public function updateCard(Card $card, array $data): bool
    {
        return $card->update($data);
    }

    /**
     * Delete an existing card.
     */
    public function deleteCard(Card $card): bool
    {
        return $card->delete();
    }
}
