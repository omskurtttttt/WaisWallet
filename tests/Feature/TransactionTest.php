<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Card;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user can store transaction', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $card = Card::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('transactions.store'), [
            'title' => 'Lunch at Jollibee',
            'amount' => 150.50,
            'type' => 'expense',
            'transaction_date' => '2026-05-28',
            'category_id' => $category->id,
            'card_id' => $card->id,
            'notes' => 'Best chicken joy',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'title' => 'Lunch at Jollibee',
        'amount' => 15050, // stored in cents
        'type' => 'expense',
        'category_id' => $category->id,
        'card_id' => $card->id,
    ]);
});
