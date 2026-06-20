<?php

use App\Models\Goal;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('guests cannot manage goals', function () {
    $response = $this->get(route('goals.index'));
    $response->assertRedirect(route('login'));

    $response = $this->post(route('goals.store'), [
        'name' => 'Emergency Fund',
        'target_amount' => 1000,
    ]);
    $response->assertRedirect(route('login'));
});

test('authenticated user can view goals list', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'name' => 'New Car',
        'target_amount' => 5000000, // $50,000.00
        'current_amount' => 1000000, // $10,000.00
        'target_date' => Carbon::now()->addYear(),
    ]);

    $response = $this->actingAs($user)
        ->get(route('goals.index'));

    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page
        ->component('Goals/Index')
        ->has('goals', 1)
        ->where('goals.0.name', 'New Car')
        ->where('goals.0.percentage', 20)
    );
});

test('authenticated user can store a savings goal', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)
        ->post(route('goals.store'), [
            'name' => 'Travel Fund',
            'target_amount' => 2500, // $2,500.00 -> will be prepareForValidation'ed to 250000 cents
            'current_amount' => 500,  // $500.00 -> 50000 cents
            'target_date' => Carbon::now()->addMonths(6)->format('Y-m-d'),
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('goals', [
        'user_id' => $user->id,
        'name' => 'Travel Fund',
        'target_amount' => 250000,
        'current_amount' => 50000,
    ]);
});

test('authenticated user can update a savings goal', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'name' => 'Home Renovation',
        'target_amount' => 1000000, // $10,000.00
        'current_amount' => 200000,
        'target_date' => Carbon::now()->addMonths(6),
    ]);

    $response = $this->actingAs($user)
        ->put(route('goals.update', $goal), [
            'name' => 'Home Renovation v2',
            'target_amount' => 15000, // $15,000.00 -> prepareForValidation to 1500000 cents
            'target_date' => Carbon::now()->addYear()->format('Y-m-d'),
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'name' => 'Home Renovation v2',
        'target_amount' => 1500000,
    ]);
});

test('authenticated user can add funds to a savings goal', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'name' => 'Laptop Fund',
        'target_amount' => 200000, // $2,000.00
        'current_amount' => 50000, // $500.00
    ]);

    $response = $this->actingAs($user)
        ->post(route('goals.funds', $goal), [
            'amount' => 150, // $150.00 -> prepareForValidation to 15000 cents
            'operation' => 'add',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'current_amount' => 65000, // 50000 + 15000
    ]);
});

test('authenticated user can withdraw funds from a savings goal', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'name' => 'Laptop Fund',
        'target_amount' => 200000, // $2,000.00
        'current_amount' => 50000, // $500.00
    ]);

    $response = $this->actingAs($user)
        ->post(route('goals.funds', $goal), [
            'amount' => 150, // $150.00 -> 15000 cents
            'operation' => 'withdraw',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'current_amount' => 35000, // 50000 - 15000
    ]);
});

test('funds cannot go below zero when withdrawing', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'name' => 'Laptop Fund',
        'target_amount' => 200000, // $2,000.00
        'current_amount' => 50000, // $500.00
    ]);

    $response = $this->actingAs($user)
        ->post(route('goals.funds', $goal), [
            'amount' => 600, // $600.00 -> 60000 cents (exceeds current_amount)
            'operation' => 'withdraw',
        ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'current_amount' => 0,
    ]);
});

test('user cannot update or add funds to another user\'s goal', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Emergency Fund',
        'target_amount' => 100000,
        'current_amount' => 10000,
    ]);

    $response = $this->actingAs($user1)
        ->put(route('goals.update', $goal), [
            'name' => 'Hacked Goal',
            'target_amount' => 500,
        ]);
    $response->assertStatus(403);

    $response = $this->actingAs($user1)
        ->post(route('goals.funds', $goal), [
            'amount' => 100,
            'operation' => 'add',
        ]);
    $response->assertStatus(403);
});

test('authenticated user can delete a savings goal', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'name' => 'Trip to Japan',
    ]);

    $response = $this->actingAs($user)
        ->delete(route('goals.destroy', $goal));

    $response->assertRedirect();
    $this->assertDatabaseMissing('goals', [
        'id' => $goal->id,
    ]);
});

test('user cannot delete another user\'s savings goal', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Trip to Japan',
    ]);

    $response = $this->actingAs($user1)
        ->delete(route('goals.destroy', $goal));

    $response->assertStatus(403);
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
    ]);
});

test('user can mark their savings goal as complete', function () {
    $user = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user->id,
        'name' => 'Car Fund',
        'target_amount' => 500000,
        'current_amount' => 100000,
    ]);

    $response = $this->actingAs($user)
        ->patch(route('goals.complete', $goal));

    $response->assertRedirect();
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'current_amount' => 500000, // equal to target_amount
    ]);
});

test('user cannot mark another user\'s savings goal as complete', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $goal = Goal::factory()->create([
        'user_id' => $user2->id,
        'name' => 'Car Fund',
        'target_amount' => 500000,
        'current_amount' => 100000,
    ]);

    $response = $this->actingAs($user1)
        ->patch(route('goals.complete', $goal));

    $response->assertStatus(403);
    $this->assertDatabaseHas('goals', [
        'id' => $goal->id,
        'current_amount' => 100000,
    ]);
});
