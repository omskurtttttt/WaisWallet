<?php

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
