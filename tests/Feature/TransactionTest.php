<?php

use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
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
        'status' => 'completed',
    ]);
});

test('authenticated user can store transaction with custom status', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $card = Card::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('transactions.store'), [
            'title' => 'Lunch at Jollibee',
            'amount' => 150.50,
            'type' => 'expense',
            'status' => 'pending',
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
        'status' => 'pending',
    ]);
});

test('user can update their transaction', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $newCategory = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $card = Card::factory()->create(['user_id' => $user->id]);
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'title' => 'Old Title',
        'amount' => 1000,
        'type' => 'expense',
        'category_id' => $category->id,
        'card_id' => $card->id,
        'status' => 'completed',
        'transaction_date' => '2026-05-28',
    ]);

    $response = $this->actingAs($user)
        ->put(route('transactions.update', $transaction), [
            'title' => 'New Title',
            'amount' => 20.50, // $20.50 -> will be prepareForValidation'ed to 2050 cents
            'type' => 'expense',
            'category_id' => $newCategory->id,
            'card_id' => $card->id,
            'status' => 'pending',
            'transaction_date' => '2026-05-29',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'title' => 'New Title',
        'amount' => 2050,
        'category_id' => $newCategory->id,
        'status' => 'pending',
    ]);
});

test('user cannot update another user\'s transaction', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $categoryOfUser1 = Category::factory()->create(['user_id' => $user1->id, 'type' => 'expense']);
    $cardOfUser1 = Card::factory()->create(['user_id' => $user1->id]);

    $categoryOfUser2 = Category::factory()->create(['user_id' => $user2->id, 'type' => 'expense']);
    $cardOfUser2 = Card::factory()->create(['user_id' => $user2->id]);
    $transaction = Transaction::factory()->create([
        'user_id' => $user2->id,
        'title' => 'User 2 Transaction',
        'amount' => 1000,
        'type' => 'expense',
        'category_id' => $categoryOfUser2->id,
        'card_id' => $cardOfUser2->id,
        'status' => 'completed',
        'transaction_date' => '2026-05-28',
    ]);

    $response = $this->actingAs($user1)
        ->put(route('transactions.update', $transaction), [
            'title' => 'Hacked Title',
            'amount' => 20.50,
            'type' => 'expense',
            'category_id' => $categoryOfUser1->id,
            'card_id' => $cardOfUser1->id,
            'status' => 'completed',
            'transaction_date' => '2026-05-28',
        ]);

    $response->assertStatus(403);
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'title' => 'User 2 Transaction',
    ]);
});

test('user can delete their transaction (soft delete)', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $card = Card::factory()->create(['user_id' => $user->id]);
    $transaction = Transaction::factory()->create([
        'user_id' => $user->id,
        'title' => 'Transaction to Delete',
        'category_id' => $category->id,
        'card_id' => $card->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('transactions.destroy', $transaction));

    $response->assertRedirect();
    $this->assertSoftDeleted('transactions', [
        'id' => $transaction->id,
    ]);
});

test('user cannot delete another user\'s transaction', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user2->id, 'type' => 'expense']);
    $card = Card::factory()->create(['user_id' => $user2->id]);
    $transaction = Transaction::factory()->create([
        'user_id' => $user2->id,
        'title' => 'User 2 Transaction',
        'category_id' => $category->id,
        'card_id' => $card->id,
    ]);

    $response = $this->actingAs($user1)
        ->delete(route('transactions.destroy', $transaction));

    $response->assertStatus(403);
    $this->assertDatabaseHas('transactions', [
        'id' => $transaction->id,
        'deleted_at' => null,
    ]);
});
