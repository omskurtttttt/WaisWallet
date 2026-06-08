<?php

use App\Models\User;
use App\Models\Transaction;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

test('user can filter transactions by status', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);

    $t1 = Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Tx Completed',
        'status' => 'completed',
        'amount' => 10000,
        'transaction_date' => Carbon::now(),
    ]);

    $t2 = Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Tx Pending',
        'status' => 'pending',
        'amount' => 20000,
        'transaction_date' => Carbon::now(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('transactions.index', ['status' => 'completed']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Transactions/Index')
        ->has('transactions.data', 1)
        ->where('transactions.data.0.title', 'Tx Completed')
    );
});

test('user can filter transactions by date range', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Tx Yesterday',
        'transaction_date' => Carbon::yesterday(),
        'status' => 'completed',
        'amount' => 10000,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Tx Today',
        'transaction_date' => Carbon::today(),
        'status' => 'completed',
        'amount' => 20000,
    ]);

    Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Tx Tomorrow',
        'transaction_date' => Carbon::tomorrow(),
        'status' => 'completed',
        'amount' => 30000,
    ]);

    $response = $this->actingAs($user)
        ->get(route('transactions.index', [
            'date_from' => Carbon::today()->format('Y-m-d'),
            'date_to' => Carbon::today()->format('Y-m-d')
        ]));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Transactions/Index')
        ->has('transactions.data', 1)
        ->where('transactions.data.0.title', 'Tx Today')
    );
});

test('user can sort transactions by Date, Amount, and Name', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);

    $t1 = Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Apple',
        'amount' => 15000,
        'transaction_date' => Carbon::now()->subDays(2),
        'status' => 'completed',
    ]);

    $t2 = Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Cherry',
        'amount' => 5000,
        'transaction_date' => Carbon::now()->subDay(),
        'status' => 'completed',
    ]);

    $t3 = Transaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Banana',
        'amount' => 25000,
        'transaction_date' => Carbon::now(),
        'status' => 'completed',
    ]);

    // Sort by name asc
    $response = $this->actingAs($user)
        ->get(route('transactions.index', ['sort_by' => 'name', 'sort_order' => 'asc']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Transactions/Index')
        ->has('transactions.data', 3)
        ->where('transactions.data.0.title', 'Apple')
        ->where('transactions.data.1.title', 'Banana')
        ->where('transactions.data.2.title', 'Cherry')
    );

    // Sort by amount desc
    $response = $this->actingAs($user)
        ->get(route('transactions.index', ['sort_by' => 'amount', 'sort_order' => 'desc']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Transactions/Index')
        ->has('transactions.data', 3)
        ->where('transactions.data.0.title', 'Banana')
        ->where('transactions.data.1.title', 'Apple')
        ->where('transactions.data.2.title', 'Cherry')
    );

    // Sort by date asc
    $response = $this->actingAs($user)
        ->get(route('transactions.index', ['sort_by' => 'date', 'sort_order' => 'asc']));

    $response->assertOk();
    $response->assertInertia(fn (Assert $page) => $page
        ->component('Transactions/Index')
        ->has('transactions.data', 3)
        ->where('transactions.data.0.title', 'Apple')
        ->where('transactions.data.1.title', 'Cherry')
        ->where('transactions.data.2.title', 'Banana')
    );
});
