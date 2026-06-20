<?php

use App\Models\Budget;
use App\Models\Card;
use App\Models\Category;
use App\Models\Goal;
use App\Models\Notification;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('authenticated user receives shared notifications list via Inertia', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'title' => 'Emergency Fund Warning',
        'message' => 'Nearing spending limit',
        'type' => 'budget_alert',
        'read_at' => null,
    ]);

    $response = $this->actingAs($user)
        ->get(route('dashboard'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->has('notifications')
        ->where('notifications.unread_count', 1)
        ->where('notifications.latest.0.id', $notification->id)
    );
});

test('user can mark notification as read', function () {
    $user = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user->id,
        'title' => 'Goal reached!',
        'message' => 'You did it',
        'type' => 'goal_milestone',
        'read_at' => null,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('notifications.read', $notification));

    $response->assertRedirect();
    $this->assertNotNull($notification->fresh()->read_at);
});

test('user cannot mark another user\'s notification as read', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $notification = Notification::factory()->create([
        'user_id' => $user2->id,
        'title' => 'Secret message',
        'read_at' => null,
    ]);

    $response = $this->actingAs($user1)
        ->patch(route('notifications.read', $notification));

    $response->assertStatus(403);
    $this->assertNull($notification->fresh()->read_at);
});

test('user can mark all notifications as read', function () {
    $user = User::factory()->create();
    Notification::factory()->count(3)->create([
        'user_id' => $user->id,
        'read_at' => null,
    ]);

    $response = $this->actingAs($user)
        ->post(route('notifications.read-all'));

    $response->assertRedirect();
    $this->assertEquals(0, $user->notifications()->whereNull('read_at')->count());
});

test('budget warning notification is triggered when transaction exceeds 80% of budget', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Dining']);
    $card = Card::factory()->create(['user_id' => $user->id]);

    // Budget limit $100.00 (10000 cents)
    $budget = Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'allocated_amount' => 10000,
        'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
    ]);

    // Create a transaction of $85.00 (exceeds 80% but under 100%)
    $response = $this->actingAs($user)
        ->post(route('transactions.store'), [
            'title' => 'Big Dinner',
            'amount' => 85.00,
            'type' => 'expense',
            'transaction_date' => Carbon::now()->format('Y-m-d'),
            'category_id' => $category->id,
            'card_id' => $card->id,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'title' => 'Budget Limit Nearing',
        'type' => 'budget_alert',
    ]);
});

test('budget exceeded notification is triggered when transaction exceeds 100% of budget', function () {
    $user = User::factory()->create();
    $category = Category::factory()->create(['user_id' => $user->id, 'type' => 'expense', 'name' => 'Dining']);
    $card = Card::factory()->create(['user_id' => $user->id]);

    // Budget limit $100.00
    $budget = Budget::factory()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'allocated_amount' => 10000,
        'month' => Carbon::now()->startOfMonth()->format('Y-m-d'),
    ]);

    // Create transaction of $120.00
    $response = $this->actingAs($user)
        ->post(route('transactions.store'), [
            'title' => 'Huge Dinner',
            'amount' => 120.00,
            'type' => 'expense',
            'transaction_date' => Carbon::now()->format('Y-m-d'),
            'category_id' => $category->id,
            'card_id' => $card->id,
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'title' => 'Budget Exceeded',
        'type' => 'budget_alert',
    ]);
});

test('goal milestone notification is triggered when savings reach 80% or 100%', function () {
    $user = User::factory()->create();
    // Goal target $1,000.00 (100000 cents), current $500.00
    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'name' => 'Emergency Fund',
        'target_amount' => 100000,
        'current_amount' => 50000,
    ]);

    // Add $300.00 (savings reaches $800.00 -> 80% of target)
    $response = $this->actingAs($user)
        ->post(route('goals.funds', $goal), [
            'amount' => 300,
            'operation' => 'add',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'title' => 'Goal Milestone (80%)',
        'type' => 'goal_milestone',
    ]);

    // Add $200.00 more (savings reaches $1,000.00 -> 100%)
    $response = $this->actingAs($user)
        ->post(route('goals.funds', $goal), [
            'amount' => 200,
            'operation' => 'add',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('notifications', [
        'user_id' => $user->id,
        'title' => 'Goal Completed 🎉',
        'type' => 'goal_milestone',
    ]);
});
