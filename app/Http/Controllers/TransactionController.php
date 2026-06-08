<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class TransactionController extends Controller
{
    protected TransactionService $service;

    public function __construct(TransactionService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['search', 'type', 'category', 'status', 'date_from', 'date_to', 'sort_by', 'sort_order']);
        $transactions = $this->service->getTransactionsList($filters);
        $categories = $this->service->getCategories();
        $cards = $this->service->getCards();

        return Inertia::render('Transactions/Index', [
            'transactions' => $transactions,
            'filters' => $filters,
            'categories' => $categories,
            'cards' => $cards,
            'currency' => $user->currency,
        ]);
    }

    public function store(StoreTransactionRequest $request)
    {
        $this->service->storeTransaction($request->validated());

        return redirect()->back()->with('success', 'Transaction created successfully.');
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction)
    {
        Gate::authorize('update', $transaction);

        $this->service->updateTransaction($transaction, $request->validated());

        return redirect()->back()->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        Gate::authorize('delete', $transaction);

        $this->service->deleteTransaction($transaction);

        return redirect()->back()->with('success', 'Transaction deleted successfully.');
    }
}
