<?php

namespace App\Http\Controllers;

use App\Models\Card;
use Illuminate\Http\Request;
use Inertia\Inertia;

class CardController extends Controller
{
    public function index(Request $request)
    {
        $cards = $request->user()->cards()
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->get()
            ->map(function ($card) {
                return [
                    'id' => $card->id,
                    'name' => $card->name,
                    'brand' => $card->brand,
                    'last_four' => $card->last_four,
                    'expiration_month' => str_pad($card->expiration_month, 2, '0', STR_PAD_LEFT),
                    'expiration_year' => $card->expiration_year,
                    'color' => $card->color,
                    'is_active' => $card->is_active,
                    'created_at' => $card->created_at->format('M d, Y'),
                ];
            });

        return Inertia::render('Wallet/Index', [
            'cards' => $cards,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'brand' => 'required|string|in:Visa,Mastercard,Amex,Discover,Other',
            'last_four' => 'required|string|size:4',
            'expiration_month' => 'required|integer|min:1|max:12',
            'expiration_year' => 'required|integer|min:' . date('Y'),
            'color' => 'required|string|size:7',
            'is_active' => 'boolean',
        ]);

        $request->user()->cards()->create($validated);

        return redirect()->back()->with('success', 'Card added successfully.');
    }

    public function destroy(Card $card)
    {
        if ($card->user_id !== auth()->id()) {
            abort(403);
        }

        // Before deleting, ensure we don't break transactions. Soft delete would be better
        // but for simplicity we will just delete it, transactions might have card_id set to null
        $card->delete();

        return redirect()->back()->with('success', 'Card removed successfully.');
    }
}
