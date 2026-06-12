<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Transaction;
use App\Models\Card;
use App\Models\Budget;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

uses(RefreshDatabase::class);

test('guests cannot view the dashboard', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated user can view the dashboard with correct properties', function () {
    $user = User::factory()->create([
        'monthly_spending_limit' => 100000, // $1,000.00
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('metrics')
        ->has('incomeChartData')
        ->has('budgetChartData')
        ->has('spendingLimitsData')
        ->has('recentTransactionsData')
        ->has('myCardsData')
        ->has('expenseCategories')
        ->has('existingBudgets')
        ->where('currency', $user->currency)
    );
});

test('dashboard displays correct balance, income, expense, savings metrics and trends', function () {
    $user = User::factory()->create([
        'monthly_spending_limit' => 200000,
    ]);

    // Setup dates
    $now = Carbon::now();
    $thisMonth = $now->format('Y-m-d H:i:s');
    $lastMonth = $now->copy()->subMonth()->format('Y-m-d H:i:s');

    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $card = Card::factory()->create(['user_id' => $user->id]);

    // Create transactions for last month:
    // Income: $1000.00 (100000 cents)
    // Expense: $400.00 (40000 cents)
    // Net last month balance: $600.00 (60000 cents)
    Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => 'income',
        'amount' => 100000,
        'transaction_date' => $lastMonth,
        'category_id' => $category->id,
        'card_id' => $card->id,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => 'expense',
        'amount' => 40000,
        'transaction_date' => $lastMonth,
        'category_id' => $category->id,
        'card_id' => $card->id,
    ]);

    // Create transactions for this month:
    // Income: $1500.00 (150000 cents)
    // Expense: $600.00 (60000 cents)
    // Net this month balance: $900.00 (90000 cents)
    // Total Balance = ($1000 + $1500) - ($400 + $600) = $2500 - $1000 = $1500.00 (150000 cents)
    Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => 'income',
        'amount' => 150000,
        'transaction_date' => $thisMonth,
        'category_id' => $category->id,
        'card_id' => $card->id,
    ]);
    Transaction::factory()->create([
        'user_id' => $user->id,
        'type' => 'expense',
        'amount' => 60000,
        'transaction_date' => $thisMonth,
        'category_id' => $category->id,
        'card_id' => $card->id,
    ]);

    // Budget allocated
    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'allocated_amount' => 120000, // $1,200.00
        'month' => $now->copy()->startOfMonth()->format('Y-m-d'),
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('metrics.totalBalance', 150000)
        ->where('metrics.balanceTrend', 150) // ($1500 - $600) / $600 * 100
        ->where('metrics.monthlyIncome', 150000)
        ->where('metrics.incomeTrend', 50) // ($1500 - $1000) / $1000 * 100
        ->where('metrics.monthlyExpense', 60000)
        ->where('metrics.expenseTrend', 50) // ($600 - $400) / $400 * 100
        ->where('metrics.monthlySavings', 90000)
        ->where('metrics.savingsTrend', 50) // ($900 - $600) / $600 * 100
        ->where('metrics.totalBudgetAllocated', 120000)
        ->where('metrics.budgetUsedPercentage', 50) // $600 / $1200 * 100
    );
});
