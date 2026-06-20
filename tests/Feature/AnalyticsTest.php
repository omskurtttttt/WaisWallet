<?php

use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot view analytics', function () {
    $response = $this->get(route('analytics.index'));
    $response->assertRedirect(route('login'));
});

test('authenticated user can view the analytics page with correct data structures', function () {
    $user = User::factory()->create();

    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $card = Card::factory()->create(['user_id' => $user->id]);

    // Create a transaction to ensure database queries run and return structured data
    Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => 'expense',
        'amount' => 5000, // $50.00
        'transaction_date' => Carbon::now()->format('Y-m-d H:i:s'),
        'category_id' => $category->id,
        'card_id' => $card->id,
    ]);

    $response = $this->actingAs($user)
        ->get(route('analytics.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Analytics/Index')
        ->has('incomeVsExpenses')
        ->has('categoryBreakdown')
        ->has('dailySpending')
        ->where('currency', $user->currency)
    );
});
