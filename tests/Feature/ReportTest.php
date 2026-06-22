<?php

use App\Models\Card;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('authenticated user can view monthly report', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Food']);

    // Create some transactions for current month
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 5000, // $50.00
        'transaction_date' => Carbon::now()->startOfMonth(),
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => Category::factory()->create(['user_id' => $user->id, 'type' => 'income'])->id,
        'type' => 'income',
        'amount' => 15000, // $150.00
        'transaction_date' => Carbon::now()->startOfMonth(),
    ]);

    $response = $this->actingAs($user)->get(route('reports.index', [
        'type' => 'monthly',
        'year' => Carbon::now()->year,
        'month' => Carbon::now()->month,
    ]));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reports/Index')
        ->has('filters')
        ->has('metrics', fn (Assert $metrics) => $metrics
            ->where('income', 15000)
            ->where('expense', 5000)
            ->where('netSavings', 10000)
            ->where('savingsRate', 66.7)
            ->where('transactionCount', 2)
            ->has('trends')
        )
        ->has('topCategories')
        ->has('chartData')
    );
});

test('authenticated user can view yearly report', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Food']);

    // Create some transactions for current year
    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 10000,
        'transaction_date' => Carbon::now()->startOfYear(),
    ]);

    $response = $this->actingAs($user)->get(route('reports.index', [
        'type' => 'yearly',
        'year' => Carbon::now()->year,
    ]));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Reports/Index')
        ->has('filters')
        ->has('metrics.income')
        ->has('metrics.expense')
        ->has('topCategories')
        ->has('chartData')
    );
});

test('authenticated user can export report as csv', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Food']);
    $card = Card::factory()->create(['user_id' => $user->id, 'brand' => 'Visa', 'last_four' => '1234']);

    // Create transaction in selected month
    $t1 = Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'card_id' => $card->id,
        'type' => 'expense',
        'amount' => 5000, // $50.00
        'title' => 'Target Transaction',
        'transaction_date' => Carbon::create(2026, 6, 15, 12, 0, 0),
        'recipient_name' => 'Jollibee',
        'status' => 'completed',
        'notes' => 'Yummy chicken',
    ]);

    // Create transaction in different month
    $t2 = Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'type' => 'expense',
        'amount' => 3000,
        'title' => 'Other Month Transaction',
        'transaction_date' => Carbon::create(2026, 5, 15, 12, 0, 0),
    ]);

    $response = $this->actingAs($user)->get(route('reports.index', [
        'type' => 'monthly',
        'year' => 2026,
        'month' => 6,
        'export' => 'csv',
    ]));

    $response->assertStatus(200);
    $response->assertHeader('Content-Type', 'text/csv; charset=utf-8');
    $response->assertHeader('Content-Disposition', 'attachment; filename=waiswallet_report_june_2026.csv');

    // Retrieve streamed content
    ob_start();
    $response->sendContent();
    $content = ob_get_clean();

    // Verify CSV content
    expect($content)->toContain('Target Transaction');
    expect($content)->toContain('Food');
    expect($content)->toContain('Visa (•••• 1234)');
    expect($content)->toContain('Jollibee');
    expect($content)->toContain('Yummy chicken');
    expect($content)->not->toContain('Other Month Transaction');
});
