<?php

use App\Models\User;
use App\Models\Category;
use App\Models\Card;
use App\Models\RecurringTransaction;
use App\Models\Transaction;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guest cannot view recurring transactions', function () {
    $response = $this->get(route('transactions.recurring.index'));
    $response->assertRedirect(route('login'));
});

test('guest cannot store recurring transaction', function () {
    $response = $this->post(route('transactions.recurring.store'), [
        'title' => 'Monthly Rent',
        'amount' => 1200.00,
        'type' => 'expense',
        'frequency' => 'monthly',
        'next_run_date' => '2026-06-25',
    ]);
    $response->assertRedirect(route('login'));
});

test('authenticated user can view recurring transactions', function () {
    $user = User::factory()->create();
    $recurring = RecurringTransaction::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->get(route('transactions.recurring.index'));

    $response->assertStatus(200);
    $response->assertSee($recurring->title);
});

test('authenticated user can create recurring transaction', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $card = Card::factory()->create(['user_id' => $user->id]);

    $response = $this->actingAs($user)
        ->post(route('transactions.recurring.store'), [
            'title' => 'Subscription',
            'amount' => 14.99,
            'type' => 'expense',
            'frequency' => 'monthly',
            'next_run_date' => '2026-06-25',
            'category_id' => $category->id,
            'card_id' => $card->id,
            'recipient_name' => 'Netflix',
            'notes' => 'Monthly billing',
            'is_active' => true,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('recurring_transactions', [
        'user_id' => $user->id,
        'title' => 'Subscription',
        'amount' => 1499, // converted to cents
        'type' => 'expense',
        'frequency' => 'monthly',
        'next_run_date' => '2026-06-25',
        'category_id' => $category->id,
        'card_id' => $card->id,
        'recipient_name' => 'Netflix',
        'notes' => 'Monthly billing',
        'is_active' => true,
    ]);
});

test('user can update their recurring transaction', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);
    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'title' => 'Old Title',
        'amount' => 5000,
        'category_id' => $category->id,
        'is_active' => true,
    ]);

    $response = $this->actingAs($user)
        ->put(route('transactions.recurring.update', $recurring), [
            'title' => 'New Title',
            'amount' => 75.50,
            'type' => 'expense',
            'frequency' => 'weekly',
            'next_run_date' => '2026-07-01',
            'category_id' => $category->id,
            'is_active' => false,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('recurring_transactions', [
        'id' => $recurring->id,
        'title' => 'New Title',
        'amount' => 7550,
        'frequency' => 'weekly',
        'next_run_date' => '2026-07-01',
        'is_active' => false,
    ]);
});

test('user cannot update another user\'s recurring transaction', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $categoryOfUser2 = Category::factory()->create(['user_id' => $user2->id, 'type' => 'expense']);
    $categoryOfUser1 = Category::factory()->create(['user_id' => $user1->id, 'type' => 'expense']);

    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user2->id,
        'title' => 'User 2 Recurring',
        'amount' => 5000,
        'category_id' => $categoryOfUser2->id,
    ]);

    $response = $this->actingAs($user1)
        ->put(route('transactions.recurring.update', $recurring), [
            'title' => 'Hacked Title',
            'amount' => 10.00,
            'type' => 'expense',
            'frequency' => 'weekly',
            'next_run_date' => '2026-07-01',
            'category_id' => $categoryOfUser1->id,
        ]);

    $response->assertStatus(403);
    $this->assertDatabaseHas('recurring_transactions', [
        'id' => $recurring->id,
        'title' => 'User 2 Recurring',
    ]);
});

test('user can delete their recurring transaction', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);
    $recurring = RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
    ]);

    $response = $this->actingAs($user)
        ->delete(route('transactions.recurring.destroy', $recurring));

    $response->assertRedirect();
    $this->assertSoftDeleted('recurring_transactions', [
        'id' => $recurring->id,
    ]);
});

test('user cannot delete another user\'s recurring transaction', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $recurring = RecurringTransaction::factory()->create(['user_id' => $user2->id]);

    $response = $this->actingAs($user1)
        ->delete(route('transactions.recurring.destroy', $recurring));

    $response->assertStatus(403);
    $this->assertDatabaseHas('recurring_transactions', [
        'id' => $recurring->id,
        'deleted_at' => null,
    ]);
});

test('artisan command processes due recurring transactions and rolls dates forward', function () {
    Carbon::setTestNow('2026-06-20 12:00:00');

    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense']);

    // Create 5 recurring transactions with different frequencies due today or in the past
    $frequencies = [
        'daily' => '2026-06-21',
        'weekly' => '2026-06-27',
        'bi-weekly' => '2026-07-04',
        'monthly' => '2026-07-20',
        'yearly' => '2027-06-20',
    ];

    $templates = [];
    foreach ($frequencies as $freq => $expectedNextDate) {
        $templates[$freq] = RecurringTransaction::factory()->create([
            'user_id' => $user->id,
            'title' => "Recurring {$freq}",
            'type' => 'expense',
            'amount' => 5000,
            'category_id' => $category->id,
            'frequency' => $freq,
            'next_run_date' => '2026-06-20',
            'is_active' => true,
        ]);
    }

    // Call the Artisan command
    $this->artisan('recurring-transactions:process')
        ->expectsOutput('Found 5 active recurring transaction templates due for processing.')
        ->assertExitCode(0);

    // Verify transactions were created
    foreach ($frequencies as $freq => $expectedNextDate) {
        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'title' => "Recurring {$freq}",
            'amount' => 5000,
            'type' => 'expense',
            'category_id' => $category->id,
            'status' => 'completed',
        ]);

        $this->assertDatabaseHas('recurring_transactions', [
            'id' => $templates[$freq]->id,
            'next_run_date' => $expectedNextDate,
            'last_run_date' => '2026-06-20',
        ]);
    }

    Carbon::setTestNow(); // Reset test time
});

test('artisan command does not process inactive or future recurring transactions', function () {
    Carbon::setTestNow('2026-06-20 12:00:00');

    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    // Inactive template due today
    $inactive = RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'title' => 'Inactive Template',
        'category_id' => $category->id,
        'next_run_date' => '2026-06-20',
        'is_active' => false,
    ]);

    // Active template due in the future
    $future = RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'title' => 'Future Template',
        'category_id' => $category->id,
        'next_run_date' => '2026-06-21',
        'is_active' => true,
    ]);

    $this->artisan('recurring-transactions:process')
        ->expectsOutput('Found 0 active recurring transaction templates due for processing.')
        ->assertExitCode(0);

    $this->assertDatabaseMissing('transactions', [
        'title' => 'Inactive Template',
    ]);
    $this->assertDatabaseMissing('transactions', [
        'title' => 'Future Template',
    ]);

    Carbon::setTestNow();
});

test('artisan command catch-up algorithm works for very overdue templates', function () {
    Carbon::setTestNow('2026-06-20 12:00:00');

    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id]);

    // A weekly template that last ran (or was set to run) on 2026-06-01 (almost 3 weeks overdue)
    // Running it today (2026-06-20) should advance it until next_run_date > 2026-06-20.
    // 2026-06-01 + 1 week = 2026-06-08 (still <= 20)
    // 2026-06-08 + 1 week = 2026-06-15 (still <= 20)
    // 2026-06-15 + 1 week = 2026-06-22 (strictly > 20) -> final next_run_date
    $template = RecurringTransaction::factory()->create([
        'user_id' => $user->id,
        'title' => 'Overdue Weekly',
        'category_id' => $category->id,
        'frequency' => 'weekly',
        'next_run_date' => '2026-06-01',
        'is_active' => true,
    ]);

    $this->artisan('recurring-transactions:process')
        ->expectsOutput('Found 1 active recurring transaction templates due for processing.')
        ->assertExitCode(0);

    $this->assertDatabaseHas('recurring_transactions', [
        'id' => $template->id,
        'next_run_date' => '2026-06-22',
        'last_run_date' => '2026-06-20',
    ]);

    // Note: It only generates *one* transaction for catch-up (this is standard behavior as designed)
    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'title' => 'Overdue Weekly',
    ]);

    $this->assertEquals(1, Transaction::where('title', 'Overdue Weekly')->count());

    Carbon::setTestNow();
});
