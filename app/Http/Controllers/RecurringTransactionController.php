<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRecurringTransactionRequest;
use App\Http\Requests\UpdateRecurringTransactionRequest;
use App\Models\RecurringTransaction;
use App\Services\RecurringTransactionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class RecurringTransactionController extends Controller
{
    protected RecurringTransactionService $service;

    public function __construct(RecurringTransactionService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the recurring transactions.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $filters = $request->only(['search']);
        $recurringTransactions = $this->service->getRecurringTransactionsList($filters);
        $categories = $this->service->getCategories();
        $cards = $this->service->getCards();

        return Inertia::render('Transactions/Recurring', [
            'recurringTransactions' => $recurringTransactions,
            'filters' => $filters,
            'categories' => $categories,
            'cards' => $cards,
            'currency' => $user->currency,
        ]);
    }

    /**
     * Store a newly created recurring transaction in storage.
     */
    public function store(StoreRecurringTransactionRequest $request)
    {
        $this->service->storeRecurringTransaction($request->validated());

        return redirect()->back()->with('success', 'Recurring transaction template created successfully.');
    }

    /**
     * Update the specified recurring transaction in storage.
     */
    public function update(UpdateRecurringTransactionRequest $request, RecurringTransaction $recurringTransaction)
    {
        Gate::authorize('update', $recurringTransaction);

        $this->service->updateRecurringTransaction($recurringTransaction, $request->validated());

        return redirect()->back()->with('success', 'Recurring transaction template updated successfully.');
    }

    /**
     * Remove the specified recurring transaction from storage.
     */
    public function destroy(RecurringTransaction $recurringTransaction)
    {
        Gate::authorize('delete', $recurringTransaction);

        $this->service->deleteRecurringTransaction($recurringTransaction);

        return redirect()->back()->with('success', 'Recurring transaction template removed successfully.');
    }
}
