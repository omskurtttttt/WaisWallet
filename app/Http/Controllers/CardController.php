<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCardRequest;
use App\Http\Requests\UpdateCardRequest;
use App\Models\Card;
use App\Services\CardService;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class CardController extends Controller
{
    protected CardService $service;

    public function __construct(CardService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $cards = $this->service->getCards();

        return Inertia::render('Wallet/Index', [
            'cards' => $cards,
        ]);
    }

    public function store(StoreCardRequest $request)
    {
        $this->service->storeCard($request->validated());

        return redirect()->back()->with('success', 'Card added successfully.');
    }

    public function update(UpdateCardRequest $request, Card $card)
    {
        Gate::authorize('update', $card);

        $this->service->updateCard($card, $request->validated());

        return redirect()->back()->with('success', 'Card updated successfully.');
    }

    public function destroy(Card $card)
    {
        Gate::authorize('delete', $card);

        $this->service->deleteCard($card);

        return redirect()->back()->with('success', 'Card removed successfully.');
    }
}
