<?php

use App\Models\Budget;
use App\Models\Category;
use App\Models\User;
use App\Policies\BudgetPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot manage budgets', function () {
    $category = Category::factory()->create();

    $response = $this->post(route('budgets.store'), [
        'month' => Carbon::now()->format('Y-m'),
        'budgets' => [
            [
                'category_id' => $category->id,
                'amount' => 150.00,
            ],
        ],
    ]);

    $response->assertRedirect(route('login'));
});

test('authenticated user can store and update category budgets', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type' => 'expense',
    ]);

    $month = Carbon::now()->format('Y-m');

    // Store budget
    $response = $this->actingAs($user)
        ->post(route('budgets.store'), [
            'month' => $month,
            'budgets' => [
                [
                    'category_id' => $category->id,
                    'amount' => 150.00, // $150.00
                ],
            ],
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'allocated_amount' => 15000,
        'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
    ]);

    // Update budget
    $response = $this->actingAs($user)
        ->post(route('budgets.store'), [
            'month' => $month,
            'budgets' => [
                [
                    'category_id' => $category->id,
                    'amount' => 200.50, // $200.50
                ],
            ],
        ]);

    $response->assertRedirect();

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'allocated_amount' => 20050,
        'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
    ]);
});

test('setting budget to 0 or null deletes the budget record', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create([
        'user_id' => $user->id,
        'type' => 'expense',
    ]);

    $month = Carbon::now()->format('Y-m');
    $monthStart = Carbon::now()->startOfMonth()->format('Y-m-d');

    // Create a budget
    Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'allocated_amount' => 10000,
        'month' => $monthStart,
    ]);

    $this->assertDatabaseHas('budgets', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'month' => $monthStart,
    ]);

    // Update to 0
    $response = $this->actingAs($user)
        ->post(route('budgets.store'), [
            'month' => $month,
            'budgets' => [
                [
                    'category_id' => $category->id,
                    'amount' => 0,
                ],
            ],
        ]);

    $response->assertRedirect();

    $this->assertDatabaseMissing('budgets', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'month' => $monthStart,
    ]);
});

test('user cannot set budgets for categories they do not own', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $categoryOfUser2 = Category::factory()->create([
        'user_id' => $user2->id,
        'type' => 'expense',
    ]);

    $month = Carbon::now()->format('Y-m');

    $response = $this->actingAs($user1)
        ->post(route('budgets.store'), [
            'month' => $month,
            'budgets' => [
                [
                    'category_id' => $categoryOfUser2->id,
                    'amount' => 100,
                ],
            ],
        ]);

    $response->assertSessionHasErrors(['budgets.0.category_id']);
});

test('user cannot update or delete another user\'s budget via policy', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $categoryOfUser2 = Category::factory()->create(['user_id' => $user2->id, 'type' => 'expense']);
    $budgetOfUser2 = Budget::factory()->create([
        'user_id' => $user2->id,
        'category_id' => $categoryOfUser2->id,
        'allocated_amount' => 10000,
        'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
    ]);

    $policy = new BudgetPolicy;
    expect($policy->update($user1, $budgetOfUser2))->toBeFalse();
    expect($policy->delete($user1, $budgetOfUser2))->toBeFalse();
});
