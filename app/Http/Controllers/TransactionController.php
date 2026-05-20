<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Category;
use App\Models\Card;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        
        $query = $user->transactions()->with(['category', 'card']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('title', 'ilike', "%{$search}%"); // ilike for postgres
        }

        // Type filter
        if ($request->filled('type') && $request->input('type') !== 'all') {
            $query->where('type', $request->input('type'));
        }

        // Category filter
        if ($request->filled('category') && $request->input('category') !== 'all') {
            $query->where('category_id', $request->input('category'));
        }

        $transactions = $query->orderByDesc('transaction_date')
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

        // Need categories and cards for the filter dropdowns and Add Transaction modal
        $categories = Category::select('id', 'name', 'type', 'icon', 'color')->orderBy('name')->get();
        $cards = $user->cards()->where('is_active', true)->select('id', 'name', 'brand', 'last_four')->get();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $request->only(['search', 'type', 'category']),
            'categories' => $categories,
            'cards' => $cards,
            'currency' => $user->currency,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'transaction_date' => 'required|date',
            'category_id' => 'required|exists:categories,id',
            'card_id' => 'nullable|exists:cards,id',
            'notes' => 'nullable|string',
        ]);

        // Convert amount to cents before saving
        $validated['amount'] = (int) round($validated['amount'] * 100);

        $request->user()->transactions()->create($validated);

        return redirect()->back()->with('success', 'Transaction created successfully.');
    }

    public function destroy(Transaction $transaction)
    {
        // Ensure the transaction belongs to the user
        if ($transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $transaction->delete();

        return redirect()->back()->with('success', 'Transaction deleted successfully.');
    }
}
