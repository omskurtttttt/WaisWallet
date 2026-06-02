<?php

namespace App\Http\Controllers;

use App\Models\Card;
use App\Http\Requests\StoreCardRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $cards = $request->user()->cards()
            ->withSum(['transactions as income_sum' => function ($query) {
                $query->where('type', 'income');
            }], 'amount')
            ->withSum(['transactions as expense_sum' => function ($query) {
                $query->where('type', 'expense');
            }], 'amount')
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

        return Inertia::render('Wallet/Index', [
            'cards' => $cards,
        ]);
    }

    public function store(StoreCardRequest $request)
    {
        $validated = $request->validated();

        $request->user()->cards()->create($validated);

        return redirect()->back()->with('success', 'Card added successfully.');
    }

    public function destroy(Card $card)
    {
        \Illuminate\Support\Facades\Gate::authorize('delete', $card);

        // Before deleting, ensure we don't break transactions. Soft delete would be better
        // but for simplicity we will just delete it, transactions might have card_id set to null
        $card->delete();

        return redirect()->back()->with('success', 'Card removed successfully.');
    }
}
