<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Http\Requests\StoreTransactionRequest;
use App\Services\TransactionService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $service = new TransactionService($user);

        $filters = $request->only(['search', 'type', 'category']);
        $transactions = $service->getTransactionsList($filters);
        $categories = $service->getCategories();
        $cards = $service->getCards();

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
        $service = new TransactionService($request->user());
        $service->storeTransaction($request->validated());

        return redirect()->back()->with('success', 'Transaction created successfully.');
    }

    public function update(StoreTransactionRequest $request, Transaction $transaction)
    {
        $service = new TransactionService($request->user());
        $service->updateTransaction($transaction, $request->validated());

        return redirect()->back()->with('success', 'Transaction updated successfully.');
    }

    public function destroy(Request $request, Transaction $transaction)
    {
        $service = new TransactionService($request->user());
        $service->deleteTransaction($transaction);

        return redirect()->back()->with('success', 'Transaction deleted successfully.');
    }
}
